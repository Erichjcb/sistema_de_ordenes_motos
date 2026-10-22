<?php
require_once 'models/OrdenDAO.php';

class OrdenController {
    private $dao;

    public function __construct() {
        $this->dao = new OrdenDAO();
    }

    public function manejarPeticiones() {
        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'] === 'proveedor' ? 'proveedor' : 'cliente';
        $vista = $_REQUEST['vista'] ?? 'nueva';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? 'guardar';
            $token = $_POST['token_csrf'] ?? '';
            if (empty($_SESSION['token_csrf']) || !is_string($token) || !hash_equals($_SESSION['token_csrf'], $token)) {
                http_response_code(400);
                exit('Solicitud inválida. Recarga la página e inténtalo de nuevo.');
            }

            // ACCIÓN: EL PROVEEDOR CAMBIA EL ESTADO
            if ($accion === 'cambiar_estado') {
                if ($rol !== 'proveedor') {
                    http_response_code(403);
                    exit('No tienes permiso para cambiar estados.');
                }
                $id_orden = $_POST['id_orden'] ?? '';
                $nuevo_estado = $_POST['nuevo_estado'] ?? '';
                if (!in_array($nuevo_estado, ['Procesando', 'Confirmado', 'Enviado', 'Cancelado'], true)) {
                    http_response_code(400);
                    exit('Estado de orden inválido.');
                }
                $this->dao->actualizarEstado($id_orden, $nuevo_estado);
                
                $_SESSION['mensaje'] = "✅ Estado actualizado a '$nuevo_estado'.";
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: index.php?rol=proveedor");
                exit;
            }

            // ACCIÓN: EL CLIENTE GUARDA/ACTUALIZA UNA ORDEN
            if ($accion === 'guardar') {
                if ($rol !== 'cliente') {
                    http_response_code(403);
                    exit('No tienes permiso para crear órdenes.');
                }
                $id          = $_POST['id'] ?? ''; 
                $tipo        = $_POST['tipo'] ?? '';
                $fecha       = $_POST['fecha'] ?? '';
                $num_orden   = $_POST['num_orden'] ?? '';
                $producto    = $_POST['producto'] ?? '';
                $proveedor   = $_POST['proveedor'] ?? '';
                $cantidad    = $_POST['cantidad'] ?? '';
                $precio      = $_POST['precio'] ?? '';
                $num_cliente = $_POST['num_cliente'] ?? '';
                $destino     = $_POST['destino'] ?? '';

                if (empty($tipo) || empty($fecha) || empty($producto) || empty($cantidad) || empty($precio) || empty($destino)) {
                    $_SESSION['mensaje'] = "⚠️ Faltan campos por rellenar.";
                    $_SESSION['tipo_mensaje'] = "error";
                    header("Location: index.php?rol=cliente&vista=nueva");
                    exit;
                }

                if ($tipo === 'local') {
                    $ordenObj = new OrdenLocal($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino);
                } else {
                    $ordenObj = new OrdenNacional($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino);
                }

                if (!empty($id)) {
                    if (!$this->dao->actualizar($id, $ordenObj, $tipo, $usuario['id'])) {
                        $_SESSION['mensaje'] = "⚠️ Solo puedes editar tus órdenes en estado Procesando.";
                        $_SESSION['tipo_mensaje'] = "error";
                        header("Location: index.php?rol=cliente&vista=seguimiento");
                        exit;
                    }
                    $_SESSION['mensaje'] = "✅ Orden actualizada exitosamente.";
                } else {
                    $this->dao->registrar($ordenObj, $tipo, $usuario['id']);
                    $_SESSION['mensaje'] = "✅ Orden generada con éxito.";
                }
                
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: index.php?rol=cliente&vista=seguimiento&buscar_orden=" . $num_orden);
                exit;
            }
        }

        // ACCIÓN: ELIMINAR
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
            $customerId = $rol === 'cliente' ? $usuario['id'] : null;
            if (!$this->dao->eliminar($_POST['eliminar'], $customerId)) {
                $_SESSION['mensaje'] = "⚠️ No se pudo eliminar: solo se eliminan órdenes en estado Procesando.";
                $_SESSION['tipo_mensaje'] = "error";
            } else {
                $_SESSION['mensaje'] = "✅ Orden eliminada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            }
            header("Location: index.php?rol=$rol&vista=$vista");
            exit;
        }
    }

    public function obtenerOrdenParaEditar() {
        if (isset($_GET['editar'])) {
            return $this->dao->obtenerPorId($_GET['editar'], $_SESSION['usuario']['id']);
        }
        return null;
    }

    public function obtenerNumeroOrden() {
        if (isset($_GET['editar'])) {
            $ordenEditar = $this->dao->obtenerPorId($_GET['editar'], $_SESSION['usuario']['id']);
            return $ordenEditar['num_orden'] ?? $this->dao->obtenerSiguienteNumeroOrden();
        }
        return $this->dao->obtenerSiguienteNumeroOrden();
    }

    public function listarOrdenes() {
        return $this->dao->listar($_SESSION['usuario']['rol'] === 'cliente' ? $_SESSION['usuario']['id'] : null);
    }

    // NUEVO: Buscar por N° Orden
    public function buscarOrdenPorNumero($num_orden) {
        $customerId = $_SESSION['usuario']['rol'] === 'cliente' ? $_SESSION['usuario']['id'] : null;
        return $this->dao->buscarPorNumOrden($num_orden, $customerId);
    }
}
?>