<?php
require_once 'session_check.php';
date_default_timezone_set('America/Mexico_City');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Menú - Comedor Comunitario</title>
    <link rel="stylesheet" href="styleEP.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap">
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
        <a href="registro_empleado/registro_empleados.html">Registrar Empleado</a>
        <a href="pedidos/reservacionesEP.php">Reservaciones</a>
        <a href="reporte/reporte.php">Reportes</a>
        <div class="menu-divider"></div>
        <a href="../inicio/index.html">Cerrar Sesión</a>
    </div>
</div>
            </div>
        </div>
    </header>
    
    <main>
        <div class="admin-container">
            <div class="admin-sidebar">
                <nav class="admin-nav">
                    <a href="empleado.php" class="nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h18v18H3z"></path>
                            <path d="M3 9h18"></path>
                            <path d="M3 15h18"></path>
                            <path d="M9 3v18"></path>
                            <path d="M15 3v18"></path>
                        </svg>
                        Menú
                    </a>
                    <a href="pedidos/reservacionesEP.php" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Reservaciones
                    </a>
                    <a href="escanear/escaneo_trabajador.php" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <rect x="7" y="7" width="3" height="3"></rect>
                            <rect x="14" y="7" width="3" height="3"></rect>
                            <rect x="7" y="14" width="3" height="3"></rect>
                            <rect x="14" y="14" width="3" height="3"></rect>
                        </svg>
                        Escaneo QR
                    </a>
                    <a href="reporte/reporte.php" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        Reportes
                    </a>
                </nav>
            </div>
            
            <div class="admin-content">
                <div class="content-header">
                    <h2>Administrar Menú</h2>
                    <div class="header-actions">
                        <div class="date-selector">
                            <input type="date" id="menu-date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="menu-editor">
                    <div class="menu-header">
                        <h2 class="menu-title">Menú del Día</h2>
                        <div class="menu-date-editor">
                            <label for="menu-price">Precio:</label>
                            <div class="price-input">
                                <span class="currency">$</span>
                                <input type="number" id="menu-price" value="11" min="0" step="0.5">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenedor principal donde se generarán las secciones dinámicamente -->
                    <div class="menu-sections">
                        <!-- Las secciones con platillos se generarán aquí mediante JavaScript -->
                        <button id="add-section-btn" class="add-section-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Añadir Nueva Sección
                        </button>
                    </div>
                    
                    <div class="menu-notes-editor">
                        <h3>Notas del Menú</h3>
                        <textarea id="menu-notes" placeholder="Añadir notas sobre el menú (ej. todos los platillos incluyen tortillas)"></textarea>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button id="preview-btn" class="secondary-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Vista Previa
                    </button>
                    <button id="save-btn" class="primary-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal para vista previa -->
    <div id="preview-modal" class="modal">
        <div class="modal-content preview-modal-content">
            <span class="close-modal">&times;</span>
            <h2>Vista Previa del Menú</h2>
            
            <div class="menu-preview" id="menu-preview-container">
                <!-- El contenido de la vista previa se generará dinámicamente -->
            </div>
            
            <div class="modal-actions">
                <button id="close-preview" class="secondary-btn">Cerrar</button>
            </div>
        </div>
    </div>
    
    <!-- Modal para añadir nueva sección -->
    <div id="add-section-modal" class="modal">
        <div class="modal-content small-modal">
            <span class="close-modal">&times;</span>
            <h2>Añadir Nueva Sección</h2>
            
            <form id="add-section-form">
                <div class="form-group">
                    <label for="section-name">Nombre de la Sección</label>
                    <input type="text" id="section-name" placeholder="Ej. Postres, Ensaladas, etc." required>
                </div>
                
                <div class="button-group">
                    <button type="button" class="cancel-button" id="cancel-add-section">Cancelar</button>
                    <button type="submit" class="submit-button">Añadir</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para añadir nuevo platillo -->
    <div id="add-item-modal" class="modal">
        <div class="modal-content small-modal">
            <span class="close-modal">&times;</span>
            <h2>Añadir Nuevo Platillo</h2>
            
            <form id="add-item-form">
                <div class="form-group">
                    <label for="item-name">Nombre del Platillo</label>
                    <input type="text" id="item-name" placeholder="Ej. Arroz con Leche" required>
                </div>
                
                <div class="form-group">
                    <label for="item-description">Descripción</label>
                    <textarea id="item-description" placeholder="Descripción del platillo"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="item-available" checked>
                        <span class="checkbox-custom"></span>
                        Disponible
                    </label>
                </div>
                
                <input type="hidden" id="section-id" value="">
                
                <div class="button-group">
                    <button type="button" class="cancel-button" id="cancel-add-item">Cancelar</button>
                    <button type="submit" class="submit-button">Añadir</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Toast para notificaciones -->
    <div id="save-confirmation" class="toast">
        <div class="toast-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span id="toast-message">Menú guardado correctamente</span>
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
    
    <script src="scriptEP.js?v=<?php echo time(); ?>"></script>
</body>
</html>