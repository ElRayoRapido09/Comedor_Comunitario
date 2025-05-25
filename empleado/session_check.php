<?php
session_start();

// Verificar si la sesión está activa
if (!isset($_SESSION['usuario'])) {
    header('Location: ../inicio/index.html');
    exit();
}

// Verificar que el usuario sea empleado o admin
$tipoUsuario = $_SESSION['usuario']['tipo_usuario'];
if ($tipoUsuario !== 'empleado' && $tipoUsuario !== 'admin') {
    // Registrar el intento de acceso no autorizado
    error_log("Intento de acceso no autorizado. Tipo de usuario: $tipoUsuario");
    header('Location: ../inicio/index.html');
    exit();
}

// Verificar que el usuario esté activo
if (isset($_SESSION['usuario']['activo']) && !$_SESSION['usuario']['activo']) {
    header('Location: ../inicio/index.html');
    exit();
}
?>