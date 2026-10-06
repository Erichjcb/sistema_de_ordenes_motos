<?php
require_once 'models/OrdenDAO.php';

class OrdenController {
    private $dao;

    public function __construct() {
        $this->dao = new OrdenDAO();
    }

    public function manejarPeticiones() {
        $rol = $_REQUEST['rol'] ?? 'inicio';
        $vista = $_REQUEST['vista'] ?? 'nueva';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? 'guardar';

            // ACCIÓN: EL PROVEEDOR CAMBIA EL ESTADO
            if ($accion === 'cambiar_estado') {
                $id_orden = $_POST['id_orden'];
                $nuevo_estado = $_POST['nuevo_estado'];
                $this->dao->actualizarEstado($id_orden, $nuevo_estado);
                
                $_SESSION['mensaje'] = "✅ Estado actualizado a '$nuevo_estado'.";
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: index.php?rol=proveedor");
                exit;
            }

            // ACCIÓN: EL CLIENTE GUARDA/ACTUALIZA UNA ORDEN
            if ($accion === 'guardar') {
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
                    $this->dao->actualizar($id, $ordenObj, $tipo);
                    $_SESSION['mensaje'] = "✅ Orden actualizada exitosamente.";
                } else {
                    $this->dao->registrar($ordenObj, $tipo);
                    $_SESSION['mensaje'] = "✅ Orden generada con éxito.";
                }
                
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: index.php?rol=cliente&vista=seguimiento&buscar_orden=" . $num_orden);
                exit;
            }
        }

        // ACCIÓN: ELIMINAR
        if (isset($_GET['eliminar'])) {
            $this->dao->eliminar($_GET['eliminar']);
            $_SESSION['mensaje'] = "✅ Orden eliminada correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: index.php?rol=$rol&vista=$vista");
            exit;
        }
    }

    public function obtenerOrdenParaEditar() {
        if (isset($_GET['editar'])) {
            return $this->dao->obtenerPorId($_GET['editar']);
        }
        return null;
    }

    public function obtenerNumeroOrden() {
        if (isset($_GET['editar'])) {
            $ordenEditar = $this->dao->obtenerPorId($_GET['editar']);
            return $ordenEditar['num_orden'];
        }
        return $this->dao->obtenerSiguienteNumeroOrden();
    }

    public function listarOrdenes() {
        return $this->dao->listar();
    }

    // NUEVO: Buscar por N° Orden
    public function buscarOrdenPorNumero($num_orden) {
        return $this->dao->buscarPorNumOrden($num_orden);
    }
}
?>