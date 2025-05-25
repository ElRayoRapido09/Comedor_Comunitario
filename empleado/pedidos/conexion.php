<?php
$servername = 'bdhfuadlo1hz1ht8dfxp-mysql.services.clever-cloud.com';
$username = 'bdhfuadlo1hz1ht8dfxp';
$password = 'uzcgof6xqfxne5xg';
$dbname = 'NcvzgSUbiBtmGEYnheBv';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>