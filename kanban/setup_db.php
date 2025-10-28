<?php
require_once('../agenda/db.php');

// Crear tabla para las secciones/categorías
$sql_sections = "CREATE TABLE IF NOT EXISTS kanban_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_sections) === TRUE) {
    echo "Tabla kanban_sections creada exitosamente<br>";
} else {
    echo "Error creando tabla kanban_sections: " . $conn->error . "<br>";
}

// Crear tabla para las tarjetas con referencia a sección
$sql_cards = "CREATE TABLE IF NOT EXISTS kanban_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    section_id INT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'todo',
    position INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (section_id) REFERENCES kanban_sections(id) ON DELETE CASCADE
)";

if ($conn->query($sql_cards) === TRUE) {
    echo "Tabla kanban_cards creada exitosamente<br>";
} else {
    echo "Error creando tabla kanban_cards: " . $conn->error . "<br>";
}

// Insertar algunas secciones por defecto si no existen
$default_sections = ['General', 'Desarrollo', 'Marketing'];
foreach ($default_sections as $index => $section) {
    $name = $conn->real_escape_string($section);
    $position = $index;
    $sql = "INSERT IGNORE INTO kanban_sections (name, position) VALUES ('$name', $position)";
    $conn->query($sql);
}

$conn->close();
?>