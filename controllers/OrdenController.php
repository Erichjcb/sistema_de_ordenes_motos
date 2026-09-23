<?php
// El controlador necesita comunicarse con la base de datos (Modelo)
require_once 'models/OrdenDAO.php';

class OrdenController {
    private $dao;

    public function __construct() {
        $this->dao = new OrdenDAO();
    }

    // Método principal para procesar envíos de formulario y eliminaciones
    public function manejarPeticiones() {
        // 1. GUARDAR O ACTUALIZAR
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = $_POST['id'] ?? ''; 
            $tipo        = $_POST['tipo'];
            $fecha       = $_POST['fecha'];
            $num_orden   = $_POST['num_orden'];
            $producto    = $_POST['producto'];
            $proveedor   = $_POST['proveedor'];
            $cantidad    = $_POST['cantidad'];
            $precio      = $_POST['precio'];
            $num_cliente = $_POST['num_cliente'];
            $destino     = $_POST['destino'];

            if ($tipo === 'local') {
                $ordenObj = new OrdenLocal($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino);
            } else {
                $ordenObj = new OrdenNacional($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino);
            }

            if (!empty($id)) {
                $this->dao->actualizar($id, $ordenObj, $tipo);
            } else {
                $this->dao->registrar($ordenObj, $tipo);
            }
            
            header("Location: index.php");
            exit;
        }

        // 2. ELIMINAR
        if (isset($_GET['eliminar'])) {
            $this->dao->eliminar($_GET['eliminar']);
            header("Location: index.php");
            exit;
        }
    }

    // Método para preparar los datos si el usuario quiere editar
    public function obtenerOrdenParaEditar() {
        if (isset($_GET['editar'])) {
            return $this->dao->obtenerPorId($_GET['editar']);
        }
        return null;
    }

    // Método para decidir si mostramos el número de orden a editar o generamos uno nuevo
    public function obtenerNumeroOrden() {
        if (isset($_GET['editar'])) {
            $ordenEditar = $this->dao->obtenerPorId($_GET['editar']);
            return $ordenEditar['num_orden'];
        }
        return $this->dao->obtenerSiguienteNumeroOrden();
    }

    // Método para obtener todas las órdenes y mandarlas a la tabla
    public function listarOrdenes() {
        return $this->dao->listar();
    }
}
?>