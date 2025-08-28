<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }
        .logo-container {
            margin-bottom: 1.5rem;
        }
        .logo-container img {
            max-width: 150px;
            height: auto;
        }
        h1 {
            text-align: center;
            color: #00923f;
            margin-bottom: 1.8rem;
            font-size: 1.8rem;
        }
        .form-group {
            margin-bottom: 1.8rem;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 0.6rem;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.85rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: all 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #00923f;
            box-shadow: 0 0 0 3px rgba(0, 146, 63, 0.2);
        }
        button {
            width: 100%;
            padding: 0.85rem;
            background-color: #00923f;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 0.5rem;
        }
        button:hover {
            background-color: #007a35;
        }
        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }
        .forgot-password a {
            color: #00923f;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .divider {
            margin: 1.8rem 0;
            border-top: 1px solid #eee;
            position: relative;
        }
        .divider-text {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 15px;
            color: #777;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Espacio para el logo -->
        <div class="logo-container">
            <img src="../asset/img/logo.png" alt="Logo de la empresa">
            <!-- Reemplaza "ruta-de-tu-logo.png" con la ruta correcta de tu logo -->
        </div>
        
        <h1>Iniciar Sesión</h1>
        
        <form action="../controllers/procesar-login.php" method="POST" autocomplete="on">
            <div class="form-group">
                <label for="usuario">Nombre de Usuario</label>
                <input type="text" id="usuario" name="usuario" required placeholder="Ingrese su usuario">
            </div>
            
            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="clave" required placeholder="Ingrese su contraseña">
            </div>
            
            <button type="submit">Acceder</button>
            
         
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
    <script>
    Swal.fire({
        icon: 'error',
        title: 'Credenciales incorrectas',
        text: 'El usuario o la contraseña no son válidos',
        confirmButtonText: 'Intentar de nuevo'
    });
    </script>
    <?php endif; ?>

</body>
</html>