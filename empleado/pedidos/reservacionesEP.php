<?php
require_once '../session_check.php';
date_default_timezone_set('America/Mexico_City');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Reservaciones - Comedor Comunitario</title>
    <link rel="stylesheet" href="stylereservacionEP.css">
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
                        <a href="../registro_empleado/registro_empleados.html">Registrar Empleado</a>
                        <a href="reservacionesEP.php">Reservaciones</a>
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
                    <a href="reservacionesEP.php" class="nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Reservaciones
                    </a>
                    <a href="../escanear/escaneo_trabajador.php" class="nav-item">
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
            
            <div class="admin-content">
                <div class="content-header">
                    <h2>Administrar Reservaciones</h2>
                    <div class="header-actions">                        <div class="date-selector">                            <button id="prev-date" class="date-nav-btn" title="Día anterior (←)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <input type="date" id="reservations-date" value="<?php echo date('Y-m-d'); ?>" title="Seleccionar fecha">
                            <button id="next-date" class="date-nav-btn" title="Día siguiente (→)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>
                        
                        
                    </div>
                </div>
                
                <div class="reservations-filters">
                    <div class="search-container">
                        <input type="text" id="search-input" placeholder="Buscar por nombre o código...">
                        <button id="search-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status-filter">Estado:</label>
                        <select id="status-filter">
                            <option value="all">Todos</option>
                            <option value="pending">Pendiente</option>
                            <option value="completed">Completada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="time-filter">Hora:</label>
                        <select id="time-filter">
                            <option value="all">Todas</option>
                            <option value="12:00">12:00</option>
                            <option value="12:30">12:30</option>
                            <option value="13:00">13:00</option>
                            <option value="13:30">13:30</option>
                            <option value="14:00">14:00</option>
                            <option value="14:30">14:30</option>
                        </select>
                    </div>
                    
                    <button id="clear-filters" class="text-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5"></path>
                            <path d="M12 19l-7-7 7-7"></path>
                        </svg>
                        Limpiar filtros
                    </button>
                </div>
                
                <div class="reservations-summary">
                    <div class="summary-card">
                        <div class="summary-icon pending">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="summary-info">
                            <h3>Pendientes</h3>
                            <p class="summary-count"></p>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="summary-icon completed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="summary-info">
                            <h3>Completadas</h3>
                            <p class="summary-count"></p>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="summary-icon cancelled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="summary-info">
                            <h3>Canceladas</h3>
                            <p class="summary-count"></p>
                        </div>
                    </div>
                    
                    <div class="summary-card">
                        <div class="summary-icon total">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="summary-info">
                            <h3>Total Porciones</h3>
                            <p class="summary-count"></p>
                        </div>
                    </div>
                </div>
                
                <div class="reservations-table-container">
                    <table class="reservations-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="th-content">
                                        <span>Código</span>
                                        <button class="sort-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M7 15l5 5 5-5"></path>
                                                <path d="M7 9l5-5 5 5"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Nombre</span>
                                        <button class="sort-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M7 15l5 5 5-5"></path>
                                                <path d="M7 9l5-5 5 5"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Hora</span>
                                        <button class="sort-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M7 15l5 5 5-5"></path>
                                                <path d="M7 9l5-5 5 5"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Porciones</span>
                                        <button class="sort-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M7 15l5 5 5-5"></path>
                                                <path d="M7 9l5-5 5 5"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Menú</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Estado</span>
                                        <button class="sort-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M7 15l5 5 5-5"></path>
                                                <path d="M7 9l5-5 5 5"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            
                        </tbody>
                    </table>
                </div>
                
                <div class="pagination">
                    <button class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 17 6 12 11 7"></polyline>
                            <polyline points="18 17 13 12 18 7"></polyline>
                        </svg>
                    </button>
                    <button class="pagination-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <div class="pagination-pages">
                        <button class="pagination-page active">1</button>
                        <button class="pagination-page">2</button>
                        <button class="pagination-page">3</button>
                    </div>
                    <button class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                    <button class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"></polyline>
                            <polyline points="6 17 11 12 6 7"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <div id="reservation-details-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Detalles de Reservación</h2>
            
            <div class="reservation-details">
                <div class="detail-header">
                    <div class="detail-code">
                        <h3>Código de Reservación</h3>
                        <p class="code-value">CC-2504-87</p>
                    </div>
                    <div class="detail-status">
                        <span class="status-badge pending">Pendiente</span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Información del Beneficiario</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nombre:</span>
                            <span class="detail-value">Juan Pérez</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Teléfono:</span>
                            <span class="detail-value">(555) 123-4567</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Dirección:</span>
                            <span class="detail-value">Calle Principal #456</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Detalles de la Reservación</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Fecha:</span>
                            <span class="detail-value">16/04/2025</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Hora:</span>
                            <span class="detail-value">12:00</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Porciones:</span>
                            <span class="detail-value">2</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha de Registro:</span>
                            <span class="detail-value">15/04/2025 14:32</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Menú Seleccionado</h4>
                    <div class="menu-selection-details">
                        <div class="menu-selection-item">
                            <span class="menu-category">Primer Tiempo:</span>
                            <span class="menu-item-name">Sopa de Verduras</span>
                        </div>
                        <div class="menu-selection-item">
                            <span class="menu-category">Plato Principal:</span>
                            <span class="menu-item-name">Guisado de Res</span>
                        </div>
                        <div class="menu-selection-item">
                            <span class="menu-category">Bebida:</span>
                            <span class="menu-item-name">Agua de Jamaica</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Notas Adicionales</h4>
                    <p class="detail-notes">Sin cebolla en la sopa por favor. Gracias.</p>
                </div>
                
                <div class="detail-actions">
                    <button class="secondary-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        Imprimir
                    </button>
                    <button class="primary-btn complete-action">
                        <!-- SVG y texto se actualizarán dinámicamente -->
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="menu-details-modal" class="modal">
        <div class="modal-content small-modal">
            <span class="close-modal">&times;</span>
            <h2>Menú Seleccionado</h2>
            
            <div class="menu-details">
                <div class="menu-details-section">
                    <h4>Primer Tiempo</h4>
                    <p>Sopa de Verduras</p>
                    <p class="menu-description">Deliciosa sopa casera con zanahorias, calabacín, papa y hierbas frescas.</p>
                </div>
                
                <div class="menu-details-section">
                    <h4>Plato Principal</h4>
                    <p>Guisado de Res</p>
                    <p class="menu-description">Tiernos trozos de res en salsa de tomate con papas y zanahorias.</p>
                </div>
                
                <div class="menu-details-section">
                    <h4>Acompañamiento</h4>
                    <p>Arroz a la Mexicana</p>
                    <p class="menu-description">Arroz con verduras y especias mexicanas.</p>
                </div>
                
                <div class="menu-details-section">
                    <h4>Bebida</h4>
                    <p>Agua de Jamaica</p>
                    <p class="menu-description">Refrescante agua de jamaica natural.</p>
                </div>
            </div>
            
            <div class="modal-actions">
                <button id="close-menu-details" class="secondary-btn">Cerrar</button>
            </div>
        </div>
    </div>
    
    <div id="status-confirmation" class="toast">
        <div class="toast-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Estado de reservación actualizado</span>
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
    
    <script src="scriptreservacionEP.js?v=<?php echo time(); ?>"></script>
</body>
</html>
