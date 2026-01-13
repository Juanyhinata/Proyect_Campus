<?php
// models/Curso.php
class Curso
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Listar todos los cursos
    public function listar()
    {
        $sql = "SELECT * FROM campus.cursos ORDER BY creado_en DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Crear curso
    public function crear($titulo, $descripcion, $imagen, $activo = 1)
    {
        $sql = "INSERT INTO campus.cursos (titulo, descripcion, imagen, activo, creado_en) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$titulo, $descripcion, $imagen, $activo]);
    }

    // Obtener un curso por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM campus.cursos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Actualizar curso
    public function actualizar($id, $titulo, $descripcion, $imagen, $activo)
    {
        $sql = "UPDATE campus.cursos 
                SET titulo = ?, descripcion = ?, imagen = ?, activo = ?
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$titulo, $descripcion, $imagen, $activo, $id]);
    }

    // Eliminar curso
    public function eliminar($id)
    {
        $sql = "DELETE FROM campus.cursos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Contar módulos por curso
    public function contarModulos($curso_id)
    {
        $sql = "SELECT COUNT(*) FROM campus.modulos WHERE curso_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$curso_id]);
        return $stmt->fetchColumn();
    }

    // Cursos donde el usuario está inscrito
    public function cursosInscritos($usuario_id)
    {
        $sql = "SELECT c.*, cu.asignado_en 
                FROM campus.cursos c
                JOIN campus.curso_usuario cu ON c.id = cu.curso_id 
                WHERE cu.usuario_id = ? AND c.activo = TRUE
                ORDER BY cu.asignado_en DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }

    // Cursos disponibles (no inscritos por el usuario)
    public function cursosDisponibles($usuario_id)
    {
        $sql = "SELECT c.* FROM campus.cursos c
                WHERE c.activo = TRUE
                AND c.id NOT IN (
                    SELECT curso_id FROM campus.curso_usuario WHERE usuario_id = ?
                )
                ORDER BY c.creado_en DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }

    // Inscribir usuario en un curso
    public function inscribirUsuario($curso_id, $usuario_id)
    {
        $sql = "INSERT INTO campus.curso_usuario (curso_id, usuario_id) 
                VALUES (?, ?) ON CONFLICT DO NOTHING";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$curso_id, $usuario_id]);
    }

    // Asignar curso a todos los usuarios de una empresa
    public function asignarCursoAEmpresa($curso_id, $empresa)
    {
        $sql = "INSERT INTO campus.curso_usuario (curso_id, usuario_id)
                SELECT ?, id FROM campus.usuarios 
                WHERE empresa = ? AND rol IN ('cliente','agente')
                ON CONFLICT DO NOTHING";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$curso_id, $empresa]);
    }

    // Calcular progreso total del curso (promedio de módulos)
    public function calcularProgresoCurso($usuario_id, $curso_id)
    {
        // Asegurar que las clases estén cargadas (si no lo están ya)
        if (!class_exists('Modulo')) {
            require_once 'Modulo.php';
        }
        if (!class_exists('Progreso')) {
            require_once 'Progreso.php';
        }

        $moduloModel = new Modulo($this->pdo);
        $progresoModel = new Progreso($this->pdo);

        // Obtener todos los módulos del curso
        $modulos = $moduloModel->listarPorCurso($curso_id);
        
        if (empty($modulos)) {
            return 0;
        }

        $suma_progreso = 0;
        $total_modulos = count($modulos);

        foreach ($modulos as $modulo) {
            // Calcular progreso de cada módulo (80% video + 20% examen)
            $progreso = $progresoModel->calcularProgresoTotal($usuario_id, $modulo['id']);
            $suma_progreso += $progreso['nota_final'];
        }

        // Calcular promedio
        $promedio_curso = $suma_progreso / $total_modulos;

        return round($promedio_curso, 0); // Redondear a entero para la barra
    }
}
?>