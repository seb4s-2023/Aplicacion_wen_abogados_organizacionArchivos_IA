<?php
// =====================================================
// Control de sesión y roles
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Exige que haya una sesión activa. Si no, redirige al login.
 */
function requireLogin() {
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Exige que el usuario tenga un rol específico ('admin' o 'abogado').
 * Debe llamarse DESPUÉS de requireLogin().
 */
function requireRole($rolRequerido) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rolRequerido) {
        header('Location: index.php?error=sin_permiso');
        exit;
    }
}

/**
 * Atajo para saber si el usuario actual es administrador.
 */
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}
