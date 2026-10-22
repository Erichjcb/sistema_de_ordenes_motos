<?php
require_once 'models/UsuarioDAO.php';

class AuthController {
    private $dao;

    public function __construct() {
        $this->dao = new UsuarioDAO();
    }

    public function obtenerTokenCsrf() {
        if (empty($_SESSION['token_csrf'])) {
            $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['token_csrf'];
    }

    public function manejarPeticiones() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['auth_action'])) {
            return;
        }

        $token = $_POST['token_csrf'] ?? '';
        if (!is_string($token) || !hash_equals($this->obtenerTokenCsrf(), $token)) {
            http_response_code(400);
            exit('Solicitud inválida. Recarga la página e inténtalo de nuevo.');
        }

        $accion = $_POST['auth_action'];

        if ($accion === 'logout') {
            $_SESSION = [];
            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        }

        if ($accion === 'login') {
            $usuarioInput = $_POST['usuario'] ?? '';
            $passwordInput = $_POST['contrasena'] ?? '';
            $usuario = is_string($usuarioInput) ? trim($usuarioInput) : '';
            $contrasena = is_string($passwordInput) ? $passwordInput : '';
            $cuenta = $this->dao->buscarPorUsuario($usuario);

            if (!$cuenta || !password_verify($contrasena, $cuenta['password_hash'])) {
                $_SESSION['mensaje'] = 'Usuario o contraseña incorrectos.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?acceso=login');
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id' => (int) $cuenta['id'],
                'usuario' => $cuenta['usuario'],
                'rol' => $cuenta['rol']
            ];
            unset($_SESSION['token_csrf']);
            header('Location: index.php?rol=' . ($cuenta['rol'] === 'proveedor' ? 'proveedor' : 'cliente'));
            exit;
        }

        if ($accion === 'registro') {
            $usuarioInput = $_POST['usuario'] ?? '';
            $passwordInput = $_POST['contrasena'] ?? '';
            $confirmationInput = $_POST['confirmacion'] ?? '';
            $usuario = is_string($usuarioInput) ? trim($usuarioInput) : '';
            $contrasena = is_string($passwordInput) ? $passwordInput : '';
            $confirmacion = is_string($confirmationInput) ? $confirmationInput : '';

            if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $usuario)) {
                $_SESSION['mensaje'] = 'El usuario debe tener entre 3 y 50 caracteres: letras, números, punto, guion o guion bajo.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?acceso=registro');
                exit;
            }

            if (strlen($contrasena) < 8) {
                $_SESSION['mensaje'] = 'La contraseña debe tener al menos 8 caracteres.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?acceso=registro');
                exit;
            }

            if ($contrasena !== $confirmacion) {
                $_SESSION['mensaje'] = 'Las contraseñas no coinciden.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?acceso=registro');
                exit;
            }

            if (!$this->dao->registrarCliente($usuario, password_hash($contrasena, PASSWORD_DEFAULT))) {
                $_SESSION['mensaje'] = 'Ese nombre de usuario ya está registrado.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?acceso=registro');
                exit;
            }

            $_SESSION['mensaje'] = 'Cuenta creada. Ya puedes iniciar sesión.';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: index.php?acceso=login');
            exit;
        }
    }
}
