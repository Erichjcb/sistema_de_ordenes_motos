<?php
// 1. Cargar el controlador
require_once 'controllers/OrdenController.php';

// 2. Instanciar el controlador
$controlador = new OrdenController();

// 3. Procesar las acciones (Si el usuario envió un formulario o dio clic en eliminar)
$controlador->manejarPeticiones();

// 4. Obtener los datos necesarios que la vista necesita mostrar
$ordenEditar          = $controlador->obtenerOrdenParaEditar();
$numero_orden_mostrar = $controlador->obtenerNumeroOrden();
$listaOrdenes         = $controlador->listarOrdenes();

// 5. Cargar la Vista (El HTML)
require_once 'views/orden_view.php';
?>