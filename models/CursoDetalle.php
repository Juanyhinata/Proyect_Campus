<?php
//Models/CursoDetalle.php
class CursoDetalle {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerCurso($curso_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM campus.cursos WHERE id = :id");
        $stmt->execute([':id' => $curso_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerModulos($curso_id) {
        // Obtenemos los módulos ordenados
        $stmt = $this->pdo->prepare("
            SELECT * FROM campus.modulos 
            WHERE curso_id = :curso_id 
            ORDER BY orden ASC
        ");
        $stmt->execute([':curso_id' => $curso_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTemas($modulo_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM campus.temas 
            WHERE modulo_id = :modulo_id 
            ORDER BY orden ASC
        ");
        $stmt->execute([':modulo_id' => $modulo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>