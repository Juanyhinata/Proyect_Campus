<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página No Encontrada | Campus LATAM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 3px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .error-title {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .error-message {
            font-size: 18px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .contact-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
        }

        .contact-info p {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .contact-info strong {
            color: #667eea;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            margin-top: 20px;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .apology {
            font-size: 20px;
            color: #e74c3c;
            font-weight: 600;
            margin-top: 30px;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 80px;
            }

            .error-title {
                font-size: 22px;
            }

            .error-message {
                font-size: 16px;
            }

            .error-container {
                padding: 30px 20px;
            }

            .icon {
                font-size: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">🤔</div>
        <div class="error-code">404</div>
        <h1 class="error-title">¡Me perdí! No sé a dónde vas...</h1>
        <p class="error-message">
            La página que buscas no existe o ha sido movida. 
            Si este error vuelve a aparecer, por favor contacta al administrador del sistema.
        </p>

        <div class="contact-info">
            <p><strong>📞 Soporte Técnico:</strong></p>
            <p>Contacta al administrador del sistema</p>
            <p>o marca a Soporte Técnico para asistencia</p>
        </div>

        <a href="index.php" class="btn-home">🏠 Volver al Inicio</a>

        <p class="apology">¡Perdona las molestias!</p>
    </div>
</body>
</html>
