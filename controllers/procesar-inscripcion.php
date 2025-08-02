<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "form");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// 1. Validar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido. Use POST.");
}

// 2. Función para validar y sanitizar datos
function validarDato($dato, $patron, $mensajeError, $requerido = true) {
    if ($requerido && empty($dato)) {
        die("Campo requerido faltante: $mensajeError");
    }
    
    if (!empty($dato) && !preg_match($patron, $dato)) {
        die("Formato inválido: $mensajeError");
    }
    
    return htmlspecialchars(trim($dato));
}

// 3. Validar archivo de foto
if (!isset($_FILES['photoUpload']) || $_FILES['photoUpload']['error'] !== UPLOAD_ERR_OK) {
    die("Debe subir una foto del estudiante");
}

$tiposPermitidos = ['image/jpeg', 'image/png'];
$tipoArchivo = $_FILES['photoUpload']['type'];
if (!in_array($tipoArchivo, $tiposPermitidos)) {
    die("Solo se permiten imágenes JPG o PNG");
}

if ($_FILES['photoUpload']['size'] > 2 * 1024 * 1024) {
    die("La imagen no debe exceder los 2MB");
}

// Crear directorio si no existe
$uploadDir = realpath(__DIR__ . '/../uploads');
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Procesar foto
$fotoNombre = time() . '_' . basename($_FILES["photoUpload"]["name"]);
$fotoRutaCompleta = $uploadDir . '/' . $fotoNombre;
$fotoRutaParaBD = 'uploads/' . $fotoNombre;

if (!move_uploaded_file($_FILES["photoUpload"]["tmp_name"], $fotoRutaCompleta)) {
    die("Error al subir la imagen");
}

// 4. Validar todos los campos del formulario
$nombres = validarDato($_POST['nombres_estudiante'], 
    '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{10,100}$/', 
    'Nombres inválidos (solo letras, 10-100 caracteres)');

// Validar niveles (checkbox)
if (!isset($_POST['nivel']) || count($_POST['nivel']) === 0) {
    die("Debe seleccionar al menos un nivel de aspiración");
}
$nivel = implode(",", $_POST['nivel']);

$horario = validarDato($_POST['horario'], 
    '/^[a-zA-Z0-9\s-]{3,50}$/', 
    'Horario inválido (3-50 caracteres alfanuméricos)');

$mensualidad = validarDato($_POST['mensualidad'], 
    '/^\d+(\.\d{1,2})?$/', 
    'Mensualidad inválida (solo números con hasta 2 decimales)');

$estrato = validarDato($_POST['estrato_socioeconomico'], 
    '/^[1-6]$/', 
    'Estrato inválido (1-6)');

$eps = validarDato($_POST['eps'], 
    '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s-]{3,50}$/', 
    'EPS/SISBEN inválido (3-50 caracteres)');

$escolaridad = validarDato($_POST['nivel_escolaridad'], 
    '/^(Primaria|Secundaria|Técnico|Tecnológico|Universitario|Postgrado)$/', 
    'Nivel de escolaridad inválido');

$doc_type = validarDato($_POST['doc_type'], 
    '/^(CC|TI|PPT)$/', 
    'Tipo de documento inválido');

$numero_documento = validarDato($_POST['numero_documento'], 
    '/^\d{8,15}$/', 
    'Número de documento inválido (8-15 dígitos)');

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    die("Email inválido");
}

$municipio = validarDato($_POST['municipio_residencia'], 
    '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/', 
    'Municipio inválido (3-50 letras)');

$direccion = validarDato($_POST['direccion_residencia'], 
    '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s#-]{5,100}$/', 
    'Dirección inválida (5-100 caracteres)');

$celular1 = validarDato($_POST['celular1'], 
    '/^[0-9+\s]{7,15}$/', 
    'Teléfono principal inválido (7-15 dígitos)');

$celular2 = isset($_POST['celular2']) ? validarDato($_POST['celular2'], 
    '/^[0-9+\s]{7,15}$|^$/', 
    'Teléfono secundario inválido (7-15 dígitos)', false) : '';

$barrio = validarDato($_POST['barrio'], 
    '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s-]{3,50}$/', 
    'Barrio inválido (3-50 caracteres)');

$nombre_acudiente = validarDato($_POST['nombre_acudiente'], 
    '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{10,100}$/', 
    'Nombre acudiente inválido (10-100 letras)');

$contacto_acudiente = validarDato($_POST['contacto_acudiente'], 
    '/^[0-9+\s]{7,15}$/', 
    'Contacto acudiente inválido (7-15 dígitos)');

$empresa = isset($_POST['empresa_acudiente']) ? validarDato($_POST['empresa_acudiente'], 
    '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s&.,-]{3,100}$|^$/', 
    'Empresa inválida (3-100 caracteres)', false) : '';

$cargo = isset($_POST['cargo_acudiente']) ? validarDato($_POST['cargo_acudiente'], 
    '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s-]{3,50}$|^$/', 
    'Cargo inválido (3-50 letras)', false) : '';

$contacto_empresa = isset($_POST['contacto_empresa_acudiente']) ? validarDato($_POST['contacto_empresa_acudiente'], 
    '/^[0-9+\s]{7,15}$|^$/', 
    'Contacto empresa inválido (7-15 dígitos)', false) : '';

$mensaje = isset($_POST['mensaje_bienvenida']) ? validarDato($_POST['mensaje_bienvenida'], 
    '/^.{0,200}$/', 
    'Mensaje demasiado largo (máx 200 caracteres)', false) : '';

$reg_type = validarDato($_POST['reg_type'], 
    '/^(new|returning)$/', 
    'Tipo de inscripción inválido');

// 5. Preparar y ejecutar la consulta SQL
$stmt = $conn->prepare("INSERT INTO inscripciones (
    foto, nombres_estudiante, nivel_aspira, horario, mensualidad, estrato_socioeconomico,
    eps, nivel_escolaridad, doc_type, numero_documento, email, municipio_residencia,
    direccion_residencia, celular1, celular2, barrio, nombre_acudiente, contacto_acudiente,
    empresa_acudiente, cargo_acudiente, contacto_empresa_acudiente, mensaje_bienvenida, reg_type
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

$stmt->bind_param("sssssdsssssssssssssssss",
    $fotoRutaParaBD, $nombres, $nivel, $horario, $mensualidad, $estrato,
    $eps, $escolaridad, $doc_type, $numero_documento, $email, $municipio,
    $direccion, $celular1, $celular2, $barrio, $nombre_acudiente, $contacto_acudiente,
    $empresa, $cargo, $contacto_empresa, $mensaje, $reg_type
);

// 6. Ejecutar y manejar resultados
if ($stmt->execute()) {
    header("Location: ../exitosa.php");
    exit;
} else {
    // Manejo de errores SQL sin mostrar detalles sensibles
    error_log("Error en la base de datos: " . $stmt->error);
    die("Ocurrió un error al procesar tu inscripción. Por favor intenta nuevamente.");
}

$stmt->close();
$conn->close();
?>