<?php
// Configuración inicial
header('Content-Type: application/json');
require_once 'database.php'; // Asegúrate de que esta ruta es correcta

// Verificar que se haya proporcionado la fecha
if (!isset($_GET['fecha'])) {
    echo json_encode(['error' => 'Fecha no proporcionada']);
    exit();
}

$fecha = $_GET['fecha'];

// Validar formato de fecha (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['error' => 'Formato de fecha inválido. Use YYYY-MM-DD']);
    exit();
}

try {
    // Conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // 1. Obtener información básica del menú del día
    $query_menu = "SELECT id_menu, fecha, precio, notas 
                   FROM menus_dia 
                   WHERE fecha = :fecha 
                   LIMIT 1";
    $stmt_menu = $db->prepare($query_menu);
    $stmt_menu->bindParam(':fecha', $fecha);
    $stmt_menu->execute();
    
    $menu_dia = $stmt_menu->fetch(PDO::FETCH_ASSOC);

    if (!$menu_dia) {
        echo json_encode(['error' => 'No existe menú para la fecha seleccionada']);
        exit();
    }

    // 2. Obtener todas las secciones disponibles
    $query_secciones = "SELECT s.id_seccion, s.nombre_seccion 
                        FROM secciones_menu s
                        WHERE s.activo = 1
                        ORDER BY s.orden";
    $stmt_secciones = $db->prepare($query_secciones);
    $stmt_secciones->execute();
    $secciones = $stmt_secciones->fetchAll(PDO::FETCH_ASSOC);

    // 3. Para cada sección, obtener sus platillos en el menú
    $menu_completo = [];
    foreach ($secciones as $seccion) {
        $query_platillos = "SELECT p.id_platillo, p.nombre_platillo, p.descripcion, 
                                   mp.disponible, p.es_vegetariano, p.es_vegano
                            FROM platillos p
                            JOIN menu_platillos mp ON p.id_platillo = mp.id_platillo
                            WHERE mp.id_menu = :id_menu 
                            AND p.id_seccion = :id_seccion
                            ORDER BY p.nombre_platillo";
        
        $stmt_platillos = $db->prepare($query_platillos);
        $stmt_platillos->bindParam(':id_menu', $menu_dia['id_menu']);
        $stmt_platillos->bindParam(':id_seccion', $seccion['id_seccion']);
        $stmt_platillos->execute();
        
        $platillos = $stmt_platillos->fetchAll(PDO::FETCH_ASSOC);

        if (count($platillos) > 0) {
            $menu_completo[] = [
                'nombre_seccion' => $seccion['nombre_seccion'],
                'platillos' => $platillos
            ];
        }
    }

    // 4. Preparar respuesta final
    $response = [
        'fecha' => $menu_dia['fecha'],
        'precio' => $menu_dia['precio'],
        'notas' => $menu_dia['notas'],
        'secciones' => $menu_completo
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener el menú']);
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    echo json_encode(['error' => 'Error inesperado']);
}
?>