<?php
session_start();

echo "<h2>Información de Sesión de Depuración</h2>";
echo "<pre>";

if (isset($_SESSION['usuario'])) {
    echo "Sesión activa: SÍ\n";
    echo "Datos del usuario:\n";
    print_r($_SESSION['usuario']);
} else {
    echo "Sesión activa: NO\n";
    echo "No hay datos de usuario en la sesión\n";
}

echo "\nTodas las variables de sesión:\n";
print_r($_SESSION);

echo "</pre>";
?>
