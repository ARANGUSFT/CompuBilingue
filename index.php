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
            <!-- Sección de foto del estudiante -->
            <section class="photo-upload-container">
                <label for="photoUpload" class="photo-upload" id="photoUploadArea">
                    <input type="file" id="photoUpload" name="photoUpload" accept="image/*" required>
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">FOTO DEL ESTUDIANTE</div>
                    <div class="upload-instruction">(Haga clic para subir una imagen)</div>
                    <img id="photoPreview" src="#" alt="Vista previa de la foto del estudiante" style="display: none;">
                </label>
            </section>

            <!-- Sección de datos personales -->
            <section>
                <h2 class="section-title">DATOS PERSONALES</h2>
                
                <div class="form-group">
                    <label for="nombres_estudiante">APELLIDOS Y NOMBRE DEL ESTUDIANTE</label>
                    <input type="text" id="nombres_estudiante" name="nombres_estudiante" class="large-input" required>
                </div>

                <table class="form-table">
                    <tr>
                        <th>NIVEL AL QUE ASPIRA</th>
                        <td><input type="checkbox" id="a1" name="nivel[]" value="A1"><label for="a1">A1</label></td>
                        <td><input type="checkbox" id="a2" name="nivel[]" value="A2"><label for="a2">A2</label></td>
                        <td><input type="checkbox" id="b1" name="nivel[]" value="B1"><label for="b1">B1</label></td>
                        <td><input type="checkbox" id="b2" name="nivel[]" value="B2"><label for="b2">B2</label></td>
                        <td><input type="text" id="horario" name="horario" placeholder="HORARIO" required></td>
                        <td><input type="number" id="mensualidad" name="mensualidad" placeholder="MENSUALIDAD" min="0" step="0.01" required></td>
                    </tr>
                </table>

                <table class="form-table">
                    <tr>
                        <td>
                            <label for="estrato">ESTRATO SOCIOECONÓMICO</label>
                            <select id="estrato" name="estrato_socioeconomico" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Estrato 1</option>
                                <option value="2">Estrato 2</option>
                                <option value="3">Estrato 3</option>
                                <option value="4">Estrato 4</option>
                                <option value="5">Estrato 5</option>
                                <option value="6">Estrato 6</option>
                            </select>
                        </td>

                        <td>
                            <label for="eps">EPS o SISBEN</label>
                            <input type="text" id="eps" name="eps" placeholder="Escriba aquí" required>
                        </td>
                        <td colspan="3"></td>
                        <td>
                            <label for="escolaridad">NIVEL ESCOLARIDAD</label>
                            <select id="escolaridad" name="nivel_escolaridad" required>
                                <option value="">Seleccione...</option>
                                <option value="Primaria">Primaria</option>
                                <option value="Secundaria">Secundaria</option>
                                <option value="Técnico">Técnico</option>
                                <option value="Tecnológico">Tecnológico</option>
                                <option value="Universitario">Universitario</option>
                                <option value="Postgrado">Postgrado</option>
                            </select>
                        </td>

                    </tr>
                    <tr>
                        <td>
                            <label>DOCUMENTO DE IDENTIDAD</label>
                            <div class="checkbox-group">
                                <label><input type="radio" name="doc_type" value="CC" required> C.C</label>
                                <label><input type="radio" name="doc_type" value="TI"> T.I</label>
                                <label><input type="radio" name="doc_type" value="PPT"> PPT</label>
                            </div>
                            <input type="text" name="numero_documento" placeholder="Número de documento" required>
                        </td>
                        <td>
                            <label for="email">EMAIL</label>
                            <input type="email" id="email" name="email" placeholder="Correo electrónico" required>
                        </td>
                        <td colspan="3"></td>
                        <td>
                            <label for="municipio">MUNICIPIO RESIDENCIA</label>
                            <input type="text" id="municipio" name="municipio_residencia" placeholder="Municipio" required>
                        </td>
                    </tr>
                </table>

                <table class="form-table">
                    <tr>
                        <td>
                            <label for="direccion">DIRECCIÓN RESIDENCIA</label>
                            <input type="text" id="direccion" name="direccion_residencia" placeholder="Dirección completa" required>
                        </td>

                        <td>
                            <label for="celular1">CELULAR 1</label>
                            <input type="tel" id="celular1" name="celular1" placeholder="Número celular principal" required>
                        </td>
                        <td>
                            <label for="celular2">CELULAR 2</label>
                            <input type="tel" id="celular2" name="celular2" placeholder="Número celular alterno">
                        </td>

                    
                        <td>
                            <label for="barrio">BARRIO</label>
                            <input type="text" id="barrio" name="barrio" placeholder="Barrio" required>
                        </td>
                    </tr>
                </table>
            </section>

            <!-- Sección de referencia personal -->
            <section>
                <h2 class="section-title">REFERENCIA PERSONAL</h2>
                
                <div class="form-group">
                    <label for="nombre_acudiente">NOMBRES Y APELLIDOS DEL ACUDIENTE</label>
                    <input type="text" id="nombre_acudiente" name="nombre_acudiente" class="large-input" required>
                </div>
                
                <div class="form-group">
                    <label for="contacto_acudiente">CELULAR</label>
                    <input type="tel" id="contacto_acudiente" name="contacto_acudiente" class="large-input" required>
                </div>

                <table class="form-table">
                    <tr>
                        <td>
                            <label for="empresa">EMPRESA DONDE TRABAJA</label>
                            <input type="text" id="empresa" name="empresa_acudiente" placeholder="Nombre de la empresa">
                        </td>
                        <td>
                            <label for="cargo">CARGO QUE DESEMPEÑA</label>
                            <input type="text" id="cargo" name="cargo_acudiente" placeholder="Cargo del acudiente">
                        </td>
                        <td>
                            <label for="contacto_empresa">CONTACTO EMPRESA</label>
                            <input type="tel" id="contacto_empresa" name="contacto_empresa_acudiente" placeholder="Teléfono empresa">
                        </td>
                    </tr>
                </table>
            </section>

            <!-- Mensaje de bienvenida -->
            <section class="welcome-container">
                <div class="welcome-message">
                    ¡BIENVENIDO A COMPUESTUDIO, SU CAMINO AL MUNDO BILINGÜE!
                </div>
                
                <div class="form-group">
                    <label for="mensaje">COMENTARIOS ADICIONALES</label>
                    <input type="text" id="mensaje" name="mensaje_bienvenida" placeholder="Escriba aquí..." class="large-input">
                </div>
            </section>

            <!-- Tipo de inscripción -->
            <section class="registration-type">
                <label><input type="radio" name="reg_type" value="new" required> <strong>NUEVO</strong></label>
                <label><input type="radio" name="reg_type" value="returning"> <strong>REINGRESO</strong></label>
            </section>

            <!-- Botón de envío -->
            <div class="form-actions">
                <button type="submit" class="submit-btn">ENVIAR INSCRIPCIÓN</button>
            </div>
        </form>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Vista previa de la foto del estudiante
            const photoUpload = document.getElementById('photoUpload');
            if (photoUpload) {
                photoUpload.addEventListener('change', function(e) {
                    handleImageUpload(e, 'photoPreview', '#photoUploadArea div');
                });
            }

            // Función para manejar la carga de imágenes
            function handleImageUpload(event, previewId, textElementsSelector) {
                const file = event.target.files[0];
                if (file) {
                    // Validar tipo de archivo
                    if (!file.type.match('image.*')) {
                        alert('Por favor, seleccione un archivo de imagen válido.');
                        return;
                    }

                    // Validar tamaño de archivo (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('La imagen no debe exceder los 2MB.');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById(previewId);
                        if (preview) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                            
                            // Ocultar elementos de texto
                            const textElements = document.querySelectorAll(textElementsSelector);
                            textElements.forEach(el => el.style.display = 'none');
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }

            // Validación del formulario
            const form = document.getElementById('globalSchoolForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Validación adicional puede ir aquí
                    // Si todo está bien, el formulario se enviará
                    // Si hay errores, usar e.preventDefault()
                });
            }
        });
    </script>
</body>
</html>