<?php
session_start();
require_once 'controllers/AuthController.php';

$authController = new AuthController();
$authController->manejarPeticiones();

if (empty($_SESSION['usuario'])) {
    $authView = $_GET['acceso'] ?? 'inicio';
    $csrfToken = $authController->obtenerTokenCsrf();
    require_once 'views/acceso_view.php';
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$rolPermitido = $usuarioActual['rol'] === 'proveedor' ? 'proveedor' : 'cliente';
$rolSolicitado = $_GET['rol'] ?? $rolPermitido;

if ($rolSolicitado !== $rolPermitido) {
    header('Location: index.php?rol=' . $rolPermitido);
    exit;
}

require_once 'controllers/OrdenController.php';

$csrfToken = $authController->obtenerTokenCsrf();
$controlador = new OrdenController();
$controlador->manejarPeticiones();

$rol = $rolSolicitado;

if ($rol === 'cliente') {
    $vista_cliente = $_GET['vista'] ?? 'nueva';
    
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