<?php
// Iniciar sesión y verificar autenticación
require_once 'session_check.php';
require_once 'conexion.php';

// Obtener ID del usuario de la sesión
$id_usuario = $_SESSION['usuario']['id'];

// Consulta para obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $datosUsuario = $result->fetch_assoc();
} else {
    die("Usuario no encontrado");
}

// Consulta para obtener la última reservación pendiente
$sqlReservacion = "SELECT codigo_reservacion, fecha_reservacion 
                   FROM reservaciones 
                   WHERE id_usuario = ? AND estado = 'pendiente'
                   ORDER BY fecha_reservacion DESC 
                   LIMIT 1";
$stmtReservacion = $conn->prepare($sqlReservacion);
$stmtReservacion->bind_param("i", $id_usuario);
$stmtReservacion->execute();
$resultReservacion = $stmtReservacion->get_result();
$reservacionPendiente = $resultReservacion->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario - Comedor Comunitario</title>
    <link rel="stylesheet" href="styleP.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo-container">
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-EOaIf34cLwcOlk71z2YxtJlOpQymdl.png" alt="Logo Comedor Comunitario" class="logo">
                <h1>Comedor Comunitario</h1>
            </div>
            
            <div class="header-right">
                <div class="date-display">
                    <span class="date-label">Fecha:</span>
                    <span class="date-value" id="current-date"></span>
                </div>
            </div>
        </div>
    </header>
    
    <main>
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-photo-container">
                    <div class="profile-photo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                </div>
                
                <nav class="profile-nav">
                    <a href="#" class="nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Información Personal
                    </a>
                </nav>
            </div>
            
            <div class="profile-content">
                <div class="content-header">
                    <h2>Mi Perfil</h2>
                </div>
                
                <div class="profile-sections">
                    <div class="profile-section">
                        <div class="section-header">
                            <h3>Información Personal</h3>
                        </div>
                        <div class="section-content">
                            <div class="profile-info">
                                <div class="info-group">
                                    <div class="info-item">
                                        <span class="info-label">Nombre Completo</span>
                                        <span class="info-value" id="user-name"><?php echo htmlspecialchars($datosUsuario['nombre'] . ' ' . $datosUsuario['apellidos']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Correo Electrónico</span>
                                        <span class="info-value" id="user-email"><?php echo htmlspecialchars($datosUsuario['correo']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="info-group">
                                    <div class="info-item">
                                        <span class="info-label">Edad</span>
                                        <span class="info-value" id="user-age"><?php echo htmlspecialchars($datosUsuario['edad']); ?> años</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Reservación Pendiente</span>
                                        <span class="info-value" id="user-reservation">
                                            <?php 
                                            if ($reservacionPendiente) {
                                                echo "Código: " . htmlspecialchars($reservacionPendiente['codigo_reservacion']) . 
                                                     " - Fecha: " . htmlspecialchars($reservacionPendiente['fecha_reservacion']);
                                            } else {
                                                echo "No tiene reservaciones pendientes";
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="info-group">
                                    <div class="info-item">
                                        <span class="info-label">Dirección</span>
                                        <span class="info-value" id="user-address"><?php echo htmlspecialchars($datosUsuario['direccion']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="info-group">
                                    <div class="info-item">
                                        <span class="info-label">Sexo</span>
                                        <span class="info-value" id="user-gender"><?php echo htmlspecialchars(ucfirst($datosUsuario['sexo'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-section">
                        <div class="section-header">
                            <h3>Código QR del Perfil</h3>
                        </div>
                        <div class="section-content qr-section">
                            <div class="qr-container">
                                <div class="qr-code" id="profile-qr">
                                    <!-- QR se generará aquí automáticamente -->
                                </div>
                                <p class="qr-description">Escanee este código QR para compartir su información de perfil</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-EOaIf34cLwcOlk71z2YxtJlOpQymdl.png" alt="Logo Comedor Comunitario" class="logo-small">
                <span>Comedor Comunitario</span>
            </div>
            <div class="footer-info">
                <p>Dirección: Calle Principal #123, Ciudad</p>
                <p>Teléfono: (123) 456-7890</p>
                <p>Horario: Lunes a Viernes 12:00 - 15:00</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Comedor Comunitario. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script src="scriptp.js"></script>
</body>
</html>