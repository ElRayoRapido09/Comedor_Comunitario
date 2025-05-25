<?php
$host = 'localhost'; // o tu servidor de base de datos
$dbname = 'comedor_comunitario';
$username = 'root';
$password = '12345';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'error' => $e->getMessage()
    ]));
}
?>