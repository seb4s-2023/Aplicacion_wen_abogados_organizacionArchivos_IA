<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'config/gemini.php';
require_once 'includes/procesador.php';

// Esta página exige sesión activa
requireLogin();

$esAdmin = esAdmin();
$idUsuario = $_SESSION['id_usuario'];

// =====================================================
// Configuración de subida de archivos
// =====================================================
define('CARPETA_UPLOADS', __DIR__ . '/uploads/');
define('TAMANO_MAXIMO', 20 * 1024 * 1024); // 20 MB
$formatosPermitidos = [
    'pdf'  => 'pdf',
    'docx' => 'docx',
    'txt'  => 'txt',
];

if (!is_dir(CARPETA_UPLOADS)) {
    mkdir(CARPETA_UPLOADS, 0775, true);
}

$mensaje = '';
$mensajeTipo = 'success'; // 'success' | 'danger'

// =====================================================
// Descargar documento (GET, streaming directo)
// =====================================================
if (isset($_GET['descargar'])) {
    $idDoc = (int) $_GET['descargar'];

    $stmt = $pdo->prepare('SELECT nombre_original, ruta_archivo FROM documentos WHERE id_documento = ?');
    $stmt->execute([$idDoc]);
    $doc = $stmt->fetch();

    $rutaCompleta = $doc ? __DIR__ . '/' . $doc['ruta_archivo'] : null;

    if (!$doc || !is_file($rutaCompleta)) {
        header('Location: documentos.php?error=archivo_no_encontrado');
        exit;
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($doc['nombre_original']) . '"');
    header('Content-Length: ' . filesize($rutaCompleta));
    header('X-Content-Type-Options: nosniff');
    readfile($rutaCompleta);
    exit;
}

// =====================================================
// Subir documento (POST)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'subir') {
    $idRepositorio = (int) ($_POST['id_repositorio'] ?? 0);

    $stmt = $pdo->prepare('SELECT id_repositorio FROM repositorios WHERE id_repositorio = ?');
    $stmt->execute([$idRepositorio]);
    $repoValido = $stmt->fetch();

    if (!$repoValido) {
        $mensaje = 'Debes seleccionar un repositorio válido.';
        $mensajeTipo = 'danger';
    } elseif (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) {
        $mensaje = 'Debes seleccionar un archivo.';
        $mensajeTipo = 'danger';
    } elseif ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $mensaje = 'Ocurrió un error al subir el archivo.';
        $mensajeTipo = 'danger';
    } elseif ($_FILES['archivo']['size'] > TAMANO_MAXIMO) {
        $mensaje = 'El archivo supera el tamaño máximo permitido (20 MB).';
        $mensajeTipo = 'danger';
    } else {
        $nombreOriginal = basename($_FILES['archivo']['name']);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!isset($formatosPermitidos[$extension])) {
            $mensaje = 'Formato no soportado. Solo se aceptan PDF, DOCX y TXT.';
            $mensajeTipo = 'danger';
        } else {
            $nombreArchivo = uniqid('doc_', true) . '.' . $extension;
            $rutaRelativa = 'uploads/' . $nombreArchivo;
            $rutaDestino = CARPETA_UPLOADS . $nombreArchivo;

            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaDestino)) {
                $stmt = $pdo->prepare(
                    'INSERT INTO documentos
                        (id_repositorio, id_usuario, nombre_original, nombre_archivo, ruta_archivo, formato)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $idRepositorio,
                    $idUsuario,
                    $nombreOriginal,
                    $nombreArchivo,
                    $rutaRelativa,
                    $formatosPermitidos[$extension],
                ]);

                // El registro queda con estado_procesamiento = 'pendiente' (valor por
                // defecto de la tabla). Aquí es donde, en el siguiente paso, se debe
                // encolar/disparar el procesamiento con IA (extracción, clasificación,
                // resumen) y actualizar analisis_documento + estado_procesamiento.

                $destino = $idRepositorio ? "documentos.php?id_repositorio={$idRepositorio}&ok=subido" : 'documentos.php?ok=subido';
                header("Location: {$destino}");
                exit;
            } else {
                $mensaje = 'No se pudo guardar el archivo en el servidor.';
                $mensajeTipo = 'danger';
            }
        }
    }
}

// =====================================================
// Eliminar documento (POST)
// Solo puede eliminar el admin o el usuario que lo subió.
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $idDoc = (int) ($_POST['id_documento'] ?? 0);
    $idRepositorioRedirect = (int) ($_POST['id_repositorio_redirect'] ?? 0);

    $stmt = $pdo->prepare('SELECT id_usuario, ruta_archivo FROM documentos WHERE id_documento = ?');
    $stmt->execute([$idDoc]);
    $doc = $stmt->fetch();

    $destino = $idRepositorioRedirect ? "documentos.php?id_repositorio={$idRepositorioRedirect}&" : 'documentos.php?';

    if (!$doc) {
        header("Location: {$destino}error=no_encontrado");
        exit;
    }

    if (!$esAdmin && (int) $doc['id_usuario'] !== $idUsuario) {
        header("Location: {$destino}error=sin_permiso");
        exit;
    }

    $rutaCompleta = __DIR__ . '/' . $doc['ruta_archivo'];
    if (is_file($rutaCompleta)) {
        unlink($rutaCompleta);
    }

    // El ON DELETE CASCADE del esquema elimina también su análisis y logs asociados.
    $stmt = $pdo->prepare('DELETE FROM documentos WHERE id_documento = ?');
    $stmt->execute([$idDoc]);

    header("Location: {$destino}ok=eliminado");
    exit;
}

// =====================================================
// Procesar documento con IA (POST)
// Dispara el flujo: extracción -> Gemini -> guardar análisis -> estado.
// Cualquier usuario autenticado puede procesar (no solo el dueño), igual
// que puede descargarlo; el control de edición/borrado ya está aparte.
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'procesar') {
    $idDoc = (int) ($_POST['id_documento'] ?? 0);
    $idRepositorioRedirect = (int) ($_POST['id_repositorio_redirect'] ?? 0);
    $destino = $idRepositorioRedirect ? "documentos.php?id_repositorio={$idRepositorioRedirect}&" : 'documentos.php?';

    $resultado = procesarDocumento($pdo, $idDoc);

    if ($resultado['exito']) {
        header("Location: {$destino}ok=procesado");
    } else {
        header("Location: {$destino}error=procesamiento&detalle=" . urlencode($resultado['mensaje']));
    }
    exit;
}

// =====================================================
// Mensajes desde redirecciones (patrón POST-REDIRECT-GET)
// =====================================================
if (isset($_GET['ok'])) {
    $textos = [
        'subido' => 'Documento subido correctamente. Queda pendiente de procesamiento.',
        'eliminado' => 'Documento eliminado correctamente.',
        'procesado' => 'Documento procesado correctamente con IA.',
    ];
    $mensaje = $textos[$_GET['ok']] ?? '';
    $mensajeTipo = 'success';
} elseif (isset($_GET['error'])) {
    $textos = [
        'sin_permiso' => 'No tienes permiso para eliminar ese documento.',
        'no_encontrado' => 'El documento indicado no existe.',
        'archivo_no_encontrado' => 'El archivo físico no se encontró en el servidor.',
        'procesamiento' => 'No se pudo procesar el documento con IA: ' . htmlspecialchars($_GET['detalle'] ?? ''),
    ];
    $mensaje = $textos[$_GET['error']] ?? 'Ocurrió un error.';
    $mensajeTipo = 'danger';
}

// =====================================================
// Repositorio activo (si se llegó desde "Abrir" en repositorios.php)
// =====================================================
$idRepositorioActivo = isset($_GET['id_repositorio']) ? (int) $_GET['id_repositorio'] : 0;
$repositorioActivo = null;

if ($idRepositorioActivo) {
    $stmt = $pdo->prepare('SELECT id_repositorio, nombre FROM repositorios WHERE id_repositorio = ?');
    $stmt->execute([$idRepositorioActivo]);
    $repositorioActivo = $stmt->fetch();
}

// Lista de repositorios para el select del formulario de subida
$repositorios = $pdo->query('SELECT id_repositorio, nombre FROM repositorios ORDER BY nombre')->fetchAll();

// =====================================================
// Listado de documentos (filtrado por repositorio si aplica)
// =====================================================
$sql = 'SELECT d.id_documento, d.id_usuario, d.nombre_original, d.formato, d.categoria,
               d.estado_procesamiento, d.fecha_carga,
               r.id_repositorio, r.nombre AS repo_nombre,
               u.nombre AS usuario_nombre,
               a.resumen, a.datos_clave, a.fecha_analisis
        FROM documentos d
        JOIN repositorios r ON r.id_repositorio = d.id_repositorio
        JOIN usuarios u ON u.id_usuario = d.id_usuario
        LEFT JOIN analisis_documento a ON a.id_documento = d.id_documento';

$params = [];
if ($idRepositorioActivo) {
    $sql .= ' WHERE d.id_repositorio = ?';
    $params[] = $idRepositorioActivo;
}
$sql .= ' ORDER BY d.fecha_carga DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documentos = $stmt->fetchAll();

// Etiquetas y badges (mismo criterio visual que index.php)
$etiquetasCategoria = [
    'contrato' => 'Contrato',
    'acta' => 'Acta',
    'concepto_juridico' => 'Concepto jurídico',
    'sin_clasificar' => 'Sin clasificar',
];

function badgeEstadoDoc($estado)
{
    $clases = ['pendiente' => 'bg-warning text-dark', 'procesado' => 'bg-success', 'error' => 'bg-danger'];
    $texto = ['pendiente' => 'Pendiente', 'procesado' => 'Procesado', 'error' => 'Error'];
    $clase = $clases[$estado] ?? 'bg-secondary';
    $label = $texto[$estado] ?? $estado;
    return "<span class=\"badge {$clase}\">{$label}</span>";
}

function iconoFormato($formato)
{
    $iconos = ['pdf' => 'bi-filetype-pdf', 'docx' => 'bi-filetype-docx', 'txt' => 'bi-filetype-txt'];
    return $iconos[$formato] ?? 'bi-file-earmark';
}

// Etiquetas legibles para las llaves que la IA devuelve en datos_clave
$etiquetasDatosClave = [
    'partes_involucradas' => 'Partes involucradas',
    'fechas_relevantes' => 'Fechas relevantes',
    'montos' => 'Montos',
    'vigencia_u_obligaciones' => 'Vigencia / Obligaciones',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentos - Bufete IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .doc-icon {
            width: 40px; height: 40px; border-radius: 0.5rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff; background-color: #6c757d;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="index.php">
            <i class="bi bi-robot me-1"></i> Bufete Restrepo &amp; Asociados
        </a>
        <div class="d-flex align-items-center">
            <span class="text-light me-3">
                <?= htmlspecialchars($_SESSION['nombre']) ?>
                <span class="badge bg-secondary ms-1"><?= htmlspecialchars(ucfirst($_SESSION['rol'])) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house"></i> Dashboard</a>
        <a href="repositorios.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-folder2-open"></i> Repositorios</a>
        <a href="buscar.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> Búsqueda</a>
        <a href="consulta_ia.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-chat-dots"></i> Preguntar a la IA</a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-file-earmark-text me-1"></i> Documentos
            <?php if ($repositorioActivo): ?>
                <span class="text-muted fs-6">— <?= htmlspecialchars($repositorioActivo['nombre']) ?></span>
            <?php endif; ?>
        </h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSubirDoc">
            <i class="bi bi-upload"></i> Subir documento
        </button>
    </div>

    <?php if ($repositorioActivo): ?>
        <a href="documentos.php" class="small d-inline-block mb-2"><i class="bi bi-arrow-left"></i> Ver documentos de todos los repositorios</a>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= htmlspecialchars($mensajeTipo) ?> py-2"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Documento</th>
                        <?php if (!$repositorioActivo): ?><th>Repositorio</th><?php endif; ?>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Subido por</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documentos)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Aún no hay documentos<?= $repositorioActivo ? ' en este repositorio' : '' ?>. Súbelos con el botón "Subir documento".
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $modalesDetalle = []; // se renderizan después, fuera de la tabla ?>
                        <?php foreach ($documentos as $doc): ?>
                            <?php $puedeEliminar = $esAdmin || (int) $doc['id_usuario'] === (int) $idUsuario; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="doc-icon"><i class="bi <?= iconoFormato($doc['formato']) ?>"></i></div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($doc['nombre_original']) ?></div>
                                            <?php if (!empty($doc['resumen'])): ?>
                                                <div class="text-muted small text-truncate" style="max-width: 320px;">
                                                    <?= htmlspecialchars($doc['resumen']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <?php if (!$repositorioActivo): ?>
                                    <td><?= htmlspecialchars($doc['repo_nombre']) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($etiquetasCategoria[$doc['categoria']] ?? $doc['categoria']) ?></td>
                                <td><?= badgeEstadoDoc($doc['estado_procesamiento']) ?></td>
                                <td><?= htmlspecialchars($doc['usuario_nombre']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($doc['fecha_carga']))) ?></td>
                                <td class="text-end">
                                    <?php if ($doc['estado_procesamiento'] === 'procesado'): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" title="Ver detalle"
                                                data-bs-toggle="modal" data-bs-target="#modalDetalle<?= (int) $doc['id_documento'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($doc['estado_procesamiento'] !== 'procesado'): ?>
                                        <form method="POST" action="documentos.php" class="d-inline">
                                            <input type="hidden" name="accion" value="procesar">
                                            <input type="hidden" name="id_documento" value="<?= (int) $doc['id_documento'] ?>">
                                            <input type="hidden" name="id_repositorio_redirect" value="<?= (int) $idRepositorioActivo ?>">
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Procesar con IA">
                                                <i class="bi bi-robot"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="documentos.php?descargar=<?= (int) $doc['id_documento'] ?>"
                                       class="btn btn-outline-primary btn-sm" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php if ($puedeEliminar): ?>
                                        <form method="POST" action="documentos.php" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este documento? También se eliminará su análisis asociado.');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_documento" value="<?= (int) $doc['id_documento'] ?>">
                                            <input type="hidden" name="id_repositorio_redirect" value="<?= (int) $idRepositorioActivo ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <?php if ($doc['estado_procesamiento'] === 'procesado'): ?>
                                <?php
                                    $datosClave = json_decode($doc['datos_clave'] ?? '[]', true) ?: [];
                                    ob_start();
                                ?>
                                <div class="modal fade" id="modalDetalle<?= (int) $doc['id_documento'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="bi <?= iconoFormato($doc['formato']) ?> me-1"></i>
                                                    <?= htmlspecialchars($doc['nombre_original']) ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <span class="text-muted small">Categoría asignada por la IA</span><br>
                                                    <span class="badge bg-primary">
                                                        <?= htmlspecialchars($etiquetasCategoria[$doc['categoria']] ?? $doc['categoria']) ?>
                                                    </span>
                                                </div>

                                                <div class="mb-3">
                                                    <h6 class="text-muted small mb-1">Resumen generado por la IA</h6>
                                                    <p class="mb-0"><?= nl2br(htmlspecialchars($doc['resumen'] ?? '')) ?></p>
                                                </div>

                                                <div class="mb-3">
                                                    <h6 class="text-muted small mb-2">Datos clave extraídos</h6>
                                                    <?php if (empty($datosClave)): ?>
                                                        <p class="text-muted small mb-0">La IA no encontró datos clave adicionales en este documento.</p>
                                                    <?php else: ?>
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <tbody>
                                                                <?php foreach ($datosClave as $llave => $valor): ?>
                                                                    <?php
                                                                        // Los valores pueden ser texto plano o listas (arreglos),
                                                                        // según lo que haya devuelto la IA para ese campo.
                                                                        if (is_array($valor)) {
                                                                            $valor = empty($valor) ? '—' : implode(', ', $valor);
                                                                        } elseif ($valor === '' || $valor === null) {
                                                                            $valor = '—';
                                                                        }
                                                                    ?>
                                                                    <tr>
                                                                        <th class="text-muted small" style="width: 220px;">
                                                                            <?= htmlspecialchars($etiquetasDatosClave[$llave] ?? ucfirst($llave)) ?>
                                                                        </th>
                                                                        <td class="small"><?= htmlspecialchars($valor) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (!empty($doc['fecha_analisis'])): ?>
                                                    <div class="text-muted" style="font-size: 0.8rem;">
                                                        Procesado el <?= htmlspecialchars(date('d/m/Y H:i', strtotime($doc['fecha_analisis']))) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="documentos.php?descargar=<?= (int) $doc['id_documento'] ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-download"></i> Descargar original
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $modalesDetalle[] = ob_get_clean(); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($modalesDetalle)): ?>
    <!-- ================= Modales: detalle de análisis IA (fuera de la tabla) ================= -->
    <?= implode("\n", $modalesDetalle) ?>
<?php endif; ?>

<!-- ================= Modal: subir documento ================= -->
<div class="modal fade" id="modalSubirDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="documentos.php<?= $idRepositorioActivo ? "?id_repositorio={$idRepositorioActivo}" : '' ?>"
              class="modal-content" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="subir">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload me-1"></i> Subir documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Repositorio</label>
                    <select name="id_repositorio" class="form-select" required>
                        <option value="">Selecciona un repositorio</option>
                        <?php foreach ($repositorios as $repo): ?>
                            <option value="<?= (int) $repo['id_repositorio'] ?>"
                                <?= $idRepositorioActivo === (int) $repo['id_repositorio'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($repo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Archivo (PDF, DOCX o TXT — máx. 20 MB)</label>
                    <input type="file" name="archivo" class="form-control" accept=".pdf,.docx,.txt" required>
                </div>
                <div class="form-text">
                    El documento quedará en estado "Pendiente" hasta que el módulo de IA lo procese.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Subir</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>