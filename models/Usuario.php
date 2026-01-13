<?php
// models/Usuario.php

class Usuario
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Obtener usuario con empresa
    public function obtenerPorId($id)
    {
        $sql = "SELECT id, nombre, email, rol, empresa, activo FROM campus.usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Listar usuarios por empresa (para agentes y clientes)
    public function listarPorEmpresa($empresa)
    {
        $sql = "SELECT id, nombre, email, rol FROM campus.usuarios 
                WHERE empresa = ? AND rol IN ('agente', 'cliente') 
                ORDER BY nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$empresa]);
        return $stmt->fetchAll();
    }

    // Actualizar empresa de un usuario
    public function actualizarEmpresa($id, $empresa)
    {
        $sql = "UPDATE campus.usuarios SET empresa = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$empresa, $id]);
    }

    // Listar usuarios con paginación y búsqueda
    public function listarPaginado($pagina = 1, $por_pagina = 10, $busqueda = '')
    {
        $offset = ($pagina - 1) * $por_pagina;
        $params = [];
        $sql = "SELECT id, nombre, email, rol, empresa, activo, creado_en FROM campus.usuarios";

        if (!empty($busqueda)) {
            $sql .= " WHERE nombre LIKE ? OR empresa LIKE ? OR email LIKE ?";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        $sql .= " ORDER BY id DESC LIMIT $por_pagina OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Contar total de usuarios para paginación
    public function contarUsuarios($busqueda = '')
    {
        $sql = "SELECT COUNT(*) FROM campus.usuarios";
        $params = [];

        if (!empty($busqueda)) {
            $sql .= " WHERE nombre LIKE ? OR empresa LIKE ? OR email LIKE ?";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Crear usuario
    public function crear($nombre, $email, $password, $rol, $empresa, $activo = 1)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO campus.usuarios (nombre, email, password, rol, empresa, activo, creado_en) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre, $email, $hash, $rol, $empresa, $activo]);
    }

    // Actualizar usuario
    public function actualizar($id, $nombre, $email, $rol, $empresa, $password = null, $activo = 1)
    {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE campus.usuarios 
                    SET nombre = ?, email = ?, rol = ?, empresa = ?, password = ?, activo = ? 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$nombre, $email, $rol, $empresa, $hash, $activo, $id]);
        } else {
            $sql = "UPDATE campus.usuarios 
                    SET nombre = ?, email = ?, rol = ?, empresa = ?, activo = ? 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$nombre, $email, $rol, $empresa, $activo, $id]);
        }
    }

    // Eliminar usuario
    public function eliminar($id)
    {
        $sql = "DELETE FROM campus.usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Actualizar perfil propio (solo nombre y email)
    public function actualizarPerfil($id, $nombre, $email)
    {
        $sql = "UPDATE campus.usuarios SET nombre = ?, email = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre, $email, $id]);
    }

    // Cambiar contraseña con verificación de contraseña actual
    public function cambiarPassword($id, $password_actual, $password_nueva)
    {
        // Obtener contraseña actual del usuario
        $sql = "SELECT password FROM campus.usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            return false;
        }

        // Verificar que la contraseña actual sea correcta
        if (!password_verify($password_actual, $usuario['password'])) {
            return false;
        }

        // Actualizar con la nueva contraseña
        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $sql = "UPDATE campus.usuarios SET password = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$hash, $id]);
    }

    // Resetear contraseña (solo para admin, no requiere contraseña anterior)
    public function resetearPassword($id, $nueva_password)
    {
        $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $sql = "UPDATE campus.usuarios SET password = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$hash, $id]);
    }

    // Listar todos los usuarios (para dropdown de reseteo de contraseña)
    public function listarTodos()
    {
        $sql = "SELECT id, nombre, email, rol, empresa FROM campus.usuarios ORDER BY nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
?>