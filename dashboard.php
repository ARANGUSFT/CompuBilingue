<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Inscripciones</title>
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #34495e;
            --gray: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--secondary);
            color: white;
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: var(--primary-dark);
            text-align: center;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu li {
            list-style: none;
            padding: 10px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar-menu li:hover {
            background-color: rgba(255,255,255,0.1);
            border-left: 4px solid var(--primary);
        }
        
        .sidebar-menu li.active {
            background-color: rgba(255,255,255,0.1);
            border-left: 4px solid var(--primary);
        }
        
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .header h1 {
            color: var(--dark);
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-primary {
            border-top: 4px solid var(--primary);
        }
        
        .card-success {
            border-top: 4px solid var(--success);
        }
        
        .card-warning {
            border-top: 4px solid var(--warning);
        }
        
        .card-danger {
            border-top: 4px solid var(--danger);
        }
        
        .card-title {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 10px;
        }
        
        .card-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark);
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        th {
            background-color: var(--primary);
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f1f7fd;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-group .btn {
            margin-right: 5px;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header span,
            .sidebar-menu li a span {
                display: none;
            }
            
            .sidebar-menu li {
                text-align: center;
                padding: 15px 10px;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> <span>Educación Plus</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <a href="#"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            </li>
            <li>
                <a href="#"><i class="fas fa-user-graduate"></i> <span>Estudiantes</span></a>
            </li>
            <li>
                <a href="#"><i class="fas fa-clipboard-list"></i> <span>Inscripciones</span></a>
            </li>
            <li>
                <a href="#"><i class="fas fa-chart-line"></i> <span>Reportes</span></a>
            </li>
            <li>
                <a href="#"><i class="fas fa-cog"></i> <span>Configuración</span></a>
            </li>
            <li>
                <a href="#"><i class="fas fa-users"></i> <span>Usuarios</span></a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Gestión de Inscripciones</h1>
            <div class="user-info">
                <img src="https://via.placeholder.com/40" alt="Usuario">
                <span>Administrador</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="card card-primary">
                <div class="card-title">Total Inscripciones</div>
                <div class="card-value">1,245</div>
                <div><i class="fas fa-arrow-up text-success"></i> 12% este mes</div>
            </div>
            <div class="card card-success">
                <div class="card-title">Activas</div>
                <div class="card-value">856</div>
                <div><i class="fas fa-arrow-up text-success"></i> 8% este mes</div>
            </div>
            <div class="card card-warning">
                <div class="card-title">Pendientes</div>
                <div class="card-value">124</div>
                <div><i class="fas fa-arrow-down text-danger"></i> 3% este mes</div>
            </div>
            <div class="card card-danger">
                <div class="card-title">Canceladas</div>
                <div class="card-value">45</div>
                <div><i class="fas fa-arrow-down text-danger"></i> 1% este mes</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <button class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Inscripción</button>
                    <button class="btn"><i class="fas fa-download"></i> Exportar</button>
                </div>
                <div>
                    <input type="text" placeholder="Buscar..." style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estudiante</th>
                        <th>Documento</th>
                        <th>Nivel</th>
                        <th>Contacto</th>
                        <th>Acudiente</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($fila = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $fila['id']; ?></td>
                        <td style="display: flex; align-items: center;">
                            <?php if(!empty($fila['foto'])): ?>
                                <img src="<?php echo $fila['foto']; ?>" class="avatar" alt="Estudiante">
                            <?php else: ?>
                                <div class="avatar" style="background: #eee; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="color: #999;"></i>
                                </div>
                            <?php endif; ?>
                            <div style="margin-left: 10px;">
                                <strong><?php echo htmlspecialchars($fila['nombres_estudiante']); ?></strong><br>
                                <small><?php echo htmlspecialchars($fila['municipio_residencia']); ?></small>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($fila['doc_type']); ?><br>
                            <?php echo htmlspecialchars($fila['numero_documento']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($fila['nivel_aspira']); ?><br>
                            <small><?php echo htmlspecialchars($fila['nivel_escolaridad']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($fila['email']); ?><br>
                            <?php echo htmlspecialchars($fila['celular']); ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($fila['nombre_acudiente']); ?></strong><br>
                            <?php echo htmlspecialchars($fila['contacto_acudiente']); ?>
                        </td>
                        <td>
                            <span class="status-badge status-active">Activo</span>
                        </td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($fila['created_at'])); ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-primary btn-sm" title="Ver"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-primary btn-sm" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <div>
                    <select style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option>Mostrar 10 registros</option>
                        <option>Mostrar 25 registros</option>
                        <option>Mostrar 50 registros</option>
                    </select>
                </div>
                <div>
                    <button class="btn"><i class="fas fa-angle-left"></i></button>
                    <button class="btn btn-primary">1</button>
                    <button class="btn">2</button>
                    <button class="btn">3</button>
                    <button class="btn"><i class="fas fa-angle-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>