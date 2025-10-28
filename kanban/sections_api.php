<?php
header('Content-Type: application/json');
require_once('../agenda/db.php');

// Obtener todas las secciones
function getSections() {
    global $conn;
    $sql = "SELECT * FROM kanban_sections ORDER BY position";
    $result = $conn->query($sql);
    $sections = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sections[] = $row;
        }
    }
    
    return $sections;
}

// Crear nueva sección
function createSection($data) {
    global $conn;
    $name = $conn->real_escape_string($data['name']);
    $position = (int)$data['position'];
    
    $sql = "INSERT INTO kanban_sections (name, position) VALUES ('$name', $position)";
    
    if ($conn->query($sql)) {
        return ['id' => $conn->insert_id, 'success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// Actualizar sección
function updateSection($id, $data) {
    global $conn;
    $updates = [];
    
    if (isset($data['name'])) {
        $name = $conn->real_escape_string($data['name']);
        $updates[] = "name = '$name'";
    }
    if (isset($data['position'])) {
        $position = (int)$data['position'];
        $updates[] = "position = $position";
    }
    
    if (empty($updates)) return ['success' => false, 'error' => 'No data to update'];
    
    $sql = "UPDATE kanban_sections SET " . implode(', ', $updates) . " WHERE id = $id";
    
    if ($conn->query($sql)) {
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// Eliminar sección
function deleteSection($id) {
    global $conn;
    // Las tarjetas se eliminarán automáticamente por la restricción ON DELETE CASCADE
    $sql = "DELETE FROM kanban_sections WHERE id = $id";
    
    if ($conn->query($sql)) {
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// Manejar peticiones
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        echo json_encode(getSections());
        break;
        
    case 'POST':
        echo json_encode(createSection($data));
        break;
        
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'No ID provided']);
            break;
        }
        echo json_encode(updateSection($id, $data));
        break;
        
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'No ID provided']);
            break;
        }
        echo json_encode(deleteSection($id));
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>