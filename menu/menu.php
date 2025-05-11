<?php
require_once 'database.php';

// Obtener fecha actual con verificación
$fecha_actual = date('Y-m-d');

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// 1. Primero verifica si existe menú para hoy
$query_menu = "SELECT id_menu, fecha, precio, notas 
               FROM menus_dia 
               WHERE fecha = :fecha 
               LIMIT 1";
$stmt_menu = $db->prepare($query_menu);
$stmt_menu->bindParam(':fecha', $fecha_actual);
$stmt_menu->execute();
$menu_dia = $stmt_menu->fetch(PDO::FETCH_ASSOC);

// 2. Si no hay menú para hoy, busca el más reciente
if (!$menu_dia) {
    $query_reciente = "SELECT id_menu, fecha, precio, notas 
                      FROM menus_dia 
                      WHERE fecha <= :fecha
                      ORDER BY fecha DESC 
                      LIMIT 1";
    $stmt_reciente = $db->prepare($query_reciente);
    $stmt_reciente->bindParam(':fecha', $fecha_actual);
    $stmt_reciente->execute();
    $menu_dia = $stmt_reciente->fetch(PDO::FETCH_ASSOC);
    
    // Si encontramos un menú reciente, actualizamos $fecha_actual
    if ($menu_dia) {
        $fecha_actual = $menu_dia['fecha'];
    }
}

// 3. Obtener secciones y platillos si hay menú
$secciones = [];
if ($menu_dia) {
    $query_secciones = "SELECT sm.id_seccion, sm.nombre_seccion, sm.descripcion
                       FROM secciones_menu sm
                       JOIN menu_platillos mp ON sm.id_seccion = mp.id_platillo
                       WHERE mp.id_menu = :id_menu
                       GROUP BY sm.id_seccion
                       ORDER BY sm.orden";
    
    $stmt_secciones = $db->prepare($query_secciones);
    $stmt_secciones->bindParam(':id_menu', $menu_dia['id_menu']);
    $stmt_secciones->execute();
    
    while ($seccion = $stmt_secciones->fetch(PDO::FETCH_ASSOC)) {
        $query_platillos = "SELECT p.id_platillo, p.nombre_platillo, p.descripcion, mp.disponible
                           FROM platillos p
                           JOIN menu_platillos mp ON p.id_platillo = mp.id_platillo
                           WHERE mp.id_menu = :id_menu AND p.id_seccion = :id_seccion
                           ORDER BY p.nombre_platillo";
        
        $stmt_platillos = $db->prepare($query_platillos);
        $stmt_platillos->bindParam(':id_menu', $menu_dia['id_menu']);
        $stmt_platillos->bindParam(':id_seccion', $seccion['id_seccion']);
        $stmt_platillos->execute();
        
        $platillos = $stmt_platillos->fetchAll(PDO::FETCH_ASSOC);
        $secciones[] = [
            'info' => $seccion,
            'platillos' => $platillos
        ];
    }
}

// Obtener menús de la semana (para la vista previa)
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));

$query_semana = "SELECT fecha, precio 
                 FROM menus_dia 
                 WHERE fecha BETWEEN :inicio_semana AND :fin_semana
                 ORDER BY fecha";
$stmt_semana = $db->prepare($query_semana);
$stmt_semana->bindParam(':inicio_semana', $inicio_semana);
$stmt_semana->bindParam(':fin_semana', $fin_semana);
$stmt_semana->execute();
$menus_semana = $stmt_semana->fetchAll(PDO::FETCH_ASSOC);

// Obtener información del usuario
$query_usuario = "SELECT nombre, apellidos FROM usuarios WHERE id_usuario = :id_usuario";
$stmt_usuario = $db->prepare($query_usuario);
$stmt_usuario->bindParam(':id_usuario', $_SESSION['usuario']['id_usuario']);
$stmt_usuario->execute();
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - Comedor Comunitario</title>
    <link rel="stylesheet" href="styleMN.css">
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
                
                <div class="user-menu" id="user-menu">
    <div class="user-icon" id="user-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
    </div>
    <span class="user-name"><?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?></span>
    <div class="dropdown-menu" id="dropdown-menu">
        <a href="perfil/perfil.php">Mi Perfil</a>
        <a href="logout.php">Cerrar Sesión</a>
    </div>
</div>
            </div>
        </div>
    </header>
    
    <main>
    <?php if ($menu_dia): ?>
        <div class="menu-container" id="menu-container">
            <div class="menu-header">
                <h2 class="menu-title">Menú del Día</h2>
                <div class="menu-price">$<?php echo htmlspecialchars($menu_dia['precio']); ?></div>
                <div class="menu-rating">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                </div>
            </div>
            
            <div class="menu-content" id="menu-content">
                <?php foreach ($secciones as $seccion): ?>
                <div class="menu-section">
                    <h3 class="section-title"><?php echo htmlspecialchars($seccion['info']['nombre_seccion']); ?></h3>
                    
                    <?php foreach ($seccion['platillos'] as $platillo): ?>
                    <div class="menu-item <?php echo $platillo['disponible'] ? 'available' : 'unavailable'; ?>">
                        <div class="item-info">
                            <h4 class="item-name"><?php echo htmlspecialchars($platillo['nombre_platillo']); ?></h4>
                            <p class="item-description"><?php echo htmlspecialchars($platillo['descripcion']); ?></p>
                        </div>
                        <div class="item-status">
                            <span class="status-indicator <?php echo $platillo['disponible'] ? 'available' : 'unavailable'; ?>"></span>
                            <span class="status-text"><?php echo $platillo['disponible'] ? 'Disponible' : 'Agotado'; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="menu-footer">
                <div class="menu-notes">
                    <?php if (!empty($menu_dia['notas'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($menu_dia['notas'])); ?></p>
                    <?php endif; ?>
                    <p>* Menú sujeto a disponibilidad.</p>
                </div>
                
                <div class="reservation-section">
                    <p class="reservation-info">Horario de servicio: 12:00 - 15:00</p>
                    <button id="reserve-button" class="reserve-button">Reservar Comida</button>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="menu-container">
            <div class="menu-header">
                <h2 class="menu-title">Menú del Día</h2>
                <p>No hay menú disponible para hoy.</p>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="weekly-menu-preview">
    <h3>Menú de la Semana</h3>
    <div class="days-container">
        <?php 
        $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $hoy = date('Y-m-d');
        $fecha_mostrada = $menu_dia ? $menu_dia['fecha'] : $hoy;
        
        foreach ($menus_semana as $menu): 
            $fecha = new DateTime($menu['fecha']);
            $dia_semana = $fecha->format('w');
            $es_hoy = ($menu['fecha'] == $hoy);
            $es_mostrado = ($menu['fecha'] == $fecha_mostrada);
            
            $nombre_dia = $dias_semana[$dia_semana];
        ?>
        <div class="day-item <?php echo ($es_hoy || $es_mostrado) ? 'active' : ''; ?>" 
             data-fecha="<?php echo $menu['fecha']; ?>"
             data-dia-semana="<?php echo $dia_semana; ?>">
            <div class="day-name"><?php echo $es_hoy ? 'Hoy' : $nombre_dia; ?></div>
            <div class="day-date"><?php echo $fecha->format('d/m'); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <a href="menu-semanal.php" class="view-all-link">Ver menú completo</a>
</div>
    </main>
    
    <!-- Los modales (reservation-modal y confirmation-modal) permanecen igual -->
    
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
            <p>&copy; <?php echo date('Y'); ?> Comedor Comunitario. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script src="scriptMN.js?v=<?php echo time(); ?>"></script>
    <script>
        // Pasar datos iniciales al JavaScript
        const menuInicial = {
            fecha: '<?php echo $fecha_actual; ?>',
            precio: <?php echo $menu_dia ? $menu_dia['precio'] : '0'; ?>,
            notas: `<?php echo $menu_dia ? addslashes($menu_dia['notas']) : ''; ?>`,
            secciones: <?php echo json_encode($secciones); ?>
        };
    </script>
</body>
</html>