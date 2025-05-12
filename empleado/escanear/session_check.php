<?php
// Configuración estricta de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 1800, // 30 minutos
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Headers de seguridad
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

function checkAuth() {
    // Verificar si existe la sesión de usuario
    if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario'])) {
        return [
            'success' => false,
            'message' => 'Sesión no iniciada',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Compatibilidad con id_usuario o id
    $id_usuario = $_SESSION['usuario']['id_usuario'] ?? $_SESSION['usuario']['id'] ?? null;
    if (empty($id_usuario)) {
        return [
            'success' => false,
            'message' => 'ID de usuario no disponible',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Verificar tipo de usuario
    $tipo_usuario = $_SESSION['usuario']['tipo_usuario'] ?? '';
    if (!in_array($tipo_usuario, ['empleado', 'admin'])) {
        return [
            'success' => false,
            'message' => 'Permisos insuficientes',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Verificar inactividad (30 minutos)
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
        return [
            'success' => false,
            'message' => 'Sesión expirada por inactividad',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Actualizar última actividad
    $_SESSION['LAST_ACTIVITY'] = time();

    // Normalizar estructura de sesión
    $_SESSION['usuario']['id_usuario'] = $id_usuario;
    if (isset($_SESSION['usuario']['id'])) {
        unset($_SESSION['usuario']['id']);
    }

    return ['success' => true];
}

$authCheck = checkAuth();
if (!$authCheck['success']) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode($authCheck);
    } else {
        $_SESSION['flash_message'] = $authCheck['message'];
        header('Location: ' . $authCheck['redirect']);
    }
    exit();
}
?>