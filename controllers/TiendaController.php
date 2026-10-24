<?php
require_once 'models/TiendaDAO.php';

class TiendaController {
    private $dao;

    public function __construct() {
        $this->dao = new TiendaDAO();
        if (!isset($_SESSION['carrito_invitado']) || !is_array($_SESSION['carrito_invitado'])) {
            $_SESSION['carrito_invitado'] = [];
        }
    }

    public function manejarPeticiones() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['tienda_action'])) {
            return;
        }

        $token = $_POST['token_csrf'] ?? '';
        if (empty($_SESSION['token_csrf']) || !is_string($token) || !hash_equals($_SESSION['token_csrf'], $token)) {
            http_response_code(400);
            exit('Solicitud inválida. Recarga la página e inténtalo de nuevo.');
        }

        $accion = $_POST['tienda_action'];
        $usuario = $_SESSION['usuario'] ?? null;
        $esAdmin = $usuario && $usuario['rol'] === 'proveedor';
        $esCustomer = $usuario && $usuario['rol'] === 'cliente';

        if (in_array($accion, ['agregar_carrito', 'actualizar_carrito', 'quitar_carrito'], true)) {
            if ($esAdmin) {
                http_response_code(403);
                exit('El carrito de compras es para customers.');
            }
            $ofertaId = filter_var($_POST['oferta_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$ofertaId) {
                $this->redirigirConMensaje('catalogo', 'Oferta no válida.', 'error');
            }

            if ($accion === 'agregar_carrito') {
                $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
                if (!$cantidad || !$this->dao->obtenerOferta($ofertaId)) {
                    $this->redirigirConMensaje('catalogo', 'Selecciona una cantidad y una oferta válidas.', 'error');
                }
                if ($esCustomer) {
                    $this->dao->agregarAlCarrito($usuario['id'], $ofertaId, $cantidad);
                } else {
                    $actual = $_SESSION['carrito_invitado'][$ofertaId] ?? null;
                    if (!is_array($actual)) {
                        $actual = [
                            'cantidad' => (int) $actual,
                            'precio_referencia' => (float) $this->dao->obtenerOferta($ofertaId)['precio']
                        ];
                    }
                    $actual['cantidad'] = min(999, (int) $actual['cantidad'] + $cantidad);
                    $_SESSION['carrito_invitado'][$ofertaId] = $actual;
                }
                $this->redirigirConMensaje('catalogo', 'Producto agregado al carrito.', 'success');
            }

            if ($esCustomer) {
                $cantidad = $accion === 'quitar_carrito' ? 0 : filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999]]);
                if ($cantidad === false || $cantidad === null) {
                    $this->redirigirConMensaje('carrito', 'Cantidad no válida.', 'error');
                }
                $this->dao->actualizarCarrito($usuario['id'], $ofertaId, (int) $cantidad);
            } elseif ($accion === 'quitar_carrito' || (int) ($_POST['cantidad'] ?? 0) < 1) {
                unset($_SESSION['carrito_invitado'][$ofertaId]);
            } else {
                $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
                if (!$cantidad) {
                    $this->redirigirConMensaje('carrito', 'Cantidad no válida.', 'error');
                }
                $item = $_SESSION['carrito_invitado'][$ofertaId] ?? null;
                if (!is_array($item)) {
                    $oferta = $this->dao->obtenerOferta($ofertaId);
                    $item = ['precio_referencia' => (float) ($oferta['precio'] ?? 0)];
                }
                $item['cantidad'] = (int) $cantidad;
                $_SESSION['carrito_invitado'][$ofertaId] = $item;
            }
            $this->redirigirConMensaje('carrito', 'Carrito actualizado.', 'success');
        }

        if ($accion === 'finalizar_compra') {
            if (!$esCustomer) {
                if (!$usuario) {
                    $_SESSION['continuar_checkout'] = true;
                    header('Location: index.php?acceso=login');
                    exit;
                }
                http_response_code(403);
                exit('Solo un customer puede finalizar una compra.');
            }

            $titular = $this->obtenerTextoPost('titular');
            $direccion = $this->obtenerTextoPost('direccion');
            $ciudad = $this->obtenerTextoPost('ciudad');
            $distrito = $this->obtenerTextoPost('distrito');
            if ($titular === '' || strlen($titular) > 120 || $direccion === '' || strlen($direccion) > 255
                || $ciudad === '' || strlen($ciudad) > 100 || $distrito === '' || strlen($distrito) > 100) {
                $this->redirigirConMensaje('checkout', 'Completa correctamente los datos del titular, dirección, ciudad y distrito.', 'error');
            }
            if (empty($_POST['pago_demo'])) {
                $this->redirigirConMensaje('checkout', 'Confirma la simulación de pago para continuar.', 'error');
            }
            $carrito = $this->obtenerCarrito();
            $hayCambiosPrecio = $this->dao->hayCambiosPrecio($carrito);
            if ($hayCambiosPrecio && empty($_POST['aceptar_cambio_precio'])) {
                $this->redirigirConMensaje('checkout', 'El precio cambió. Revisa el total actualizado y confirma que aceptas el nuevo precio.', 'error');
            }

            try {
                $pedido = $this->dao->crearPedido($usuario['id'], [
                    'titular' => $titular,
                    'direccion' => $direccion
                ], $ciudad, $distrito, !empty($_POST['guardar_direccion']), !empty($_POST['aceptar_cambio_precio']));
                if (!empty($_POST['guardar_direccion'])) {
                    $_SESSION['usuario']['ciudad'] = $ciudad;
                    $_SESSION['usuario']['distrito'] = $distrito;
                }
            } catch (RuntimeException $e) {
                $this->redirigirConMensaje('checkout', $e->getMessage(), 'error');
            }
            header('Location: index.php?pagina=confirmacion&pedido_id=' . (int) $pedido['id']);
            exit;
        }

        if ($accion === 'cancelar_mi_pedido') {
            if (!$esCustomer) {
                http_response_code(403);
                exit('Solo un customer puede cancelar sus propios pedidos.');
            }
            $pedidoId = filter_var($_POST['pedido_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$pedidoId) {
                $this->redirigirConMensaje('pedidos', 'Pedido no válido.', 'error');
            }
            try {
                $this->dao->actualizarEstadoPedido($pedidoId, 'Cancelado', $usuario['id']);
                $this->redirigirConMensaje('pedidos', 'Pedido cancelado; las unidades asignadas regresaron al inventario.', 'success');
            } catch (RuntimeException $e) {
                $this->redirigirConMensaje('pedidos', $e->getMessage(), 'error');
            }
        }

        if ($accion === 'agregar_producto') {
            if (!$esAdmin) {
                http_response_code(403);
                exit('Solo Supplier/Admin puede agregar productos al catálogo.');
            }
            $sku = strtoupper($this->obtenerTextoPost('sku'));
            $nombre = $this->obtenerTextoPost('nombre');
            $descripcion = $this->obtenerTextoPost('descripcion');
            $proveedorId = filter_var($_POST['proveedor_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
            if (!preg_match('/^[A-Z0-9._-]{2,40}$/', $sku) || $nombre === '' || strlen($nombre) > 120
                || $descripcion === '' || strlen($descripcion) > 255 || !$proveedorId
                || $precio === false || $precio < 0 || $stock === false || $stock === null) {
                $this->redirigirConMensaje('admin_tienda', 'Revisa el SKU, nombre, descripción, proveedor, precio y stock del producto.', 'error');
            }

            $imagenUrl = '';
            try {
                $imagenUrl = $this->guardarImagenProducto($nombre);
                $this->dao->agregarProductoOferta($sku, $nombre, $descripcion, $imagenUrl, $proveedorId, $precio, $stock);
            } catch (PDOException $e) {
                error_log('Error al registrar producto: ' . $e->getMessage());
                if (strpos($imagenUrl, 'assets/uploads/catalogo/') === 0) {
                    $rutaImagen = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagenUrl);
                    if (is_file($rutaImagen) && !unlink($rutaImagen)) {
                        error_log('No se pudo eliminar la imagen temporal: ' . $rutaImagen);
                    }
                }
                $this->redirigirConMensaje('admin_tienda', 'No se pudo guardar el producto por un error de base de datos.', 'error');
            } catch (RuntimeException $e) {
                if (strpos($imagenUrl, 'assets/uploads/catalogo/') === 0) {
                    $rutaImagen = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagenUrl);
                    if (is_file($rutaImagen) && !unlink($rutaImagen)) {
                        error_log('No se pudo eliminar la imagen temporal: ' . $rutaImagen);
                    }
                }
                $this->redirigirConMensaje('admin_tienda', $e->getMessage(), 'error');
            }
            $this->redirigirConMensaje('admin_tienda', 'Producto y oferta registrados en el catálogo.', 'success');
        }

        if (in_array($accion, ['actualizar_precio', 'reponer_stock', 'cambiar_estado_pedido'], true)) {
            if (!$esAdmin) {
                http_response_code(403);
                exit('Solo Supplier/Admin puede administrar catálogo, inventario y pedidos.');
            }
            try {
                if ($accion === 'actualizar_precio') {
                    $ofertaId = filter_var($_POST['oferta_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
                    if (!$ofertaId || $precio === false || $precio < 0) {
                        throw new RuntimeException('Indica una oferta y un precio válidos.');
                    }
                    $this->dao->actualizarPrecio($ofertaId, $precio);
                    $mensaje = 'Precio actualizado.';
                } elseif ($accion === 'reponer_stock') {
                    $ofertaId = filter_var($_POST['oferta_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
                    if (!$ofertaId || !$cantidad) {
                        throw new RuntimeException('Indica una oferta y una cantidad de reposición válidas.');
                    }
                    $this->dao->reponerStock($ofertaId, $cantidad);
                    $mensaje = 'Reposición registrada; los backorders se asignaron por antigüedad.';
                } else {
                    $pedidoId = filter_var($_POST['pedido_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $estado = $_POST['estado'] ?? '';
                    if (!$pedidoId) {
                        throw new RuntimeException('Pedido no válido.');
                    }
                    $this->dao->actualizarEstadoPedido($pedidoId, $estado);
                    $mensaje = 'Estado del pedido actualizado.';
                }
                $this->redirigirConMensaje('admin_tienda', $mensaje, 'success');
            } catch (RuntimeException $e) {
                $this->redirigirConMensaje('admin_tienda', $e->getMessage(), 'error');
            }
        }
    }

    public function obtenerCatalogo() {
        return $this->dao->listarCatalogo();
    }

    public function obtenerCarrito() {
        $usuario = $_SESSION['usuario'] ?? null;
        if ($usuario && $usuario['rol'] === 'cliente') {
            return $this->dao->listarCarrito($usuario['id']);
        }
        $lineas = [];
        foreach ($_SESSION['carrito_invitado'] as $ofertaId => $item) {
            $oferta = $this->dao->obtenerOferta($ofertaId);
            if ($oferta) {
                $oferta['cantidad'] = is_array($item) ? (int) $item['cantidad'] : (int) $item;
                $oferta['precio_referencia'] = is_array($item) ? (float) $item['precio_referencia'] : (float) $oferta['precio'];
                $lineas[] = $oferta;
            }
        }
        return $lineas;
    }

    public function obtenerPedidos() {
        $usuario = $_SESSION['usuario'];
        return $this->dao->listarPedidos($usuario['rol'] === 'cliente' ? $usuario['id'] : null);
    }

    public function obtenerOfertasAdministracion() {
        return $this->dao->listarOfertasAdministracion();
    }

    public function obtenerProveedores() {
        return $this->dao->listarProveedores();
    }

    public function hayCambiosPrecio($carrito) {
        return $this->dao->hayCambiosPrecio($carrito);
    }

    private function obtenerTextoPost($campo) {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }

    private function guardarImagenProducto($nombreProducto) {
        $archivo = $_FILES['imagen'] ?? null;
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return 'https://placehold.co/640x420/172554/FFFFFF?text=' . rawurlencode($nombreProducto);
        }
        if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo recibir la imagen. Intenta nuevamente.');
        }
        if (($archivo['size'] ?? 0) < 1 || $archivo['size'] > 3 * 1024 * 1024 || !is_uploaded_file($archivo['tmp_name'] ?? '')) {
            throw new RuntimeException('La imagen debe pesar como máximo 3 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);
        $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensiones[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
            throw new RuntimeException('Solo se permiten imágenes JPEG, PNG o WebP válidas.');
        }

        $directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'catalogo';
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar el directorio de imágenes.');
        }
        $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensiones[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], $directorio . DIRECTORY_SEPARATOR . $nombreArchivo)) {
            throw new RuntimeException('No se pudo guardar la imagen del producto.');
        }
        return 'assets/uploads/catalogo/' . $nombreArchivo;
    }

    private function redirigirConMensaje($pagina, $mensaje, $tipo) {
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = $tipo;
        header('Location: index.php?pagina=' . urlencode($pagina));
        exit;
    }
}
