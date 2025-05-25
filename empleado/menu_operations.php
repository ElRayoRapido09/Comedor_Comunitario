<?php
header('Content-Type: application/json');
require_once 'session_check.php';

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'comedor_comunitario';
$username = 'root';
$password = '12345';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_menu':
            $fecha = $_POST['fecha'];
            getMenu($conn, $fecha);
            break;
            
        case 'save_menu':
            $fecha = $_POST['fecha'];
            $precio = $_POST['precio'];
            $notas = $_POST['notas'];
            $secciones = json_decode($_POST['secciones'], true);
            saveMenu($conn, $fecha, $precio, $notas, $secciones, $_SESSION['usuario']['id']);
            break;
            
        case 'get_platillos':
            getPlatillos($conn);
            break;
            
        case 'add_platillo':
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $id_seccion = $_POST['id_seccion'];
            addPlatillo($conn, $nombre, $descripcion, $id_seccion);
            break;
            
        case 'update_platillo':
            $id_platillo = $_POST['id_platillo'];
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $disponible = $_POST['disponible'];
            updatePlatillo($conn, $id_platillo, $nombre, $descripcion, $disponible);
            break;
            
        case 'delete_platillo':
            $id_platillo = $_POST['id_platillo'];
            deletePlatillo($conn, $id_platillo);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}

function getMenu($conn, $fecha) {
    // Primero obtener todas las secciones existentes
    $stmt = $conn->query("SELECT * FROM secciones_menu WHERE activo = TRUE ORDER BY orden");
    $todasSecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar menú para la fecha específica
    $stmt = $conn->prepare("
        SELECT m.*, mp.id_platillo, p.nombre_platillo, p.descripcion, mp.disponible, s.id_seccion, s.nombre_seccion 
        FROM menus_dia m
        LEFT JOIN menu_platillos mp ON m.id_menu = mp.id_menu
        LEFT JOIN platillos p ON mp.id_platillo = p.id_platillo
        LEFT JOIN secciones_menu s ON p.id_seccion = s.id_seccion
        WHERE m.fecha = :fecha
        ORDER BY s.orden, p.nombre_platillo
    ");
    $stmt->bindParam(':fecha', $fecha);
    $stmt->execute();
    
    $menu = [
        'precio' => 0,
        'notas' => '',
        'secciones' => []
    ];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (empty($menu['precio'])) $menu['precio'] = $row['precio'];
        if (empty($menu['notas'])) $menu['notas'] = $row['notas'];
        
        if ($row['id_platillo']) {
            $seccion = $row['nombre_seccion'];
            if (!isset($menu['secciones'][$seccion])) {
                $menu['secciones'][$seccion] = [];
            }
            
            $menu['secciones'][$seccion][] = [
                'id' => $row['id_platillo'],
                'nombre' => $row['nombre_platillo'],
                'descripcion' => $row['descripcion'],
                'disponible' => (bool)$row['disponible'],
                'id_seccion' => $row['id_seccion']
            ];
        }
    }
    
    // Si no hay menú para esta fecha, mostrar todos los platillos activos
    if (empty($menu['secciones'])) {
        $stmt = $conn->query("
            SELECT p.id_platillo, p.nombre_platillo, p.descripcion, s.id_seccion, s.nombre_seccion 
            FROM platillos p
            JOIN secciones_menu s ON p.id_seccion = s.id_seccion
            WHERE p.activo = TRUE
            ORDER BY s.orden, p.nombre_platillo
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $seccion = $row['nombre_seccion'];
            if (!isset($menu['secciones'][$seccion])) {
                $menu['secciones'][$seccion] = [];
            }
            
            $menu['secciones'][$seccion][] = [
                'id' => $row['id_platillo'],
                'nombre' => $row['nombre_platillo'],
                'descripcion' => $row['descripcion'],
                'disponible' => true,
                'id_seccion' => $row['id_seccion']
            ];
        }
    }
    
    // Asegurarse de que todas las secciones aparezcan, incluso si están vacías
    foreach ($todasSecciones as $seccion) {
        if (!isset($menu['secciones'][$seccion['nombre_seccion']])) {
            $menu['secciones'][$seccion['nombre_seccion']] = [];
        }
    }
    
    echo json_encode(['success' => true, 'menu' => $menu]);
}

function saveMenu($conn, $fecha, $precio, $notas, $secciones, $id_usuario) {
    // Iniciar transacción
    $conn->beginTransaction();
    
    try {
        // Verificar si ya existe un menú para esta fecha
        $stmt = $conn->prepare("SELECT id_menu FROM menus_dia WHERE fecha = :fecha");
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $menu = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_menu = $menu['id_menu'];
            
            // Actualizar menú existente
            $stmt = $conn->prepare("
                UPDATE menus_dia 
                SET precio = :precio, notas = :notas, id_usuario_creador = :id_usuario 
                WHERE id_menu = :id_menu
            ");
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':notas', $notas);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':id_menu', $id_menu);
            $stmt->execute();
            
            // Eliminar platillos existentes
            $stmt = $conn->prepare("DELETE FROM menu_platillos WHERE id_menu = :id_menu");
            $stmt->bindParam(':id_menu', $id_menu);
            $stmt->execute();
        } else {
            // Crear nuevo menú
            $stmt = $conn->prepare("
                INSERT INTO menus_dia (fecha, precio, notas, id_usuario_creador)
                VALUES (:fecha, :precio, :notas, :id_usuario)
            ");
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':notas', $notas);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            $id_menu = $conn->lastInsertId();
        }
        
        // Insertar platillos del menú
        foreach ($secciones as $seccion => $platillos) {
            foreach ($platillos as $platillo) {
                $stmt = $conn->prepare("
                    INSERT INTO menu_platillos (id_menu, id_platillo, disponible)
                    VALUES (:id_menu, :id_platillo, :disponible)
                ");
                $stmt->bindParam(':id_menu', $id_menu);
                $stmt->bindParam(':id_platillo', $platillo['id']);
                $stmt->bindParam(':disponible', $platillo['disponible'], PDO::PARAM_BOOL);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al guardar el menú: ' . $e->getMessage()]);
    }
}

function getPlatillos($conn) {
    $stmt = $conn->query("
        SELECT p.*, s.nombre_seccion 
        FROM platillos p
        JOIN secciones_menu s ON p.id_seccion = s.id_seccion
        WHERE p.activo = TRUE
        ORDER BY s.orden, p.nombre_platillo
    ");
    
    $platillos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $platillos[] = [
            'id' => $row['id_platillo'],
            'nombre' => $row['nombre_platillo'],
            'descripcion' => $row['descripcion'],
            'seccion' => $row['nombre_seccion'],
            'seccion_id' => $row['id_seccion'],
            'disponible' => $row['activo']
        ];
    }
    
    echo json_encode(['success' => true, 'platillos' => $platillos]);
}

function addPlatillo($conn, $nombre, $descripcion, $id_seccion) {
    $stmt = $conn->prepare("
        INSERT INTO platillos (nombre_platillo, descripcion, id_seccion)
        VALUES (:nombre, :descripcion, :id_seccion)
    ");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':id_seccion', $id_seccion);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
}

function updatePlatillo($conn, $id_platillo, $nombre, $descripcion, $disponible) {
    $stmt = $conn->prepare("
        UPDATE platillos 
        SET nombre_platillo = :nombre, descripcion = :descripcion, activo = :disponible
        WHERE id_platillo = :id
    ");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':disponible', $disponible, PDO::PARAM_BOOL);
    $stmt->bindParam(':id', $id_platillo);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Menú guardado correctamente'
    ]);
}

function deletePlatillo($conn, $id_platillo) {
    // En lugar de eliminar, marcamos como inactivo
    $stmt = $conn->prepare("UPDATE platillos SET activo = FALSE WHERE id_platillo = :id");
    $stmt->bindParam(':id', $id_platillo);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
}
?>