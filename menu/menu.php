<?php
session_start();

// Verificación de sesión mejorada
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header("Location: ../inicio/index.php?error=session_required");
    exit();
}

require_once 'database.php';

// Configuración de fecha
$fecha_actual = date('Y-m-d');
$database = new Database();
$db = $database->getConnection();

// 1. Obtener menú del día actual
$query_menu = "SELECT id_menu, fecha, precio, notas FROM menus_dia WHERE fecha = :fecha LIMIT 1";
$stmt_menu = $db->prepare($query_menu);
$stmt_menu->bindParam(':fecha', $fecha_actual);
$stmt_menu->execute();
$menu_dia = $stmt_menu->fetch(PDO::FETCH_ASSOC);

// 2. Si no hay menú hoy, buscar el más reciente
if (!$menu_dia) {
    $query_reciente = "SELECT id_menu, fecha, precio, notas FROM menus_dia WHERE fecha <= :fecha ORDER BY fecha DESC LIMIT 1";
    $stmt_reciente = $db->prepare($query_reciente);
    $stmt_reciente->bindParam(':fecha', $fecha_actual);
    $stmt_reciente->execute();
    $menu_dia = $stmt_reciente->fetch(PDO::FETCH_ASSOC);
    
    if ($menu_dia) {
        $fecha_actual = $menu_dia['fecha'];
    }
}

// 3. Obtener secciones y platillos
$secciones = [];
if ($menu_dia) {
    // Obtener secciones activas
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
        // Obtener platillos por sección
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

// 4. Obtener menús de la semana para la vista previa
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));

$query_semana = "SELECT fecha, precio FROM menus_dia WHERE fecha BETWEEN :inicio_semana AND :fin_semana ORDER BY fecha";
$stmt_semana = $db->prepare($query_semana);
$stmt_semana->bindParam(':inicio_semana', $inicio_semana);
$stmt_semana->bindParam(':fin_semana', $fin_semana);
$stmt_semana->execute();
$menus_semana = $stmt_semana->fetchAll(PDO::FETCH_ASSOC);

// Información del usuario desde sesión
$usuario = $_SESSION['usuario'];
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
                    <span class="date-value" id="current-date"><?= date('d/m/Y') ?></span>
                </div>
                
                <div class="user-menu" id="user-menu">
                    <div class="user-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <span class="user-name"><?= htmlspecialchars($usuario['nombre']) ?></span>
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
                    <div class="menu-price">$<?= htmlspecialchars($menu_dia['precio']) ?></div>
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
                        <h3 class="section-title" data-seccion-id="<?= $seccion['info']['id_seccion'] ?>">
                            <?= htmlspecialchars($seccion['info']['nombre_seccion']) ?>
                        </h3>
                        
                        <?php foreach ($seccion['platillos'] as $platillo): ?>
                            <div class="menu-item <?= $platillo['disponible'] ? 'available' : 'unavailable' ?>">
                                <div class="item-info">
                                    <h4 class="item-name">
                                        <input type="radio" 
                                            name="seccion_<?= $seccion['info']['id_seccion'] ?>" 
                                            value="<?= $platillo['id_platillo'] ?>"
                                            class="platillo-radio"
                                            data-seccion="<?= $seccion['info']['id_seccion'] ?>"
                                            <?= !$platillo['disponible'] ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($platillo['nombre_platillo']) ?>
                                    </h4>
                                    <p class="item-description"><?= htmlspecialchars($platillo['descripcion']) ?></p>
                                </div>
                                <div class="item-status">
                                    <span class="status-indicator <?= $platillo['disponible'] ? 'available' : 'unavailable' ?>"></span>
                                    <span class="status-text"><?= $platillo['disponible'] ? 'Disponible' : 'Agotado' ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="menu-footer">
                    <div class="menu-notes">
                        <?php if (!empty($menu_dia['notas'])): ?>
                        <p><?= nl2br(htmlspecialchars($menu_dia['notas'])) ?></p>
                        <?php endif; ?>
                        <p>* Menú sujeto a disponibilidad.</p>
                    </div>
                    
                    <div class="reservation-section">
                        <p class="reservation-info">Horario de servicio: 12:00 - 15:00</p>
                        <button id="reserve-button" class="reserve-button" type="button">Reservar Comida</button>
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
            <p>&copy; <?= date('Y') ?> Comedor Comunitario. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script>
        // Datos iniciales para JavaScript
        window.menuInicial = {
            id_usuario: <?= $usuario['id'] ?>,
            id_menu: <?= isset($menu_dia['id_menu']) ? $menu_dia['id_menu'] : 'null' ?>,
            fecha: '<?= isset($fecha_actual) ? $fecha_actual : date('Y-m-d') ?>',
            precio: <?= isset($menu_dia['precio']) ? $menu_dia['precio'] : 0 ?>,
            notas: `<?= isset($menu_dia['notas']) ? addslashes($menu_dia['notas']) : '' ?>`,
            secciones: <?= isset($secciones) ? json_encode($secciones) : '[]' ?>
        };
    </script>
    <script src="scriptMN.js"></script>
</body>
</html>