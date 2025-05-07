<?php
require 'config.php';

$test_email = "itanxolalpa2@gmail.com"; // Email de prueba
$asunto = "Prueba de correo";
$mensaje = "<h1>Hola nalgon</h1><p>Esta es una prueba del sistema de correo.</p>";

if(enviarCorreo($test_email, $asunto, $mensaje)) {
    echo "Correo enviado correctamente a $test_email";
} else {
    echo "Error al enviar el correo. Revisa los logs del servidor.";
}
?>