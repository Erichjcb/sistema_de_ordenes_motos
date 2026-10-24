<?php
$pagina = $_GET['pagina'] ?? 'catalogo';
$usuarioEsCustomer = isset($usuarioActual) && $usuarioActual && $usuarioActual['rol'] === 'cliente';
$cantidadCarrito = array_sum(array_map(function ($linea) {
    return (int) $linea['cantidad'];
}, $carrito));
$subtotalCarrito = 0;
foreach ($carrito as $linea) {
    $subtotalCarrito += (float) $linea['precio'] * (int) $linea['cantidad'];
}
$ciudadCheckout = $usuarioActual['ciudad'] ?? '';
$distritoCheckout = $usuarioActual['distrito'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo - Sistema de Repuestos</title>
    <link rel="stylesheet" href="assets/css/tienda.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand-lockup" href="index.php?pagina=catalogo"><span class="brand-mark">🏍</span><span>Ruta Moto<br><small>Repuestos & accesorios</small></span></a>
            <nav class="site-nav">
                <a href="index.php?pagina=catalogo">Catálogo</a>
                <?php if ($usuarioEsCustomer): ?>
                    <a href="index.php?pagina=pedidos">Mis pedidos</a>
                <?php endif; ?>
            </nav>
            <div class="header-actions">
                <a class="cart-link" href="index.php?pagina=carrito">🛒 Carrito (<?= (int) $cantidadCarrito ?>)</a>
                <?php if ($usuarioActual): ?>
                    <form action="index.php" method="POST">
                        <input type="hidden" name="auth_action" value="logout">
                        <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button class="secundario" type="submit">Salir</button>
                    </form>
                <?php else: ?>
                    <a href="index.php?acceso=login">Ingresar</a>
                    <a class="boton" href="index.php?acceso=registro">Crear cuenta</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="page-shell">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alerta <?= htmlspecialchars($_SESSION['tipo_mensaje'] ?? 'error', ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

    <?php if ($pagina === 'catalogo'): ?>
        <section class="hero">
            <h1>Tu próxima ruta empieza con el repuesto correcto.</h1>
            <p>Encuentra piezas y accesorios para mantener tu moto lista. Precios y existencias de demostración; verifica la compatibilidad antes de comprar.</p>
            <a class="boton" href="#productos">Explorar repuestos</a>
        </section>
        <div class="section-heading" id="productos">
            <div><h2>Catálogo de repuestos</h2><span class="muted">Productos seleccionados para tu motocicleta.</span></div>
        </div>
        <?php if (!$catalogo): ?>
            <section class="panel empty-state"><p>Aún no hay productos disponibles.</p></section>
        <?php else: ?>
        <div class="catalogo">
            <?php foreach ($catalogo as $oferta): ?>
                <article class="producto">
                    <img class="product-image" src="<?= htmlspecialchars($oferta['imagen_url'] ?: 'https://placehold.co/640x420/172554/FFFFFF?text=Repuesto+moto', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($oferta['producto'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                    <div class="product-body">
                        <span class="product-sku"><?= htmlspecialchars($oferta['sku'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars($oferta['producto'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="product-description"><?= htmlspecialchars($oferta['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="product-vendor">Proveedor: <?= htmlspecialchars($oferta['proveedor'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="product-price">S/ <?= number_format((float) $oferta['precio'], 2) ?></p>
                        <span class="product-stock"><?= (int) $oferta['stock'] ?> disponibles</span>
                        <form class="product-form" action="index.php" method="POST">
                            <input type="hidden" name="tienda_action" value="agregar_carrito">
                            <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="oferta_id" value="<?= (int) $oferta['oferta_id'] ?>">
                            <label for="cantidad-<?= (int) $oferta['oferta_id'] ?>">Cantidad
                                <input id="cantidad-<?= (int) $oferta['oferta_id'] ?>" type="number" name="cantidad" value="1" min="1" max="999" required>
                            </label>
                            <button type="submit">Agregar</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php elseif ($pagina === 'carrito'): ?>
        <section class="panel">
            <h2>Tu carrito</h2>
            <?php if (!$carrito): ?>
                <p>El carrito está vacío.</p>
                <a class="boton" href="index.php?pagina=catalogo">Explorar catálogo</a>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="tabla-pedido">
                        <thead><tr><th>Repuesto</th><th>Proveedor</th><th>Precio</th><th>Disponibles</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($carrito as $linea): ?>
                            <tr>
                                <td><?= htmlspecialchars($linea['producto'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars($linea['sku'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td><?= htmlspecialchars($linea['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>S/ <?= number_format((float) $linea['precio'], 2) ?></td>
                                <td>
                                    <?php if (isset($linea['precio_referencia']) && abs((float) $linea['precio'] - (float) $linea['precio_referencia']) >= 0.005): ?>
                                        <span class="aviso">Precio actualizado: S/ <?= number_format((float) $linea['precio_referencia'], 2) ?> → S/ <?= number_format((float) $linea['precio'], 2) ?></span><br>
                                    <?php endif; ?>
                                    <?= (int) $linea['stock'] ?><br>Asignadas si confirmas: <?= min((int) $linea['cantidad'], (int) $linea['stock']) ?><?php if ((int) $linea['cantidad'] > (int) $linea['stock']): ?><br><span class="aviso">Backorder: <?= (int) $linea['cantidad'] - (int) $linea['stock'] ?></span><?php endif; ?>
                                </td>
                                <td>
                                    <form class="acciones" action="index.php" method="POST">
                                        <input type="hidden" name="tienda_action" value="actualizar_carrito">
                                        <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="oferta_id" value="<?= (int) $linea['oferta_id'] ?>">
                                        <input type="number" name="cantidad" value="<?= (int) $linea['cantidad'] ?>" min="0" max="999" required>
                                        <button type="submit">Actualizar</button>
                                    </form>
                                </td>
                                <td>S/ <?= number_format((float) $linea['precio'] * (int) $linea['cantidad'], 2) ?></td>
                                <td>
                                    <form action="index.php" method="POST">
                                        <input type="hidden" name="tienda_action" value="quitar_carrito">
                                        <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="oferta_id" value="<?= (int) $linea['oferta_id'] ?>">
                                        <button class="secundario" type="submit">Quitar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="product-price">Subtotal actual: S/ <?= number_format($subtotalCarrito, 2) ?> · El precio se confirma al finalizar.</p>
                <p class="aviso">Las unidades disponibles se descuentan al crear el pedido. Si falta stock, el pedido esperará a completar todas las unidades antes del despacho.</p>
                <a class="boton" href="index.php?pagina=checkout">Proceder al pago</a>
            <?php endif; ?>
        </section>
    <?php elseif ($pagina === 'checkout'): ?>
        <section class="panel">
            <h2>Finalizar compra</h2>
            <?php if (!$carrito): ?>
                <p>Tu carrito está vacío.</p>
                <a href="index.php?pagina=catalogo">Volver al catálogo</a>
            <?php else: ?>
                <p>Un solo pedido incluirá todas las líneas, incluso si pertenecen a distintos proveedores. El precio vigente y el stock se validan al confirmar.</p>
                <form action="index.php" method="POST">
                    <input type="hidden" name="tienda_action" value="finalizar_compra">
                    <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <h3>Datos de envío</h3>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="titular">Nombre del titular</label>
                            <input id="titular" name="titular" maxlength="120" autocomplete="name" required>
                        </div>
                        <div class="form-field">
                            <label for="direccion">Dirección de envío</label>
                            <input id="direccion" name="direccion" maxlength="255" autocomplete="street-address" required>
                        </div>
                        <div class="form-field">
                            <label for="ciudad">Ciudad</label>
                            <input id="ciudad" name="ciudad" value="<?= htmlspecialchars($ciudadCheckout, ENT_QUOTES, 'UTF-8') ?>" maxlength="100" autocomplete="address-level2" required>
                        </div>
                        <div class="form-field">
                            <label for="distrito">Distrito</label>
                            <input id="distrito" name="distrito" value="<?= htmlspecialchars($distritoCheckout, ENT_QUOTES, 'UTF-8') ?>" maxlength="100" autocomplete="address-level3" required>
                        </div>
                    </div>
                    <div class="panel">
                        <h3>Resumen de compra</h3>
                        <p>Subtotal vigente: <strong>S/ <?= number_format($subtotalCarrito, 2) ?></strong></p>
                        <p>Envío estimado: <strong id="costo-envio" data-local="10.00" data-nacional="50.00">S/ 50.00 (nacional)</strong></p>
                        <p class="product-price">Total estimado: <span id="total-estimado" data-subtotal="<?= htmlspecialchars(number_format($subtotalCarrito, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>">S/ <?= number_format($subtotalCarrito + 50, 2) ?></span></p>
                    </div>
                    <?php if ($hayCambiosPrecio): ?>
                        <div class="alerta error"><strong>El precio de uno o más productos cambió.</strong> Revisa el precio vigente en el carrito y acepta el nuevo total para continuar.</div>
                        <label class="check-row"><input type="checkbox" name="aceptar_cambio_precio" value="1" required> Acepto los precios actualizados de los productos.</label>
                    <?php endif; ?>
                    <label class="check-row"><input type="checkbox" name="guardar_direccion" value="1"> Guardar ciudad y distrito en mi perfil para futuras compras.</label>
                    <label class="check-row"><input type="checkbox" name="pago_demo" value="1" required> Simular aprobación del pago (demo; no se procesan datos de tarjeta).</label>
                    <button type="submit">Simular pago y crear pedido</button>
                </form>
            <?php endif; ?>
        </section>
    <?php elseif ($pagina === 'confirmacion'): ?>
        <section class="panel">
            <h2>Pedido confirmado</h2>
            <?php if ($pedidoConfirmado): ?>
                <p>Tu compra se registró correctamente. A continuación encontrarás el detalle guardado de tu pedido.</p>
                <div class="order-meta">
                    <div class="meta-card"><small>Número de pedido</small><strong><?= htmlspecialchars($pedidoConfirmado['numero'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="meta-card"><small>Fecha</small><strong><?= htmlspecialchars($pedidoConfirmado['fecha'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="meta-card"><small>Estado</small><strong><?= htmlspecialchars($pedidoConfirmado['estado'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="meta-card"><small>Pago</small><strong><?= htmlspecialchars($pedidoConfirmado['estado_pago'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                </div>
                <h3>Datos del titular y envío</h3>
                <p><strong><?= htmlspecialchars($pedidoConfirmado['titular'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                    <?= htmlspecialchars($pedidoConfirmado['direccion'], ENT_QUOTES, 'UTF-8') ?><br>
                    <?= htmlspecialchars($pedidoConfirmado['distrito'] ?? '', ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($pedidoConfirmado['ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <div class="table-wrap">
                    <table class="tabla-pedido">
                        <thead><tr><th>SKU</th><th>Repuesto</th><th>Proveedor</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
                        <tbody>
                        <?php foreach ($pedidoConfirmado['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) $item['cantidad'] ?></td>
                                <td>S/ <?= number_format((float) $item['precio_unitario'], 2) ?></td>
                                <td>S/ <?= number_format((float) $item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr><th colspan="5">Subtotal de productos</th><td>S/ <?= number_format((float) $pedidoConfirmado['subtotal'], 2) ?></td></tr>
                            <tr><th colspan="5">Envío <?= htmlspecialchars($pedidoConfirmado['tipo_envio'], ENT_QUOTES, 'UTF-8') ?></th><td>S/ <?= number_format((float) $pedidoConfirmado['costo_envio'], 2) ?></td></tr>
                            <tr><th colspan="5">Total</th><td>S/ <?= number_format((float) $pedidoConfirmado['total'], 2) ?></td></tr>
                        </tfoot>
                    </table>
                </div>
                <?php if ((int) $pedidoConfirmado['unidades_backorder'] > 0): ?>
                    <p class="aviso">Hay <?= (int) $pedidoConfirmado['unidades_backorder'] ?> unidad(es) pendientes de abastecimiento. El pedido se despachará cuando se cubra el faltante.</p>
                <?php endif; ?>
                <a class="boton" href="index.php?pagina=pedidos">Ver mis pedidos</a>
            <?php else: ?>
                <p>No hay una confirmación reciente. Puedes revisar tus pedidos.</p>
                <a href="index.php?pagina=pedidos">Mis pedidos</a>
            <?php endif; ?>
        </section>
    <?php elseif ($pagina === 'pedidos' && $usuarioEsCustomer): ?>
        <section class="panel">
            <h2>Mis pedidos</h2>
            <?php if (!$listaPedidos): ?><p>Aún no tienes pedidos.</p><?php else: ?>
                <div class="table-wrap"><table class="tabla-pedido">
                    <thead><tr><th>Número</th><th>Fecha</th><th>Artículos</th><th>Estado</th><th>Asignadas / pendientes</th><th>Total</th><th>Pago</th><th>Acciones</th></tr></thead>
                    <tbody><?php foreach ($listaPedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['numero'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($pedido['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($pedido['resumen_items'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($pedido['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $pedido['unidades_asignadas'] ?> / <?= (int) $pedido['unidades_backorder'] ?></td>
                            <td>S/ <?= number_format((float) $pedido['total'], 2) ?></td>
                            <td><?= htmlspecialchars($pedido['estado_pago'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (in_array($pedido['estado'], ['Procesando', 'Pendiente de abastecimiento'], true)): ?>
                                    <form action="index.php" method="POST" onsubmit="return confirm('¿Cancelar este pedido? Las unidades asignadas volverán al stock.');">
                                        <input type="hidden" name="tienda_action" value="cancelar_mi_pedido">
                                        <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                                        <button class="secundario" type="submit">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?></tbody>
                </table></div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="panel"><p>Página no encontrada.</p><a href="index.php?pagina=catalogo">Volver al catálogo</a></section>
    <?php endif; ?>
    </main>
    <footer class="site-footer">
        <div class="footer-inner">
            <section><h2>Ruta Moto</h2><p>Repuestos para seguir disfrutando cada recorrido. Catálogo y precios con fines demostrativos.</p></section>
            <section><h3>Atención</h3><p>Consultas sobre productos y pedidos:</p><p><a href="mailto:ventas@rutamoto.example">ventas@rutamoto.example</a><br>Atención: lunes a sábado, 9:00 a 18:00</p></section>
            <section><h3>Información</h3><ul><li><a href="index.php?pagina=catalogo">Catálogo</a></li><li><a href="index.php?pagina=carrito">Carrito</a></li><?php if ($usuarioEsCustomer): ?><li><a href="index.php?pagina=pedidos">Mis pedidos</a></li><?php endif; ?></ul></section>
        </div>
        <div class="footer-bottom">© <?= date('Y') ?> Ruta Moto · Proyecto educativo · Pagos simulados</div>
    </footer>
    <script>
        const ciudadInput = document.getElementById('ciudad');
        const costoEnvio = document.getElementById('costo-envio');
        const totalEstimado = document.getElementById('total-estimado');
        if (ciudadInput && costoEnvio && totalEstimado) {
            const actualizarEnvio = () => {
                const ciudadLocal = ['lima', 'oxapampa'].includes(ciudadInput.value.trim().toLocaleLowerCase('es'));
                const costo = Number(costoEnvio.dataset[ciudadLocal ? 'local' : 'nacional']);
                const subtotal = Number(totalEstimado.dataset.subtotal);
                costoEnvio.textContent = `S/ ${costo.toFixed(2)} (${ciudadLocal ? 'local' : 'nacional'})`;
                totalEstimado.textContent = `S/ ${(subtotal + costo).toFixed(2)}`;
            };
            ciudadInput.addEventListener('input', actualizarEnvio);
            actualizarEnvio();
        }
    </script>
</body>
</html>
