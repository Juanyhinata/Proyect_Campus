<?php
// update_db_usuarios.php
require_once __DIR__ . '/config/db.php';

try {
    $sql = "ALTER TABLE campus.usuarios ADD COLUMN activo BOOLEAN DEFAULT TRUE";
    $pdo->exec($sql);
    echo "Columna 'activo' agregada correctamente.";
} catch (PDOException $e) {
    echo "Error (puede que la columna ya exista): " . $e->getMessage();
}
?>
