<?php
// =====================================================
// includes/procesador.php
// Orquestador del flujo de IA sobre un documento:
//
//   documento en BD (estado 'pendiente')
//        -> extraerTexto()            (includes/extractor.php)
//        -> analizarDocumentoConIA()  (includes/ia.php)
//        -> guardar en analisis_documento
//        -> actualizar documentos.categoria y estado_procesamiento
//        -> si algo falla: log en logs_procesamiento + estado 'error'
//
// Este archivo NO imprime HTML: solo expone la función procesarDocumento()
// para que la usen documentos.php (botón "Procesar") y, si quieres,
// un futuro procesamiento automático al subir el archivo.
// =====================================================

require_once __DIR__ . '/extractor.php';
require_once __DIR__ . '/ia.php';

/**
 * Procesa un documento completo: extracción + IA + guardado.
 * Requiere que $pdo (la conexión PDO) ya esté disponible al incluir este
 * archivo (se incluye siempre junto con config/database.php).
 *
 * @return array{exito: bool, mensaje: string}
 */
function procesarDocumento(PDO $pdo, int $idDocumento): array
{
    // 1. Traer los datos del documento
    $stmt = $pdo->prepare(
        'SELECT id_documento, ruta_archivo, formato, nombre_original
         FROM documentos WHERE id_documento = ?'
    );
    $stmt->execute([$idDocumento]);
    $doc = $stmt->fetch();

    if (!$doc) {
        return ['exito' => false, 'mensaje' => 'El documento no existe.'];
    }

    // 2. Extraer el texto del archivo físico
    $extraccion = extraerTexto($doc['ruta_archivo'], $doc['formato']);

    if (!$extraccion['exito']) {
        registrarErrorProcesamiento($pdo, $idDocumento, 'extraccion', $extraccion['error']);
        marcarEstadoDocumento($pdo, $idDocumento, 'error');
        return ['exito' => false, 'mensaje' => 'Error en la extracción: ' . $extraccion['error']];
    }

    // 3. Analizar el texto con Gemini (clasificación + resumen + datos clave)
    $analisis = analizarDocumentoConIA($extraccion['texto'], $doc['nombre_original']);

    if (!$analisis['exito']) {
        registrarErrorProcesamiento($pdo, $idDocumento, 'clasificacion_ia', $analisis['error']);
        marcarEstadoDocumento($pdo, $idDocumento, 'error');
        return ['exito' => false, 'mensaje' => 'Error en el análisis de IA: ' . $analisis['error']];
    }

    // 4. Guardar (o actualizar, si ya existía un análisis previo) en analisis_documento
    try {
        guardarAnalisis($pdo, $idDocumento, $extraccion['texto'], $analisis);
    } catch (Throwable $e) {
        registrarErrorProcesamiento($pdo, $idDocumento, 'almacenamiento', $e->getMessage());
        marcarEstadoDocumento($pdo, $idDocumento, 'error');
        return ['exito' => false, 'mensaje' => 'Error guardando el análisis: ' . $e->getMessage()];
    }

    // 5. Actualizar la categoría y marcar como procesado
    $stmt = $pdo->prepare('UPDATE documentos SET categoria = ?, estado_procesamiento = ? WHERE id_documento = ?');
    $stmt->execute([$analisis['categoria'], 'procesado', $idDocumento]);

    return ['exito' => true, 'mensaje' => 'Documento procesado correctamente.'];
}

/**
 * Inserta el análisis, o lo reemplaza si el documento ya se había
 * procesado antes (por ejemplo, al usar un botón "Reprocesar").
 */
function guardarAnalisis(PDO $pdo, int $idDocumento, string $textoExtraido, array $analisis): void
{
    $stmtExiste = $pdo->prepare('SELECT id_analisis FROM analisis_documento WHERE id_documento = ?');
    $stmtExiste->execute([$idDocumento]);
    $existente = $stmtExiste->fetch();

    $datosClaveJson = json_encode($analisis['datos_clave'], JSON_UNESCAPED_UNICODE);

    if ($existente) {
        $stmt = $pdo->prepare(
            'UPDATE analisis_documento
             SET resumen = ?, texto_extraido = ?, datos_clave = ?, fecha_analisis = NOW()
             WHERE id_documento = ?'
        );
        $stmt->execute([$analisis['resumen'], $textoExtraido, $datosClaveJson, $idDocumento]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO analisis_documento (id_documento, resumen, texto_extraido, datos_clave)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$idDocumento, $analisis['resumen'], $textoExtraido, $datosClaveJson]);
    }
}

function marcarEstadoDocumento(PDO $pdo, int $idDocumento, string $estado): void
{
    $stmt = $pdo->prepare('UPDATE documentos SET estado_procesamiento = ? WHERE id_documento = ?');
    $stmt->execute([$estado, $idDocumento]);
}

/**
 * Registra el error en logs_procesamiento, tal como pide el alcance
 * funcional mínimo del proyecto ("Registro de errores y estados de
 * procesamiento").
 */
function registrarErrorProcesamiento(PDO $pdo, int $idDocumento, string $tipoError, ?string $mensaje): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO logs_procesamiento (id_documento, tipo_error, mensaje)
         VALUES (?, ?, ?)'
    );
    $stmt->execute([$idDocumento, $tipoError, $mensaje ?? 'Error desconocido']);
}
