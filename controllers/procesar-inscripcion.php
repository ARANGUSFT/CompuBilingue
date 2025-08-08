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

// Validación de campos obligatorios
$camposObligatorios = [
    'nombres_estudiante' => 'Nombres del estudiante',
    'horario' => 'Horario',
    'estrato_socioeconomico' => 'Estrato socioeconómico',
    'eps' => 'EPS',
    'nivel_escolaridad' => 'Nivel de escolaridad',
    'doc_type' => 'Tipo de documento',
    'numero_documento' => 'Número de documento',
    'email' => 'Email',
    'municipio_residencia' => 'Municipio de residencia',
    'direccion_residencia' => 'Dirección de residencia',
    'celular1' => 'Celular principal',
    'nombre_acudiente' => 'Nombre del acudiente',
    'contacto_acudiente' => 'Contacto del acudiente',
    'reg_type' => 'Tipo de registro'
];

foreach ($camposObligatorios as $campo => $nombre) {
    if (empty($_POST[$campo])) {
        $errores[$campo] = "El campo $nombre es obligatorio";
    }
}

// Validación de foto
if (!isset($_FILES["photoUpload"]) || $_FILES["photoUpload"]["error"] != UPLOAD_ERR_OK) {
    $errores['photoUpload'] = "Debe subir una foto";
} else {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $tipoArchivo = $_FILES["photoUpload"]["type"];
    if (!in_array($tipoArchivo, $permitidos)) {
        $errores['photoUpload'] = "Solo se permiten imágenes JPEG, PNG o GIF";
    }
    
    $tamanoMaximo = 2 * 1024 * 1024; // 2MB
    if ($_FILES["photoUpload"]["size"] > $tamanoMaximo) {
        $errores['photoUpload'] = "La imagen no debe superar los 2MB";
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

if (!empty($_POST['contacto_empresa_acudiente']) && !preg_match('/^[0-9]{6,12}$/', $_POST['contacto_empresa_acudiente'])) {
    $errores['contacto_empresa_acudiente'] = "El contacto de la empresa debe contener entre 6 y 12 dígitos";
}

// Validación de nivel (checkbox)
if (!isset($_POST['nivel']) || count($_POST['nivel']) == 0) {
    $errores['nivel'] = "Debe seleccionar al menos un nivel";
}

if (count($errores) > 0) {
    session_start();
    $_SESSION['errores'] = $errores;
    $_SESSION['old'] = $_POST;
    header("Location: ../index.php");
    exit;
}


// Si no hay errores, continuar con el procesamiento...

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "form");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejo de la foto (solo si no hay errores)
$uploadDir = realpath(__DIR__ . '/../uploads');
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$fotoNombre = time() . '_' . basename($_FILES["photoUpload"]["name"]);
$fotoRutaCompleta = $uploadDir . '/' . $fotoNombre;
$fotoRutaParaBD = 'uploads/' . $fotoNombre;

if (!move_uploaded_file($_FILES["photoUpload"]["tmp_name"], $fotoRutaCompleta)) {
    die("Error al subir la foto");
}

// Sanitizar y recolectar datos del formulario
$nombres = validarDatos($_POST['nombres_estudiante']);
$nivel = isset($_POST['nivel']) ? implode(",", array_map('validarDatos', $_POST['nivel'])) : '';
$horario = validarDatos($_POST['horario']);
$estrato = validarDatos($_POST['estrato_socioeconomico']);
$eps = validarDatos($_POST['eps']);
$escolaridad = validarDatos($_POST['nivel_escolaridad']);
$doc_type = validarDatos($_POST['doc_type']);
$numero_documento = validarDatos($_POST['numero_documento']);
$email = validarDatos($_POST['email']);
$municipio = validarDatos($_POST['municipio_residencia']);
$direccion = validarDatos($_POST['direccion_residencia']);
$celular1 = validarDatos($_POST['celular1']);
$celular2 = validarDatos($_POST['celular2']);
$barrio = validarDatos($_POST['barrio']);
$nombre_acudiente = validarDatos($_POST['nombre_acudiente']);
$contacto_acudiente = validarDatos($_POST['contacto_acudiente']);
$empresa = validarDatos($_POST['empresa_acudiente']);
$cargo = validarDatos($_POST['cargo_acudiente']);
$contacto_empresa = validarDatos($_POST['contacto_empresa_acudiente']);
$mensaje = validarDatos($_POST['mensaje_bienvenida']);
$reg_type = validarDatos($_POST['reg_type']);

// Preparar consulta SQL con sentencias preparadas
$stmt = $conn->prepare("INSERT INTO inscripciones (
    foto, nombres_estudiante, nivel_aspira, horario, estrato_socioeconomico,
    eps, nivel_escolaridad, doc_type, numero_documento, email, municipio_residencia,
    direccion_residencia, celular1, celular2, barrio, nombre_acudiente, contacto_acudiente,
    empresa_acudiente, cargo_acudiente, contacto_empresa_acudiente, mensaje_bienvenida, reg_type
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("Error en la preparación: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("sssssdssssssssssssssss",
    $fotoRutaParaBD, $nombres, $nivel, $horario, $estrato,
    $eps, $escolaridad, $doc_type, $numero_documento, $email, $municipio,
    $direccion, $celular1, $celular2, $barrio, $nombre_acudiente, $contacto_acudiente,
    $empresa, $cargo, $contacto_empresa, $mensaje, $reg_type
);

// Ejecutar consulta
if ($stmt->execute()) {
    // Regenerar token CSRF para el próximo formulario
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header("Location: ../exitosa.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>