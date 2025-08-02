<?php
session_start();
$conn = new mysqli("localhost", "root", "", "form");

$usuario = $_POST['usuario'];
$clave = $_POST['clave'];

$stmt = $conn->prepare("SELECT id, contrasena, rol FROM usuarios WHERE nombre_usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($clave, $row['contrasena'])) {
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario_nombre'] = $usuario;
        $_SESSION['usuario_rol'] = $row['rol'];
        header("Location: ../dashboard.php");
        exit;
    }
}
echo "Credenciales incorrectas.";
?>
