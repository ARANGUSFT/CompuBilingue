<?php
session_start();

/* 1) Acepta solo POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

/* 2) Si el archivo supera post_max_size, $_POST estará vacío -> mensaje claro */
if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $_SESSION['errores']['photoUpload'] = 'El archivo supera el tamaño permitido por el servidor.';
    $_SESSION['old'] = [];
    header('Location: ../index.php');
    exit;
}

/* 3) CSRF seguro */
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('Token CSRF inválido');
}

/* 4) Sanitizador */
function validarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/* 5) Validaciones */
$errores = [];

/* Campos obligatorios */
$camposObligatorios = [
    'nombres_estudiante'      => 'Nombres del estudiante',
    'horario'                 => 'Horario',
    'estrato_socioeconomico'  => 'Estrato socioeconómico',
    'eps'                     => 'EPS',
    'nivel_escolaridad'       => 'Nivel de escolaridad',
    'doc_type'                => 'Tipo de documento',
    'numero_documento'        => 'Número de documento',
    'email'                   => 'Email',
    'municipio_residencia'    => 'Municipio de residencia',
    'direccion_residencia'    => 'Dirección de residencia',
    'celular1'                => 'Celular principal',
    'nombre_acudiente'        => 'Nombre del acudiente',
    'contacto_acudiente'      => 'Contacto del acudiente',
    'reg_type'                => 'Tipo de registro',
];
foreach ($camposObligatorios as $campo => $nombre) {
    if (empty($_POST[$campo])) {
        $errores[$campo] = "El campo $nombre es obligatorio";
    }
}

/* Horarios permitidos */
$horariosPermitidos = [
    'Lunes a Jueves 6:00 a 8:00 PM',
    'Lunes a Jueves 3:00 a 5:00 PM',
    'Sábados 8:00 a 11:00 AM',
    'Lunes a Jueves 6:30 a 8:30 PM',
];
if (!empty($_POST['horario']) && !in_array($_POST['horario'], $horariosPermitidos)) {
    $errores['horario'] = "Por favor seleccione un horario válido de la lista";
}

/* Foto obligatoria + validaciones */
if (!isset($_FILES["photoUpload"]) || $_FILES["photoUpload"]["error"] != UPLOAD_ERR_OK) {
    $errores['photoUpload'] = "Debe subir una foto";
} else {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $tipoArchivo = $_FILES["photoUpload"]["type"] ?? '';
    if (!in_array($tipoArchivo, $permitidos)) {
        $errores['photoUpload'] = "Solo se permiten imágenes JPEG, PNG o GIF";
    }
    $tamanoMaximo = 5 * 1024 * 1024; // 5MB
    if (($_FILES["photoUpload"]["size"] ?? 0) > $tamanoMaximo) {
        $errores['photoUpload'] = "La imagen no debe superar los 5MB";
    }
}

/* Email */
if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errores['email'] = "El email no tiene un formato válido";
}

/* Números */
if (!empty($_POST['numero_documento']) && !preg_match('/^[0-9]{6,10}$/', $_POST['numero_documento'])) {
    $errores['numero_documento'] = "El número de documento debe contener entre 6 y 10 dígitos";
}
if (!empty($_POST['celular1']) && !preg_match('/^[0-9]{10}$/', $_POST['celular1'])) {
    $errores['celular1'] = "El celular debe tener 10 dígitos";
}
if (!empty($_POST['celular2']) && !preg_match('/^[0-9]{10}$/', $_POST['celular2'])) {
    $errores['celular2'] = "El celular secundario debe tener 10 dígitos";
}
if (!empty($_POST['contacto_empresa_acudiente']) && !preg_match('/^[0-9]{6,12}$/', $_POST['contacto_empresa_acudiente'])) {
    $errores['contacto_empresa_acudiente'] = "El contacto de la empresa debe contener entre 6 y 12 dígitos";
}

/* Nivel (checkbox) */
if (empty($_POST['nivel']) || !is_array($_POST['nivel'])) {
    $errores['nivel'] = "Debe seleccionar al menos un nivel";
}

/* Si hay errores -> volver al form con errores y old */
if ($errores) {
    $_SESSION['errores'] = $errores;
    $_SESSION['old']     = $_POST;
    header("Location: ../index.php");
    exit;
}

/* 6) Conexión a la BD */
$conn = new mysqli("localhost", "hveozvte_root980", "960013aA@", "hveozvte_inscripcioningles");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}



/* 7) Guardar la foto */
$uploadDir = realpath(__DIR__ . '/../uploads');
if (!$uploadDir) {
    $uploadDir = __DIR__ . '/../uploads';
}
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$origName  = basename($_FILES["photoUpload"]["name"]);
$cleanName = preg_replace('/[^A-Za-z0-9._-]/', '_', $origName);
$fotoNombre = time() . '_' . $cleanName;

$fotoRutaCompleta = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $fotoNombre;
$fotoRutaParaBD   = 'uploads/' . $fotoNombre;

if (!move_uploaded_file($_FILES["photoUpload"]["tmp_name"], $fotoRutaCompleta)) {
    die("Error al subir la foto");
}

/* 8) Sanitizar para DB */
$nombres           = validarDatos($_POST['nombres_estudiante']);
$nivel             = isset($_POST['nivel']) ? implode(",", array_map('validarDatos', $_POST['nivel'])) : '';
$horario           = validarDatos($_POST['horario']);
$estrato           = validarDatos($_POST['estrato_socioeconomico']);
$eps               = validarDatos($_POST['eps']);
$escolaridad       = validarDatos($_POST['nivel_escolaridad']);
$doc_type          = validarDatos($_POST['doc_type']);
$numero_documento  = validarDatos($_POST['numero_documento']);
$email             = validarDatos($_POST['email']);
$municipio         = validarDatos($_POST['municipio_residencia']);
$direccion         = validarDatos($_POST['direccion_residencia']);
$celular1          = validarDatos($_POST['celular1']);
$celular2          = validarDatos($_POST['celular2'] ?? '');
$barrio            = validarDatos($_POST['barrio'] ?? '');
$nombre_acudiente  = validarDatos($_POST['nombre_acudiente']);
$contacto_acudiente= validarDatos($_POST['contacto_acudiente']);
$empresa           = validarDatos($_POST['empresa_acudiente'] ?? '');
$cargo             = validarDatos($_POST['cargo_acudiente'] ?? '');
$contacto_empresa  = validarDatos($_POST['contacto_empresa_acudiente'] ?? '');
$mensaje           = validarDatos($_POST['mensaje_bienvenida'] ?? '');
$reg_type          = validarDatos($_POST['reg_type']);

/* 9) Insert con prepared statement (todo string para simplificar) */
$sql = "INSERT INTO inscripciones (
    foto, nombres_estudiante, nivel_aspira, horario, estrato_socioeconomico,
    eps, nivel_escolaridad, doc_type, numero_documento, email, municipio_residencia,
    direccion_residencia, celular1, celular2, barrio, nombre_acudiente, contacto_acudiente,
    empresa_acudiente, cargo_acudiente, contacto_empresa_acudiente, mensaje_bienvenida, reg_type
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la preparación: " . $conn->error);
}

/* Todos como 's' (string) para no complicar tipos) */
$stmt->bind_param(
    "ssssssssssssssssssssss",
    $fotoRutaParaBD, $nombres, $nivel, $horario, $estrato,
    $eps, $escolaridad, $doc_type, $numero_documento, $email, $municipio,
    $direccion, $celular1, $celular2, $barrio, $nombre_acudiente, $contacto_acudiente,
    $empresa, $cargo, $contacto_empresa, $mensaje, $reg_type
);

if ($stmt->execute()) {
    // Opcional: regenerar el token para el próximo formulario
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header("Location: ../exitosa.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
