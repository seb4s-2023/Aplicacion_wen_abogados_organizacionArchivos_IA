<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Esta página exige sesión activa
requireLogin();

$esAdmin = esAdmin();
$idUsuario = $_SESSION['id_usuario'];

// =====================================================
// Indicadores generales (visibles para todos los roles)
// =====================================================

// Total de documentos en el sistema
$totalDocumentos = (int) $pdo->query('SELECT COUNT(*) FROM documentos')->fetchColumn();

// Total de repositorios
$totalRepositorios = (int) $pdo->query('SELECT COUNT(*) FROM repositorios')->fetchColumn();

// Documentos agrupados por estado de procesamiento
$stmt = $pdo->query(
    'SELECT estado_procesamiento, COUNT(*) AS total
     FROM documentos
     GROUP BY estado_procesamiento'
);
$porEstado = ['pendiente' => 0, 'procesado' => 0, 'error' => 0];
foreach ($stmt->fetchAll() as $fila) {
    $porEstado[$fila['estado_procesamiento']] = (int) $fila['total'];
}

// Documentos agrupados por categoría
$stmt = $pdo->query(
    'SELECT categoria, COUNT(*) AS total
     FROM documentos
     GROUP BY categoria'
);
$porCategoria = [
    'contrato' => 0,
    'acta' => 0,
    'concepto_juridico' => 0,
    'sin_clasificar' => 0,
];
foreach ($stmt->fetchAll() as $fila) {
    $porCategoria[$fila['categoria']] = (int) $fila['total'];
}

// Documentos recientes (últimos 5) con nombre de repositorio y usuario que los cargó
$stmt = $pdo->query(
    'SELECT d.id_documento, d.nombre_original, d.formato, d.categoria,
            d.estado_procesamiento, d.fecha_carga,
            r.nombre AS repo_nombre, u.nombre AS usuario_nombre
     FROM documentos d
     JOIN repositorios r ON r.id_repositorio = d.id_repositorio
     JOIN usuarios u ON u.id_usuario = d.id_usuario
     ORDER BY d.fecha_carga DESC
     LIMIT 5'
);
$documentosRecientes = $stmt->fetchAll();

// Últimas consultas en lenguaje natural (historial de uso de la IA)
$stmt = $pdo->query(
    'SELECT c.pregunta, c.respuesta, c.fecha, u.nombre AS usuario_nombre
     FROM consultas_ia c
     JOIN usuarios u ON u.id_usuario = c.id_usuario
     ORDER BY c.fecha DESC
     LIMIT 5'
);
$consultasRecientes = $stmt->fetchAll();

// =====================================================
// Indicadores propios del usuario (útil sobre todo para 'abogado')
// =====================================================
$stmt = $pdo->prepare('SELECT COUNT(*) FROM documentos WHERE id_usuario = ?');
$stmt->execute([$idUsuario]);
$misDocumentos = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM repositorios WHERE id_usuario_creador = ?');
$stmt->execute([$idUsuario]);
$misRepositorios = (int) $stmt->fetchColumn();

// =====================================================
// Indicadores solo para administrador
// =====================================================
$totalUsuarios = null;
$erroresRecientes = [];
if ($esAdmin) {
    $totalUsuarios = (int) $pdo->query('SELECT COUNT(*) FROM usuarios WHERE activo = 1')->fetchColumn();

    $stmt = $pdo->query(
        'SELECT l.tipo_error, l.mensaje, l.fecha, d.nombre_original
         FROM logs_procesamiento l
         JOIN documentos d ON d.id_documento = l.id_documento
         ORDER BY l.fecha DESC
         LIMIT 5'
    );
    $erroresRecientes = $stmt->fetchAll();
}

// Etiquetas legibles para mostrar en la interfaz
$etiquetasCategoria = [
    'contrato' => 'Contrato',
    'acta' => 'Acta',
    'concepto_juridico' => 'Concepto jurídico',
    'sin_clasificar' => 'Sin clasificar',
];
$etiquetasEstado = [
    'pendiente' => 'Pendiente',
    'procesado' => 'Procesado',
    'error' => 'Error',
];
function badgeEstado($estado)
{
    $clases = [
        'pendiente' => 'bg-warning text-dark',
        'procesado' => 'bg-success',
        'error' => 'bg-danger',
    ];
    $texto = [
        'pendiente' => 'Pendiente',
        'procesado' => 'Procesado',
        'error' => 'Error',
    ];
    $clase = $clases[$estado] ?? 'bg-secondary';
    $label = $texto[$estado] ?? $estado;
    return "<span class=\"badge {$clase}\">{$label}</span>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Bufete IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .kpi-card { border: none; border-radius: 0.75rem; }
        .kpi-icon {
            width: 48px; height: 48px; border-radius: 0.6rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff;
        }
        .navbar-brand { font-weight: 600; }
        .chart-card { border-radius: 0.75rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
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

    <!-- Navegación rápida a los módulos -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="repositorios.php" class="btn btn-primary btn-sm"><i class="bi bi-folder2-open"></i> Repositorios</a>
        <a href="documentos.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text"></i> Documentos</a>
        <a href="buscar.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> Búsqueda</a>
        <a href="consulta_ia.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-chat-dots"></i> Preguntar a la IA</a>
        <?php if ($esAdmin): ?>
            <a href="usuarios.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people"></i> Usuarios</a>
        <?php endif; ?>
    </div>

    <!-- ================= KPIs generales ================= -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-primary"><i class="bi bi-file-earmark-richtext"></i></div>
                    <div>
                        <div class="text-muted small">Documentos totales</div>
                        <div class="fs-4 fw-bold"><?= $totalDocumentos ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-info"><i class="bi bi-folder2-open"></i></div>
                    <div>
                        <div class="text-muted small">Repositorios</div>
                        <div class="fs-4 fw-bold"><?= $totalRepositorios ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-success"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="text-muted small">Procesados</div>
                        <div class="fs-4 fw-bold"><?= $porEstado['procesado'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-warning"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="text-muted small">Pendientes / Error</div>
                        <div class="fs-4 fw-bold"><?= $porEstado['pendiente'] + $porEstado['error'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= KPIs personales / admin ================= -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-muted small">Mis documentos</div>
                    <div class="fs-5 fw-bold"><?= $misDocumentos ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="text-muted small">Mis repositorios</div>
                    <div class="fs-5 fw-bold"><?= $misRepositorios ?></div>
                </div>
            </div>
        </div>
        <?php if ($esAdmin): ?>
            <div class="col-6 col-lg-3">
                <div class="card kpi-card shadow-sm h-100 border-start border-4 border-secondary">
                    <div class="card-body">
                        <div class="text-muted small">Usuarios activos</div>
                        <div class="fs-5 fw-bold"><?= $totalUsuarios ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card kpi-card shadow-sm h-100 border-start border-4 border-danger">
                    <div class="card-body">
                        <div class="text-muted small">Errores registrados</div>
                        <div class="fs-5 fw-bold"><?= $porEstado['error'] ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================= Gráficos ================= -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card chart-card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Documentos por categoría</div>
                <div class="card-body">
                    <canvas id="chartCategoria" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card chart-card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Documentos por estado de procesamiento</div>
                <div class="card-body">
                    <canvas id="chartEstado" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= Documentos recientes ================= -->
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Documentos recientes
                    <a href="documentos.php" class="small">Ver todos</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Repositorio</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documentosRecientes)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">Aún no hay documentos cargados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($documentosRecientes as $doc): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark-text me-1"></i>
                                            <?= htmlspecialchars($doc['nombre_original']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($doc['repo_nombre']) ?></td>
                                        <td><?= htmlspecialchars($etiquetasCategoria[$doc['categoria']] ?? $doc['categoria']) ?></td>
                                        <td><?= badgeEstado($doc['estado_procesamiento']) ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($doc['fecha_carga']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= Consultas IA recientes ================= -->
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Últimas preguntas a la IA
                    <a href="consulta_ia.php" class="small">Ir a consultas</a>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (empty($consultasRecientes)): ?>
                        <li class="list-group-item text-center text-muted py-3">Todavía no se han hecho consultas.</li>
                    <?php else: ?>
                        <?php foreach ($consultasRecientes as $c): ?>
                            <li class="list-group-item">
                                <div class="small text-muted">
                                    <?= htmlspecialchars($c['usuario_nombre']) ?> ·
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['fecha']))) ?>
                                </div>
                                <div class="fw-semibold">
                                    <i class="bi bi-question-circle me-1"></i><?= htmlspecialchars($c['pregunta']) ?>
                                </div>
                                <?php if (!empty($c['respuesta'])): ?>
                                    <div class="text-truncate small text-secondary">
                                        <i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars($c['respuesta']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- ================= Errores recientes (solo admin) ================= -->
    <?php if ($esAdmin): ?>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-semibold text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i> Errores de procesamiento recientes
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Documento</th>
                                    <th>Tipo de error</th>
                                    <th>Mensaje</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($erroresRecientes)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin errores registrados. </td></tr>
                                <?php else: ?>
                                    <?php foreach ($erroresRecientes as $err): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($err['nombre_original']) ?></td>
                                            <td><span class="badge bg-danger-subtle text-danger-emphasis"><?= htmlspecialchars($err['tipo_error']) ?></span></td>
                                            <td class="small"><?= htmlspecialchars($err['mensaje']) ?></td>
                                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($err['fecha']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    // Gráfico de dona: documentos por categoría
    new Chart(document.getElementById('chartCategoria'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_values($etiquetasCategoria)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($porCategoria)) ?>,
                backgroundColor: ['#0d6efd', '#20c997', '#6f42c1', '#adb5bd']
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    // Gráfico de barras: documentos por estado de procesamiento
    new Chart(document.getElementById('chartEstado'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_values($etiquetasEstado)) ?>,
            datasets: [{
                label: 'Documentos',
                data: <?= json_encode(array_values($porEstado)) ?>,
                backgroundColor: ['#ffc107', '#198754', '#dc3545']
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>

</body>
</html>
