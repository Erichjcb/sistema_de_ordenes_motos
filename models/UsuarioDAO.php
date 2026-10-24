<?php
require_once 'Conexion.php';

class UsuarioDAO extends Conexion {
    public function buscarPorUsuario($usuario) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare('SELECT id, usuario, password_hash, rol, ciudad, distrito FROM usuarios WHERE usuario = ?');
        $stmt->execute([$usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarCliente($usuario, $passwordHash, $ciudad, $distrito) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, password_hash, rol, ciudad, distrito) VALUES (?, ?, 'cliente', ?, ?)");
        try {
            return $stmt->execute([$usuario, $passwordHash, $ciudad, $distrito]);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return false;
            }
            throw $e;
        }
    }

}
