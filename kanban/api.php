<?php
header('Content-Type: application/json');
require_once('../agenda/db.php');

// Obtener todas las tarjetas
function getCards() {
    global $conn;
    $sql = "SELECT * FROM kanban_cards ORDER BY position";
    $result = $conn->query($sql);
    $cards = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cards[] = $row;
        }
    }
    
    return $cards;
}

// Crear nueva tarjeta
function createCard($data) {
    global $conn;
    $title = $conn->real_escape_string($data['title']);
    $description = $conn->real_escape_string($data['description'] ?? '');
    $status = $conn->real_escape_string($data['status']);
    $section_id = (int)$data['section_id'];
    $position = (int)$data['position'];
    
    $sql = "INSERT INTO kanban_cards (title, description, status, section_id, position) 
            VALUES ('$title', '$description', '$status', $section_id, $position)";
    
    if ($conn->query($sql)) {
        return ['id' => $conn->insert_id, 'success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// Actualizar tarjeta
function updateCard($id, $data) {
    global $conn;
    $updates = [];
    
    if (isset($data['title'])) {
        $title = $conn->real_escape_string($data['title']);
        $updates[] = "title = '$title'";
    }
    if (isset($data['description'])) {
        $description = $conn->real_escape_string($data['description']);
        $updates[] = "description = '$description'";
    }
    if (isset($data['status'])) {
        $status = $conn->real_escape_string($data['status']);
        $updates[] = "status = '$status'";
    }
    if (isset($data['position'])) {
        $position = (int)$data['position'];
        $updates[] = "position = $position";
    }
    
    if (empty($updates)) return ['success' => false, 'error' => 'No data to update'];
    
    $sql = "UPDATE kanban_cards SET " . implode(', ', $updates) . " WHERE id = $id";
    
    if ($conn->query($sql)) {
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// Eliminar tarjeta
function deleteCard($id) {
    global $conn;
    $sql = "DELETE FROM kanban_cards WHERE id = $id";
    
    if ($conn->query($sql)) {
        return ['success' => true];
    }
    return ['success' => false, 'error' => $conn->error];
}

// Manejar peticiones
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

switch ($method) {
    case 'GET':
        echo json_encode(getCards());
        break;
        
    case 'POST':
        $result = createCard($data);
        if (!is_array($result)) {
            header('Content-Type: text/plain');
            echo "RAW OUTPUT: ".$result;
        } else {
            echo json_encode($result);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'No ID provided']);
            break;
        }
        echo json_encode(updateCard($id, $data));
        break;
        
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'No ID provided']);
            break;
        }
        echo json_encode(deleteCard($id));
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>