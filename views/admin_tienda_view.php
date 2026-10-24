<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administración de catálogo e inventario</title>
    <link rel="stylesheet" href="assets/css/tienda.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
        <a class="brand-lockup" href="index.php?pagina=admin_tienda"><span class="brand-mark">🏍</span><span>Ruta Moto<br><small>Administración</small></span></a>
        <nav class="site-nav"><a href="index.php?pagina=admin_tienda">Catálogo e inventario</a><a href="index.php?pagina=ordenes_anteriores">Órdenes anteriores</a></nav>
        <div class="header-actions">
            <form action="index.php" method="POST" style="display:inline">
                <input type="hidden" name="auth_action" value="logout">
                <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button class="secundario" type="submit">Salir (<?= htmlspecialchars($usuarioActual['usuario'], ENT_QUOTES, 'UTF-8') ?>)</button>
            </form>
        </div>
        </div>
    </header>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alerta <?= htmlspecialchars($_SESSION['tipo_mensaje'] ?? 'error', ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <main class="page-shell">
    <section class="panel">
        <h2>Ofertas, precios y existencias</h2>
        <p>Las cantidades que ingreses como reposición se aplicarán primero a los backorders más antiguos.</p>
        <div class="table-wrap"><table class="tabla-pedido">
            <thead><tr><th>SKU</th><th>Repuesto</th><th>Proveedor</th><th>Precio (S/)</th><th>Stock disponible</th><th>Reposición</th></tr></thead>
            <tbody>
            <?php foreach ($ofertasAdministracion as $oferta): ?>
                <tr>
                    <td><?= htmlspecialchars($oferta['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($oferta['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($oferta['proveedor'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form class="fila-form" action="index.php" method="POST">
                            <input type="hidden" name="tienda_action" value="actualizar_precio">
                            <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="oferta_id" value="<?= (int) $oferta['oferta_id'] ?>">
                            <input type="number" name="precio" min="0" step="0.01" value="<?= htmlspecialchars($oferta['precio'], ENT_QUOTES, 'UTF-8') ?>" required>
                            <button type="submit">Guardar precio</button>
                        </form>
                    </td>
                    <td><?= (int) $oferta['stock'] ?></td>
                    <td>
                        <form class="fila-form" action="index.php" method="POST">
                            <input type="hidden" name="tienda_action" value="reponer_stock">
                            <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="oferta_id" value="<?= (int) $oferta['oferta_id'] ?>">
                            <input type="number" name="cantidad" min="1" max="100000" placeholder="Unidades" required>
                            <button type="submit">Registrar reposición</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </section>

    <section class="panel">
        <h2>Agregar nuevo item</h2>
        <p>Registra el producto y su primera oferta de proveedor para publicarlo en el catálogo.</p>
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tienda_action" value="agregar_producto">
            <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="form-field">
                    <label for="sku">SKU</label>
                    <input id="sku" name="sku" maxlength="40" pattern="[A-Za-z0-9._-]{2,40}" required>
                </div>
                <div class="form-field">
                    <label for="nombre">Nombre del producto</label>
                    <input id="nombre" name="nombre" maxlength="120" required>
                </div>
                <div class="form-field full">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" maxlength="255" rows="3" required></textarea>
                </div>
                <div class="form-field">
                    <label for="proveedor_id">Proveedor</label>
                    <select id="proveedor_id" name="proveedor_id" required>
                        <option value="">Selecciona un proveedor</option>
                        <?php foreach ($proveedoresCatalogo as $proveedor): ?>
                            <option value="<?= (int) $proveedor['id'] ?>"><?= htmlspecialchars($proveedor['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="precio">Precio (S/)</label>
                    <input id="precio" name="precio" type="number" min="0" step="0.01" required>
                </div>
                <div class="form-field">
                    <label for="stock">Stock inicial</label>
                    <input id="stock" name="stock" type="number" min="0" max="100000" step="1" required>
                </div>
                <div class="form-field">
                    <label for="imagen">Imagen (opcional, JPEG/PNG/WebP, máximo 3 MB)</label>
                    <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <button type="submit">Guardar producto en catálogo</button>
        </form>
    </section>

    <section class="panel">
        <h2>Pedidos del catálogo</h2>
        <?php if (!$listaPedidos): ?>
            <p>Aún no hay pedidos del catálogo.</p>
        <?php else: ?>
            <div class="table-wrap"><table class="tabla-pedido">
                <thead><tr><th>Número</th><th>Customer</th><th>Fecha</th><th>Artículos</th><th>Destino</th><th>Estado</th><th>Asignadas / pendientes</th><th>Total</th><th>Pago</th><th>Acción</th></tr></thead>
                <tbody>
                <?php foreach ($listaPedidos as $pedido): ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['numero'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($pedido['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($pedido['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($pedido['resumen_items'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($pedido['destino'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($pedido['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $pedido['unidades_asignadas'] ?> / <?= (int) $pedido['unidades_backorder'] ?></td>
                        <td>S/ <?= number_format((float) $pedido['total'], 2) ?></td>
                        <td><?= htmlspecialchars($pedido['estado_pago'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php
                                $estadosSiguientes = [
                                    'Procesando' => ['Confirmado', 'Cancelado'],
                                    'Pendiente de abastecimiento' => ['Cancelado'],
                                    'Confirmado' => ['Enviado', 'Cancelado'],
                                    'Enviado' => ['Entregado']
                                ];
                                $opcionesEstado = $estadosSiguientes[$pedido['estado']] ?? [];
                            ?>
                            <?php if ($opcionesEstado): ?>
                                <form class="fila-form" action="index.php" method="POST">
                                    <input type="hidden" name="tienda_action" value="cambiar_estado_pedido">
                                    <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                                    <select name="estado" required>
                                        <?php foreach ($opcionesEstado as $estado): ?>
                                            <option value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Actualizar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
    </section>
    </main>
    <footer class="site-footer">
        <div class="footer-inner">
            <section><h2>Ruta Moto</h2><p>Panel de gestión del catálogo, inventario y pedidos de demostración.</p></section>
            <section><h3>Herramientas</h3><ul><li><a href="index.php?pagina=admin_tienda">Catálogo e inventario</a></li><li><a href="index.php?pagina=ordenes_anteriores">Órdenes anteriores</a></li></ul></section>
            <section><h3>Soporte</h3><p><a href="mailto:soporte@rutamoto.example">soporte@rutamoto.example</a><br>Proyecto educativo · Pagos simulados</p></section>
        </div>
        <div class="footer-bottom">© <?= date('Y') ?> Ruta Moto · Administración</div>
    </footer>
</body>
</html>
