<?php
$host = "localhost";
$user = "root"; // Usuario de MySQL
$pass = "";     // Contraseña de MySQL (en XAMPP suele estar vacía)
$db = "agenda_db";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
