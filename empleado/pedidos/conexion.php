<?php
$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "comedor_comunitario";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>