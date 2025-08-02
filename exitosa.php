<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Exitosa | Global School</title>
    <style>
        :root {
            --color-primario: hsl(143, 87%, 12%);
            --color-secundario: #00923f;
            --color-terciario: #c9d2db;
            --color-fondo: #f0f8f3;
            --color-texto: #333;
            --color-texto-secundario: #555;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--color-fondo);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            line-height: 1.6;
        }
        
        .success-container {
            max-width: 600px;
            margin: 20px;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .success-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--color-primario), var(--color-secundario));
        }
        
        h1 {
            color: var(--color-primario);
            font-size: 2.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--color-secundario);
        }
        
        p {
            color: var(--color-texto);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .success-icon {
            font-size: 5rem;
            color: var(--color-secundario);
            margin-bottom: 20px;
            animation: bounce 1s ease infinite alternate;
        }
        
        .details-box {
            background-color: rgba(0, 146, 63, 0.05);
            border-left: 4px solid var(--color-secundario);
            padding: 15px;
            margin: 25px 0;
            border-radius: 0 5px 5px 0;
            text-align: left;
        }
        
        .btn-container {
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(to right, var(--color-primario), var(--color-secundario));
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 146, 63, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 146, 63, 0.4);
        }
        
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .success-container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .success-icon {
                font-size: 4rem;
            }
        }
        
        @media (max-width: 480px) {
            h1 {
                font-size: 1.5rem;
            }
            
            p {
                font-size: 1rem;
            }
            
            .btn {
                padding: 10px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">

        <div class="success-icon">✓</div>
        <h1>¡Gracias por inscribirte COMPUESTUDIO!</h1>
        <p>Tu inscripción ha sido registrada con éxito. Bienvenido a tu camino al mundo bilingüe.</p>
        
     
        
        <div class="btn-container">
            <a href="index.php" class="btn">Volver al inicio</a>
        </div>

    </div>
</body>
</html>