<?php
header('Content-Type: application/json');
session_start();

// Verificación de sesión mejorada
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    echo json_encode(['error' => 'Acceso no autorizado', 'code' => 401]);
    exit();
}

// Validación de parámetros
if (!isset($_GET['fecha'])) {
    echo json_encode(['error' => 'Parámetro "fecha" requerido', 'code' => 400]);
    exit();
}

$fecha = $_GET['fecha'];

// Validación de formato de fecha (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['error' => 'Formato de fecha inválido. Use YYYY-MM-DD', 'code' => 400]);
    exit();
}

require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Obtener información básica del menú
    $query_menu = "SELECT 
                    md.id_menu, 
                    md.fecha, 
                    md.precio, 
                    md.notas,
                    COUNT(mp.id_platillo) as total_platillos
                   FROM menus_dia md
                   LEFT JOIN menu_platillos mp ON md.id_menu = mp.id_menu
                   WHERE md.fecha = :fecha
                   GROUP BY md.id_menu
                   LIMIT 1";
    
    $stmt_menu = $db->prepare($query_menu);
    $stmt_menu->bindParam(':fecha', $fecha);
    $stmt_menu->execute();
    
    $menu_dia = $stmt_menu->fetch(PDO::FETCH_ASSOC);

    if (!$menu_dia || $menu_dia['total_platillos'] == 0) {
        echo json_encode([
            'error' => 'No existe menú disponible para la fecha seleccionada',
            'code' => 404,
            'fecha' => $fecha
        ]);
        exit();
    }

    // 2. Obtener todas las secciones activas
    $query_secciones = "SELECT 
                        sm.id_seccion, 
                        sm.nombre_seccion,
                        sm.icono
                       FROM secciones_menu sm
                       WHERE sm.activo = 1
                       ORDER BY sm.orden";
    
    $stmt_secciones = $db->prepare($query_secciones);
    $stmt_secciones->execute();
    $secciones = $stmt_secciones->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener platillos por sección
    $menu_completo = [];
    foreach ($secciones as $seccion) {
        $query_platillos = "SELECT 
                            p.id_platillo, 
                            p.nombre_platillo, 
                            p.descripcion, 
                            mp.disponible,
                            p.es_vegetariano,
                            p.es_vegano,
                            p.contiene_gluten,
                            p.calorias
                           FROM platillos p
                           JOIN menu_platillos mp ON p.id_platillo = mp.id_platillo
                           WHERE mp.id_menu = :id_menu 
                           AND p.id_seccion = :id_seccion
                           ORDER BY p.orden, p.nombre_platillo";
        
        $stmt_platillos = $db->prepare($query_platillos);
        $stmt_platillos->bindParam(':id_menu', $menu_dia['id_menu']);
        $stmt_platillos->bindParam(':id_seccion', $seccion['id_seccion']);
        $stmt_platillos->execute();
        
        $platillos = $stmt_platillos->fetchAll(PDO::FETCH_ASSOC);

        if (count($platillos) > 0) {
            $menu_completo[] = [
                'id_seccion' => $seccion['id_seccion'],
                'nombre_seccion' => $seccion['nombre_seccion'],
                'icono' => $seccion['icono'] ?? '🍽️',
                'platillos' => $platillos
            ];
        }
    }

    // 4. Preparar respuesta final
    $response = [
        'success' => true,
        'id_menu' => $menu_dia['id_menu'],
        'fecha' => $menu_dia['fecha'],
        'precio' => (float)$menu_dia['precio'],
        'notas' => $menu_dia['notas'],
        'secciones' => $menu_completo,
        'meta' => [
            'total_secciones' => count($menu_completo),
            'total_platillos' => (int)$menu_dia['total_platillos'],
            'fecha_consulta' => date('Y-m-d H:i:s')
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error al obtener el menú',
        'detalle' => $e->getMessage(),
        'code' => 500
    ]);
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error inesperado',
        'detalle' => $e->getMessage(),
        'code' => 500
    ]);
}
?>