<?php

class ComentarioCurso {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Guardar o actualizar comentario de un usuario para un curso
     * Si ya existe, se actualiza; si no, se crea nuevo
     */
    public function guardarComentario($datos) {
        $sql = "
            INSERT INTO campus.comentarios_cursos 
                (curso_id, usuario_id, calidad_contenido, facilidad_uso, utilidad_practica, otros_comentarios)
            VALUES 
                (:curso_id, :usuario_id, :calidad_contenido, :facilidad_uso, :utilidad_practica, :otros_comentarios)
            ON CONFLICT (curso_id, usuario_id) 
            DO UPDATE SET
                calidad_contenido = EXCLUDED.calidad_contenido,
                facilidad_uso = EXCLUDED.facilidad_uso,
                utilidad_practica = EXCLUDED.utilidad_practica,
                otros_comentarios = EXCLUDED.otros_comentarios,
                actualizado_en = CURRENT_TIMESTAMP
            RETURNING id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':curso_id' => $datos['curso_id'],
            ':usuario_id' => $datos['usuario_id'],
            ':calidad_contenido' => $datos['calidad_contenido'] ?? null,
            ':facilidad_uso' => $datos['facilidad_uso'] ?? null,
            ':utilidad_practica' => $datos['utilidad_practica'] ?? null,
            ':otros_comentarios' => $datos['otros_comentarios'] ?? null
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todos los comentarios de un curso con información del usuario
     */
    public function obtenerPorCurso($curso_id) {
        $sql = "
            SELECT 
                cc.*,
                u.nombre as usuario_nombre,
                u.rol as usuario_rol,
                u.empresa as usuario_empresa
            FROM campus.comentarios_cursos cc
            JOIN campus.usuarios u ON cc.usuario_id = u.id
            WHERE cc.curso_id = :curso_id
            ORDER BY cc.creado_en DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':curso_id' => $curso_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todos los comentarios de un usuario
     */
    public function obtenerPorUsuario($usuario_id) {
        $sql = "
            SELECT 
                cc.*,
                c.titulo as curso_titulo
            FROM campus.comentarios_cursos cc
            JOIN campus.cursos c ON cc.curso_id = c.id
            WHERE cc.usuario_id = :usuario_id
            ORDER BY cc.creado_en DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener comentario específico de un usuario para un curso
     */
    public function obtenerComentarioUsuarioCurso($usuario_id, $curso_id) {
        $sql = "
            SELECT * FROM campus.comentarios_cursos
            WHERE usuario_id = :usuario_id AND curso_id = :curso_id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':curso_id' => $curso_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas globales de todos los comentarios
     */
    public function obtenerEstadisticas() {
        $sql = "
            SELECT 
                COUNT(*) as total_comentarios,
                ROUND(AVG(calidad_contenido), 2) as avg_calidad,
                ROUND(AVG(facilidad_uso), 2) as avg_facilidad,
                ROUND(AVG(utilidad_practica), 2) as avg_utilidad,
                ROUND(AVG((calidad_contenido + facilidad_uso + utilidad_practica) / 3.0), 2) as promedio_general,
                COUNT(CASE WHEN otros_comentarios IS NOT NULL AND otros_comentarios != '' THEN 1 END) as con_comentarios_texto
            FROM campus.comentarios_cursos
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas por rol de usuario
     */
    public function obtenerEstadisticasPorRol() {
        $sql = "
            SELECT 
                u.rol,
                COUNT(*) as total,
                ROUND(AVG((cc.calidad_contenido + cc.facilidad_uso + cc.utilidad_practica) / 3.0), 2) as promedio
            FROM campus.comentarios_cursos cc
            JOIN campus.usuarios u ON cc.usuario_id = u.id
            GROUP BY u.rol
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas específicas de un curso
     */
    public function obtenerEstadisticasPorCurso($curso_id) {
        $sql = "
            SELECT 
                COUNT(*) as total_comentarios,
                ROUND(AVG(calidad_contenido), 2) as avg_calidad,
                ROUND(AVG(facilidad_uso), 2) as avg_facilidad,
                ROUND(AVG(utilidad_practica), 2) as avg_utilidad,
                ROUND(AVG((calidad_contenido + facilidad_uso + utilidad_practica) / 3.0), 2) as promedio_general
            FROM campus.comentarios_cursos
            WHERE curso_id = :curso_id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':curso_id' => $curso_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas resumidas por curso (para tabla admin)
     */
    public function obtenerResumenPorCursos() {
        $sql = "
            SELECT 
                c.id as curso_id,
                c.titulo as curso_titulo,
                COUNT(cc.id) as total_feedbacks,
                ROUND(AVG((cc.calidad_contenido + cc.facilidad_uso + cc.utilidad_practica) / 3.0), 2) as promedio_general,
                ROUND(AVG(cc.calidad_contenido), 2) as avg_calidad,
                ROUND(AVG(cc.facilidad_uso), 2) as avg_facilidad,
                ROUND(AVG(cc.utilidad_practica), 2) as avg_utilidad
            FROM campus.cursos c
            LEFT JOIN campus.comentarios_cursos cc ON c.id = cc.curso_id
            WHERE c.activo = true
            GROUP BY c.id, c.titulo
            ORDER BY total_feedbacks DESC, promedio_general DESC
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener últimos N comentarios con texto
     */
    public function obtenerUltimosComentarios($limit = 10) {
        $sql = "
            SELECT 
                cc.*,
                u.nombre as usuario_nombre,
                u.rol as usuario_rol,
                c.titulo as curso_titulo
            FROM campus.comentarios_cursos cc
            JOIN campus.usuarios u ON cc.usuario_id = u.id
            JOIN campus.cursos c ON cc.curso_id = c.id
            WHERE cc.otros_comentarios IS NOT NULL AND cc.otros_comentarios != ''
            ORDER BY cc.creado_en DESC
            LIMIT :limit
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
