<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Esta página exige sesión activa
requireLogin();

$esAdmin = esAdmin();
$idUsuario = $_SESSION['id_usuario'];

$mensaje = '';
$mensajeTipo = 'success'; // 'success' | 'danger'

// =====================================================
// Crear repositorio (POST)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '') {
        $mensaje = 'El nombre del repositorio es obligatorio.';
        $mensajeTipo = 'danger';
    } elseif (mb_strlen($nombre) > 150) {
        $mensaje = 'El nombre no puede superar los 150 caracteres.';
        $mensajeTipo = 'danger';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO repositorios (nombre, descripcion, id_usuario_creador)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$nombre, $descripcion !== '' ? $descripcion : null, $idUsuario]);

        header('Location: repositorios.php?ok=creado');
        exit;
    }
}

// =====================================================
// Eliminar repositorio (POST, con confirmación en el front)
// Solo puede eliminar el admin o el usuario que lo creó.
// El ON DELETE CASCADE del esquema se encarga de borrar
// documentos, análisis y logs asociados.
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $idRepo = (int) ($_POST['id_repositorio'] ?? 0);

    $stmt = $pdo->prepare('SELECT id_usuario_creador FROM repositorios WHERE id_repositorio = ?');
    $stmt->execute([$idRepo]);
    $repo = $stmt->fetch();

    if (!$repo) {
        header('Location: repositorios.php?error=no_encontrado');
        exit;
    }

    if (!$esAdmin && (int) $repo['id_usuario_creador'] !== $idUsuario) {
        header('Location: repositorios.php?error=sin_permiso');
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM repositorios WHERE id_repositorio = ?');
    $stmt->execute([$idRepo]);

    header('Location: repositorios.php?ok=eliminado');
    exit;
}

// =====================================================
// Mensajes desde redirecciones (patrón POST-REDIRECT-GET)
// =====================================================
if (isset($_GET['ok'])) {
    $textos = [
        'creado' => 'Repositorio creado correctamente.',
        'eliminado' => 'Repositorio eliminado correctamente.',
    ];
    $mensaje = $textos[$_GET['ok']] ?? '';
    $mensajeTipo = 'success';
} elseif (isset($_GET['error'])) {
    $textos = [
        'sin_permiso' => 'No tienes permiso para eliminar ese repositorio.',
        'no_encontrado' => 'El repositorio indicado no existe.',
    ];
    $mensaje = $textos[$_GET['error']] ?? 'Ocurrió un error.';
    $mensajeTipo = 'danger';
}

// =====================================================
// Listado de repositorios con contador de documentos
// y nombre del usuario que lo creó
// =====================================================
$stmt = $pdo->query(
    'SELECT r.id_repositorio, r.nombre, r.descripcion, r.fecha_creacion,
            r.id_usuario_creador, u.nombre AS creador_nombre,
            COUNT(d.id_documento) AS total_documentos
     FROM repositorios r
     JOIN usuarios u ON u.id_usuario = r.id_usuario_creador
     LEFT JOIN documentos d ON d.id_repositorio = r.id_repositorio
     GROUP BY r.id_repositorio, r.nombre, r.descripcion, r.fecha_creacion,
              r.id_usuario_creador, u.nombre
     ORDER BY r.fecha_creacion DESC'
);
$repositorios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Repositorios - Bufete IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .repo-card { border-radius: 0.75rem; transition: transform .1s ease; }
        .repo-card:hover { transform: translateY(-2px); }
        .repo-icon {
            width: 44px; height: 44px; border-radius: 0.6rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff; background-color: #0d6efd;
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
        <a href="documentos.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text"></i> Documentos</a>
        <a href="buscar.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> Búsqueda</a>
        <a href="consulta_ia.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-chat-dots"></i> Preguntar a la IA</a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-folder2-open me-1"></i> Repositorios</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoRepo">
            <i class="bi bi-plus-lg"></i> Nuevo repositorio
        </button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= htmlspecialchars($mensajeTipo) ?> py-2"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <?php if (empty($repositorios)): ?>
            <div class="col-12">
                <div class="alert alert-light border text-center text-muted py-4">
                    Aún no hay repositorios. Crea el primero con el botón "Nuevo repositorio".
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($repositorios as $repo): ?>
                <?php
                    $puedeEliminar = $esAdmin || (int) $repo['id_usuario_creador'] === $idUsuario;
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card repo-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <div class="repo-icon"><i class="bi bi-folder2"></i></div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= htmlspecialchars($repo['nombre']) ?></h6>
                                    <div class="small text-muted">
                                        Creado por <?= htmlspecialchars($repo['creador_nombre']) ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($repo['descripcion'])): ?>
                                <p class="text-secondary small mb-2"><?= htmlspecialchars($repo['descripcion']) ?></p>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                                <span><i class="bi bi-file-earmark-text me-1"></i><?= (int) $repo['total_documentos'] ?> documento(s)</span>
                                <span><?= htmlspecialchars(date('d/m/Y', strtotime($repo['fecha_creacion']))) ?></span>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="documentos.php?id_repositorio=<?= (int) $repo['id_repositorio'] ?>"
                                   class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="bi bi-box-arrow-in-right"></i> Abrir
                                </a>
                                <?php if ($puedeEliminar): ?>
                                    <form method="POST" action="repositorios.php"
                                          onsubmit="return confirm('¿Eliminar este repositorio? Se eliminarán también sus documentos y análisis asociados.');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_repositorio" value="<?= (int) $repo['id_repositorio'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ================= Modal: nuevo repositorio ================= -->
<div class="modal fade" id="modalNuevoRepo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="repositorios.php" class="modal-content">
            <input type="hidden" name="accion" value="crear">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-folder-plus me-1"></i> Nuevo repositorio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" maxlength="150" required autofocus
                           placeholder="Ej: Casos activos 2026">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción (opcional)</label>
                    <textarea name="descripcion" class="form-control" rows="3" maxlength="255"
                              placeholder="Breve descripción del contenido del repositorio"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
