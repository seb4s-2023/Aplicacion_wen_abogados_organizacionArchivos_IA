<?php
// =====================================================
// includes/extractor.php
// Extracción de texto plano desde documentos PDF, DOCX y TXT.
// Este es el PRIMER eslabón del flujo de IA:
//   archivo -> [EXTRACCIÓN DE TEXTO] -> IA (Gemini) -> análisis -> almacenamiento
//
// Requiere:
//   - Composer + librería smalot/pdfparser para leer PDFs.
//     Instalación (desde la raíz del proyecto, donde vas a crear composer.json):
//         composer require smalot/pdfparser
//   - Extensión ZipArchive de PHP habilitada (viene activada por defecto en XAMPP)
//     para leer DOCX.
// =====================================================

// Autoload de Composer (para smalot/pdfparser). Si tu proyecto aún no tiene
// vendor/, este require fallará silenciosamente más abajo y el PDF dará error
// controlado en vez de tumbar la aplicación.
$rutaAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($rutaAutoload)) {
    require_once $rutaAutoload;
}

/**
 * Punto de entrada único: recibe la ruta física del archivo y su formato
 * ('pdf', 'docx', 'txt') y devuelve un arreglo con el resultado.
 *
 * @return array{exito: bool, texto: string, error: string|null}
 */
function extraerTexto(string $rutaCompleta, string $formato): array
{
    if (!is_file($rutaCompleta)) {
        return ['exito' => false, 'texto' => '', 'error' => 'El archivo no existe en el servidor.'];
    }

    try {
        switch ($formato) {
            case 'txt':
                $texto = extraerTextoTXT($rutaCompleta);
                break;
            case 'docx':
                $texto = extraerTextoDOCX($rutaCompleta);
                break;
            case 'pdf':
                $texto = extraerTextoPDF($rutaCompleta);
                break;
            default:
                return ['exito' => false, 'texto' => '', 'error' => "Formato no soportado: {$formato}"];
        }
    } catch (Throwable $e) {
        return ['exito' => false, 'texto' => '', 'error' => $e->getMessage()];
    }

    $texto = limpiarTexto($texto);

    if ($texto === '') {
        return ['exito' => false, 'texto' => '', 'error' => 'No se pudo extraer texto legible del archivo (¿está escaneado como imagen?).'];
    }

    return ['exito' => true, 'texto' => $texto, 'error' => null];
}

/**
 * TXT: lectura directa. Se intenta detectar la codificación y convertir a UTF-8
 * porque muchos .txt vienen en Windows-1252/Latin1.
 */
function extraerTextoTXT(string $ruta): string
{
    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        throw new RuntimeException('No se pudo leer el archivo TXT.');
    }

    $codificacion = mb_detect_encoding($contenido, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
    if ($codificacion !== 'UTF-8' && $codificacion !== false) {
        $contenido = mb_convert_encoding($contenido, 'UTF-8', $codificacion);
    }

    return $contenido;
}

/**
 * DOCX: un .docx es un ZIP que contiene word/document.xml con el texto
 * envuelto en tags <w:t>. Lo abrimos con ZipArchive y quitamos las etiquetas.
 */
function extraerTextoDOCX(string $ruta): string
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('La extensión ZipArchive de PHP no está habilitada.');
    }

    $zip = new ZipArchive();
    if ($zip->open($ruta) !== true) {
        throw new RuntimeException('No se pudo abrir el archivo DOCX (¿está corrupto?).');
    }

    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($xml === false) {
        throw new RuntimeException('El DOCX no contiene word/document.xml (formato inesperado).');
    }

    // Los saltos de párrafo en el XML son </w:p>; los convertimos en saltos
    // de línea ANTES de quitar las etiquetas, para no pegar todo en un bloque.
    $xml = str_replace('</w:p>', "\n", $xml);
    $xml = str_replace('</w:tab>', "\t", $xml);

    $texto = strip_tags($xml);
    $texto = html_entity_decode($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');

    return $texto;
}

/**
 * PDF: usa smalot/pdfparser (pura en PHP, no requiere binarios externos
 * como pdftotext). Solo funciona con PDFs de texto real, no con PDFs
 * escaneados como imagen (para eso se necesitaría OCR, fuera del alcance
 * mínimo del proyecto).
 */
function extraerTextoPDF(string $ruta): string
{
    if (!class_exists('Smalot\PdfParser\Parser')) {
        throw new RuntimeException(
            'Falta la librería smalot/pdfparser. Instálala con: composer require smalot/pdfparser'
        );
    }

    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($ruta);

    return $pdf->getText();
}

/**
 * Limpieza básica: normaliza espacios/saltos de línea repetidos y recorta
 * el resultado para que no crezca sin control en la base de datos.
 */
function limpiarTexto(string $texto): string
{
    $texto = preg_replace('/[ \t]+/', ' ', $texto);
    $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
    $texto = trim($texto);

    // Límite defensivo: evita que un documento gigante sature la petición
    // a la API de IA más adelante (Gemini tiene límite de tokens por request).
    $LIMITE_CARACTERES = 100000;
    if (mb_strlen($texto) > $LIMITE_CARACTERES) {
        $texto = mb_substr($texto, 0, $LIMITE_CARACTERES) . "\n\n[...texto truncado por longitud...]";
    }

    return $texto;
}
