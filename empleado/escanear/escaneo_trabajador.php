<?php
require_once '../session_check.php';
if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">'.$_SESSION['success_message'].'</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">'.$_SESSION['error_message'].'</div>';
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escaneo QR - Comedor Comunitario</title>
    <link rel="stylesheet" href="escaneo_trabajador_style.css">
    <link rel="stylesheet" href="styleP.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap">
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
                    <span class="date-value" id="current-date"><?php echo date('d/m/Y'); ?></span>
                </div>
                
                <div class="user-menu">
                    <div class="user-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="dropdown-menu">
                        <div class="user-info">
                            <p class="user-name"><?php echo $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos']; ?></p>
                            <p class="user-role"><?php echo $_SESSION['usuario']['tipo_usuario'] === 'admin' ? 'Administrador' : 'Empleado'; ?></p>
                        </div>
                        <div class="menu-divider"></div>
                        <a href="../registro_empleado/registro_empleados.html">Registrar Empleado</a>
                        <a href="../pedidos/reservacionesEP.php">Reservaciones</a>
                        <a href="../reporte/reporte.php">Reportes</a>
                        <div class="menu-divider"></div>
                        <a href="../../inicio/index.html">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main>
        <div class="admin-container">
            <div class="admin-sidebar">
                <nav class="admin-nav">
                    <a href="../empleado.php" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h18v18H3z"></path>
                            <path d="M3 9h18"></path>
                            <path d="M3 15h18"></path>
                            <path d="M9 3v18"></path>
                            <path d="M15 3v18"></path>
                        </svg>
                        Menú
                    </a>
                    <a href="../pedidos/reservacionesEP.php" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Reservaciones
                    </a>
                    <a href="escaneo_trabajador.php" class="nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <rect x="7" y="7" width="3" height="3"></rect>
                            <rect x="14" y="7" width="3" height="3"></rect>
                            <rect x="7" y="14" width="3" height="3"></rect>
                            <rect x="14" y="14" width="3" height="3"></rect>
                        </svg>
                        Escaneo QR
                    </a>
                    <a href="../reporte/reporte.php" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        Reportes
                    </a>
                </nav>
            </div>
                
                <div class="qr-scanner-container">
                    <div class="scanner-section">
                        <div class="scanner-header">
                            <h3>Escanear Código QR del Usuario</h3>
                            <p>Coloque el código QR del usuario frente a la cámara para escanear automáticamente</p>
                        </div>
                        
                        <div class="scanner-wrapper">
                            <div class="scanner-frame">
                                <div id="qr-reader"></div>
                                <div class="scanner-corners">
                                    <div class="corner top-left"></div>
                                    <div class="corner top-right"></div>
                                    <div class="corner bottom-left"></div>
                                    <div class="corner bottom-right"></div>
                                </div>
                            </div>
                            
                            <div class="scanner-controls">
                                <button id="start-scan" class="primary-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                    </svg>
                                    Iniciar Cámara
                                </button>
                                <button id="switch-camera" class="secondary-btn" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 15h.01"></path>
                                        <path d="M11 19L2 12l9-7"></path>
                                        <path d="M21.5 12H4"></path>
                                    </svg>
                                    Cambiar Cámara
                                </button>
                            </div>
                            
                            <div class="scanner-status">
                                <div id="scan-status" class="status-indicator">
                                    <span class="status-dot"></span>
                                    <span class="status-text">Listo para escanear</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-header">
                            <h3>Detalles del Pedido</h3>
                            <div class="form-actions">
                                <button id="clear-form" class="text-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                    Limpiar
                                </button>
                            </div>
                        </div>
                        
                        <form id="reservation-form">
                            <div class="form-group">
                                <label for="codigo-reservacion">Código de Reservación</label>
                                <input type="text" id="codigo-reservacion" name="codigo-reservacion" required>
                                <input type="hidden" name="id_usuario_atendio" value="<?php echo $_SESSION['usuario']['id_usuario'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre Completo</label>
                                    <input type="text" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico</label>
                                    <input type="email" id="correo" name="correo">
                                </div>
                                
                            </div>
                            
                            
                            
                            <div class="form-actions-bottom">
                                <button type="button" id="cancel-btn" class="secondary-btn">Cancelar</button>
                                <button type="submit" id="saveBtn" class="primary-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                                    Completar Pedido
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <div id="success-modal" class="modal">
        <div class="modal-content small-modal">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h2>¡Pedido Completado!</h2>
            <p>El pedido se ha completado exitosamente.</p>
            <p class="reservation-code">Código: <span id="reservation-code">CC-2504-87</span></p>
            
            <div class="modal-actions">
                <button id="print-reservation" class="secondary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Imprimir Comprobante
                </button>
                <button id="new-reservation" class="primary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3h18v18H3z"></path>
                        <path d="M12 8v8"></path>
                        <path d="M8 12h8"></path>
                    </svg>
                    Nuevo Pedido
                </button>
            </div>
        </div>
    </div>
    
    <div id="status-notification" class="toast">
        <div class="toast-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span id="notification-message">Código QR escaneado correctamente</span>
        </div>
    </div>
    
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
    
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="escaneo_trabajador_script.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>