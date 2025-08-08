<?php
session_start();

// Restaurar errores y valores antiguos
$errores = $_SESSION['errores'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errores'], $_SESSION['old']);

// Crear token CSRF si no existe o si se regeneró
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inscripción - Global School</title>
    <link rel="stylesheet" href="asset/css/style.css">
</head>
<body>
    <div class="form-container">
    <!-- Encabezado con logo institucional -->
    <header class="header-container">
        <div class="header-text">
            <h1>INSTITUCIÓN DE EDUCACIÓN PARA EL TRABAJO Y EL DESARROLLO HUMANO</h1>
            <h2>FORMULARIO DE INSCRIPCIÓN</h2>
            <h3>6700</h3>
        </div>
        <div class="logo-container">
            <img id="institutionalLogo" src="asset/img/logo.png" alt="Logo de Global School" 
                onerror="this.style.display='none'; document.getElementById('logoPlaceholder').style.display='block'">
            <div id="logoPlaceholder" class="logo-placeholder" style="display: none;">
                Logo no disponible
            </div>
        </div>
    </header>

    <form id="globalSchoolForm" action="controllers/procesar-inscripcion.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
  
    <!-- Sección de foto del estudiante -->
    <section class="photo-upload-container">
        <label for="photoUpload" class="photo-upload <?php echo isset($errores['photoUpload']) ? 'input-error' : ''; ?>" id="photoUploadArea">
            <input type="file" id="photoUpload" name="photoUpload" accept="image/*">
            <div class="upload-icon">📷</div>
            <div class="upload-text">FOTO DEL ESTUDIANTE</div>
            <div class="upload-instruction">(Haga clic para subir una imagen)</div>
            <img id="photoPreview" src="#" alt="Vista previa de la foto del estudiante" style="display: none;">
        </label>
        <?php if (isset($errores['photoUpload'])): ?>
            <small style="color:red;"><?php echo $errores['photoUpload']; ?></small>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="section-title">DATOS PERSONALES</h2>

        <div class="form-group">
            <label for="nombres_estudiante">APELLIDOS Y NOMBRE DEL ESTUDIANTE</label>
            <input type="text" id="nombres_estudiante" name="nombres_estudiante" class="large-input <?php echo isset($errores['nombres_estudiante']) ? 'input-error' : ''; ?>"
                value="<?php echo htmlspecialchars($old['nombres_estudiante'] ?? ''); ?>">
            <?php if (isset($errores['nombres_estudiante'])): ?>
                <small style="color:red;"><?php echo $errores['nombres_estudiante']; ?></small>
            <?php endif; ?>
        </div>

        <div class="responsive-table">
            <table class="form-table">
                <tr>
                    <th>NIVEL AL QUE ASPIRA</th>
                    <?php
                    $niveles = ['A1', 'A2', 'B1', 'B2'];
                    foreach ($niveles as $nivel) {
                        $checked = (isset($old['nivel']) && in_array($nivel, $old['nivel'])) ? 'checked' : '';
                        echo "<td><input type='checkbox' id='$nivel' name='nivel[]' value='$nivel' $checked><label for='$nivel'>$nivel</label></td>";
                    }
                    ?>
                    <?php if (isset($errores['nivel'])): ?>
                        <td colspan="2"><small style="color:red;"><?php echo $errores['nivel']; ?></small></td>
                    <?php endif; ?>
                    <td>
                        <input type="text" id="horario" name="horario" placeholder="HORARIO"
                            class="<?php echo isset($errores['horario']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['horario'] ?? ''); ?>">
                        <?php if (isset($errores['horario'])): ?>
                            <small style="color:red;"><?php echo $errores['horario']; ?></small>
                        <?php endif; ?>
                    </td>
                   
                </tr>
            </table>
        </div>

        <div class="responsive-table">
            <table class="form-table">
                <tr>
                    <td>
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
                            <small style="color:red;"><?php echo $errores['estrato_socioeconomico']; ?></small>
                        <?php endif; ?>
                    </td>

                    <td>
                        <label for="eps">EPS o SISBEN</label>
                        <input type="text" id="eps" name="eps" placeholder="Escriba aquí"
                            class="<?php echo isset($errores['eps']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['eps'] ?? ''); ?>">
                        <?php if (isset($errores['eps'])): ?>
                            <small style="color:red;"><?php echo $errores['eps']; ?></small>
                        <?php endif; ?>
                    </td>

                    <td colspan="3"></td>

                    <td>
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
                            <small style="color:red;"><?php echo $errores['nivel_escolaridad']; ?></small>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label>DOCUMENTO DE IDENTIDAD</label>
                        <div class="checkbox-group">
                            <?php
                            $docTypes = ['CC', 'TI', 'PPT'];
                            foreach ($docTypes as $doc) {
                                $checked = ($old['doc_type'] ?? '') == $doc ? 'checked' : '';
                                echo "<label><input type='radio' name='doc_type' value='$doc' $checked> $doc</label>";
                            }
                            ?>
                        </div>
                        <input type="text" name="numero_documento" placeholder="Número de documento"
                            class="<?php echo isset($errores['numero_documento']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['numero_documento'] ?? ''); ?>">
                        <?php if (isset($errores['numero_documento'])): ?>
                            <small style="color:red;"><?php echo $errores['numero_documento']; ?></small>
                        <?php endif; ?>
                    </td>

                    <td>
                        <label for="email">EMAIL</label>
                        <input type="email" id="email" name="email" placeholder="Correo electrónico"
                            class="<?php echo isset($errores['email']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>">
                        <?php if (isset($errores['email'])): ?>
                            <small style="color:red;"><?php echo $errores['email']; ?></small>
                        <?php endif; ?>
                    </td>

                    <td colspan="3"></td>

                    <td>
                        <label for="municipio">MUNICIPIO RESIDENCIA</label>
                        <input type="text" id="municipio" name="municipio_residencia" placeholder="Municipio"
                            class="<?php echo isset($errores['municipio_residencia']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['municipio_residencia'] ?? ''); ?>">
                        <?php if (isset($errores['municipio_residencia'])): ?>
                            <small style="color:red;"><?php echo $errores['municipio_residencia']; ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="responsive-table">
            <table class="form-table">
                <tr>
                    <td>
                        <label for="direccion">DIRECCIÓN RESIDENCIA</label>
                        <input type="text" id="direccion" name="direccion_residencia" placeholder="Dirección completa"
                            class="<?php echo isset($errores['direccion_residencia']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['direccion_residencia'] ?? ''); ?>">
                        <?php if (isset($errores['direccion_residencia'])): ?>
                            <small style="color:red;"><?php echo $errores['direccion_residencia']; ?></small>
                        <?php endif; ?>
                    </td>

                    <td>
                        <label for="celular1">CELULAR 1</label>
                        <input type="tel" id="celular1" name="celular1" placeholder="Número celular principal"
                            class="<?php echo isset($errores['celular1']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['celular1'] ?? ''); ?>">
                        <?php if (isset($errores['celular1'])): ?>
                            <small style="color:red;"><?php echo $errores['celular1']; ?></small>
                        <?php endif; ?>
                    </td>

                    <td>
                        <label for="celular2">CELULAR 2</label>
                        <input type="tel" id="celular2" name="celular2" placeholder="Número celular alterno"
                            class="<?php echo isset($errores['celular2']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['celular2'] ?? ''); ?>">
                        <?php if (isset($errores['celular2'])): ?>
                            <small style="color:red;"><?php echo $errores['celular2']; ?></small>
                        <?php endif; ?>
                    </td>

                    <td>
                        <label for="barrio">BARRIO</label>
                        <input type="text" id="barrio" name="barrio" placeholder="Barrio"
                            class="<?php echo isset($errores['barrio']) ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($old['barrio'] ?? ''); ?>">
                        <?php if (isset($errores['barrio'])): ?>
                            <small style="color:red;"><?php echo $errores['barrio']; ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </section>

    <!-- Sección de referencia personal -->
    <section>
            <h2 class="section-title">REFERENCIA PERSONAL</h2>
            
            <div class="form-group">
                <label for="nombre_acudiente">NOMBRES Y APELLIDOS DEL ACUDIENTE</label>
                <input type="text" id="nombre_acudiente" name="nombre_acudiente" class="large-input <?php echo isset($errores['nombre_acudiente']) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($old['nombre_acudiente'] ?? ''); ?>">
                <?php if (isset($errores['nombre_acudiente'])): ?>
                    <small style="color:red;"><?php echo $errores['nombre_acudiente']; ?></small>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="contacto_acudiente">CELULAR</label>
                <input type="tel" id="contacto_acudiente" name="contacto_acudiente" class="large-input <?php echo isset($errores['contacto_acudiente']) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($old['contacto_acudiente'] ?? ''); ?>">
                <?php if (isset($errores['contacto_acudiente'])): ?>
                    <small style="color:red;"><?php echo $errores['contacto_acudiente']; ?></small>
                <?php endif; ?>
            </div>

            <div class="responsive-table">
                <table class="form-table">
                    <tr>
                        <td>
                            <label for="empresa">EMPRESA DONDE TRABAJA</label>
                            <input type="text" id="empresa" name="empresa_acudiente" placeholder="Nombre de la empresa"
                                value="<?php echo htmlspecialchars($old['empresa_acudiente'] ?? ''); ?>">
                        </td>
                        <td>
                            <label for="cargo">CARGO QUE DESEMPEÑA</label>
                            <input type="text" id="cargo" name="cargo_acudiente" placeholder="Cargo del acudiente"
                                value="<?php echo htmlspecialchars($old['cargo_acudiente'] ?? ''); ?>">
                        </td>
                        <td>
                            <label for="contacto_empresa">CONTACTO EMPRESA</label>
                            <input type="tel" id="contacto_empresa" name="contacto_empresa_acudiente" placeholder="Teléfono empresa"
                                class="<?php echo isset($errores['contacto_empresa_acudiente']) ? 'input-error' : ''; ?>"
                                value="<?php echo htmlspecialchars($old['contacto_empresa_acudiente'] ?? ''); ?>">
                            <?php if (isset($errores['contacto_empresa_acudiente'])): ?>
                                <small style="color:red;"><?php echo $errores['contacto_empresa_acudiente']; ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
    </section>

    <!-- Mensaje de bienvenida -->
    <section class="welcome-container">
        <div class="welcome-message">
            ¡BIENVENIDO A COMPUESTUDIO, SU CAMINO AL MUNDO BILINGÜE!
        </div>
        
        <div class="form-group">
            <label for="mensaje">COMENTARIOS ADICIONALES</label>
            <input type="text" id="mensaje" name="mensaje_bienvenida" placeholder="Escriba aquí..." class="large-input"
                value="<?php echo htmlspecialchars($old['mensaje_bienvenida'] ?? ''); ?>">
        </div>
    </section>

    <!-- Tipo de inscripción -->
    <section class="registration-type">
        <label>
            <input type="radio" name="reg_type" value="new"
                <?php echo (($old['reg_type'] ?? '') == 'new') ? 'checked' : ''; ?>>
            <strong>NUEVO</strong>
        </label>
        <label>
            <input type="radio" name="reg_type" value="returning"
                <?php echo (($old['reg_type'] ?? '') == 'returning') ? 'checked' : ''; ?>>
            <strong>REINGRESO</strong>
        </label>
        <?php if (isset($errores['reg_type'])): ?>
            <br><small style="color:red;"><?php echo $errores['reg_type']; ?></small>
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
</body>
</html>