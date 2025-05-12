<?php
// Iniciar o reanudar la sesión
session_start();

// Configuración de encabezados para evitar caché
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

/**
 * Verifica si el usuario está autenticado y tiene permisos adecuados
 */
function checkSession() {
    // Verificar si la sesión está activa y tiene datos de usuario
    if (!isset($_SESSION['usuario'])) {
        return [
            'success' => false,
            'message' => 'Usuario no autenticado',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Verificar que el usuario sea empleado o admin
    $tipoUsuario = $_SESSION['usuario']['tipo_usuario'] ?? '';
    if ($tipoUsuario !== 'empleado' && $tipoUsuario !== 'admin') {
        return [
            'success' => false,
            'message' => 'Acceso no autorizado',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Verificar que el usuario esté activo
    if (isset($_SESSION['usuario']['activo']) && !$_SESSION['usuario']['activo']) {
        return [
            'success' => false,
            'message' => 'Usuario inactivo',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Verificar tiempo de inactividad (30 minutos)
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
        return [
            'success' => false,
            'message' => 'Sesión expirada por inactividad',
            'redirect' => '../../inicio/index.html'
        ];
    }

    // Actualizar marca de tiempo de última actividad
    $_SESSION['LAST_ACTIVITY'] = time();

    return ['success' => true];
}

// Realizar la verificación de sesión
$sessionCheck = checkSession();

// Manejar la respuesta según el tipo de solicitud
if (!$sessionCheck['success']) {
    // Para peticiones AJAX (fetch, axios, etc.)
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode($sessionCheck);
        exit();
    } 
    // Para peticiones normales (navegación directa)
    else {
        // Almacenar mensaje en sesión flash para mostrarlo después de redirigir
        $_SESSION['flash_message'] = $sessionCheck['message'];
        header('Location: ' . $sessionCheck['redirect']);
        exit();
    }
}

// Si todo está bien, continuar con la ejecución normal
?>