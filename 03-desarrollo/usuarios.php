<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Esta página exige sesión activa Y rol de administrador
requireLogin();
requireRole('admin');

$idUsuarioActual = $_SESSION['id_usuario'];

$mensaje = '';
$mensajeTipo = 'success'; // 'success' | 'danger'

$rolesValidos = ['admin', 'abogado'];

// =====================================================
// Crear usuario (POST)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? '';

    if ($nombre === '' || $email === '' || $password === '') {
        $mensaje = 'Nombre, correo y contraseña son obligatorios.';
        $mensajeTipo = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El correo electrónico no es válido.';
        $mensajeTipo = 'danger';
    } elseif (mb_strlen($password) < 8) {
        $mensaje = 'La contraseña debe tener al menos 8 caracteres.';
        $mensajeTipo = 'danger';
    } elseif (!in_array($rol, $rolesValidos, true)) {
        $mensaje = 'Debes seleccionar un rol válido.';
        $mensajeTipo = 'danger';
    } else {
        // Verificar que el correo no esté ya registrado
        $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $mensaje = 'Ya existe un usuario registrado con ese correo.';
            $mensajeTipo = 'danger';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, email, password_hash, rol, activo)
                 VALUES (?, ?, ?, ?, 1)'
            );
            $stmt->execute([$nombre, $email, $hash, $rol]);

            header('Location: usuarios.php?ok=creado');
            exit;
        }
    }
}

// =====================================================
// Cambiar rol (POST)
// No se permite cambiar el propio rol (evita que el admin
// se quite a sí mismo el permiso por accidente).
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_rol') {
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
    $nuevoRol = $_POST['rol'] ?? '';

    if ($idUsuario === $idUsuarioActual) {
        header('Location: usuarios.php?error=auto_rol');
        exit;
    }

    if (!in_array($nuevoRol, $rolesValidos, true)) {
        header('Location: usuarios.php?error=rol_invalido');
        exit;
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET rol = ? WHERE id_usuario = ?');
    $stmt->execute([$nuevoRol, $idUsuario]);

    header('Location: usuarios.php?ok=rol_actualizado');
    exit;
}

// =====================================================
// Activar / Desactivar usuario (POST)
// No se permite desactivar la propia cuenta.
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_estado') {
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
    $nuevoEstado = (int) ($_POST['nuevo_estado'] ?? 0);

    if ($idUsuario === $idUsuarioActual) {
        header('Location: usuarios.php?error=auto_estado');
        exit;
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET activo = ? WHERE id_usuario = ?');
    $stmt->execute([$nuevoEstado, $idUsuario]);

    $destino = $nuevoEstado ? 'ok=activado' : 'ok=desactivado';
    header("Location: usuarios.php?{$destino}");
    exit;
}

// =====================================================
// Restablecer contraseña (POST)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'restablecer_password') {
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
    $nuevaPassword = $_POST['nueva_password'] ?? '';

    if (mb_strlen($nuevaPassword) < 8) {
        header('Location: usuarios.php?error=password_corta');
        exit;
    }

    $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?');
    $stmt->execute([$hash, $idUsuario]);

    header('Location: usuarios.php?ok=password_restablecida');
    exit;
}

// =====================================================
// Mensajes desde redirecciones (patrón POST-REDIRECT-GET)
// =====================================================
if (isset($_GET['ok'])) {
    $textos = [
        'creado' => 'Usuario creado correctamente.',
        'rol_actualizado' => 'Rol actualizado correctamente.',
        'activado' => 'Usuario activado correctamente.',
        'desactivado' => 'Usuario desactivado correctamente.',
        'password_restablecida' => 'Contraseña restablecida correctamente.',
    ];
    $mensaje = $textos[$_GET['ok']] ?? '';
    $mensajeTipo = 'success';
} elseif (isset($_GET['error'])) {
    $textos = [
        'auto_rol' => 'No puedes cambiar tu propio rol.',
        'auto_estado' => 'No puedes desactivar tu propia cuenta.',
        'rol_invalido' => 'El rol indicado no es válido.',
        'password_corta' => 'La nueva contraseña debe tener al menos 8 caracteres.',
    ];
    $mensaje = $textos[$_GET['error']] ?? 'Ocurrió un error.';
    $mensajeTipo = 'danger';
}

// =====================================================
// Listado de usuarios con contador de repositorios y
// documentos que ha creado/subido cada uno
// =====================================================
$stmt = $pdo->query(
    "SELECT u.id_usuario, u.nombre, u.email, u.rol, u.activo, u.fecha_creacion,
            COUNT(DISTINCT r.id_repositorio) AS total_repositorios,
            COUNT(DISTINCT d.id_documento) AS total_documentos
     FROM usuarios u
     LEFT JOIN repositorios r ON r.id_usuario_creador = u.id_usuario
     LEFT JOIN documentos d ON d.id_usuario = u.id_usuario
     GROUP BY u.id_usuario, u.nombre, u.email, u.rol, u.activo, u.fecha_creacion
     ORDER BY u.fecha_creacion DESC"
);
$usuarios = $stmt->fetchAll();

function badgeRol($rol)
{
    $clase = $rol === 'admin' ? 'bg-primary' : 'bg-info text-dark';
    $texto = $rol === 'admin' ? 'Admin' : 'Abogado';
    return "<span class=\"badge {$clase}\">{$texto}</span>";
}

function badgeActivo($activo)
{
    return $activo
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-secondary">Inactivo</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - Bufete IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .user-icon {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff; background-color: #6c757d;
        }
        .rol-select { width: auto; display: inline-block; }
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
        <a href="buscar.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> Búsqueda</a>
        <a href="consulta_ia.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-chat-dots"></i> Preguntar a la IA</a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-people me-1"></i> Usuarios</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
            <i class="bi bi-person-plus"></i> Nuevo usuario
        </button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= htmlspecialchars($mensajeTipo) ?> py-2"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Repositorios</th>
                        <th>Documentos</th>
                        <th>Desde</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <?php $esUsuarioActual = (int) $u['id_usuario'] === $idUsuarioActual; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-icon"><i class="bi bi-person-fill"></i></div>
                                        <div>
                                            <?= htmlspecialchars($u['nombre']) ?>
                                            <?php if ($esUsuarioActual): ?>
                                                <span class="badge bg-light text-dark border ms-1">Tú</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= badgeRol($u['rol']) ?></td>
                                <td><?= badgeActivo($u['activo']) ?></td>
                                <td><?= (int) $u['total_repositorios'] ?></td>
                                <td><?= (int) $u['total_documentos'] ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($u['fecha_creacion']))) ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">

                                        <!-- Cambiar rol -->
                                        <form method="POST" action="usuarios.php" class="d-inline-flex gap-1">
                                            <input type="hidden" name="accion" value="cambiar_rol">
                                            <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                                            <select name="rol" class="form-select form-select-sm rol-select" <?= $esUsuarioActual ? 'disabled' : '' ?>>
                                                <option value="abogado" <?= $u['rol'] === 'abogado' ? 'selected' : '' ?>>Abogado</option>
                                                <option value="admin" <?= $u['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <?php if (!$esUsuarioActual): ?>
                                                <button type="submit" class="btn btn-outline-secondary btn-sm" title="Guardar rol">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Activar / desactivar -->
                                        <?php if (!$esUsuarioActual): ?>
                                            <form method="POST" action="usuarios.php" class="d-inline"
                                                  onsubmit="return confirm('<?= $u['activo'] ? '¿Desactivar este usuario? No podrá iniciar sesión.' : '¿Reactivar este usuario?' ?>');">
                                                <input type="hidden" name="accion" value="cambiar_estado">
                                                <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?= $u['activo'] ? 0 : 1 ?>">
                                                <button type="submit" class="btn btn-outline-<?= $u['activo'] ? 'warning' : 'success' ?> btn-sm"
                                                        title="<?= $u['activo'] ? 'Desactivar' : 'Activar' ?>">
                                                    <i class="bi <?= $u['activo'] ? 'bi-person-dash' : 'bi-person-check' ?>"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Restablecer contraseña -->
                                        <button type="button" class="btn btn-outline-primary btn-sm" title="Restablecer contraseña"
                                                data-bs-toggle="modal" data-bs-target="#modalPassword"
                                                data-id="<?= (int) $u['id_usuario'] ?>" data-nombre="<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= Modal: nuevo usuario ================= -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="usuarios.php" class="modal-content">
            <input type="hidden" name="accion" value="crear">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i> Nuevo usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control" maxlength="100" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                    <div class="form-text">Mínimo 8 caracteres.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="abogado">Abogado</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= Modal: restablecer contraseña ================= -->
<div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="usuarios.php" class="modal-content">
            <input type="hidden" name="accion" value="restablecer_password">
            <input type="hidden" name="id_usuario" id="passwordIdUsuario" value="">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key me-1"></i> Restablecer contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Usuario: <strong id="passwordNombreUsuario"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="nueva_password" class="form-control" minlength="8" required>
                    <div class="form-text">Mínimo 8 caracteres.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Restablecer</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Rellena el modal de restablecer contraseña con el usuario elegido
    document.getElementById('modalPassword').addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        document.getElementById('passwordIdUsuario').value = boton.getAttribute('data-id');
        document.getElementById('passwordNombreUsuario').textContent = boton.getAttribute('data-nombre');
    });
</script>
</body>
</html>
