<?php
// =====================================================
// includes/ia.php
// Integración con Google Gemini para el análisis de documentos.
//
// Flujo que implementa esta pieza:
//   texto extraído -> [PROMPT + LLAMADA A GEMINI] -> JSON con
//   categoría, resumen y datos clave.
//
// Requiere config/gemini.php ya cargado (GEMINI_API_KEY, GEMINI_API_URL).
// =====================================================

/**
 * Envía el texto de un documento a Gemini y pide que devuelva
 * clasificación + resumen + datos clave en un JSON estricto.
 *
 * @return array{
 *   exito: bool,
 *   categoria: string,
 *   resumen: string,
 *   datos_clave: array,
 *   error: string|null
 * }
 */
function analizarDocumentoConIA(string $textoExtraido, string $nombreArchivo): array
{
    if (trim($textoExtraido) === '') {
        return respuestaErrorIA('El texto extraído está vacío, no hay nada que analizar.');
    }

    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'PEGA_AQUI_TU_API_KEY') {
        return respuestaErrorIA('No se ha configurado la API key de Gemini en config/gemini.php.');
    }

    $prompt = construirPromptAnalisis($textoExtraido, $nombreArchivo);

    $resultado = llamarGemini($prompt);
    if (!$resultado['exito']) {
        return respuestaErrorIA($resultado['error']);
    }

    $json = extraerJSONDeRespuesta($resultado['texto']);
    if ($json === null) {
        return respuestaErrorIA('La IA no devolvió un JSON válido. Respuesta cruda: ' . mb_substr($resultado['texto'], 0, 300));
    }

    $categoriasValidas = ['contrato', 'acta', 'concepto_juridico', 'sin_clasificar'];
    $categoria = in_array($json['categoria'] ?? '', $categoriasValidas, true)
        ? $json['categoria']
        : 'sin_clasificar';

    return [
        'exito' => true,
        'categoria' => $categoria,
        'resumen' => trim($json['resumen'] ?? ''),
        'datos_clave' => is_array($json['datos_clave'] ?? null) ? $json['datos_clave'] : [],
        'error' => null,
    ];
}

/**
 * Arma el prompt que le da a Gemini el "contrato" de qué devolver.
 * Pedir explícitamente JSON estricto es la forma más confiable de
 * poder parsear la respuesta después con json_decode().
 */
function construirPromptAnalisis(string $texto, string $nombreArchivo): string
{
    return <<<PROMPT
Eres un asistente jurídico que analiza documentos para un bufete de abogados.

Analiza el siguiente documento (nombre de archivo: "{$nombreArchivo}") y responde
ÚNICAMENTE con un objeto JSON válido, sin texto adicional, sin markdown, sin
```json, con exactamente esta estructura:

{
  "categoria": "contrato" | "acta" | "concepto_juridico" | "sin_clasificar",
  "resumen": "resumen de máximo 5 líneas del contenido del documento",
  "datos_clave": {
    "partes_involucradas": ["nombre1", "nombre2"],
    "fechas_relevantes": ["fecha y su significado"],
    "montos": ["monto y su concepto, si aplica"],
    "vigencia_u_obligaciones": "texto breve, si aplica"
  }
}

Reglas:
- Si el documento no encaja claramente en contrato, acta o concepto_juridico,
  usa "sin_clasificar".
- Si algún dato clave no aparece en el documento, deja el arreglo/campo vacío,
  no inventes información.
- El resumen debe estar en español y ser fiel al contenido, sin opiniones.

Documento a analizar:
---
{$texto}
---
PROMPT;
}

/**
 * Llamada HTTP cruda a la API de Gemini (generateContent) usando cURL.
 */
function llamarGemini(string $prompt): array
{
    $body = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt],
                ],
            ],
        ],
        'generationConfig' => [
            'temperature' => 0.2,
            'maxOutputTokens' => 2048,
        ],
    ];

    $ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 60,
    ]);

    $respuestaCruda = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuestaCruda === false) {
        return ['exito' => false, 'texto' => '', 'error' => "Error de conexión con Gemini: {$curlError}"];
    }

    $data = json_decode($respuestaCruda, true);

    if ($httpCode !== 200) {
        $mensajeError = $data['error']['message'] ?? "HTTP {$httpCode}";
        return ['exito' => false, 'texto' => '', 'error' => "Gemini respondió con error: {$mensajeError}"];
    }

    $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($texto === null) {
        return ['exito' => false, 'texto' => '', 'error' => 'Respuesta de Gemini sin contenido de texto.'];
    }

    return ['exito' => true, 'texto' => $texto, 'error' => null];
}

/**
 * Gemini a veces envuelve el JSON en ```json ... ``` a pesar de pedirle que
 * no lo haga. Esta función limpia eso y decodifica de forma tolerante.
 */
function extraerJSONDeRespuesta(string $texto): ?array
{
    $texto = trim($texto);
    $texto = preg_replace('/^```json\s*/i', '', $texto);
    $texto = preg_replace('/^```\s*/', '', $texto);
    $texto = preg_replace('/```\s*$/', '', $texto);
    $texto = trim($texto);

    $json = json_decode($texto, true);
    return is_array($json) ? $json : null;
}

function respuestaErrorIA(string $mensaje): array
{
    return [
        'exito' => false,
        'categoria' => 'sin_clasificar',
        'resumen' => '',
        'datos_clave' => [],
        'error' => $mensaje,
    ];
}

// =====================================================
// Preguntas en lenguaje natural sobre los documentos (RAG básico)
//
// Flujo: pregunta del usuario -> [se buscan documentos relevantes en la BD,
// eso lo hace consulta_ia.php] -> aquí se arma el prompt con esos documentos
// como CONTEXTO -> Gemini responde SOLO con base en ese contexto.
// =====================================================

/**
 * @param string $pregunta       Pregunta del usuario en lenguaje natural.
 * @param array  $documentosContexto  Lista de documentos relevantes, cada uno
 *        como ['nombre' => string, 'resumen' => string, 'texto' => string].
 *
 * @return array{exito: bool, respuesta: string, error: string|null}
 */
function responderPreguntaConIA(string $pregunta, array $documentosContexto): array
{
    if (empty($documentosContexto)) {
        return [
            'exito' => true,
            'respuesta' => 'No encontré documentos procesados que se relacionen con tu pregunta. '
                . 'Intenta con otros términos o verifica que los documentos ya hayan sido procesados con IA.',
            'error' => null,
        ];
    }

    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'PEGA_AQUI_TU_API_KEY') {
        return ['exito' => false, 'respuesta' => '', 'error' => 'No se ha configurado la API key de Gemini.'];
    }

    $prompt = construirPromptPregunta($pregunta, $documentosContexto);

    $resultado = llamarGemini($prompt);
    if (!$resultado['exito']) {
        return ['exito' => false, 'respuesta' => '', 'error' => $resultado['error']];
    }

    return ['exito' => true, 'respuesta' => trim($resultado['texto']), 'error' => null];
}

function construirPromptPregunta(string $pregunta, array $documentosContexto): string
{
    $bloquesContexto = '';
    foreach ($documentosContexto as $i => $doc) {
        $n = $i + 1;
        $bloquesContexto .= "\n--- Documento {$n}: \"{$doc['nombre']}\" ---\n";
        if (!empty($doc['resumen'])) {
            $bloquesContexto .= "Resumen: {$doc['resumen']}\n";
        }
        $bloquesContexto .= "Contenido: {$doc['texto']}\n";
    }

    return <<<PROMPT
Eres un asistente jurídico que responde preguntas basándote ÚNICAMENTE en los
documentos que se te entregan a continuación como contexto. No inventes
información que no esté en los documentos.

Si la respuesta no se encuentra en los documentos entregados, dilo claramente
en vez de inventar una respuesta.

Cuando uses información de un documento, menciona su nombre entre paréntesis,
por ejemplo: (según "contrato_arrendamiento.pdf").

Responde en español, de forma clara y concisa.

CONTEXTO (documentos disponibles):
{$bloquesContexto}

PREGUNTA DEL USUARIO:
{$pregunta}
PROMPT;
}
