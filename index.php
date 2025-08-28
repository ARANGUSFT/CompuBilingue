<?php
    // ====== SESIÓN / CSRF / CONTADOR ======
    session_start();

    /* Errores y old input provenientes del procesador */
    $errores = $_SESSION['errores'] ?? [];
    $old     = $_SESSION['old']     ?? [];
    unset($_SESSION['errores'], $_SESSION['old']);

    /* CSRF: generar solo si no existe */
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];

    /* Contador de inscripciones (total + 1000) */
    $numero_para_mostrar = 1000; // valor por defecto si hay error de DB
    $mysqli = @new mysqli("localhost", "root", "", "form");
    if (!$mysqli->connect_errno) {
        if ($res = $mysqli->query("SELECT COUNT(*) AS total FROM inscripciones")) {
            if ($row = $res->fetch_assoc()) {
                $numero_para_mostrar = (int)$row['total'] + 1000;
            }
            $res->free();
        }
        $mysqli->close();
    }
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inscripción - Global School</title>
</head>
<body>

<style>
    :root {
        --color-primario: #2E7D32;
        --color-secundario: #4CAF50;
        --color-terciario: #E8F5E9;
        --color-fondo: #F5F9F6;
        --color-borde: #C8E6C9;
        --color-texto: #1B5E20;
        --color-texto-secundario: #388E3C;
        --color-destacado: #FF9800;
        --color-error: #f44336;
        --transition: all 0.3s ease;
        --sombra-suave: 0 4px 12px rgba(46, 125, 50, 0.08);
        --sombra-media: 0 6px 20px rgba(46, 125, 50, 0.12);
        --border-radius: 8px;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: var(--color-fondo);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--color-texto);
        line-height: 1.6;
    }

    .form-container {
        width: 100%;
        max-width: 800px;
        margin: 20px auto;
        background-color: white;
        padding: 35px;
        box-shadow: var(--sombra-media);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--color-borde);
    }

    .form-container::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--color-primario), var(--color-secundario), var(--color-destacado), var(--color-secundario), var(--color-primario));
        background-size: 200% 100%;
        animation: gradientMove 3s ease infinite;
    }

    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Estilos para secciones */
    .photo-section,
    .personal-data-section,
    .reference-section,
    .welcome-section,
    .registration-section {
        margin: 30px 0;
        padding: 25px 20px;
        background: linear-gradient(to bottom, #f8fff8, var(--color-terciario));
        border-radius: var(--border-radius);
        box-shadow: var(--sombra-suave);
        border: 1px solid var(--color-borde);
    }

    .section-title {
        font-weight: bold;
        margin: 0 0 25px 0;
        font-size: 20px;
        color: var(--color-primario);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-align: center;
    }

    .section-title i {
        color: var(--color-destacado);
        font-size: 24px;
    }

    /* Sección de foto */
    .photo-upload-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .photo-upload {
        width: 180px;
        height: 220px;
        border: 2px dashed var(--color-primario);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        cursor: pointer;
        background-color: white;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        transition: var(--transition);
        padding: 15px;
    }

    .photo-upload:hover {
        background-color: #e8f5e9;
        transform: translateY(-5px);
        box-shadow: var(--sombra-media);
        border-color: var(--color-destacado);
    }

    .photo-upload.input-error {
        border-color: var(--color-error);
        animation: shake 0.5s;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    .photo-upload input[type="file"] {
        display: none;
    }

    .photo-upload img {
        max-width: 100%;
        max-height: 100%;
        display: none;
        border-radius: 8px;
        object-fit: cover;
    }

    .photo-upload .upload-icon {
        font-size: 42px;
        margin-bottom: 15px;
        color: var(--color-primario);
    }

    .photo-upload .upload-text {
        font-size: 16px;
        color: var(--color-texto);
        font-weight: 600;
        margin-bottom: 8px;
    }

    .photo-upload .upload-instruction {
        font-size: 13px;
        color: var(--color-texto-secundario);
        line-height: 1.4;
    }

    .photo-requirements {
        margin-top: 20px;
        padding: 15px;
        background-color: #f1f8e9;
        border-radius: 8px;
        font-size: 14px;
        color: var(--color-texto-secundario);
        text-align: left;
        max-width: 500px;
    }

    .photo-requirements ul {
        padding-left: 20px;
        margin: 10px 0 0 0;
    }

    .photo-requirements li {
        margin-bottom: 5px;
    }

    /* Formulario general */
    .form-group {
        margin-bottom: 20px;
        width: 100%;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--color-texto);
    }

    input[type="text"],
    input[type="tel"],
    input[type="email"],
    select,
    textarea,
    .large-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--color-borde);
        box-sizing: border-box;
        font-size: 15px;
        border-radius: var(--border-radius);
        transition: var(--transition);
        background-color: #fff;
        color: var(--color-texto);
        font-family: inherit;
    }

    input[type="text"]:focus,
    input[type="tel"]:focus,
    input[type="email"]:focus,
    select:focus,
    textarea:focus,
    .large-input:focus {
        border-color: var(--color-primario);
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        outline: none;
    }

    .input-error {
        border-color: var(--color-error) !important;
        box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.2) !important;
    }

    .error-message {
        color: var(--color-error);
        margin-top: 8px;
        font-size: 14px;
        font-weight: 500;
        background: #ffebee;
        padding: 8px 12px;
        border-radius: 6px;
        display: inline-block;
    }

    /* Sistema de grid para formulario */
    .form-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    /* Checkboxes y radios */
    .checkbox-group,
    .radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin: 10px 0;
    }

    .checkbox-label,
    .radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 12px;
        background: var(--color-terciario);
        border-radius: 20px;
        transition: var(--transition);
    }

    .checkbox-label:hover,
    .radio-label:hover {
        background-color: #c8e6c9;
    }

    .checkbox-label input[type="checkbox"],
    .radio-label input[type="radio"] {
        margin: 0;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 12px 20px;
        background: var(--color-terciario);
        border-radius: var(--border-radius);
        transition: var(--transition);
        border: 2px solid transparent;
    }

    .radio-option:hover {
        background-color: #e0f2e1;
        border-color: var(--color-primario);
    }

    .radio-option input[type="radio"] {
        margin: 0;
    }

    .radio-checkmark {
        width: 18px;
        height: 18px;
        border: 2px solid var(--color-primario);
        border-radius: 50%;
        position: relative;
    }

    .radio-option input[type="radio"]:checked + .radio-checkmark::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background: var(--color-primario);
        border-radius: 50%;
    }

    .radio-label {
        font-weight: 600;
        color: var(--color-texto);
    }

    /* Sección de bienvenida */
    .welcome-message {
        text-align: center;
        font-weight: bold;
        margin: 25px 0;
        font-size: clamp(18px, 4vw, 22px);
        color: var(--color-primario);
        padding: 20px;
        background: linear-gradient(to right, transparent, var(--color-terciario), transparent);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .welcome-message i {
        color: var(--color-destacado);
        font-size: 28px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-container {
            padding: 25px 20px;
        }
        
        .photo-section,
        .personal-data-section,
        .reference-section,
        .welcome-section,
        .registration-section {
            padding: 20px 15px;
            margin: 20px 0;
        }
        
        .photo-upload {
            width: 160px;
            height: 200px;
        }
        
        .section-title {
            font-size: 18px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .checkbox-group,
        .radio-group {
            flex-direction: column;
            gap: 10px;
        }
        
        .welcome-message {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 15px 10px;
        }
        
        .form-container {
            padding: 20px 15px;
        }
        
        .photo-upload {
            width: 140px;
            height: 180px;
        }
        
        .photo-upload .upload-icon {
            font-size: 36px;
        }
        
        .photo-upload .upload-text {
            font-size: 14px;
        }
        
        .photo-upload .upload-instruction {
            font-size: 12px;
        }
        
        .photo-requirements {
            font-size: 13px;
        }
        
        input[type="text"],
        input[type="tel"],
        input[type="email"],
        select,
        textarea,
        .large-input {
            padding: 10px 12px;
            font-size: 14px;
        }
        
        .welcome-message {
            font-size: 16px;
            padding: 15px;
        }
        
        .radio-option {
            padding: 10px 15px;
        }
    }
</style>


<style>
        /* Encabezado */
    .header-section {
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
        border-radius: 12px;
        color: white;
        box-shadow: var(--sombra-media);
    }

    .header-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .header-text {
        text-align: center;
        order: 2;
    }

    .header-text h1 {
        font-size: clamp(14px, 3vw, 16px);
        margin: 5px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 500;
        opacity: 0.9;
    }

    .header-text h2 {
        font-size: clamp(20px, 5vw, 28px);
        margin: 10px 0 15px 0;
        font-weight: 700;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
    }

    .counter-wrap {
        background: rgba(255, 255, 255, 0.15);
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .counter-number {
        font-size: clamp(24px, 6vw, 36px);
        font-weight: 800;
        margin: 0;
        color: #FFFFFF;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .counter-label {
        font-size: clamp(12px, 2.5vw, 14px);
        opacity: 0.9;
        margin-top: 5px;
        font-weight: 500;
    }

    .logo-container {
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 50%;
        padding: 12px;
        box-shadow: var(--sombra-media);
        display: flex;
        align-items: center;
        justify-content: center;
        order: 1;
        transition: var(--transition);
    }

    .logo-container:hover {
        transform: scale(1.05) rotate(5deg);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .logo-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .logo-placeholder {
        text-align: center;
        color: var(--color-texto-secundario);
        font-size: 12px;
        padding: 10px;
    }

    /* Responsive para el encabezado */
    @media (min-width: 768px) {
        .header-container {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            text-align: left;
        }

        .header-text {
            text-align: left;
            order: 1;
            flex: 1;
        }

        .logo-container {
            order: 2;
            width: 120px;
            height: 120px;
            margin-left: 20px;
        }
    }

    @media (max-width: 480px) {
        .header-section {
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .counter-wrap {
            padding: 12px;
        }
        
        .logo-container {
            width: 80px;
            height: 80px;
            padding: 8px;
        }
    }


    /* Botón de envío */
    .form-actions {
        text-align: center;
        margin: 40px 0 20px 0;
        padding: 20px 0;
        border-top: 2px solid var(--color-borde);
    }

    .submit-btn {
        background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
        color: white;
        padding: 16px 40px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-size: 18px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.4s ease;
        box-shadow: 0 6px 15px rgba(46, 125, 50, 0.3);
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: 0.5s;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(46, 125, 50, 0.4);
        background: linear-gradient(135deg, var(--color-secundario), var(--color-primario));
    }

    .submit-btn:hover::before {
        left: 100%;
    }

    .submit-btn:active {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
    }

    .submit-btn:disabled {
        background: linear-gradient(135deg, #cccccc, #999999);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .submit-btn:disabled:hover::before {
        display: none;
    }

    /* Efecto de carga */
    .submit-btn.loading {
        position: relative;
        color: transparent;
    }

    .submit-btn.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive para el botón */
    @media (max-width: 768px) {
        .form-actions {
            margin: 30px 0 15px 0;
            padding: 15px 0;
        }
        
        .submit-btn {
            padding: 14px 35px;
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        .submit-btn {
            width: 100%;
            padding: 14px 20px;
            font-size: 15px;
        }
    }
</style>


<div class="form-container">

    <!-- Encabezado con logo institucional -->
    <header class="header-section" role="banner">
        <div class="header-container">
            <!-- Texto institucional -->
            <div class="header-text" aria-label="Encabezado institucional">
                <h1>INSTITUCIÓN DE EDUCACIÓN PARA EL TRABAJO Y EL DESARROLLO HUMANO</h1>
                <h2>FORMULARIO DE INSCRIPCIÓN</h2>

                <!-- Contador -->
                <div class="counter-wrap" aria-live="polite">
                    <h3 class="counter-number">
                        <?= number_format($numero_para_mostrar, 0, ',', '.') ?>
                    </h3>
                    <div class="counter-label">Inscripciones acumuladas</div>
                </div>
            </div>

            <!-- Logo -->
            <div class="logo-container" aria-label="Logo institucional">
                <img
                    id="institutionalLogo"
                    src="asset/img/logo.png"
                    alt="Logo de Global School"
                    onerror="this.style.display='none'; document.getElementById('logoPlaceholder').style.display='block'">
                <div id="logoPlaceholder" class="logo-placeholder" style="display:none;">
                    Logo no disponible
                </div>
            </div>
        </div>
    </header>


    <form id="globalSchoolForm" action="controllers/procesar-inscripcion.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    
        <!-- Sección de foto del estudiante -->
        <section class="photo-section">
            <h3 class="section-title"><i class="fas fa-camera"></i> Foto del Estudiante</h3>
            
            <div class="photo-upload-container">
                <label for="photoUpload" class="photo-upload <?php echo isset($errores['photoUpload']) ? 'input-error' : ''; ?>" id="photoUploadArea">
                    <input type="file" id="photoUpload" name="photoUpload" accept="image/*">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">FOTO DEL ESTUDIANTE</div>
                    <div class="upload-instruction">(Haga clic para subir una imagen)</div>
                    <img id="photoPreview" src="#" alt="Vista previa de la foto del estudiante" style="display: none;">
                </label>
                
                <?php if (isset($errores['photoUpload'])): ?>
                    <div class="error-message"><?php echo $errores['photoUpload']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="photo-requirements">
                <p><strong>Requisitos de la foto:</strong></p>
                <ul>
                    <li>Formato: JPG o PNG</li>
                    <li>Tamaño máximo: 2MB</li>
                    <li>Fondo claro o neutro</li>
                    <li>La cara debe ser claramente visible</li>
                </ul>
            </div>
        </section>

        <!-- Sección de datos personales -->
        <section class="personal-data-section">
            <h3 class="section-title"><i class="fas fa-user"></i> DATOS PERSONALES</h3>

            <div class="form-group">
                <label for="nombres_estudiante">APELLIDOS Y NOMBRE DEL ESTUDIANTE</label>
                <input type="text" id="nombres_estudiante" name="nombres_estudiante" class="large-input <?php echo isset($errores['nombres_estudiante']) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($old['nombres_estudiante'] ?? ''); ?>">
                <?php if (isset($errores['nombres_estudiante'])): ?>
                    <div class="error-message"><?php echo $errores['nombres_estudiante']; ?></div>
                <?php endif; ?>
            </div>

            <!-- Nivel y Horario -->
            <div class="form-row">
                <div class="form-group">
                    <label>NIVEL AL QUE ASPIRA</label>
                    <div class="checkbox-group">
                        <?php
                        $niveles = ['A1', 'A2', 'B1', 'B2'];
                        foreach ($niveles as $nivel) {
                            $checked = (isset($old['nivel']) && in_array($nivel, $old['nivel'])) ? 'checked' : '';
                            echo "<label class='checkbox-label'><input type='checkbox' id='nivel_$nivel' name='nivel[]' value='$nivel' $checked><span>$nivel</span></label>";
                        }
                        ?>
                    </div>
                    <?php if (isset($errores['nivel'])): ?>
                        <div class="error-message"><?php echo $errores['nivel']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="horario">HORARIO</label>
                    <select id="horario" name="horario" class="<?php echo isset($errores['horario']) ? 'input-error' : ''; ?>">
                        <option value="">Seleccione un horario...</option>
                        <optgroup label="Modalidad Presencial">
                            <option value="Lunes a Jueves 6:00 a 8:00 PM" <?php echo (isset($old['horario']) && $old['horario'] == 'Lunes a Jueves 6:00 a 8:00 PM') ? 'selected' : ''; ?>>Lunes a Jueves 6:00 a 8:00 PM</option>
                            <option value="Lunes a Jueves 3:00 a 5:00 PM" <?php echo (isset($old['horario']) && $old['horario'] == 'Lunes a Jueves 3:00 a 5:00 PM') ? 'selected' : ''; ?>>Lunes a Jueves 3:00 a 5:00 PM</option>
                            <option value="Sábados 8:00 a 11:00 AM" <?php echo (isset($old['horario']) && $old['horario'] == 'Sábados 8:00 a 11:00 AM') ? 'selected' : ''; ?>>Sábados 8:00 a 11:00 AM</option>
                        </optgroup>
                        <optgroup label="Modalidad Virtual">
                            <option value="Lunes a Jueves 6:30 a 8:30 PM" <?php echo (isset($old['horario']) && $old['horario'] == 'Lunes a Jueves 6:30 a 8:30 PM') ? 'selected' : ''; ?>>Lunes a Jueves 6:30 a 8:30 PM</option>
                        </optgroup>
                    </select>
                    <?php if (isset($errores['horario'])): ?>
                        <div class="error-message"><?php echo $errores['horario']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información socioeconómica -->
            <div class="form-row">
                <div class="form-group">
                    <label for="estrato">ESTRATO SOCIOECONÓMICO</label>
                    <select id="estrato" name="estrato_socioeconomico" class="<?php echo isset($errores['estrato_socioeconomico']) ? 'input-error' : ''; ?>">
                        <option value="">Seleccione...</option>
                        <?php
                        foreach (range(1, 6) as $i) {
                            $selected = ($old['estrato_socioeconomico'] ?? '') == $i ? 'selected' : '';
                            echo "<option value='$i' $selected>Estrato $i</option>";
                        }
                        ?>
                    </select>
                    <?php if (isset($errores['estrato_socioeconomico'])): ?>
                        <div class="error-message"><?php echo $errores['estrato_socioeconomico']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="eps">EPS o SISBEN</label>
                    <input type="text" id="eps" name="eps" placeholder="Escriba aquí"
                        class="<?php echo isset($errores['eps']) ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($old['eps'] ?? ''); ?>">
                    <?php if (isset($errores['eps'])): ?>
                        <div class="error-message"><?php echo $errores['eps']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="escolaridad">NIVEL ESCOLARIDAD</label>
                    <select id="escolaridad" name="nivel_escolaridad" class="<?php echo isset($errores['nivel_escolaridad']) ? 'input-error' : ''; ?>">
                        <option value="">Seleccione...</option>
                        <?php
                        $niveles = ['Primaria', 'Secundaria', 'Técnico', 'Tecnológico', 'Universitario', 'Postgrado'];
                        foreach ($niveles as $nivel) {
                            $selected = ($old['nivel_escolaridad'] ?? '') == $nivel ? 'selected' : '';
                            echo "<option value='$nivel' $selected>$nivel</option>";
                        }
                        ?>
                    </select>
                    <?php if (isset($errores['nivel_escolaridad'])): ?>
                        <div class="error-message"><?php echo $errores['nivel_escolaridad']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Documento e identificación -->
            <div class="form-row">
                <div class="form-group">
                    <label>DOCUMENTO DE IDENTIDAD</label>
                    <div class="radio-group">
                        <?php
                        $docTypes = ['CC', 'TI', 'PPT'];
                        foreach ($docTypes as $doc) {
                            $checked = ($old['doc_type'] ?? '') == $doc ? 'checked' : '';
                            echo "<label class='radio-label'><input type='radio' name='doc_type' value='$doc' $checked><span>$doc</span></label>";
                        }
                        ?>
                    </div>
                    <input type="text" name="numero_documento" placeholder="Número de documento"
                        class="<?php echo isset($errores['numero_documento']) ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($old['numero_documento'] ?? ''); ?>">
                    <?php if (isset($errores['numero_documento'])): ?>
                        <div class="error-message"><?php echo $errores['numero_documento']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">EMAIL</label>
                    <input type="email" id="email" name="email" placeholder="Correo electrónico"
                        class="<?php echo isset($errores['email']) ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                    <?php if (isset($errores['email'])): ?>
                        <div class="error-message"><?php echo $errores['email']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="municipio">MUNICIPIO RESIDENCIA</label>
                                  <select id="municipio" name="municipio_residencia" class="<?php echo isset($errores['municipio_residencia']) ? 'input-error' : ''; ?>">
                            <option value="">Seleccione un municipio</option>
                                <!-- Valle de Aburrá -->
                                <option value="medellin" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'medellin') ? 'selected' : ''; ?>>Medellín</option>
                                <option value="bello" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'bello') ? 'selected' : ''; ?>>Bello</option>
                                <option value="itagui" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'itagui') ? 'selected' : ''; ?>>Itagüí</option>
                                <option value="sabaneta" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sabaneta') ? 'selected' : ''; ?>>Sabaneta</option>
                                <option value="envigado" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'envigado') ? 'selected' : ''; ?>>Envigado</option>
                                <option value="caldas" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'caldas') ? 'selected' : ''; ?>>Caldas</option>
                                <option value="laestrella" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'laestrella') ? 'selected' : ''; ?>>La Estrella</option>
                                <option value="copacabana" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'copacabana') ? 'selected' : ''; ?>>Copacabana</option>
                                <option value="barbosa" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'barbosa') ? 'selected' : ''; ?>>Barbosa</option>
                                <option value="girardota" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'girardota') ? 'selected' : ''; ?>>Girardota</option>
                                
                                <!-- Resto de municipios de Antioquia (orden alfabético) -->
                                <option value="abejorral" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'abejorral') ? 'selected' : ''; ?>>Abejorral</option>
                                <option value="abriaqui" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'abriaqui') ? 'selected' : ''; ?>>Abriaquí</option>
                                <option value="alejandria" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'alejandria') ? 'selected' : ''; ?>>Alejandría</option>
                                <option value="amaga" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'amaga') ? 'selected' : ''; ?>>Amagá</option>
                                <option value="amalfi" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'amalfi') ? 'selected' : ''; ?>>Amalfi</option>
                                <option value="andes" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'andes') ? 'selected' : ''; ?>>Andes</option>
                                <option value="angelopolis" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'angelopolis') ? 'selected' : ''; ?>>Angelópolis</option>
                                <option value="angostura" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'angostura') ? 'selected' : ''; ?>>Angostura</option>
                                <option value="anori" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'anori') ? 'selected' : ''; ?>>Anorí</option>
                                <option value="antioquia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'antioquia') ? 'selected' : ''; ?>>Santa Fe de Antioquia</option>
                                <option value="anza" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'anza') ? 'selected' : ''; ?>>Anzá</option>
                                <option value="apartado" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'apartado') ? 'selected' : ''; ?>>Apartadó</option>
                                <option value="arboletes" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'arboletes') ? 'selected' : ''; ?>>Arboletes</option>
                                <option value="argelia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'argelia') ? 'selected' : ''; ?>>Argelia</option>
                                <option value="armenia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'armenia') ? 'selected' : ''; ?>>Armenia</option>
                                <option value="belmira" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'belmira') ? 'selected' : ''; ?>>Belmira</option>
                                <option value="betania" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'betania') ? 'selected' : ''; ?>>Betania</option>
                                <option value="betulia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'betulia') ? 'selected' : ''; ?>>Betulia</option>
                                <option value="briceño" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'briceño') ? 'selected' : ''; ?>>Briceño</option>
                                <option value="buritica" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'buritica') ? 'selected' : ''; ?>>Buriticá</option>
                                <option value="caceres" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'caceres') ? 'selected' : ''; ?>>Cáceres</option>
                                <option value="caicedo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'caicedo') ? 'selected' : ''; ?>>Caicedo</option>
                                <option value="campamento" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'campamento') ? 'selected' : ''; ?>>Campamento</option>
                                <option value="canasgordas" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'canasgordas') ? 'selected' : ''; ?>>Cañasgordas</option>
                                <option value="caracoli" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'caracoli') ? 'selected' : ''; ?>>Caracolí</option>
                                <option value="caramanta" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'caramanta') ? 'selected' : ''; ?>>Caramanta</option>
                                <option value="carepa" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'carepa') ? 'selected' : ''; ?>>Carepa</option>
                                <option value="carolina" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'carolina') ? 'selected' : ''; ?>>Carolina del Príncipe</option>
                                <option value="caucasia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'caucasia') ? 'selected' : ''; ?>>Caucasia</option>
                                <option value="chigorodo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'chigorodo') ? 'selected' : ''; ?>>Chigorodó</option>
                                <option value="cisneros" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'cisneros') ? 'selected' : ''; ?>>Cisneros</option>
                                <option value="cocorna" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'cocorna') ? 'selected' : ''; ?>>Cocorná</option>
                                <option value="concepcion" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'concepcion') ? 'selected' : ''; ?>>Concepción</option>
                                <option value="concordia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'concordia') ? 'selected' : ''; ?>>Concordia</option>
                                <option value="dabeiba" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'dabeiba') ? 'selected' : ''; ?>>Dabeiba</option>
                                <option value="donmatias" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'donmatias') ? 'selected' : ''; ?>>Donmatías</option>
                                <option value="ebejico" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'ebejico') ? 'selected' : ''; ?>>Ebéjico</option>
                                <option value="elbagre" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'elbagre') ? 'selected' : ''; ?>>El Bagre</option>
                                <option value="elcarmen" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'elcarmen') ? 'selected' : ''; ?>>El Carmen de Viboral</option>
                                <option value="elpenol" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'elpenol') ? 'selected' : ''; ?>>El Peñol</option>
                                <option value="elretiro" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'elretiro') ? 'selected' : ''; ?>>El Retiro</option>
                                <option value="elsantuario" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'elsantuario') ? 'selected' : ''; ?>>El Santuario</option>
                                <option value="entrerrios" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'entrerrios') ? 'selected' : ''; ?>>Entrerríos</option>
                                <option value="fredonia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'fredonia') ? 'selected' : ''; ?>>Fredonia</option>
                                <option value="frontino" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'frontino') ? 'selected' : ''; ?>>Frontino</option>
                                <option value="giraldo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'giraldo') ? 'selected' : ''; ?>>Giraldo</option>
                                <option value="gomezplata" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'gomezplata') ? 'selected' : ''; ?>>Gómez Plata</option>
                                <option value="granada" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'granada') ? 'selected' : ''; ?>>Granada</option>
                                <option value="guadalupe" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'guadalupe') ? 'selected' : ''; ?>>Guadalupe</option>
                                <option value="guarne" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'guarne') ? 'selected' : ''; ?>>Guarne</option>
                                <option value="guatape" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'guatape') ? 'selected' : ''; ?>>Guatapé</option>
                                <option value="heliconia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'heliconia') ? 'selected' : ''; ?>>Heliconia</option>
                                <option value="hispania" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'hispania') ? 'selected' : ''; ?>>Hispania</option>
                                <option value="ituango" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'ituango') ? 'selected' : ''; ?>>Ituango</option>
                                <option value="jardin" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'jardin') ? 'selected' : ''; ?>>Jardín</option>
                                <option value="jerico" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'jerico') ? 'selected' : ''; ?>>Jericó</option>
                                <option value="laceja" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'laceja') ? 'selected' : ''; ?>>La Ceja</option>
                                <option value="lapintada" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'lapintada') ? 'selected' : ''; ?>>La Pintada</option>
                                <option value="launion" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'launion') ? 'selected' : ''; ?>>La Unión</option>
                                <option value="liborina" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'liborina') ? 'selected' : ''; ?>>Liborina</option>
                                <option value="maceo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'maceo') ? 'selected' : ''; ?>>Maceo</option>
                                <option value="marinilla" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'marinilla') ? 'selected' : ''; ?>>Marinilla</option>
                                <option value="montebello" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'montebello') ? 'selected' : ''; ?>>Montebello</option>
                                <option value="murindo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'murindo') ? 'selected' : ''; ?>>Murindó</option>
                                <option value="mutata" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'mutata') ? 'selected' : ''; ?>>Mutatá</option>
                                <option value="narino" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'narino') ? 'selected' : ''; ?>>Nariño</option>
                                <option value="necocli" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'necocli') ? 'selected' : ''; ?>>Necoclí</option>
                                <option value="nechi" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'nechi') ? 'selected' : ''; ?>>Nechí</option>
                                <option value="olaya" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'olaya') ? 'selected' : ''; ?>>Olaya</option>
                                <option value="peque" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'peque') ? 'selected' : ''; ?>>Peque</option>
                                <option value="pueblorrico" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'pueblorrico') ? 'selected' : ''; ?>>Pueblorrico</option>
                                <option value="puertoberrio" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'puertoberrio') ? 'selected' : ''; ?>>Puerto Berrío</option>
                                <option value="puertonare" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'puertonare') ? 'selected' : ''; ?>>Puerto Nare</option>
                                <option value="puertotriunfo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'puertotriunfo') ? 'selected' : ''; ?>>Puerto Triunfo</option>
                                <option value="remedios" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'remedios') ? 'selected' : ''; ?>>Remedios</option>
                                <option value="retiro" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'retiro') ? 'selected' : ''; ?>>El Retiro</option>
                                <option value="rionegro" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'rionegro') ? 'selected' : ''; ?>>Rionegro</option>
                                <option value="sabanalarga" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sabanalarga') ? 'selected' : ''; ?>>Sabanalarga</option>
                                <option value="salgar" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'salgar') ? 'selected' : ''; ?>>Salgar</option>
                                <option value="sanandres" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanandres') ? 'selected' : ''; ?>>San Andrés de Cuerquia</option>
                                <option value="sancarlos" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sancarlos') ? 'selected' : ''; ?>>San Carlos</option>
                                <option value="sanfrancisco" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanfrancisco') ? 'selected' : ''; ?>>San Francisco</option>
                                <option value="sanjerónimo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanjerónimo') ? 'selected' : ''; ?>>San Jerónimo</option>
                                <option value="sanjose" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanjose') ? 'selected' : ''; ?>>San José de la Montaña</option>
                                <option value="sanjuan" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanjuan') ? 'selected' : ''; ?>>San Juan de Urabá</option>
                                <option value="sanluis" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanluis') ? 'selected' : ''; ?>>San Luis</option>
                                <option value="sanpedro" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanpedro') ? 'selected' : ''; ?>>San Pedro de los Milagros</option>
                                <option value="sanpedrouraba" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanpedrouraba') ? 'selected' : ''; ?>>San Pedro de Urabá</option>
                                <option value="sanrafael" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanrafael') ? 'selected' : ''; ?>>San Rafael</option>
                                <option value="sanroque" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanroque') ? 'selected' : ''; ?>>San Roque</option>
                                <option value="sanvicente" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sanvicente') ? 'selected' : ''; ?>>San Vicente</option>
                                <option value="santabarbara" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'santabarbara') ? 'selected' : ''; ?>>Santa Bárbara</option>
                                <option value="santarosa" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'santarosa') ? 'selected' : ''; ?>>Santa Rosa de Osos</option>
                                <option value="santodomingo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'santodomingo') ? 'selected' : ''; ?>>Santo Domingo</option>
                                <option value="santuario" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'santuario') ? 'selected' : ''; ?>>El Santuario</option>
                                <option value="segovia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'segovia') ? 'selected' : ''; ?>>Segovia</option>
                                <option value="sonson" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sonson') ? 'selected' : ''; ?>>Sonsón</option>
                                <option value="sopetran" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'sopetran') ? 'selected' : ''; ?>>Sopetrán</option>
                                <option value="tamesis" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'tamesis') ? 'selected' : ''; ?>>Támesis</option>
                                <option value="taraza" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'taraza') ? 'selected' : ''; ?>>Tarazá</option>
                                <option value="tarso" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'tarso') ? 'selected' : ''; ?>>Tarso</option>
                                <option value="titiribi" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'titiribi') ? 'selected' : ''; ?>>Titiribí</option>
                                <option value="toledo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'toledo') ? 'selected' : ''; ?>>Toledo</option>
                                <option value="turbo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'turbo') ? 'selected' : ''; ?>>Turbo</option>
                                <option value="uramita" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'uramita') ? 'selected' : ''; ?>>Uramita</option>
                                <option value="urrao" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'urrao') ? 'selected' : ''; ?>>Urrao</option>
                                <option value="valdivia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'valdivia') ? 'selected' : ''; ?>>Valdivia</option>
                                <option value="valparaiso" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'valparaiso') ? 'selected' : ''; ?>>Valparaíso</option>
                                <option value="vegachi" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'vegachi') ? 'selected' : ''; ?>>Vegachí</option>
                                <option value="venecia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'venecia') ? 'selected' : ''; ?>>Venecia</option>
                                <option value="vigia" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'vigia') ? 'selected' : ''; ?>>Vigía del Fuerte</option>
                                <option value="yali" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'yali') ? 'selected' : ''; ?>>Yalí</option>
                                <option value="yarumal" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'yarumal') ? 'selected' : ''; ?>>Yarumal</option>
                                <option value="yolombo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'yolombo') ? 'selected' : ''; ?>>Yolombó</option>
                                <option value="yondo" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'yondo') ? 'selected' : ''; ?>>Yondó</option>
                                <option value="zaragoza" <?php echo (isset($old['municipio_residencia']) && $old['municipio_residencia'] == 'zaragoza') ? 'selected' : ''; ?>>Zaragoza</option>
                        </select>
                    <?php if (isset($errores['municipio_residencia'])): ?>
                        <div class="error-message"><?php echo $errores['municipio_residencia']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información de contacto -->
            <div class="form-row">
                <div class="form-group">
                    <label for="direccion">DIRECCIÓN RESIDENCIA</label>
                    <input type="text" id="direccion" name="direccion_residencia" placeholder="Dirección completa"
                        class="<?php echo isset($errores['direccion_residencia']) ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($old['direccion_residencia'] ?? ''); ?>">
                    <?php if (isset($errores['direccion_residencia'])): ?>
                        <div class="error-message"><?php echo $errores['direccion_residencia']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="celular1">CELULAR 1</label>
                    <input type="tel" id="celular1" name="celular1" placeholder="Número celular principal"
                        class="<?php echo isset($errores['celular1']) ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($old['celular1'] ?? ''); ?>">
                    <?php if (isset($errores['celular1'])): ?>
                        <div class="error-message"><?php echo $errores['celular1']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="celular2">CELULAR 2</label>
                    <input type="tel" id="celular2" name="celular2" placeholder="Número celular alterno"
                        class="<?php echo isset($errores['celular2']) ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($old['celular2'] ?? ''); ?>">
                    <?php if (isset($errores['celular2'])): ?>
                        <div class="error-message"><?php echo $errores['celular2']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="barrio">BARRIO</label>
                    <input type="text" id="barrio" name="barrio" placeholder="Barrio"
                        class="<?php echo isset($errores['barrio']) ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($old['barrio'] ?? ''); ?>">
                    <?php if (isset($errores['barrio'])): ?>
                        <div class="error-message"><?php echo $errores['barrio']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Sección de referencia personal -->
        <section class="reference-section">
            <h3 class="section-title"><i class="fas fa-user-friends"></i> REFERENCIA PERSONAL</h3>
            
            <div class="form-group">
                <label for="nombre_acudiente">NOMBRES Y APELLIDOS DEL ACUDIENTE</label>
                <input type="text" id="nombre_acudiente" name="nombre_acudiente" class="large-input <?php echo isset($errores['nombre_acudiente']) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($old['nombre_acudiente'] ?? ''); ?>">
                <?php if (isset($errores['nombre_acudiente'])): ?>
                    <div class="error-message"><?php echo $errores['nombre_acudiente']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="contacto_acudiente">CELULAR</label>
                <input type="tel" id="contacto_acudiente" name="contacto_acudiente" class="large-input <?php echo isset($errores['contacto_acudiente']) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($old['contacto_acudiente'] ?? ''); ?>">
                <?php if (isset($errores['contacto_acudiente'])): ?>
                    <div class="error-message"><?php echo $errores['contacto_acudiente']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-grid">
                <div class="form-row">
                    <div class="form-group">
                        <label for="empresa">EMPRESA DONDE TRABAJA</label>
                        <input type="text" id="empresa" name="empresa_acudiente" placeholder="Nombre de la empresa"
                            value="<?php echo htmlspecialchars($old['empresa_acudiente'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="cargo">CARGO QUE DESEMPEÑA</label>
                        <input type="text" id="cargo" name="cargo_acudiente" placeholder="Cargo del acudiente"
                            value="<?php echo htmlspecialchars($old['cargo_acudiente'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="contacto_empresa">CONTACTO EMPRESA</label>
                        <input type="tel" id="contacto_empresa" name="contacto_empresa_acudiente" placeholder="Teléfono empresa"
                            class="<?php echo isset($errores['contacto_empresa_acudiente']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['contacto_empresa_acudiente'] ?? ''); ?>">
                        <?php if (isset($errores['contacto_empresa_acudiente'])): ?>
                            <div class="error-message"><?php echo $errores['contacto_empresa_acudiente']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mensaje de bienvenida -->
        <section class="welcome-section">
            <div class="welcome-message">
                <i class="fas fa-graduation-cap"></i>
                ¡BIENVENIDO A COMPUESTUDIO, SU CAMINO AL MUNDO LABORAL BILINGÜE!
            </div>
            
            <div class="form-group">
                <label for="mensaje">COMENTARIOS ADICIONALES</label>
                <input type="text" id="mensaje" name="mensaje_bienvenida" placeholder="Escriba aquí..." class="large-input"
                    value="<?php echo htmlspecialchars($old['mensaje_bienvenida'] ?? ''); ?>">
            </div>
        </section>

        <!-- Tipo de inscripción -->
        <section class="registration-section">
            <h3 class="section-title"><i class="fas fa-clipboard-list"></i> TIPO DE INSCRIPCIÓN</h3>
            
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="reg_type" value="new"
                        <?php echo (($old['reg_type'] ?? '') == 'new') ? 'checked' : ''; ?>>
                    <span class="radio-checkmark"></span>
                    <span class="radio-label"><strong>NUEVO</strong></span>
                </label>
                
                <label class="radio-option">
                    <input type="radio" name="reg_type" value="returning"
                        <?php echo (($old['reg_type'] ?? '') == 'returning') ? 'checked' : ''; ?>>
                    <span class="radio-checkmark"></span>
                    <span class="radio-label"><strong>REINGRESO</strong></span>
                </label>
            </div>
            
            <?php if (isset($errores['reg_type'])): ?>
                <div class="error-message"><?php echo $errores['reg_type']; ?></div>
            <?php endif; ?>
        </section>


        <!-- Botón de envío -->
        <div class="form-actions">
            <button type="submit" class="submit-btn">ENVIAR INSCRIPCIÓN</button>
        </div>
        
    </form>

</div>



<script>
    // Script para la vista previa de la foto
    document.getElementById('photoUpload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('photoPreview');
                preview.src = event.target.result;
                preview.style.display = 'block';
                
                // Ocultar elementos de upload
                document.querySelector('.upload-icon').style.display = 'none';
                document.querySelector('.upload-text').style.display = 'none';
                document.querySelector('.upload-instruction').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<script>
        // Script para mejorar la interactividad
        document.addEventListener('DOMContentLoaded', function() {
            const municipioSelect = document.getElementById('municipio');
            
            // Ejemplo de cómo podrías añadir validación adicional con JavaScript
            municipioSelect.addEventListener('change', function() {
                if (this.value !== "") {
                    this.classList.remove('input-error');
                }
            });
            
            // Simular un error para propósitos de demostración
            setTimeout(function() {
                // municipioSelect.classList.add('input-error');
            }, 1000);
        });
</script>

</body>
</html>