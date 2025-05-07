<?php
// Configuración sin manejo de sesiones
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "comedor_comunitario";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

function obtenerPrimerUsuario($conn) {
    $sql = "SELECT * FROM usuarios LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}
?>