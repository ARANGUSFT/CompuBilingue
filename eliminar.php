<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login/login.php");
    exit;
}

include 'conexion/cone.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("DELETE FROM inscripciones WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirige al dashboard con indicador de éxito
        header("Location: dashboard.php?eliminado=1");
        exit;
    } else {
        echo "❌ Error al eliminar el registro.";
    }
} else {
    echo "⚠️ Petición inválida.";
}
