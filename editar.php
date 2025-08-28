<?php
// Iniciar sesión para CSRF token
session_start();

// Función para validar y sanitizar datos
function validarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Array para almacenar errores
$errores = [];

// Verificar token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF inválido");
    }
}

// Validar que exista el ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("ID de inscripción inválido");
}
$id = (int)$_POST['id'];

// Validación de campos obligatorios
$camposObligatorios = [
    'nombres_estudiante' => 'Nombres del estudiante',
    'horario' => 'Horario',
    'doc_type' => 'Tipo de documento',
    'numero_documento' => 'Número de documento',
    'email' => 'Email',
    'celular1' => 'Celular principal',
    'reg_type' => 'Tipo de registro',
];

foreach ($camposObligatorios as $campo => $nombre) {
    if (empty($_POST[$campo])) {
        $errores[$campo] = "El campo $nombre es obligatorio";
    }
}

// Validación de foto (opcional en edición)
if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $tipoArchivo = $_FILES["foto"]["type"];
    if (!in_array($tipoArchivo, $permitidos)) {
        $errores['foto'] = "Solo se permiten imágenes JPEG, PNG o GIF";
    }
    
    $tamanoMaximo = 2 * 1024 * 1024; // 2MB
    if ($_FILES["foto"]["size"] > $tamanoMaximo) {
        $errores['foto'] = "La imagen no debe superar los 2MB";
    }
}

// Validación de email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errores['email'] = "El email no tiene un formato válido";
}

// Validación de números
if (!preg_match('/^[0-9]{6,10}$/', $_POST['numero_documento'])) {
    $errores['numero_documento'] = "El número de documento debe contener entre 6 y 10 dígitos";
}

if (!preg_match('/^[0-9]{10}$/', $_POST['celular1'])) {
    $errores['celular1'] = "El celular debe tener 10 dígitos";
}

if (!empty($_POST['celular2']) && !preg_match('/^[0-9]{10}$/', $_POST['celular2'])) {
    $errores['celular2'] = "El celular secundario debe tener 10 dígitos";
}

if (count($errores) > 0) {
    $_SESSION['errores_edicion'] = $errores;
    header("Location: listado.php?editar=$id");
    exit;
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "form");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos actuales para comparar y manejar la foto
$sqlActual = "SELECT foto FROM inscripciones WHERE id = ?";
$stmtActual = $conn->prepare($sqlActual);
$stmtActual->bind_param("i", $id);
$stmtActual->execute();
$resultadoActual = $stmtActual->get_result();
$filaActual = $resultadoActual->fetch_assoc();
$stmtActual->close();

$fotoRutaParaBD = $filaActual['foto']; // Mantener la misma foto por defecto

// Manejo de la foto (solo si se subió una nueva)
if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
    $uploadDir = realpath(__DIR__ . '/uploads');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Eliminar foto anterior si existe
    if (!empty($filaActual['foto'])) {
        $fotoAnterior = realpath(__DIR__ . '/../' . $filaActual['foto']);
        if (file_exists($fotoAnterior)) {
            unlink($fotoAnterior);
        }
    }
    
    $fotoNombre = time() . '_' . basename($_FILES["foto"]["name"]);
    $fotoRutaCompleta = $uploadDir . '/' . $fotoNombre;
    $fotoRutaParaBD = 'uploads/' . $fotoNombre;

    if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $fotoRutaCompleta)) {
        die("Error al subir la foto");
    }
}

// Sanitizar y recolectar datos del formulario
$nombres = validarDatos($_POST['nombres_estudiante']);
$horario = validarDatos($_POST['horario']);
$doc_type = validarDatos($_POST['doc_type']);
$numero_documento = validarDatos($_POST['numero_documento']);
$email = validarDatos($_POST['email']);
$celular1 = validarDatos($_POST['celular1']);
$celular2 = validarDatos($_POST['celular2']);
$reg_type = validarDatos($_POST['reg_type']);
$created_at = validarDatos($_POST['created_at']);

// Preparar consulta SQL con sentencias preparadas
$sql = "UPDATE inscripciones SET 
    foto = ?,
    nombres_estudiante = ?,
    horario = ?,
    doc_type = ?,
    numero_documento = ?,
    email = ?,
    celular1 = ?,
    celular2 = ?,
    reg_type = ?,
    created_at = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la preparación: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("ssssssssssi",
    $fotoRutaParaBD,
    $nombres,
    $horario,
    $doc_type,
    $numero_documento,
    $email,
    $celular1,
    $celular2,
    $reg_type,
    $created_at,
    $id
);

// Ejecutar consulta
if ($stmt->execute()) {
    // Regenerar token CSRF para el próximo formulario
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // Redireccionar con mensaje de éxito
    $_SESSION['mensaje_exito'] = "Los cambios se guardaron correctamente";
    header("Location: dashboard.php");
    exit;
} else {
    // Manejar error
    $_SESSION['error_edicion'] = "Error al actualizar: " . $stmt->error;
    header("Location: dashboard.php?editar=$id");
    exit;
}

$stmt->close();
$conn->close();
?>