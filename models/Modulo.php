<?php
// models/Modulo.php

class Modulo
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Listar módulos de un curso (ordenados)
    public function listarPorCurso($curso_id)
    {
        $sql = "SELECT * FROM campus.modulos WHERE curso_id = ? ORDER BY orden";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$curso_id]);
        return $stmt->fetchAll();
    }

    // Crear módulo
    public function crear($curso_id, $titulo, $evaluacion_activa = 0)
    {
        // Obtener el último orden
        $sql = "SELECT COALESCE(MAX(orden), -1) + 1 FROM campus.modulos WHERE curso_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$curso_id]);
        $orden = $stmt->fetchColumn();

        $sql = "INSERT INTO campus.modulos (curso_id, titulo, orden, evaluacion_activa, creado_en) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$curso_id, $titulo, $orden, $evaluacion_activa]);
    }

    // Actualizar módulo
    public function actualizar($id, $titulo, $evaluacion_activa)
    {
        $sql = "UPDATE campus.modulos SET titulo = ?, evaluacion_activa = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$titulo, $evaluacion_activa, $id]);
    }

    // Eliminar módulo
    public function eliminar($id)
    {
        $sql = "DELETE FROM campus.modulos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Reordenar módulos (drag & drop)
   public function reordenar($curso_id, $orden_nuevo)
    {
    $sql = "UPDATE campus.modulos SET orden = ? WHERE id = ? AND curso_id = ?";
    $stmt = $this->pdo->prepare($sql);

    foreach ($orden_nuevo as $item) {
        $stmt->execute([$item['orden'], $item['id'], $curso_id]);
    }

    return true;
}

    // Obtener un módulo por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM campus.modulos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}