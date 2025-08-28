<?php
    session_start();

    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login/login.php');
        exit;
    }

    // Verificar rol de administrador
    if ($_SESSION['usuario_rol'] !== 'admin') {
        die("No tienes permisos para acceder a esta página");
    }

    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "form");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

   // Procesar actualización de registro (con actualización de foto)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        $_SESSION['error'] = "ID inválido";
        header("Location: dashboard.php");
        exit;
    }

    // Sanitizar y validar datos
    function validarDatos($data) {
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    // Campos de texto
    $nombres           = validarDatos($_POST['nombres_estudiante'] ?? '');
    $nivel             = isset($_POST['nivel']) ? implode(",", array_map('validarDatos', (array)$_POST['nivel'])) : '';
    $horario           = validarDatos($_POST['horario'] ?? '');
    $estrato           = validarDatos($_POST['estrato_socioeconomico'] ?? '');
    $eps               = validarDatos($_POST['eps'] ?? '');
    $escolaridad       = validarDatos($_POST['nivel_escolaridad'] ?? '');
    $doc_type          = validarDatos($_POST['doc_type'] ?? '');
    $numero_documento  = validarDatos($_POST['numero_documento'] ?? '');
    $email             = validarDatos($_POST['email'] ?? '');
    $municipio         = validarDatos($_POST['municipio_residencia'] ?? '');
    $direccion         = validarDatos($_POST['direccion_residencia'] ?? '');
    $celular1          = validarDatos($_POST['celular1'] ?? '');
    $celular2          = validarDatos($_POST['celular2'] ?? '');
    $barrio            = validarDatos($_POST['barrio'] ?? '');
    $nombre_acudiente  = validarDatos($_POST['nombre_acudiente'] ?? '');
    $contacto_acudiente= validarDatos($_POST['contacto_acudiente'] ?? '');
    $empresa           = validarDatos($_POST['empresa_acudiente'] ?? '');
    $cargo             = validarDatos($_POST['cargo_acudiente'] ?? '');
    $contacto_empresa  = validarDatos($_POST['contacto_empresa_acudiente'] ?? '');
    $mensaje           = validarDatos($_POST['mensaje_bienvenida'] ?? '');
    $reg_type          = validarDatos($_POST['reg_type'] ?? '');

    // Traer foto actual
    $fotoActual = null;
    if ($sel = $conn->prepare("SELECT foto FROM inscripciones WHERE id = ?")) {
        $sel->bind_param('i', $id);
        $sel->execute();
        $sel->bind_result($fotoActual);
        $sel->fetch();
        $sel->close();
    }

    // Subida de nueva foto (opcional)
    $nuevaFotoRuta = null; // ruta relativa para la BD (p. ej. 'uploads/fotos/archivo.jpg')
    $campoArchivo = null;
    if (isset($_FILES['nueva_foto'])) $campoArchivo = 'nueva_foto';
    elseif (isset($_FILES['foto']))   $campoArchivo = 'foto';

    if ($campoArchivo && is_array($_FILES[$campoArchivo]) && $_FILES[$campoArchivo]['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES[$campoArchivo];

        if ($f['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error al subir la foto (código ' . $f['error'] . ').';
            header("Location: dashboard.php"); exit;
        }
        if ($f['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'La foto no debe superar 5MB.';
            header("Location: dashboard.php"); exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($f['tmp_name']);
        $map   = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif'];
        if (!isset($map[$mime])) {
            $_SESSION['error'] = 'Formato inválido (solo JPG, PNG o GIF).';
            header("Location: dashboard.php"); exit;
        }
        $ext = $map[$mime];

        $destDir = __DIR__ . '/uploads/fotos';
        if (!is_dir($destDir)) @mkdir($destDir, 0775, true);

        $baseName = 'foto_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destPath = $destDir . '/' . $baseName;

        if (!move_uploaded_file($f['tmp_name'], $destPath)) {
            $_SESSION['error'] = 'No se pudo guardar la imagen.';
            header("Location: dashboard.php"); exit;
        }

        $nuevaFotoRuta = 'uploads/fotos/' . $baseName;
    }

    // Construir UPDATE dinámico (incluye foto solo si hubo nueva)
    $campos = [
        'nombres_estudiante'         => $nombres,
        'nivel_aspira'               => $nivel,
        'horario'                    => $horario,
        'estrato_socioeconomico'     => $estrato,
        'eps'                        => $eps,
        'nivel_escolaridad'          => $escolaridad,
        'doc_type'                   => $doc_type,
        'numero_documento'           => $numero_documento,
        'email'                      => $email,
        'municipio_residencia'       => $municipio,
        'direccion_residencia'       => $direccion,
        'celular1'                   => $celular1,
        'celular2'                   => $celular2,
        'barrio'                     => $barrio,
        'nombre_acudiente'           => $nombre_acudiente,
        'contacto_acudiente'         => $contacto_acudiente,
        'empresa_acudiente'          => $empresa,
        'cargo_acudiente'            => $cargo,
        'contacto_empresa_acudiente' => $contacto_empresa,
        'mensaje_bienvenida'         => $mensaje,
        'reg_type'                   => $reg_type,
    ];
    if ($nuevaFotoRuta !== null) {
        $campos['foto'] = $nuevaFotoRuta;
    }

    $set = [];
    $values = [];
    $types  = '';
    foreach ($campos as $col => $val) {
        $set[]   = "$col = ?";
        $values[]= $val;
        $types  .= 's';
    }
    $sql = "UPDATE inscripciones SET " . implode(', ', $set) . " WHERE id = ?";
    $types .= 'i';
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Error al preparar: " . $conn->error;
        header("Location: dashboard.php"); exit;
    }
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        // Si se subió una nueva, elimina la anterior
        if ($nuevaFotoRuta !== null && $fotoActual && file_exists(__DIR__ . '/' . $fotoActual)) {
            @unlink(__DIR__ . '/' . $fotoActual);
        }
        $_SESSION['mensaje'] = "Registro actualizado correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar: " . $stmt->error;
    }
    $stmt->close();

    header("Location: dashboard.php");
    exit;
}


    // Procesar eliminación de registro
    if (isset($_GET['eliminar'])) {
        $id = $_GET['eliminar'];
        $sql = "DELETE FROM inscripciones WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Registro eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar: " . $stmt->error;
        }
        
        $stmt->close();
        header("Location: dashboard.php");
        exit;
    }

    // Obtener valores únicos para los filtros
    $regTypes = $conn->query("SELECT DISTINCT reg_type FROM inscripciones ORDER BY reg_type")->fetch_all(MYSQLI_ASSOC);
    $municipios = $conn->query("SELECT DISTINCT municipio_residencia FROM inscripciones WHERE municipio_residencia IS NOT NULL AND municipio_residencia != '' ORDER BY municipio_residencia")->fetch_all(MYSQLI_ASSOC);

    // Construir consulta con filtros
    $whereConditions = [];
    $params = [];
    $types = "";

    // Filtro por tipo de registro (nuevo/reingreso)
    if (!empty($_GET['filtro_reg_type'])) {
        $whereConditions[] = "reg_type = ?";
        $params[] = $_GET['filtro_reg_type'];
        $types .= "s";
    }

    // Filtro por municipio
    if (!empty($_GET['filtro_municipio'])) {
        $whereConditions[] = "municipio_residencia = ?";
        $params[] = $_GET['filtro_municipio'];
        $types .= "s";
    }

    // Filtro por nivel de inglés (VERSIÓN CORREGIDA)
    if (!empty($_GET['filtro_nivel'])) {
        $whereConditions[] = "nivel_aspira LIKE ? OR nivel_aspira LIKE ? OR nivel_aspira LIKE ? OR nivel_aspira LIKE ?";
        $params[] = $_GET['filtro_nivel'];
        $params[] = $_GET['filtro_nivel'] . ',%';
        $params[] = '%,' . $_GET['filtro_nivel'];
        $params[] = '%,' . $_GET['filtro_nivel'] . ',%';
        $types .= "ssss";
    }

    // Búsqueda general (nombre, cédula, correo)
    if (!empty($_GET['busqueda'])) {
        $searchTerm = "%" . $_GET['busqueda'] . "%";
        $whereConditions[] = "(nombres_estudiante LIKE ? OR email LIKE ? OR numero_documento LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    // Construir la consulta WHERE
    $whereClause = "";
    if (!empty($whereConditions)) {
        $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    }

    // Ordenamiento por defecto
    $orden = "ORDER BY id DESC";

    // Límite de resultados
    $limite = "";
    if (!empty($_GET['limite']) && $_GET['limite'] != '0') {
        $limite = "LIMIT " . (int)$_GET['limite'];
    }

    // Obtener registros con filtros aplicados
    $sql = "SELECT * FROM inscripciones $whereClause $orden $limite";
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Obtener contadores
    $sqlContadores = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN reg_type = 'nuevo' THEN 1 END) as nuevos,
        COUNT(CASE WHEN reg_type = 'antiguo' THEN 1 END) as antiguos
        FROM inscripciones";

    $contadores = $conn->query($sqlContadores)->fetch_assoc();

    $conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-right: 1px solid #dee2e6;
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .edit-modal {
            max-height: 80vh;
            overflow-y: auto;
        }
        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">


            <!-- Sidebar Simplificado -->
            <div class="col-md-3 col-lg-2 sidebar py-4">
                <h4 class="text-center mb-4">Panel Administrativo</h4>
                <p class="text-center text-muted">Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></p>
                
                <!-- Filtros Simplificados -->
                <form method="GET" action="dashboard.php">
                    <div class="filter-section">
                        <!-- Búsqueda general -->
                        <div class="mb-3">
                            <label class="form-label">🔍 Búsqueda:</label>
                            <input type="text" name="busqueda" class="form-control form-control-sm" 
                                   placeholder="Nombre, cédula o correo" 
                                   value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        </div>

                        <!-- Filtro por tipo de registro -->
                        <div class="mb-3">
                            <label class="form-label">📋 Tipo de registro:</label>
                            <select name="filtro_reg_type" class="form-select form-select-sm">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($regTypes as $type): ?>
                                    <option value="<?= $type['reg_type'] ?>" <?= isset($_GET['filtro_reg_type']) && $_GET['filtro_reg_type'] == $type['reg_type'] ? 'selected' : '' ?>>
                                        <?= ucfirst($type['reg_type']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro por municipio -->
                        <div class="mb-3">
                            <label class="form-label">🏙️ Municipio:</label>
                            <select name="filtro_municipio" class="form-select form-select-sm">
                                <option value="">Todos los municipios</option>
                                <?php foreach ($municipios as $municipio): ?>
                                    <option value="<?= $municipio['municipio_residencia'] ?>" <?= isset($_GET['filtro_municipio']) && $_GET['filtro_municipio'] == $municipio['municipio_residencia'] ? 'selected' : '' ?>>
                                        <?= ucfirst(str_replace('_', ' ', $municipio['municipio_residencia'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro por nivel de inglés -->
                        <div class="mb-3">
                            <label class="form-label">🌐 Nivel de inglés:</label>
                            <select name="filtro_nivel" class="form-select form-select-sm">
                                <option value="">Todos los niveles</option>
                                <option value="A1" <?= isset($_GET['filtro_nivel']) && $_GET['filtro_nivel'] == 'A1' ? 'selected' : '' ?>>A1</option>
                                <option value="A2" <?= isset($_GET['filtro_nivel']) && $_GET['filtro_nivel'] == 'A2' ? 'selected' : '' ?>>A2</option>
                                <option value="B1" <?= isset($_GET['filtro_nivel']) && $_GET['filtro_nivel'] == 'B1' ? 'selected' : '' ?>>B1</option>
                                <option value="B2" <?= isset($_GET['filtro_nivel']) && $_GET['filtro_nivel'] == 'B2' ? 'selected' : '' ?>>B2</option>
                            </select>
                        </div>

                        <!-- Límite de resultados -->
                        <div class="mb-3">
                            <label class="form-label">📄 Resultados por página:</label>
                            <select name="limite" class="form-select form-select-sm">
                                <option value="10" <?= isset($_GET['limite']) && $_GET['limite'] == '10' ? 'selected' : '' ?>>10 registros</option>
                                <option value="25" <?= isset($_GET['limite']) && $_GET['limite'] == '25' ? 'selected' : '' ?>>25 registros</option>
                                <option value="50" <?= isset($_GET['limite']) && $_GET['limite'] == '50' ? 'selected' : '' ?>>50 registros</option>
                                <option value="100" <?= isset($_GET['limite']) && $_GET['limite'] == '100' ? 'selected' : '' ?>>100 registros</option>
                                <option value="0" <?= isset($_GET['limite']) && $_GET['limite'] == '0' ? 'selected' : '' ?>>Todos</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Aplicar Filtros
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </form>
                
                <div class="mt-4">
                
                    <a href="logout.php" class="btn btn-outline-danger w-100 mt-2 btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>

                <!-- Mostrar filtros activos -->
                <?php if (!empty(array_filter($_GET))): ?>
                <div class="mt-3">
                    <h6>Filtros activos:</h6>
                    <div class="bg-light p-2 rounded">
                        <?php
                        $filtrosActivos = [];
                        foreach ($_GET as $key => $value) {
                            if (!empty($value) && $key !== 'limite') {
                                $filtrosActivos[] = "<span class='badge bg-info text-dark me-1 mb-1'>$key: $value</span>";
                            }
                        }
                        echo implode(' ', $filtrosActivos);
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Contenido principal -->
            <div class="col-md-9 col-lg-10 py-4">
                <h2 class="mb-4">Gestión de Inscripciones</h2>
                
                <!-- Mostrar mensajes -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['mensaje'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <!-- Contadores -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-primary text-white text-center p-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-users"></i> Total Registros</h5>
                                <h2 class="card-text"><?= $contadores['total'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white text-center p-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-user-plus"></i> Nuevos</h5>
                                <h2 class="card-text"><?= $contadores['nuevos'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white text-center p-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-user-check"></i> Antiguos</h5>
                                <h2 class="card-text"><?= $contadores['antiguos'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla de registros -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lista de Inscripciones</h5>
                        <span class="badge bg-primary"><?= $result->num_rows ?> registros</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Nombres</th>
                                        <th>Documento</th>
                                        <th>Email</th>
                                        <th>Celular</th>
                                        <th>Nivel Inglés</th>
                                        <th>Municipio</th>
                                        <th>Tipo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= htmlspecialchars($row['foto']) ?>" alt="Foto" class="rounded-circle" width="40" height="40" 
                                                onerror="this.src='https://via.placeholder.com/40/e9ecef/6c757d?text=Sin+Foto'">
                                        </td>
                                        <td><?= htmlspecialchars($row['nombres_estudiante']) ?></td>
                                        <td><?= htmlspecialchars($row['doc_type']) ?>: <?= htmlspecialchars($row['numero_documento']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['celular1']) ?></td>
                                        <td>
                                            <?php
                                            $niveles = explode(',', $row['nivel_aspira']);
                                            foreach ($niveles as $nivel) {
                                                echo '<span class="badge bg-secondary me-1">' . htmlspecialchars($nivel) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['municipio_residencia']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['reg_type'] == 'nuevo' ? 'success' : 'info' ?>">
                                                <?= htmlspecialchars($row['reg_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="dashboard.php?eliminar=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    
                               <!-- Modal de edición COMPLETO con cambio de foto -->
                            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg edit-modal">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="editModalLabel<?= $row['id'] ?>">
                                                <i class="fas fa-user-edit me-2"></i>Editar Registro: <?= htmlspecialchars($row['nombres_estudiante']) ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="dashboard.php" enctype="multipart/form-data" id="formEditar<?= $row['id'] ?>">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($row['foto']) ?>">
                                      <!-- Sección de Foto -->
<div class="text-center mb-4">
  <div class="photo-preview-container position-relative d-inline-block">
    <img id="previewFoto<?= $row['id'] ?>" src="<?= htmlspecialchars($row['foto']) ?>"
         alt="Foto actual" class="rounded-circle shadow" width="140" height="140"
         style="object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"
         onerror="this.src='https://via.placeholder.com/140/e9ecef/6c757d?text=Sin+Foto'">

    <label for="nuevaFoto<?= $row['id'] ?>" class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle"
           style="width: 40px; height: 40px; padding: 0;" title="Cambiar foto">
      <i class="fas fa-camera"></i>
    </label>
  </div>

  <input type="file" id="nuevaFoto<?= $row['id'] ?>" name="nueva_foto"
         accept="image/*" class="d-none"
         onchange="previewImage(this, 'previewFoto<?= $row['id'] ?>')">

  <div class="mt-2">
    <small class="text-muted">
      <i class="fas fa-info-circle"></i> Formatos: JPG, PNG, GIF • Máx. 5MB
    </small>
  </div>

  <!-- Botones de acción (Descargar / Ver) -->
  <div class="mt-3 d-flex justify-content-center gap-2">
    <?php if (!empty($row['foto'])): ?>
      <!-- Descargar (usa el endpoint PHP que fuerza la descarga) -->
   <a href="<?= htmlspecialchars($row['foto']) ?>" download class="btn btn-outline-secondary btn-sm">
  <i class="fas fa-download me-1"></i> Descargar
</a>


      <!-- Ver en nueva pestaña (link directo al archivo) -->
      <a href="<?= htmlspecialchars($row['foto']) ?>"
         target="_blank" rel="noopener"
         class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-external-link-alt me-1"></i> Ver
      </a>
    <?php else: ?>
      <button type="button" class="btn btn-outline-secondary btn-sm" disabled
              title="Este registro no tiene foto">
        <i class="fas fa-download me-1"></i> Descargar
      </button>
    <?php endif; ?>
  </div>
</div>


                                                <!-- Información Principal -->
                                                <div class="card mb-4">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Información del Estudiante</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Nombres del estudiante *</label>
                                                                <input type="text" name="nombres_estudiante" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['nombres_estudiante']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Horario *</label>
                                                                <select name="horario" class="form-select" required>
                                                                    <option value="Lunes a Jueves 6:00 a 8:00 PM" <?= $row['horario'] == 'Lunes a Jueves 6:00 a 8:00 PM' ? 'selected' : '' ?>>Lunes a Jueves 6:00 a 8:00 PM</option>
                                                                    <option value="Lunes a Jueves 3:00 a 5:00 PM" <?= $row['horario'] == 'Lunes a Jueves 3:00 a 5:00 PM' ? 'selected' : '' ?>>Lunes a Jueves 3:00 a 5:00 PM</option>
                                                                    <option value="Sábados 8:00 a 11:00 AM" <?= $row['horario'] == 'Sábados 8:00 a 11:00 AM' ? 'selected' : '' ?>>Sábados 8:00 a 11:00 AM</option>
                                                                    <option value="Lunes a Jueves 6:30 a 8:30 PM" <?= $row['horario'] == 'Lunes a Jueves 6:30 a 8:30 PM' ? 'selected' : '' ?>>Lunes a Jueves 6:30 a 8:30 PM</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Estrato socioeconómico *</label>
                                                                <select name="estrato_socioeconomico" class="form-select" required>
                                                                    <?php foreach (range(1, 6) as $i): ?>
                                                                        <option value="<?= $i ?>" <?= $row['estrato_socioeconomico'] == $i ? 'selected' : '' ?>>Estrato <?= $i ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">EPS *</label>
                                                                <input type="text" name="eps" class="form-control" value="<?= htmlspecialchars($row['eps']) ?>" required>
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Nivel de escolaridad *</label>
                                                                <select name="nivel_escolaridad" class="form-select" required>
                                                                    <?php
                                                                    $nivelesEscolaridad = ['Primaria', 'Secundaria', 'Técnico', 'Tecnológico', 'Universitario', 'Postgrado'];
                                                                    foreach ($nivelesEscolaridad as $nivel):
                                                                    ?>
                                                                        <option value="<?= $nivel ?>" <?= $row['nivel_escolaridad'] == $nivel ? 'selected' : '' ?>><?= $nivel ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Documentación y Contacto -->
                                                <div class="card mb-4">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Documentación y Contacto</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Tipo de documento *</label>
                                                                <select name="doc_type" class="form-select" required>
                                                                    <option value="CC" <?= $row['doc_type'] == 'CC' ? 'selected' : '' ?>>CC</option>
                                                                    <option value="TI" <?= $row['doc_type'] == 'TI' ? 'selected' : '' ?>>TI</option>
                                                                    <option value="PPT" <?= $row['doc_type'] == 'PPT' ? 'selected' : '' ?>>PPT</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Número de documento *</label>
                                                                <input type="text" name="numero_documento" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['numero_documento']) ?>" required pattern="[0-9]{6,10}">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Email *</label>
                                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Municipio de residencia *</label>
                                                                <select name="municipio_residencia" class="form-select" required>
                                                                    <option value="">Seleccione un municipio</option>
                                                                    <?php
                                                                    $municipiosOptions = [
                                                                        'medellin', 'bello', 'itagui', 'sabaneta', 'envigado', 'caldas', 'laestrella', 
                                                                        'copacabana', 'barbosa', 'girardota', 'abejorral', 'abriaqui', 'alejandria',
                                                                        'amaga', 'amalfi', 'andes', 'angelopolis', 'angostura', 'anori', 'antioquia',
                                                                        'anza', 'apartado', 'arboletes', 'argelia', 'armenia', 'belmira', 'betania',
                                                                        'betulia', 'briceño', 'buritica', 'caceres', 'caicedo', 'campamento',
                                                                        'canasgordas', 'caracoli', 'caramanta', 'carepa', 'carolina', 'caucasia',
                                                                        'chigorodo', 'cisneros', 'cocorna', 'concepcion', 'concordia', 'dabeiba',
                                                                        'donmatias', 'ebejico', 'elbagre', 'elcarmen', 'elpenol', 'elretiro',
                                                                        'elsantuario', 'entrerrios', 'fredonia', 'frontino', 'giraldo', 'gomezplata',
                                                                        'granada', 'guadalupe', 'guarne', 'guatape', 'heliconia', 'hispania',
                                                                        'ituango', 'jardin', 'jerico', 'laceja', 'lapintada', 'launion', 'liborina',
                                                                        'maceo', 'marinilla', 'montebello', 'murindo', 'mutata', 'narino', 'necocli',
                                                                        'nechi', 'olaya', 'peque', 'pueblorrico', 'puertoberrio', 'puertonare',
                                                                        'puertotriunfo', 'remedios', 'retiro', 'rionegro', 'sabanalarga', 'salgar',
                                                                        'sanandres', 'sancarlos', 'sanfrancisco', 'sanjerónimo', 'sanjose', 'sanjuan',
                                                                        'sanluis', 'sanpedro', 'sanpedrouraba', 'sanrafael', 'sanroque', 'sanvicente',
                                                                        'santabarbara', 'santarosa', 'santodomingo', 'santuario', 'segovia', 'sonson',
                                                                        'sopetran', 'tamesis', 'taraza', 'tarso', 'titiribi', 'toledo', 'turbo',
                                                                        'uramita', 'urrao', 'valdivia', 'valparaiso', 'vegachi', 'venecia', 'vigia',
                                                                        'yali', 'yarumal', 'yolombo', 'yondo', 'zaragoza'
                                                                    ];
                                                                    
                                                                    foreach ($municipiosOptions as $municipioOption):
                                                                        $municipioDisplay = ucfirst(str_replace('_', ' ', $municipioOption));
                                                                    ?>
                                                                        <option value="<?= $municipioOption ?>" <?= $row['municipio_residencia'] == $municipioOption ? 'selected' : '' ?>>
                                                                            <?= $municipioDisplay ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Dirección de residencia *</label>
                                                                <input type="text" name="direccion_residencia" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['direccion_residencia']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Celular principal *</label>
                                                                <input type="text" name="celular1" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['celular1']) ?>" required pattern="[0-9]{10}">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Celular secundario</label>
                                                                <input type="text" name="celular2" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['celular2']) ?>" pattern="[0-9]{10}">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Barrio</label>
                                                                <input type="text" name="barrio" class="form-control" value="<?= htmlspecialchars($row['barrio']) ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Información del Acudiente -->
                                                <div class="card mb-4">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="fas fa-user-friends me-2"></i>Información del Acudiente</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Nombre del acudiente *</label>
                                                                <input type="text" name="nombre_acudiente" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['nombre_acudiente']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Contacto del acudiente *</label>
                                                                <input type="text" name="contacto_acudiente" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['contacto_acudiente']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Empresa acudiente</label>
                                                                <input type="text" name="empresa_acudiente" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['empresa_acudiente']) ?>">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Cargo acudiente</label>
                                                                <input type="text" name="cargo_acudiente" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['cargo_acudiente']) ?>">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label fw-semibold">Contacto empresa</label>
                                                                <input type="text" name="contacto_empresa_acudiente" class="form-control" 
                                                                    value="<?= htmlspecialchars($row['contacto_empresa_acudiente']) ?>" pattern="[0-9]{6,12}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Información Adicional -->
                                                <div class="card mb-4">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Adicional</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Mensaje de bienvenida</label>
                                                            <textarea name="mensaje_bienvenida" class="form-control" rows="3" 
                                                                    placeholder="Escriba un mensaje opcional..."><?= htmlspecialchars($row['mensaje_bienvenida']) ?></textarea>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Tipo de registro *</label>
                                                                <select name="reg_type" class="form-select" required>
                                                                    <option value="nuevo" <?= $row['reg_type'] == 'nuevo' ? 'selected' : '' ?>>Nuevo</option>
                                                                    <option value="antiguo" <?= $row['reg_type'] == 'antiguo' ? 'selected' : '' ?>>Antiguo</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label fw-semibold">Nivel al que aspira *</label>
                                                                <div class="row">
                                                                    <?php
                                                                    $niveles = ['A1', 'A2', 'B1', 'B2'];
                                                                    $nivelesSeleccionados = explode(',', $row['nivel_aspira']);
                                                                    foreach ($niveles as $nivel):
                                                                    ?>
                                                                        <div class="col-6 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="checkbox" name="nivel[]" value="<?= $nivel ?>" 
                                                                                    id="nivel_<?= $nivel ?>_<?= $row['id'] ?>"
                                                                                    <?= in_array($nivel, $nivelesSeleccionados) ? 'checked' : '' ?>>
                                                                                <label class="form-check-label" for="nivel_<?= $nivel ?>_<?= $row['id'] ?>">
                                                                                    <span class="badge bg-secondary"><?= $nivel ?></span>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i> Cancelar
                                                    </button>
                                                    <button type="submit" name="actualizar" class="btn btn-primary">
                                                        <i class="fas fa-save me-1"></i> Guardar Cambios
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- JavaScript para previsualización de imagen -->
                            <script>
                                function previewImage(input, previewId) {
                                    const preview = document.getElementById(previewId);
                                    const file = input.files[0];
                                    
                                    if (file) {
                                        // Validar tamaño máximo (5MB)
                                        if (file.size > 5 * 1024 * 1024) {
                                            alert('⚠️ La imagen no debe superar los 5MB');
                                            input.value = '';
                                            return;
                                        }
                                        
                                        // Validar tipo de archivo
                                        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                                        if (!validTypes.includes(file.type)) {
                                            alert('⚠️ Solo se permiten imágenes JPEG, PNG o GIF');
                                            input.value = '';
                                            return;
                                        }
                                        
                                        const reader = new FileReader();
                                        
                                        reader.onload = function(e) {
                                            preview.src = e.target.result;
                                            preview.style.borderColor = '#0d6efd';
                                        }
                                        
                                        reader.readAsDataURL(file);
                                    }
                                }
                            </script>

                            <style>
                                .photo-preview-container {
                                    transition: all 0.3s ease;
                                }

                                .photo-preview-container:hover {
                                    transform: scale(1.05);
                                }

                                .card {
                                    border: 1px solid #e9ecef;
                                    border-radius: 10px;
                                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                                }

                                .card-header {
                                    border-bottom: 2px solid #e9ecef;
                                    border-radius: 10px 10px 0 0 !important;
                                }

                                .form-label.fw-semibold {
                                    color: #495057;
                                    font-size: 0.9rem;
                                }

                                .btn-primary {
                                    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                                    border: none;
                                    border-radius: 8px;
                                    padding: 10px 20px;
                                    font-weight: 500;
                                }

                                .btn-primary:hover {
                                    background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
                                    transform: translateY(-1px);
                                    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
                                }

                                .modal-content {
                                    border-radius: 15px;
                                    border: none;
                                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                                }

                                .modal-header {
                                    border-radius: 15px 15px 0 0;
                                    padding: 1.5rem;
                                }

                                .modal-body {
                                    padding: 2rem;
                                    max-height: 70vh;
                                    overflow-y: auto;
                                }

                                .form-control, .form-select {
                                    border-radius: 8px;
                                    border: 2px solid #e9ecef;
                                    padding: 12px 15px;
                                    transition: all 0.3s ease;
                                }

                                .form-control:focus, .form-select:focus {
                                    border-color: #86b7fe;
                                    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
                                }

                                .form-check-input:checked {
                                    background-color: #0d6efd;
                                    border-color: #0d6efd;
                                }
                            </style>

                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>