<?php
session_start();
require_once 'controllers/OrdenController.php';

$controlador = new OrdenController();
$controlador->manejarPeticiones();

$rol = $_GET['rol'] ?? 'inicio';

if ($rol === 'cliente') {
    // Definimos qué pestaña del cliente se va a mostrar (por defecto mostramos opciones)
    $vista_cliente = $_GET['vista'] ?? 'opciones'; 
    
    $ordenEditar          = $controlador->obtenerOrdenParaEditar();
    $numero_orden_mostrar = $controlador->obtenerNumeroOrden();
    
    // Lógica para el buscador de seguimiento
    $ordenBuscada = null;
    if (!empty($_GET['buscar_orden'])) {
        $ordenBuscada = $controlador->buscarOrdenPorNumero($_GET['buscar_orden']);
        if (!$ordenBuscada) {
            $_SESSION['mensaje'] = "⚠️ No se encontró la orden: " . htmlspecialchars($_GET['buscar_orden']);
            $_SESSION['tipo_mensaje'] = "error";
        }
    }

    require_once 'views/cliente_view.php';

} elseif ($rol === 'proveedor') {
    $listaOrdenes = $controlador->listarOrdenes();
    require_once 'views/proveedor_view.php';
    
} else {
    require_once 'views/inicio_view.php';
}
?>