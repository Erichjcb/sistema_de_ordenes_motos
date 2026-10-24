<?php
session_start();
require_once 'controllers/AuthController.php';
require_once 'controllers/TiendaController.php';

$authController = new AuthController();
$authController->manejarPeticiones();
$csrfToken = $authController->obtenerTokenCsrf();

$tienda = new TiendaController();
$tienda->manejarPeticiones();
$usuarioActual = $_SESSION['usuario'] ?? null;
$pagina = $_GET['pagina'] ?? ($usuarioActual && $usuarioActual['rol'] === 'proveedor' ? 'admin_tienda' : 'catalogo');

if (!$usuarioActual && in_array($_GET['acceso'] ?? '', ['login', 'registro'], true)) {
    $authView = $_GET['acceso'];
    require_once 'views/acceso_view.php';
    exit;
}

if ($pagina === 'checkout' && (!$usuarioActual || $usuarioActual['rol'] !== 'cliente')) {
    if (!$usuarioActual) {
        $_SESSION['continuar_checkout'] = true;
        header('Location: index.php?acceso=login');
        exit;
    }
    http_response_code(403);
    exit('Solo un customer puede continuar al checkout.');
}

if (!$usuarioActual && !in_array($pagina, ['catalogo', 'carrito'], true)) {
    header('Location: index.php?pagina=catalogo');
    exit;
}

if ($usuarioActual && $usuarioActual['rol'] === 'proveedor') {
    if ($pagina === 'ordenes_anteriores') {
        require_once 'controllers/OrdenController.php';
        $controlador = new OrdenController();
        $controlador->manejarPeticiones();
        $listaOrdenes = $controlador->listarOrdenes();
        require_once 'views/proveedor_view.php';
        exit;
    }
    if (!in_array($pagina, ['admin_tienda', 'catalogo_admin'], true)) {
        header('Location: index.php?pagina=admin_tienda');
        exit;
    }
    $ofertasAdministracion = $tienda->obtenerOfertasAdministracion();
    $proveedoresCatalogo = $tienda->obtenerProveedores();
    $listaPedidos = $tienda->obtenerPedidos();
    require_once 'views/admin_tienda_view.php';
    exit;
}

$catalogo = $tienda->obtenerCatalogo();
$carrito = $tienda->obtenerCarrito();
$listaPedidos = $usuarioActual ? $tienda->obtenerPedidos() : [];
$hayCambiosPrecio = $tienda->hayCambiosPrecio($carrito);
$pedidoConfirmado = null;
if ($pagina === 'confirmacion' && $usuarioActual && $usuarioActual['rol'] === 'cliente') {
    $pedidoId = filter_var($_GET['pedido_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($pedidoId) {
        $pedidoConfirmado = (new TiendaDAO())->obtenerPedidoCompleto($pedidoId, $usuarioActual['id']);
    }
}
require_once 'views/tienda_view.php';
?>