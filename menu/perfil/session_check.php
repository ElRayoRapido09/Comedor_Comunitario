<?php
session_start();

// Verificar si la sesión existe y tiene el ID del usuario (ahora usando 'id' en lugar de 'id_usuario')
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header("Location: ../../inicio/index.html");
    exit();
}
?>