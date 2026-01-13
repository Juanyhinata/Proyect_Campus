<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Modulo.php';

$data = json_decode(file_get_contents("php://input"), true);

$curso_id = $data['curso_id'];
$orden = $data['orden'];

$moduloModel = new Modulo($pdo);

// Guarda el orden correcto
$moduloModel->reordenar($curso_id, $orden);

echo "ok";
?>