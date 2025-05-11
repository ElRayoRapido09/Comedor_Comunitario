<?php
session_start();

// Verificar si el usuario está logueado y tiene la estructura correcta
if (!isset($_SESSION['usuario']) || 
    !isset($_SESSION['usuario']['id']) || 
    !isset($_SESSION['usuario']['nombre']) || 
    !isset($_SESSION['usuario']['apellidos']) || 
    !isset($_SESSION['usuario']['correo']) || 
    !isset($_SESSION['usuario']['tipo_usuario'])) {
    
    // Si no está logueado correctamente, redirigir al login
    header("Location: ../inicio/index.html");
    exit();
}

// Opcional: Verificar el tipo de usuario si es necesario
$allowed_types = ['usuario', 'admin']; // Ajusta según tus necesidades
if (!in_array($_SESSION['usuario']['tipo_usuario'], $allowed_types)) {
    header("Location: ../inicio/index.html?error=unauthorized");
    exit();
}
?>