<?php
//Models/Progreso.php
class Progreso {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Obtener el estado actual, retorna array o false
    public function obtenerProgreso($usuario_id, $modulo_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM campus.modulo_progreso 
            WHERE usuario_id = :uid AND modulo_id = :mid
        ");
        $stmt->execute([':uid' => $usuario_id, ':mid' => $modulo_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Guardar tiempo y calcular porcentaje
    public function guardarTiempo($usuario_id, $modulo_id, $tiempo, $duracion) {
        // Calcular porcentaje entrante
        $porcentaje_calculado = 0;
        if ($duracion > 0) {
            $porcentaje_calculado = ($tiempo / $duracion) * 100;
        }
        if ($porcentaje_calculado > 100) $porcentaje_calculado = 100;

        // Verificar si ya existe registro
        $actual = $this->obtenerProgreso($usuario_id, $modulo_id);

        if ($actual) {
            // Lógica: Nunca reducir el porcentaje, pero actualizar el tiempo si es mayor o si se está viendo
            // Aquí priorizamos guardar la MAYOR cantidad alcanzada
            $nuevo_porcentaje = max($actual['porcentaje'], $porcentaje_calculado);
            
            // Si ya estaba marcado como completado, mantener 100% y true
            $es_completado = $actual['completado'] ? 'true' : 'false'; 
            if ($nuevo_porcentaje >= 99) $nuevo_porcentaje = 100; // Redondeo visual

            $sql = "UPDATE campus.modulo_progreso 
                    SET tiempo_visto = :tiempo, 
                        porcentaje = :porcentaje, 
                        ultima_actualizacion = NOW() 
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tiempo' => $tiempo,
                ':porcentaje' => (int)$nuevo_porcentaje,
                ':id' => $actual['id']
            ]);
            
            return ['nuevo_porcentaje' => $nuevo_porcentaje];

        } else {
            // Crear nuevo registro
            $sql = "INSERT INTO campus.modulo_progreso 
                    (usuario_id, modulo_id, porcentaje, completado, tiempo_visto, ultima_actualizacion) 
                    VALUES (:uid, :mid, :porcentaje, false, :tiempo, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':uid' => $usuario_id,
                ':mid' => $modulo_id,
                ':porcentaje' => (int)$porcentaje_calculado,
                ':tiempo' => $tiempo
            ]);
            
            return ['nuevo_porcentaje' => $porcentaje_calculado];
        }
    }

    public function marcarCompletado($usuario_id, $modulo_id) {
        $actual = $this->obtenerProgreso($usuario_id, $modulo_id);

        if ($actual) {
            $sql = "UPDATE campus.modulo_progreso 
                    SET completado = true, 
                        porcentaje = 100, 
                        ultima_actualizacion = NOW() 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $actual['id']]);
        } else {
            // Si marcan completado sin haber visto nada antes (raro pero posible)
            $sql = "INSERT INTO campus.modulo_progreso 
                    (usuario_id, modulo_id, porcentaje, completado, tiempo_visto, ultima_actualizacion) 
                    VALUES (:uid, :mid, 100, true, 0, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':uid' => $usuario_id, ':mid' => $modulo_id]);
        }
    }

    // Calcular progreso total del módulo (80% Videos + 20% Evaluación)
    public function calcularProgresoTotal($usuario_id, $modulo_id) {
        // 1. Calcular promedio de videos
        // Recuperar el registro único de progreso del módulo (asumiendo que representa el avance global de videos)
        $progreso_modulo = $this->obtenerProgreso($usuario_id, $modulo_id);
        $promedio_videos = $progreso_modulo ? $progreso_modulo['porcentaje'] : 0;

        // 2. Obtener nota de evaluación
        require_once 'Evaluacion.php';
        $evalModel = new Evaluacion($this->pdo);
        
        // Buscar evaluación del módulo
        $evaluacion = $evalModel->obtenerPorModulo($modulo_id);
        $nota_examen = 0;
        
        if ($evaluacion) {
            $nota_examen = $evalModel->obtenerMejorIntento($usuario_id, $evaluacion['id']);
        }

        // 3. Calcular ponderado
        // Videos 80%, Examen 20%
        $nota_final = ($promedio_videos * 0.80) + ($nota_examen * 0.20);
        
        // 4. Verificar aprobación (>= 92%)
        $aprobado = ($nota_final >= 92);

        return [
            'promedio_videos' => round($promedio_videos, 2),
            'nota_examen' => round($nota_examen, 2),
            'nota_final' => round($nota_final, 2),
            'aprobado' => $aprobado
        ];
    }
}
?>