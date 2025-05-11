<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Borrar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Borrar cookie de recordar si existe
if (isset($_COOKIE['recordar_token'])) {
    setcookie('recordar_token', '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al login con parámetro de logout
header("Location: ../inicio/index.html?logout=1");
exit();
?>