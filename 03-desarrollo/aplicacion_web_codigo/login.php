<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Si ya hay sesión activa, no tiene sentido ver el login de nuevo
if (isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Debes ingresar correo y contraseña.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id_usuario, nombre, email, password_hash, rol
             FROM usuarios
             WHERE email = ? AND activo = 1'
        );
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            // Credenciales correctas: guardamos datos mínimos en sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['rol']        = $usuario['rol'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Bufete IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
        <div class="card shadow-sm" style="width: 100%; max-width: 400px;">
            <div class="card-body p-4">
                <h4 class="mb-1 text-center">Bufete Restrepo &amp; Asociados</h4>
                <p class="text-center text-muted mb-4">Gestión y Análisis Documental</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_permiso'): ?>
                    <div class="alert alert-warning py-2">No tienes permiso para acceder a esa sección.</div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>

                <hr>
                <p class="small text-muted mb-0">
                    Prueba: <strong>admin@bufete.com</strong> / Admin123!<br>
                    Abogado: <strong>carlos.restrepo@bufete.com</strong> / Abogado123!
                </p>
            </div>
        </div>
    </div>
</body>
</html>
