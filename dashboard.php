<?php

    include 'conexion/cone.php';

    session_start();

    if (!isset($_SESSION['usuario_id'])) {
        // Redirige al login si no hay sesión
        header("Location: login/login.php");
        exit;
    }

    if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // genera token seguro
}


    // Configuración de paginación
    $registrosPorPagina = 10;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina - 1) * $registrosPorPagina;

    // Búsqueda y filtros
    $busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';
    $filtroNivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
    $filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

    // Consulta base con filtros
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM inscripciones WHERE 1=1";
    $params = [];

    if (!empty($busqueda)) {
        $sql .= " AND (nombres_estudiante LIKE ? OR numero_documento LIKE ? OR email LIKE ?)";
        $paramBusqueda = "%$busqueda%";
        $params = array_merge($params, [$paramBusqueda, $paramBusqueda, $paramBusqueda]);
    }

    if (!empty($filtroNivel)) {
        $sql .= " AND FIND_IN_SET(?, nivel_aspira)";
        $params[] = $filtroNivel;
    }

    if (!empty($filtroTipo)) {
        $sql .= " AND reg_type = ?";
        $params[] = $filtroTipo;
    }

    $sql .= " ORDER BY created_at DESC LIMIT ?, ?";
    $params[] = $inicio;
    $params[] = $registrosPorPagina;

    // Preparar y ejecutar consulta
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $tipos = str_repeat('s', count($params) - 2) . 'ii';
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        // Obtener total de registros
        $totalRegistros = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
    } else {
        die("Error en la consulta: " . $conn->error);
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Inscripciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --color-primario: #2c3e50;
            --color-secundario: #00923f;
            --color-terciario: #e9ecef;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-custom {
            background-color: var(--color-primario);
        }
        
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--color-primario);
            color: white;
            font-weight: 500;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge-estudiante {
            background-color: #3498db;
        }
        
        .badge-reingreso {
            background-color: #2ecc71;
        }
        
        .foto-perfil {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #eee;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--color-primario);
            border-color: var(--color-primario);
        }
        
        .pagination .page-link {
            color: var(--color-primario);
        }
        
        .btn-primary {
            background-color: var(--color-primario);
            border-color: var(--color-primario);
        }
        
        .btn-primary:hover {
            background-color: #1a252f;
            border-color: #1a252f;
        }
        
        .btn-outline-primary {
            color: var(--color-primario);
            border-color: var(--color-primario);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--color-primario);
            color: white;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box i {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #6c757d;
        }
        
        .search-box input {
            padding-left: 35px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 14px;
            }
            
            .main-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-people-fill"></i> Panel de Administración
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
              
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
         
    <!-- Contenido principal -->
    <div class="container-fluid main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people-fill"></i> Listado de Inscripciones</h2>
        </div>

                <!-- Filtros y búsqueda -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" name="busqueda" placeholder="Buscar por nombre, documento o email..." value="<?= htmlspecialchars($busqueda) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="nivel">
                                    <option value="">Todos los niveles</option>
                                    <option value="A1" <?= $filtroNivel == 'A1' ? 'selected' : '' ?>>A1</option>
                                    <option value="A2" <?= $filtroNivel == 'A2' ? 'selected' : '' ?>>A2</option>
                                    <option value="B1" <?= $filtroNivel == 'B1' ? 'selected' : '' ?>>B1</option>
                                    <option value="B2" <?= $filtroNivel == 'B2' ? 'selected' : '' ?>>B2</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="tipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="new" <?= $filtroTipo == 'new' ? 'selected' : '' ?>>Nuevo</option>
                                    <option value="returning" <?= $filtroTipo == 'returning' ? 'selected' : '' ?>>Reingreso</option>
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

            <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1): ?>
                <p style="color: green;">✅ Registro eliminado correctamente.</p>
            <?php endif; ?>

                <!-- Resumen estadístico -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Inscritos</h6>
                                        <h3 class="card-text"><?= number_format($totalRegistros) ?></h3>
                                    </div>
                                    <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Nuevos</h6>
                                        <h3 class="card-text">
                                            <?php 
                                                $sqlNuevos = "SELECT COUNT(*) FROM inscripciones WHERE reg_type = 'new'";
                                                $nuevos = $conn->query($sqlNuevos)->fetch_row()[0];
                                                echo number_format($nuevos);
                                            ?>
                                        </h3>
                                    </div>
                                    <i class="bi bi-person-plus" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Reingresos</h6>
                                        <h3 class="card-text">
                                            <?php 
                                                $sqlReingresos = "SELECT COUNT(*) FROM inscripciones WHERE reg_type = 'returning'";
                                                $reingresos = $conn->query($sqlReingresos)->fetch_row()[0];
                                                echo number_format($reingresos);
                                            ?>
                                        </h3>
                                    </div>
                                    <i class="bi bi-arrow-repeat" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de inscripciones -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Listado de Inscripciones</h5>
                        <div>
                            <span class="badge bg-secondary">Mostrando <?= $resultado->num_rows ?> de <?= number_format($totalRegistros) ?></span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Foto</th>
                                        <th>Nombre</th>
                                        <th>Nivel</th>
                                        <th>Documento</th>
                                        <th>Contacto</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultado->num_rows > 0): ?>
                                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $fila['id'] ?></td>
                                                <td>
                                                    <?php if (!empty($fila['foto'])): ?>
                                                        <img src="<?= htmlspecialchars($fila['foto']) ?>" alt="Foto" class="foto-perfil">
                                                    <?php else: ?>
                                                        <div class="foto-perfil bg-light d-flex align-items-center justify-content-center">
                                                            <i class="bi bi-person text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($fila['nombres_estudiante']) ?></strong>
                                                    <div class="text-muted small"><?= htmlspecialchars($fila['email']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($fila['nivel_aspira']) ?></span>
                                                    <div class="text-muted small"><?= htmlspecialchars($fila['horario']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($fila['doc_type']) ?></span>
                                                    <div><?= htmlspecialchars($fila['numero_documento']) ?></div>
                                                </td>
                                                <td>
                                                    <div><?= htmlspecialchars($fila['celular1']) ?></div>
                                                    <?php if (!empty($fila['celular2'])): ?>
                                                        <div class="text-muted small"><?= htmlspecialchars($fila['celular2']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($fila['reg_type'] == 'new'): ?>
                                                        <span class="badge bg-success">Nuevo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Reingreso</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($fila['created_at'])) ?>
                                                    <div class="text-muted small"><?= date('H:i', strtotime($fila['created_at'])) ?></div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" title="Ver detalles">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-secondary btn-edit" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>


                                                        <form method="POST" action="eliminar.php" class="d-inline form-eliminar">
                                                            <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
                                                <h5 class="mt-2">No se encontraron inscripciones</h5>
                                                <p class="text-muted">Intenta con otros criterios de búsqueda</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Paginación -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($pagina > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina-1 ?>&busqueda=<?= urlencode($busqueda) ?>&nivel=<?= $filtroNivel ?>&tipo=<?= $filtroTipo ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                        <a class="page-link" href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>&nivel=<?= $filtroNivel ?>&tipo=<?= $filtroTipo ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagina < $totalPaginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $pagina+1 ?>&busqueda=<?= urlencode($busqueda) ?>&nivel=<?= $filtroNivel ?>&tipo=<?= $filtroTipo ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarLabel">Editar Inscripción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar" method="POST" action="editar.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editarId">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="row g-3">
                        <!-- Sección de información personal -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Información Personal</h6>
                            
                            <div class="mb-3">
                                <label for="editarNombres" class="form-label">Nombres Completos</label>
                                <input type="text" class="form-control" id="editarNombres" name="nombres_estudiante" 
                                       required maxlength="100" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                                       title="Solo letras y espacios (máximo 100 caracteres)">
                                <div class="form-text">Máximo 100 caracteres</div>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="editarTipoDoc" class="form-label">Tipo de Documento</label>
                                    <select class="form-select" id="editarTipoDoc" name="doc_type" required>
                                        <option value="CC">C.C</option>
                                        <option value="TI">T.I</option>
                                        <option value="PPT">PPT</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editarNumDoc" class="form-label">Número de Documento</label>
                                    <input type="text" class="form-control" id="editarNumDoc" name="numero_documento" 
                                           required minlength="6" maxlength="12" pattern="[0-9]+"
                                           title="Entre 6 y 12 dígitos numéricos">
                                    <div class="form-text">Entre 6 y 12 dígitos</div>
                                </div>
                            </div>
                            
                            <div class="mb-3 mt-3">
                                <label for="editarFoto" class="form-label">Foto</label>
                                <input type="file" class="form-control" id="editarFoto" name="foto" 
                                       accept="image/jpeg, image/png" 
                                       title="Solo imágenes JPEG o PNG (máximo 2MB)">
                                <div class="form-text">Formatos: JPEG o PNG (Max. 2MB)</div>
                                <div class="mt-2 text-center">
                                    <img id="previewFoto" src="" alt="Foto actual" class="img-thumbnail" style="max-width: 150px; display: none;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección de contacto -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Información de Contacto</h6>
                            
                            <div class="mb-3">
                                <label for="editarEmail" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="editarEmail" name="email" 
                                       required maxlength="100">
                                <div class="form-text">Máximo 100 caracteres</div>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="editarCelular1" class="form-label">Celular Principal</label>
                                    <input type="tel" class="form-control" id="editarCelular1" name="celular1" 
                                           required minlength="10" maxlength="15" pattern="[0-9]+"
                                           title="Entre 10 y 15 dígitos">
                                    <div class="form-text">Ejemplo: 3101234567</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="editarCelular2" class="form-label">Celular Secundario (opcional)</label>
                                    <input type="tel" class="form-control" id="editarCelular2" name="celular2"
                                           minlength="10" maxlength="15" pattern="[0-9]+"
                                           title="Entre 10 y 15 dígitos (opcional)">
                                    <div class="form-text">Opcional - mismo formato</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editarTipoRegistro" class="form-label">Tipo de Registro</label>
                                <select class="form-select" id="editarTipoRegistro" name="reg_type" required>
                                    <option value="new">Nuevo</option>
                                    <option value="returning">Reingreso</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Sección de información académica -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Información Académica</h6>
                            
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label for="editarNivel" class="form-label">Nivel que Aspira</label>
                                    <select class="form-select" id="editarNivel" name="nivel_aspira" required>
                                        <option value="A1">A1</option>
                                        <option value="A2">A2</option>
                                        <option value="B1">B1</option>
                                        <option value="B2">B2</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="editarHorario" class="form-label">Horario Preferido</label>
                                    <input type="text" class="form-control" id="editarHorario" name="horario" 
                                           required maxlength="50">
                                    <div class="form-text">Ejemplo: Lunes a Viernes 8am-12pm</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editarFecha" class="form-label">Fecha de Inscripción</label>
                                    <input type="datetime-local" class="form-control" id="editarFecha" name="created_at" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formEditar" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Script para manejar el modal y validaciones -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar clic en botones de editar
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                
                // Obtener datos de la fila
                const id = row.querySelector('td:nth-child(1)').textContent;
                const foto = row.querySelector('td:nth-child(2) img')?.src || '';
                const nombres = row.querySelector('td:nth-child(3) strong').textContent;
                const email = row.querySelector('td:nth-child(3) .text-muted').textContent;
                const nivel = row.querySelector('td:nth-child(4) .badge').textContent.trim();
                const horario = row.querySelector('td:nth-child(4) .text-muted').textContent;
                const tipoDoc = row.querySelector('td:nth-child(5) .badge').textContent.trim();
                const numDoc = row.querySelector('td:nth-child(5) div').textContent.trim();
                const celular1 = row.querySelector('td:nth-child(6) div').textContent.trim();
                const celular2 = row.querySelector('td:nth-child(6) .text-muted')?.textContent.trim() || '';
                const tipoRegistro = row.querySelector('td:nth-child(7) .badge').textContent.trim() === 'Nuevo' ? 'new' : 'returning';
                const fecha = row.querySelector('td:nth-child(8)').textContent.split('/').reverse().join('-') + 
                            'T' + row.querySelector('td:nth-child(8) .text-muted').textContent;
                
                // Llenar el formulario del modal
                document.getElementById('editarId').value = id;
                document.getElementById('editarNombres').value = nombres;
                document.getElementById('editarTipoDoc').value = tipoDoc;
                document.getElementById('editarNumDoc').value = numDoc;
                document.getElementById('editarEmail').value = email;
                document.getElementById('editarCelular1').value = celular1;
                document.getElementById('editarCelular2').value = celular2;
                document.getElementById('editarTipoRegistro').value = tipoRegistro;
                document.getElementById('editarNivel').value = nivel;
                document.getElementById('editarHorario').value = horario;
                
                // Formatear fecha para el input datetime-local
                const [datePart, timePart] = fecha.split('T');
                const formattedDate = datePart.split('-').reverse().join('-') + 'T' + timePart;
                document.getElementById('editarFecha').value = formattedDate;
                
                // Mostrar foto actual si existe
                const previewFoto = document.getElementById('previewFoto');
                if (foto) {
                    previewFoto.src = foto;
                    previewFoto.style.display = 'block';
                } else {
                    previewFoto.style.display = 'none';
                }
                
                // Mostrar el modal
                const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
                modal.show();
            });
        });
        
        // Previsualización de la nueva foto seleccionada
        document.getElementById('editarFoto').addEventListener('change', function(e) {
            const previewFoto = document.getElementById('previewFoto');
            if (this.files && this.files[0]) {
                // Validar tamaño máximo (2MB)
                if (this.files[0].size > 2 * 1024 * 1024) {
                    alert('La imagen no debe exceder los 2MB');
                    this.value = '';
                    return;
                }
                
                // Validar tipo de archivo
                const validTypes = ['image/jpeg', 'image/png'];
                if (!validTypes.includes(this.files[0].type)) {
                    alert('Solo se permiten imágenes JPEG o PNG');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFoto.src = e.target.result;
                    previewFoto.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Validar longitud máxima en tiempo real
        document.getElementById('editarNombres').addEventListener('input', function() {
            if (this.value.length > 100) {
                this.value = this.value.substring(0, 100);
            }
        });

        document.getElementById('editarEmail').addEventListener('input', function() {
            if (this.value.length > 100) {
                this.value = this.value.substring(0, 100);
            }
        });

        document.getElementById('editarHorario').addEventListener('input', function() {
            if (this.value.length > 50) {
                this.value = this.value.substring(0, 50);
            }
        });

        // Validar solo números para documentos y celulares
        document.getElementById('editarNumDoc').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });

        document.getElementById('editarCelular1').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });

        document.getElementById('editarCelular2').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });
    });
</script>

    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.form-eliminar').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault(); // Detiene el envío del formulario

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Esta acción eliminará el registro permanentemente.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Ahora sí, enviar el formulario
                        }
                    });
                });
            });
        });
    </script>


</body>
</html>

<?php 
$stmt->close();
$conn->close(); 
?>