<?php
$host = 'localhost';       // Cambia si usas otro host
$user = 'root';            // Tu usuario de MySQL
$pass = '';                // Tu contraseña de MySQL
$dbname = 'incripcion'; // Nombre de la base de datos

$conn = new mysqli($host, $user, $pass, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

// Opcional: mensaje si todo va bien
// echo "✅ Conexión exitosa";
?>
