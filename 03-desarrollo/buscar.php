<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Esta página exige sesión activa
requireLogin();

$idUsuario = $_SESSION['id_usuario'];

// =====================================================
// Parámetros de búsqueda
// =====================================================
$q = trim($_GET['q'] ?? '');
$categoriaFiltro = $_GET['categoria'] ?? '';
$idRepositorioFiltro = isset($_GET['id_repositorio']) ? (int) $_GET['id_repositorio'] : 0;

$categoriasValidas = ['contrato', 'acta', 'concepto_juridico', 'sin_clasificar'];
if (!in_array($categoriaFiltro, $categoriasValidas, true)) {
    $categoriaFiltro = '';
}

$resultados = [];
$totalResultados = 0;

// =====================================================
// Búsqueda dentro del contenido documental
// Busca coincidencias en: nombre del archivo, resumen generado
// por la IA y el texto extraído completo del documento.
// =====================================================
if ($q !== '') {
    $sql = 'SELECT d.id_documento, d.nombre_original, d.formato, d.categoria,
                   d.estado_procesamiento, d.fecha_carga,
                   r.id_repositorio, r.nombre AS repo_nombre,
                   u.nombre AS usuario_nombre,
                   a.resumen, a.texto_extraido
            FROM documentos d
            JOIN repositorios r ON r.id_repositorio = d.id_repositorio
            JOIN usuarios u ON u.id_usuario = d.id_usuario
            LEFT JOIN analisis_documento a ON a.id_documento = d.id_documento
            WHERE (d.nombre_original LIKE :q1
                   OR a.resumen LIKE :q2
                   OR a.texto_extraido LIKE :q3)';
    $params = [
        ':q1' => "%{$q}%",
        ':q2' => "%{$q}%",
        ':q3' => "%{$q}%",
    ];

    if ($categoriaFiltro !== '') {
        $sql .= ' AND d.categoria = :categoria';
        $params[':categoria'] = $categoriaFiltro;
    }
    if ($idRepositorioFiltro) {
        $sql .= ' AND d.id_repositorio = :id_repositorio';
        $params[':id_repositorio'] = $idRepositorioFiltro;
    }

    $sql .= ' ORDER BY d.fecha_carga DESC LIMIT 50';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll();
    $totalResultados = count($resultados);
}

// Lista de repositorios para el filtro
$repositorios = $pdo->query('SELECT id_repositorio, nombre FROM repositorios ORDER BY nombre')->fetchAll();

// =====================================================
// Registro de la búsqueda como "consulta" de uso
// (útil para trazabilidad y para el historial del dashboard)
// =====================================================
if ($q !== '') {
    $stmt = $pdo->prepare(
        'INSERT INTO consultas_ia (id_usuario, pregunta, respuesta)
         VALUES (?, ?, ?)'
    );
    $stmt->execute([
        $idUsuario,
        "[Búsqueda] {$q}",
        "{$totalResultados} resultado(s) encontrado(s).",
    ]);
}

// =====================================================
// Etiquetas y helpers visuales (mismo criterio que el resto del sistema)
// =====================================================
$etiquetasCategoria = [
    'contrato' => 'Contrato',
    'acta' => 'Acta',
    'concepto_juridico' => 'Concepto jurídico',
    'sin_clasificar' => 'Sin clasificar',
];

function badgeEstadoBuscar($estado)
{
    $clases = ['pendiente' => 'bg-warning text-dark', 'procesado' => 'bg-success', 'error' => 'bg-danger'];
    $texto = ['pendiente' => 'Pendiente', 'procesado' => 'Procesado', 'error' => 'Error'];
    $clase = $clases[$estado] ?? 'bg-secondary';
    $label = $texto[$estado] ?? $estado;
    return "<span class=\"badge {$clase}\">{$label}</span>";
}

function iconoFormatoBuscar($formato)
{
    $iconos = ['pdf' => 'bi-filetype-pdf', 'docx' => 'bi-filetype-docx', 'txt' => 'bi-filetype-txt'];
    return $iconos[$formato] ?? 'bi-file-earmark';
}

/**
 * Resalta el término buscado dentro de un texto (htmlspecialchars-safe).
 */
function resaltar($texto, $termino)
{
    $escapado = htmlspecialchars($texto);
    if ($termino === '') {
        return $escapado;
    }
    $patron = '/' . preg_quote(htmlspecialchars($termino), '/') . '/iu';
    return preg_replace($patron, '<mark>$0</mark>', $escapado);
}

/**
 * Devuelve un fragmento del texto extraído centrado en la primera
 * coincidencia del término buscado, con el término resaltado.
 */
function fragmentoConContexto($textoCompleto, $termino, $radio = 120)
{
    if ($textoCompleto === null || $textoCompleto === '') {
        return '';
    }
    $pos = mb_stripos($textoCompleto, $termino);
    if ($pos === false) {
        // No aparece en el texto extraído; no se muestra fragmento.
        return '';
    }
    $inicio = max(0, $pos - $radio);
    $largo = $radio * 2 + mb_strlen($termino);
    $fragmento = mb_substr($textoCompleto, $inicio, $largo);

    $prefijo = $inicio > 0 ? '…' : '';
    $sufijo = ($inicio + $largo) < mb_strlen($textoCompleto) ? '…' : '';

    return $prefijo . resaltar($fragmento, $termino) . $sufijo;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda - Bufete IA</title>
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
        mark { background-color: #fff3a3; padding: 0 2px; border-radius: 2px; }
        .fragmento { font-size: 0.85rem; }
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
        <a href="documentos.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text"></i> Documentos</a>
        <a href="consulta_ia.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-chat-dots"></i> Preguntar a la IA</a>
    </div>

    <h4 class="mb-3"><i class="bi bi-search me-1"></i> Búsqueda documental</h4>

    <!-- ================= Formulario de búsqueda ================= -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="buscar.php" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Buscar en nombre, resumen y contenido</label>
                    <input type="text" name="q" class="form-control" placeholder="Ej: cláusula de confidencialidad"
                           value="<?= htmlspecialchars($q) ?>" autofocus>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Categoría</label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($etiquetasCategoria as $valor => $etiqueta): ?>
                            <option value="<?= htmlspecialchars($valor) ?>" <?= $categoriaFiltro === $valor ? 'selected' : '' ?>>
                                <?= htmlspecialchars($etiqueta) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Repositorio</label>
                    <select name="id_repositorio" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($repositorios as $repo): ?>
                            <option value="<?= (int) $repo['id_repositorio'] ?>"
                                <?= $idRepositorioFiltro === (int) $repo['id_repositorio'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($repo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= Resultados ================= -->
    <?php if ($q === ''): ?>
        <div class="alert alert-light border text-center text-muted py-4">
            Escribe un término para buscar dentro del nombre, el resumen generado por la IA y el contenido extraído de los documentos.
        </div>
    <?php else: ?>
        <p class="text-muted small mb-2">
            <?= $totalResultados ?> resultado(s) para "<strong><?= htmlspecialchars($q) ?></strong>"
        </p>

        <?php if (empty($resultados)): ?>
            <div class="alert alert-light border text-center text-muted py-4">
                No se encontraron documentos que coincidan con tu búsqueda.
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($resultados as $doc): ?>
                    <div class="list-group-item">
                        <div class="d-flex align-items-start gap-3">
                            <div class="doc-icon"><i class="bi <?= iconoFormatoBuscar($doc['formato']) ?>"></i></div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold">
                                            <?= resaltar($doc['nombre_original'], $q) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= htmlspecialchars($doc['repo_nombre']) ?> ·
                                            <?= htmlspecialchars($etiquetasCategoria[$doc['categoria']] ?? $doc['categoria']) ?> ·
                                            Subido por <?= htmlspecialchars($doc['usuario_nombre']) ?> ·
                                            <?= htmlspecialchars(date('d/m/Y H:i', strtotime($doc['fecha_carga']))) ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <?= badgeEstadoBuscar($doc['estado_procesamiento']) ?>
                                    </div>
                                </div>

                                <?php
                                    $fragmento = fragmentoConContexto($doc['texto_extraido'] ?? '', $q);
                                    $resumenResaltado = !empty($doc['resumen']) ? resaltar($doc['resumen'], $q) : '';
                                ?>
                                <?php if ($fragmento !== ''): ?>
                                    <div class="fragmento text-secondary mt-2">
                                        <i class="bi bi-file-text me-1"></i>"…<?= $fragmento ?>…"
                                    </div>
                                <?php elseif ($resumenResaltado !== ''): ?>
                                    <div class="fragmento text-secondary mt-2">
                                        <i class="bi bi-card-text me-1"></i><?= $resumenResaltado ?>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-2">
                                    <a href="documentos.php?id_repositorio=<?= (int) $doc['id_repositorio'] ?>"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-box-arrow-in-right"></i> Ver documento
                                    </a>
                                    <a href="documentos.php?descargar=<?= (int) $doc['id_documento'] ?>"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download"></i> Descargar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
