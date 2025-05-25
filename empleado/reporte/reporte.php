<?php
require_once '../session_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Comedor Comunitario</title>
    <link rel="stylesheet" href="styleRP.css">
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
                    <span class="date-value" id="current-date">16/04/2025</span>
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
                    <a href="reporte/reporte.html" class="nav-item active">
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
                    <h2>Reportes y Estadísticas</h2>
                    <div class="header-actions">
                        <div class="date-range-selector">
                            <label for="date-range">Periodo:</label>
                            <select id="date-range">
                                <option value="week">Última semana</option>
                                <option value="month" selected>Último mes</option>
                                <option value="quarter">Último trimestre</option>
                                <option value="year">Último año</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>
                        <div class="custom-date-range" id="custom-date-range" style="display: none;">
                            <input type="date" id="start-date" value="2025-03-16">
                            <span>a</span>
                            <input type="date" id="end-date" value="2025-04-16">
                        </div>
                        <button id="update-report-btn" class="primary-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                            Actualizar
                        </button>
                    </div>
                </div>
                
                <div class="report-tabs">
                    <button class="tab-btn active" data-tab="general">General</button>
                </div>
                
                <div class="report-content">
                    <!-- General Tab Content -->
                    <div class="tab-content active" id="general-tab">
                        <div class="report-summary">
                            <div class="summary-card">
                                <div class="summary-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <div class="summary-info">
                                    <h3>Total Beneficiarios</h3>
                                    <p class="summary-count">248</p>
                                    <p class="summary-trend positive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                            <polyline points="17 6 23 6 23 12"></polyline>
                                        </svg>
                                        +12% vs mes anterior
                                    </p>
                                </div>
                            </div>
                            
                            <div class="summary-card">
                                <div class="summary-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                                <div class="summary-info">
                                    <h3>Total Reservaciones</h3>
                                    <p class="summary-count">1,254</p>
                                    <p class="summary-trend positive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                            <polyline points="17 6 23 6 23 12"></polyline>
                                        </svg>
                                        +8% vs mes anterior
                                    </p>
                                </div>
                            </div>
                            
                            <div class="summary-card">
                                <div class="summary-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 3h18v18H3z"></path>
                                        <path d="M3 9h18"></path>
                                        <path d="M3 15h18"></path>
                                        <path d="M9 3v18"></path>
                                        <path d="M15 3v18"></path>
                                    </svg>
                                </div>
                                <div class="summary-info">
                                    <h3>Total Porciones</h3>
                                    <p class="summary-count">3,542</p>
                                    <p class="summary-trend positive">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                            <polyline points="17 6 23 6 23 12"></polyline>
                                        </svg>
                                        +15% vs mes anterior
                                    </p>
                                </div>
                            </div>
                            
                            <div class="summary-card">
                                <div class="summary-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <div class="summary-info">
                                    <h3>Costo Promedio</h3>
                                    <p class="summary-count">$10.75</p>
                                    <p class="summary-trend negative">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                                            <polyline points="17 18 23 18 23 12"></polyline>
                                        </svg>
                                        -3% vs mes anterior
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-charts">
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Reservaciones por Día</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="reservations-chart">
                                    <canvas id="reservations-by-day-chart"></canvas>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Distribución de Beneficiarios</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="users-chart">
                                    <canvas id="users-distribution-chart"></canvas>
                                </div>
                            </div>
                        </div>
                      

                        <div class="report-table-container">
                            <div class="table-header">
                                <h3>Resumen de Actividad Reciente</h3>
                               
                            </div>
                            <div class="table-container">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Reservaciones</th>
                                            <th>Porciones</th>
                                            <th>Completadas</th>
                                            <th>Canceladas</th>
                                            <th>Tasa de Asistencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>16/04/2025</td>
                                            <td>42</td>
                                            <td>118</td>
                                            <td>38</td>
                                            <td>4</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 90%"></div>
                                                    <span>90%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>15/04/2025</td>
                                            <td>38</td>
                                            <td>105</td>
                                            <td>35</td>
                                            <td>3</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 92%"></div>
                                                    <span>92%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>14/04/2025</td>
                                            <td>45</td>
                                            <td>132</td>
                                            <td>40</td>
                                            <td>5</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 89%"></div>
                                                    <span>89%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>13/04/2025</td>
                                            <td>36</td>
                                            <td>98</td>
                                            <td>34</td>
                                            <td>2</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 94%"></div>
                                                    <span>94%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>12/04/2025</td>
                                            <td>41</td>
                                            <td>115</td>
                                            <td>37</td>
                                            <td>4</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 90%"></div>
                                                    <span>90%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reservations Tab Content -->
                    <div class="tab-content" id="reservations-tab">
                        <div class="report-charts">
                            <div class="chart-container full-width">
                                <div class="chart-header">
                                    <h3>Tendencia de Reservaciones</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="reservations-trend-chart">
                                    <canvas id="reservations-trend-canvas"></canvas>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Reservaciones por Hora</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="reservations-by-hour-chart">
                                    <canvas id="reservations-by-hour-canvas"></canvas>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Estado de Reservaciones</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="reservations-status-chart">
                                    <canvas id="reservations-status-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-stats-cards">
                            <div class="stats-card">
                                <h4>Promedio Diario</h4>
                                <p class="stats-value">42</p>
                                <p class="stats-label">reservaciones</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Hora Pico</h4>
                                <p class="stats-value">13:00</p>
                                <p class="stats-label">más reservaciones</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Día Más Ocupado</h4>
                                <p class="stats-value">Miércoles</p>
                                <p class="stats-label">promedio semanal</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Tasa de Cancelación</h4>
                                <p class="stats-value">8.2%</p>
                                <p class="stats-label">del total</p>
                            </div>
                        </div>
                        
                        <div class="report-table-container">
                            <div class="table-header">
                                <h3>Reservaciones por Día de la Semana</h3>
                                <div class="table-actions">
                                    <button class="secondary-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                            <div class="table-container">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Día</th>
                                            <th>Reservaciones</th>
                                            <th>Porciones</th>
                                            <th>Promedio por Reservación</th>
                                            <th>% del Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Lunes</td>
                                            <td>38</td>
                                            <td>102</td>
                                            <td>2.7</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 15%"></div>
                                                    <span>15%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Martes</td>
                                            <td>42</td>
                                            <td>118</td>
                                            <td>2.8</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 17%"></div>
                                                    <span>17%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Miércoles</td>
                                            <td>48</td>
                                            <td>135</td>
                                            <td>2.8</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 19%"></div>
                                                    <span>19%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Jueves</td>
                                            <td>45</td>
                                            <td>126</td>
                                            <td>2.8</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 18%"></div>
                                                    <span>18%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Viernes</td>
                                            <td>46</td>
                                            <td>130</td>
                                            <td>2.8</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 18%"></div>
                                                    <span>18%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Sábado</td>
                                            <td>18</td>
                                            <td>45</td>
                                            <td>2.5</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 7%"></div>
                                                    <span>7%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Domingo</td>
                                            <td>15</td>
                                            <td>38</td>
                                            <td>2.5</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 6%"></div>
                                                    <span>6%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Menu Tab Content -->
                    <div class="tab-content" id="menu-tab">
                        <div class="report-charts">
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Platillos Más Populares</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="popular-dishes-chart">
                                    <canvas id="popular-dishes-canvas"></canvas>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Categorías de Platillos</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="dish-categories-chart">
                                    <canvas id="dish-categories-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-table-container">
                            <div class="table-header">
                                <h3>Rendimiento de Platillos</h3>
                                <div class="table-actions">
                                    <button class="secondary-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                            <div class="table-container">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Platillo</th>
                                            <th>Categoría</th>
                                            <th>Veces Servido</th>
                                            <th>Costo Promedio</th>
                                            <th>Popularidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Guisado de Res</td>
                                            <td>Plato Principal</td>
                                            <td>428</td>
                                            <td>$8.50</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 85%"></div>
                                                    <span>85%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Pollo en Mole</td>
                                            <td>Plato Principal</td>
                                            <td>385</td>
                                            <td>$9.20</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 78%"></div>
                                                    <span>78%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Sopa de Verduras</td>
                                            <td>Primer Tiempo</td>
                                            <td>356</td>
                                            <td>$3.75</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 72%"></div>
                                                    <span>72%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Arroz con Leche</td>
                                            <td>Postre</td>
                                            <td>312</td>
                                            <td>$2.50</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 65%"></div>
                                                    <span>65%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Agua de Jamaica</td>
                                            <td>Bebida</td>
                                            <td>298</td>
                                            <td>$1.25</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 60%"></div>
                                                    <span>60%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="report-stats-cards">
                            <div class="stats-card">
                                <h4>Platillo Más Popular</h4>
                                <p class="stats-value">Guisado de Res</p>
                                <p class="stats-label">428 porciones</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Categoría Preferida</h4>
                                <p class="stats-value">Plato Principal</p>
                                <p class="stats-label">48% de selecciones</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Costo Promedio</h4>
                                <p class="stats-value">$5.85</p>
                                <p class="stats-label">por platillo</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Rotación de Menú</h4>
                                <p class="stats-value">12</p>
                                <p class="stats-label">platillos nuevos/mes</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Users Tab Content -->
                    <div class="tab-content" id="users-tab">
                        <div class="report-charts">
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Nuevos Beneficiarios</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="new-users-chart">
                                    <canvas id="new-users-canvas"></canvas>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Distribución por Edad</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="age-distribution-chart">
                                    <canvas id="age-distribution-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-stats-cards">
                            <div class="stats-card">
                                <h4>Total Beneficiarios</h4>
                                <p class="stats-value">248</p>
                                <p class="stats-label">activos</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Nuevos este Mes</h4>
                                <p class="stats-value">32</p>
                                <p class="stats-label">beneficiarios</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Frecuencia Promedio</h4>
                                <p class="stats-value">2.8</p>
                                <p class="stats-label">visitas/semana</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Retención</h4>
                                <p class="stats-value">92%</p>
                                <p class="stats-label">tasa mensual</p>
                            </div>
                        </div>
                        
                        <div class="report-table-container">
                            <div class="table-header">
                                <h3>Beneficiarios Más Frecuentes</h3>
                                <div class="table-actions">
                                    <button class="secondary-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                            <div class="table-container">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Visitas</th>
                                            <th>Porciones</th>
                                            <th>Primera Visita</th>
                                            <th>Última Visita</th>
                                            <th>Frecuencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Juan Pérez</td>
                                            <td>24</td>
                                            <td>48</td>
                                            <td>02/01/2025</td>
                                            <td>16/04/2025</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 95%"></div>
                                                    <span>95%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>María Rodríguez</td>
                                            <td>22</td>
                                            <td>66</td>
                                            <td>15/01/2025</td>
                                            <td>15/04/2025</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 90%"></div>
                                                    <span>90%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Roberto Sánchez</td>
                                            <td>20</td>
                                            <td>40</td>
                                            <td>05/02/2025</td>
                                            <td>16/04/2025</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 85%"></div>
                                                    <span>85%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Ana Martínez</td>
                                            <td>19</td>
                                            <td>76</td>
                                            <td>10/01/2025</td>
                                            <td>14/04/2025</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 80%"></div>
                                                    <span>80%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Carlos Gómez</td>
                                            <td>18</td>
                                            <td>18</td>
                                            <td>20/01/2025</td>
                                            <td>15/04/2025</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 75%"></div>
                                                    <span>75%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Financial Tab Content -->
                    <div class="tab-content" id="financial-tab">
                        <div class="report-charts">
                            <div class="chart-container full-width">
                                <div class="chart-header">
                                    <h3>Costos vs Presupuesto</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="costs-budget-chart">
                                    <canvas id="costs-budget-canvas"></canvas>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Distribución de Gastos</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="expense-distribution-chart">
                                    <canvas id="expense-distribution-canvas"></canvas>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-header">
                                    <h3>Costo por Porción</h3>
                                    <div class="chart-actions">
                                        <button class="chart-action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart" id="cost-per-serving-chart">
                                    <canvas id="cost-per-serving-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-stats-cards">
                            <div class="stats-card">
                                <h4>Costo Total</h4>
                                <p class="stats-value">$38,075</p>
                                <p class="stats-label">último mes</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Costo por Porción</h4>
                                <p class="stats-value">$10.75</p>
                                <p class="stats-label">promedio</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Presupuesto Utilizado</h4>
                                <p class="stats-value">92%</p>
                                <p class="stats-label">del total</p>
                            </div>
                            
                            <div class="stats-card">
                                <h4>Ahorro Estimado</h4>
                                <p class="stats-value">$3,250</p>
                                <p class="stats-label">vs mes anterior</p>
                            </div>
                        </div>
                        
                        <div class="report-table-container">
                            <div class="table-header">
                                <h3>Resumen de Gastos</h3>
                                <div class="table-actions">
                                    <button class="secondary-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                            <div class="table-container">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Categoría</th>
                                            <th>Gasto</th>
                                            <th>Presupuesto</th>
                                            <th>Variación</th>
                                            <th>% del Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Ingredientes</td>
                                            <td>$24,850</td>
                                            <td>$25,000</td>
                                            <td class="positive-value">+$150</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 65%"></div>
                                                    <span>65%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Personal</td>
                                            <td>$8,500</td>
                                            <td>$8,000</td>
                                            <td class="negative-value">-$500</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 22%"></div>
                                                    <span>22%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Servicios</td>
                                            <td>$2,850</td>
                                            <td>$3,000</td>
                                            <td class="positive-value">+$150</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 7.5%"></div>
                                                    <span>7.5%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mantenimiento</td>
                                            <td>$1,200</td>
                                            <td>$1,500</td>
                                            <td class="positive-value">+$300</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 3%"></div>
                                                    <span>3%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Otros</td>
                                            <td>$675</td>
                                            <td>$1,000</td>
                                            <td class="positive-value">+$325</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 1.8%"></div>
                                                    <span>1.8%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td><strong>Total</strong></td>
                                            <td><strong>$38,075</strong></td>
                                            <td><strong>$38,500</strong></td>
                                            <td class="positive-value"><strong>+$425</strong></td>
                                            <td><strong>100%</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                    <button id="save-report-btn" class="primary-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Guardar Reporte
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <div id="export-modal" class="modal">
        <div class="modal-content small-modal">
            <span class="close-modal">&times;</span>
            <h2>Exportar Reporte</h2>
            
            <form id="export-form">
                <div class="form-group">
                    <label for="export-format">Formato:</label>
                    <select id="export-format">
                        <option value="xlsx">Excel (.xlsx)</option>
                        <option value="csv">CSV (.csv)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Incluir:</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="export-summary" checked>
                            <span class="checkbox-custom"></span>
                            Resumen General
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="export-charts" checked>
                            <span class="checkbox-custom"></span>
                            Datos de Gráficos
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="export-tables" checked>
                            <span class="checkbox-custom"></span>
                            Tablas de Datos
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="export-name">Nombre del archivo:</label>
                    <input type="text" id="export-name" value="Reporte_Comedor" placeholder="Nombre del archivo">
                </div>
                
                <div class="button-group">
                    <button type="button" class="cancel-button" id="cancel-export">Cancelar</button>
                    <button type="submit" class="submit-button">Exportar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="export-confirmation" class="toast">
        <div class="toast-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Reporte exportado correctamente</span>
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
    
    <script src="scriptRP.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
// Función para formatear fechas
function formatDate(date) {
    if (!(date instanceof Date)) {
        date = new Date(date);
    }
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// Mostrar notificación
function showToast(message) {
    const toast = document.getElementById("export-confirmation");
    toast.querySelector("span").textContent = message;
    toast.style.display = "block";
    setTimeout(() => {
        toast.style.display = "none";
    }, 3000);
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>
</body>
</html>
