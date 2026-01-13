<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error del Servidor | Campus LATAM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            max-width: 700px;
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
            color: #f5576c;
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
            background: #fff3cd;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #ffc107;
        }

        .contact-info p {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .contact-info strong {
            color: #f5576c;
        }

        .error-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border: 2px dashed #dee2e6;
            max-height: 200px;
            overflow-y: auto;
        }

        .error-details h3 {
            color: #495057;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .error-details code {
            display: block;
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #dc3545;
            text-align: left;
            word-wrap: break-word;
            white-space: pre-wrap;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
            margin-top: 20px;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.6);
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

        .copy-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: #5a6268;
            transform: scale(1.05);
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
        <div class="icon">⚠️</div>
        <div class="error-code">500</div>
        <h1 class="error-title">¡Algo está muy mal por aquí!</h1>
        <p class="error-message">
            Ha ocurrido un error interno en el servidor. 
            Por favor, contacta al administrador del sistema y compártele el mensaje de código que aparece abajo.
        </p>

        <div class="error-details">
            <h3>📋 Mensaje de Error (comparte esto con el administrador):</h3>
            <code id="errorCode"><?php
                // Capturar información del error si está disponible
                if (isset($_SERVER['REDIRECT_STATUS'])) {
                    echo "Error Status: " . htmlspecialchars($_SERVER['REDIRECT_STATUS']) . "\n";
                }
                if (isset($_SERVER['REQUEST_URI'])) {
                    echo "Request URI: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "\n";
                }
                if (isset($_SERVER['HTTP_REFERER'])) {
                    echo "Referer: " . htmlspecialchars($_SERVER['HTTP_REFERER']) . "\n";
                }
                echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
                
                // Si hay un error de PHP capturado
                $error = error_get_last();
                if ($error) {
                    echo "\nPHP Error:\n";
                    echo "Type: " . $error['type'] . "\n";
                    echo "Message: " . htmlspecialchars($error['message']) . "\n";
                    echo "File: " . htmlspecialchars($error['file']) . "\n";
                    echo "Line: " . $error['line'];
                }
            ?></code>
            <button class="copy-btn" onclick="copyErrorCode()">📋 Copiar Código de Error</button>
        </div>

        <div class="contact-info">
            <p><strong>🔧 Administrador del Sistema:</strong></p>
            <p>Comparte el código de error de arriba</p>
            <p>El administrador sabrá qué hacer</p>
        </div>

        <a href="/" class="btn-home">🏠 Volver al Inicio</a>

        <p class="apology">¡Perdona las molestias!</p>
    </div>

    <script>
        function copyErrorCode() {
            const errorCode = document.getElementById('errorCode').textContent;
            
            // Usar la API moderna del portapapeles
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(errorCode).then(() => {
                    const btn = document.querySelector('.copy-btn');
                    const originalText = btn.textContent;
                    btn.textContent = '✅ Copiado!';
                    btn.style.background = '#28a745';
                    
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.background = '#6c757d';
                    }, 2000);
                }).catch(err => {
                    console.error('Error al copiar:', err);
                    fallbackCopy(errorCode);
                });
            } else {
                fallbackCopy(errorCode);
            }
        }

        function fallbackCopy(text) {
            // Método alternativo para navegadores antiguos
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                const btn = document.querySelector('.copy-btn');
                btn.textContent = '✅ Copiado!';
                btn.style.background = '#28a745';
                
                setTimeout(() => {
                    btn.textContent = '📋 Copiar Código de Error';
                    btn.style.background = '#6c757d';
                }, 2000);
            } catch (err) {
                alert('Por favor, selecciona y copia el texto manualmente');
            }
            
            document.body.removeChild(textArea);
        }
    </script>
</body>
</html>
