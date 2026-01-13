-- ============================================================
-- Migration: Add Course Feedback/Comments System
-- Description: Creates comentarios_cursos table for user feedback
-- Date: 2025-12-16
-- ============================================================

CREATE TABLE IF NOT EXISTS campus.comentarios_cursos (
    id SERIAL PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    
    -- Satisfaction ratings (1-5 scale)
    calidad_contenido SMALLINT CHECK (calidad_contenido >= 1 AND calidad_contenido <= 5),
    facilidad_uso SMALLINT CHECK (facilidad_uso >= 1 AND facilidad_uso <= 5),
    utilidad_practica SMALLINT CHECK (utilidad_practica >= 1 AND utilidad_practica <= 5),
    
    -- Open comments
    otros_comentarios TEXT,
    
    -- Metadata
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (curso_id) REFERENCES campus.cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES campus.usuarios(id) ON DELETE CASCADE,
    UNIQUE (curso_id, usuario_id) -- One feedback per user per course
);

-- Indexes for performance
CREATE INDEX idx_comentarios_curso ON campus.comentarios_cursos(curso_id);
CREATE INDEX idx_comentarios_usuario ON campus.comentarios_cursos(usuario_id);
CREATE INDEX idx_comentarios_fecha ON campus.comentarios_cursos(creado_en DESC);

-- Update trigger for actualizado_en
CREATE OR REPLACE FUNCTION update_comentarios_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.actualizado_en = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_comentarios_timestamp
    BEFORE UPDATE ON campus.comentarios_cursos
    FOR EACH ROW
    EXECUTE FUNCTION update_comentarios_timestamp();
