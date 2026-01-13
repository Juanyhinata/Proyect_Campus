<?php
// models/Evaluacion.php

class Evaluacion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- EVALUACIONES ---

    public function obtenerPorModulo($modulo_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM campus.evaluaciones WHERE modulo_id = ?");
        $stmt->execute([$modulo_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($modulo_id, $titulo, $descripcion)
    {
        $sql = "INSERT INTO campus.evaluaciones (modulo_id, titulo, descripcion) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$modulo_id, $titulo, $descripcion]);
    }

    public function actualizar($id, $titulo, $descripcion)
    {
        $sql = "UPDATE campus.evaluaciones SET titulo = ?, descripcion = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$titulo, $descripcion, $id]);
    }

    // --- PREGUNTAS ---

    public function obtenerPreguntas($evaluacion_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM campus.preguntas WHERE evaluacion_id = ? ORDER BY orden ASC");
        $stmt->execute([$evaluacion_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarPregunta($evaluacion_id, $texto, $orden = 0)
    {
        $sql = "INSERT INTO campus.preguntas (evaluacion_id, texto_pregunta, orden) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$evaluacion_id, $texto, $orden]);
        return $this->pdo->lastInsertId();
    }

    public function eliminarPregunta($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM campus.preguntas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- OPCIONES ---

    public function obtenerOpciones($pregunta_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM campus.opciones WHERE pregunta_id = ?");
        $stmt->execute([$pregunta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarOpcion($pregunta_id, $texto, $es_correcta)
    {
        $sql = "INSERT INTO campus.opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        // Postgres usa 't'/'f' o 1/0 para booleanos, PDO suele manejarlo bien con bool PHP
        return $stmt->execute([$pregunta_id, $texto, $es_correcta ? 'true' : 'false']);
    }
    
    public function eliminarOpcionesPregunta($pregunta_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM campus.opciones WHERE pregunta_id = ?");
        return $stmt->execute([$pregunta_id]);
    }

    // --- INTENTOS Y CALIFICACIÓN ---

    public function calificar($usuario_id, $evaluacion_id, $respuestas_usuario)
    {
        // $respuestas_usuario es un array [pregunta_id => opcion_id_seleccionada]
        
        $preguntas = $this->obtenerPreguntas($evaluacion_id);
        $total_preguntas = count($preguntas);
        
        if ($total_preguntas === 0) return 0;

        $aciertos = 0;

        foreach ($preguntas as $pregunta) {
            $pregunta_id = $pregunta['id'];
            
            // Verificar si el usuario respondió a esta pregunta
            if (isset($respuestas_usuario[$pregunta_id])) {
                $opcion_seleccionada_id = $respuestas_usuario[$pregunta_id];
                
                // Verificar si la opción seleccionada es la correcta
                $stmt = $this->pdo->prepare("SELECT es_correcta FROM campus.opciones WHERE id = ?");
                $stmt->execute([$opcion_seleccionada_id]);
                $es_correcta = $stmt->fetchColumn(); // Devuelve true/false (o 't'/'f' en pg)

                // Normalizar booleano de Postgres
                if ($es_correcta === true || $es_correcta === 't' || $es_correcta === 1 || $es_correcta === '1') {
                    $aciertos++;
                }
            }
        }

        $calificacion = ($aciertos / $total_preguntas) * 100;
        
        // Guardar intento
        // Aprobado si >= 60 en el examen (aunque la regla global es 92% ponderado, aquí guardamos si aprobó el examen per se, o podemos dejarlo en false y que la lógica global decida. 
        // Pero para ser consistentes con "aprobado", usaremos un umbral estándar para el examen, ej 70, o simplemente guardamos el resultado.)
        // El requerimiento dice "92% aprobatorio" ponderado. Así que el booleano 'aprobado' en esta tabla es un poco ambiguo.
        // Lo dejaremos basado en si la nota del examen es decente (ej > 70) o simplemente true siempre que lo termine.
        // Mejor: Guardamos la calificación exacta.
        
        $aprobado_examen = ($calificacion >= 70); // Umbral interno del examen, no del módulo completo.

        $sql = "INSERT INTO campus.intentos_evaluacion (usuario_id, evaluacion_id, calificacion, aprobado, fecha_intento) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $evaluacion_id, $calificacion, $aprobado_examen ? 'true' : 'false']);

        return $calificacion;
    }

    public function obtenerMejorIntento($usuario_id, $evaluacion_id)
    {
        $sql = "SELECT MAX(calificacion) as mejor_nota FROM campus.intentos_evaluacion 
                WHERE usuario_id = ? AND evaluacion_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $evaluacion_id]);
        return $stmt->fetchColumn() ?: 0;
    }
}
?>
