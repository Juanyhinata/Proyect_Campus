<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Tabla Evaluaciones
    $sql = "CREATE TABLE IF NOT EXISTS campus.evaluaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        modulo_id INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        descripcion TEXT,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (modulo_id) REFERENCES campus.modulos(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Tabla 'evaluaciones' creada o ya existe.<br>";

    // 2. Tabla Preguntas
    $sql = "CREATE TABLE IF NOT EXISTS campus.preguntas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        evaluacion_id INT NOT NULL,
        texto_pregunta TEXT NOT NULL,
        orden INT DEFAULT 0,
        FOREIGN KEY (evaluacion_id) REFERENCES campus.evaluaciones(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Tabla 'preguntas' creada o ya existe.<br>";

    // 3. Tabla Opciones
    $sql = "CREATE TABLE IF NOT EXISTS campus.opciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pregunta_id INT NOT NULL,
        texto_opcion TEXT NOT NULL,
        es_correcta BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (pregunta_id) REFERENCES campus.preguntas(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Tabla 'opciones' creada o ya existe.<br>";

    // 4. Tabla Intentos / Resultados
    $sql = "CREATE TABLE IF NOT EXISTS campus.intentos_evaluacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        evaluacion_id INT NOT NULL,
        calificacion DECIMAL(5,2) NOT NULL, -- 0 a 100
        aprobado BOOLEAN DEFAULT FALSE,
        fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES campus.usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (evaluacion_id) REFERENCES campus.evaluaciones(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Tabla 'intentos_evaluacion' creada o ya existe.<br>";

    echo "<h3>¡Configuración de BD completada con éxito!</h3>";

} catch (PDOException $e) {
    die("Error al configurar BD: " . $e->getMessage());
}
?>
