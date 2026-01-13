<?php
// models/Tema.php

class Tema
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarPorModulo($modulo_id)
    {
    $sql = "SELECT * FROM campus.temas WHERE modulo_id = ? ORDER BY orden";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$modulo_id]);
    return $stmt->fetchAll();
    }

    public function crear($modulo_id, $titulo, $tipo, $video_id = null, $pdf_ruta = null, $duracion_segundos = 0)
    {
        $sql = "SELECT COALESCE(MAX(orden), -1) + 1 FROM campus.temas WHERE modulo_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$modulo_id]);
        $orden = $stmt->fetchColumn();

        $sql = "INSERT INTO campus.temas (modulo_id, titulo, tipo, video_id, pdf_ruta, orden, duracion_segundos) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$modulo_id, $titulo, $tipo, $video_id, $pdf_ruta, $orden, $duracion_segundos]);
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM campus.temas WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function actualizar($id, $titulo, $tipo, $video_id = null, $pdf_ruta = null, $duracion_segundos = 0)
    {
        $sql = "UPDATE campus.temas SET titulo = ?, tipo = ?, video_id = ?, pdf_ruta = ?, duracion_segundos = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$titulo, $tipo, $video_id, $pdf_ruta, $duracion_segundos, $id]);
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM campus.temas WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function reordenar($modulo_id, $orden_nuevo)
    {
        $sql = "UPDATE campus.temas SET orden = ? WHERE id = ? AND modulo_id = ?";
        $stmt = $this->pdo->prepare($sql);
        foreach ($orden_nuevo as $orden => $tema_id) {
            $stmt->execute([$orden, $tema_id, $modulo_id]);
        }
    }
}
?>
