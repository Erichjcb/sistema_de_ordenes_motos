<?php
require_once 'Conexion.php';

class UsuarioDAO extends Conexion {
    public function buscarPorUsuario($usuario) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare('SELECT id, usuario, password_hash, rol FROM usuarios WHERE usuario = ?');
        $stmt->execute([$usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarCliente($usuario, $passwordHash) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, password_hash, rol) VALUES (?, ?, 'cliente')");
        try {
            return $stmt->execute([$usuario, $passwordHash]);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return false;
            }
            throw $e;
        }
    }
}
