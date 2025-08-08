<?php
session_start();

// Función para validar y sanitizar datos
function validarDatos($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Array para almacenar errores
$errores = [];

// Verificar token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF inválido");
    }
}

// Validar ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("ID de inscripción inválido");
}
$id = (int)$_POST['id'];

// Validar campos obligatorios
$camposObligatorios = [
    'nombres_estudiante' => 'Nombres del estudiante',
    'horario' => 'Horario',
    'doc_type' => 'Tipo de documento',
    'numero_documento' => 'Número de documento',
    'email' => 'Email',
    'celular1' => 'Celular principal',
    'reg_type' => 'Tipo de registro'
];

foreach ($camposObligatorios as $campo => $nombre) {
    if (empty($_POST[$campo])) {
        $errores[$campo] = "El campo $nombre es obligatorio";
    }
}

// Validar nivel_aspira como arreglo
if (empty($_POST['nivel_aspira']) || !is_array($_POST['nivel_aspira'])) {
    $errores['nivel_aspira'] = "Debes seleccionar al menos un nivel";
}

// Validar formato del email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errores['email'] = "El email no tiene un formato válido";
}

// Validar documento
if (!preg_match('/^[0-9]{6,10}$/', $_POST['numero_documento'])) {
    $errores['numero_documento'] = "El número de documento debe contener entre 6 y 10 dígitos";
}

// Validar celular1
if (!preg_match('/^[0-9]{10}$/', $_POST['celular1'])) {
    $errores['celular1'] = "El celular debe tener 10 dígitos";
}

// Validar celular2 si se ingresó
if (!empty($_POST['celular2']) && !preg_match('/^[0-9]{10}$/', $_POST['celular2'])) {
    $errores['celular2'] = "El celular secundario debe tener 10 dígitos";
}

// Validar imagen (opcional)
if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES["foto"]["type"], $permitidos)) {
        $errores['foto'] = "Solo se permiten imágenes JPEG, PNG o GIF";
    }

    if ($_FILES["foto"]["size"] > 2 * 1024 * 1024) {
        $errores['foto'] = "La imagen no debe superar los 2MB";
    }
}

// Si hay errores, redirigimos y guardamos los valores anteriores
if (count($errores) > 0) {
    $_SESSION['errores_edicion'] = $errores;
    $_SESSION['old_edicion'] = [
        'id' => $_POST['id'],
        'nombres_estudiante' => $_POST['nombres_estudiante'] ?? '',
        'nivel_aspira'       => $_POST['nivel_aspira'] ?? [],
        'horario'            => $_POST['horario'] ?? '',
        'doc_type'           => $_POST['doc_type'] ?? '',
        'numero_documento'   => $_POST['numero_documento'] ?? '',
        'email'              => $_POST['email'] ?? '',
        'celular1'           => $_POST['celular1'] ?? '',
        'celular2'           => $_POST['celular2'] ?? '',
        'reg_type'           => $_POST['reg_type'] ?? '',
        'created_at'         => $_POST['created_at'] ?? ''
    ];
    header("Location: dashboard.php?editar=$id");
    exit;
}

// Conexión
$conn = new mysqli("localhost", "root", "", "form");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener foto anterior
$sqlActual = "SELECT foto FROM inscripciones WHERE id = ?";
$stmtActual = $conn->prepare($sqlActual);
$stmtActual->bind_param("i", $id);
$stmtActual->execute();
$resultadoActual = $stmtActual->get_result();
$filaActual = $resultadoActual->fetch_assoc();
$stmtActual->close();

$fotoRutaParaBD = $filaActual['foto'];

// Manejo de nueva foto
if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Eliminar anterior si existe
    if (!empty($filaActual['foto'])) {
        $fotoAnterior = __DIR__ . '/' . $filaActual['foto'];
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

// Recolectar y limpiar
$nombres = validarDatos($_POST['nombres_estudiante']);
$nivel = isset($_POST['nivel_aspira']) ? implode(',', array_map('validarDatos', $_POST['nivel_aspira'])) : '';
$horario = validarDatos($_POST['horario']);
$doc_type = validarDatos($_POST['doc_type']);
$numero_documento = validarDatos($_POST['numero_documento']);
$email = validarDatos($_POST['email']);
$celular1 = validarDatos($_POST['celular1']);
$celular2 = validarDatos($_POST['celular2']);
$reg_type = validarDatos($_POST['reg_type']);
$created_at = validarDatos($_POST['created_at']);

// Actualizar datos
$sql = "UPDATE inscripciones SET 
    foto = ?, nombres_estudiante = ?, nivel_aspira = ?, horario = ?, doc_type = ?,
    numero_documento = ?, email = ?, celular1 = ?, celular2 = ?, reg_type = ?, created_at = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la preparación: " . $conn->error);
}

$stmt->bind_param("sssssssssssi",
    $fotoRutaParaBD,
    $nombres,
    $nivel,
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

if ($stmt->execute()) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['old_edicion']);
    $_SESSION['mensaje_exito'] = "Los cambios se guardaron correctamente";
    header("Location: dashboard.php");
    exit;
} else {
    $_SESSION['error_edicion'] = "Error al actualizar: " . $stmt->error;
    header("Location: dashboard.php?editar=$id");
    exit;
}

$stmt->close();
$conn->close();
?>
