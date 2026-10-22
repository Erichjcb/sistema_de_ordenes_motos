<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Proveedor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .volver { display: inline-block; margin-bottom: 20px; text-decoration: none; background: #6c757d; color: white; padding: 8px 15px; border-radius: 4px;}
        table { width: 100%; border-collapse: collapse; font-size: 14px; background: white;}
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #28a745; color: white; }
        .col-total { font-weight: bold; color: #28a745; }
        .select-estado { padding: 5px; border-radius: 4px; font-weight: bold; cursor: pointer;}
        
        .alerta { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .alerta-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        /* Estilos para los botones de exportar de DataTables */
        .btn-exportar { background-color: #007bff !important; color: white !important; border-radius: 4px !important; border: none !important; font-weight: bold !important; margin-bottom: 15px !important; }
        .btn-exportar:hover { background-color: #0056b3 !important; }

        /* ================= NUEVOS ESTILOS PARA EL PANEL DE FILTROS ================= */
        .panel-filtros { background-color: #e9ecef; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ced4da; }
        .panel-filtros h4 { margin-top: 0; margin-bottom: 15px; color: #495057; font-size: 16px; }
        .filtros-grid { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
        .filtro-grupo { display: flex; flex-direction: column; }
        .filtro-grupo label { font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #495057; }
        .filtro-grupo select, .filtro-grupo input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; width: 160px;}
        .btn-limpiar { background-color: #6c757d; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-limpiar:hover { background-color: #5a6268; }
    </style>

    <!-- CSS de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>

    <form action="index.php" method="POST" style="float: right;">
        <input type="hidden" name="auth_action" value="logout">
        <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Cerrar sesión (<?= htmlspecialchars($usuarioActual['usuario'], ENT_QUOTES, 'UTF-8') ?>)</button>
    </form>
    <h2>📦 Panel Administrativo - Proveedor (Supplier)</h2>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alerta alerta-<?= $_SESSION['tipo_mensaje'] ?>">
            <?= $_SESSION['mensaje'] ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ccc; overflow-x: auto;">
        <h3>Todas las órdenes del sistema</h3>

        <!-- ================= PANEL DE FILTROS ================= -->
        <div class="panel-filtros">
            <h4>Filtros de Reporte (Aplica automáticamente)</h4>
            <div class="filtros-grid">
                <div class="filtro-grupo">
                    <label>Desde (Fecha):</label>
                    <input type="date" id="filtro_desde">
                </div>
                <div class="filtro-grupo">
                    <label>Hasta (Fecha):</label>
                    <input type="date" id="filtro_hasta">
                </div>
                <!-- AGREGADO: FILTRO DE PRODUCTO -->
                <div class="filtro-grupo">
                    <label>Producto:</label>
                    <select id="filtro_producto">
                        <option value="">-- Todos --</option>
                        <?php 
                            $productos = array_unique(array_column($listaOrdenes, 'producto'));
                            sort($productos);
                            foreach($productos as $prod) {
                                echo "<option value=\"$prod\">$prod</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="filtro-grupo">
                    <label>Proveedor:</label>
                    <select id="filtro_proveedor">
                        <option value="">-- Todos --</option>
                        <?php 
                            $proveedores = array_unique(array_column($listaOrdenes, 'proveedor'));
                            sort($proveedores);
                            foreach($proveedores as $prov) {
                                echo "<option value=\"$prov\">$prov</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="filtro-grupo">
                    <label>Destino:</label>
                    <select id="filtro_destino">
                        <option value="">-- Todos --</option>
                        <?php 
                            $destinos = array_unique(array_column($listaOrdenes, 'destino'));
                            sort($destinos);
                            foreach($destinos as $dest) {
                                echo "<option value=\"$dest\">$dest</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="filtro-grupo">
                    <label>Estado:</label>
                    <select id="filtro_estado">
                        <option value="">-- Todos --</option>
                        <option value="Procesando">Procesando</option>
                        <option value="Confirmado">Confirmado</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="filtro-grupo">
                    <button id="btn_limpiar_filtros" class="btn-limpiar">Limpiar Filtros</button>
                </div>
            </div>
        </div>
        <!-- ================= FIN PANEL DE FILTROS ================= -->
        
        <table id="tablaOrdenes">
            <thead>
                <tr>
                    <th>N° Orden</th>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Proveedor</th>
                    <th>Cant.</th>
                    <th>Destino</th>
                    <th>Total Pagado</th>
                    <th>Estado (Cambiar)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaOrdenes as $orden): ?>
                <tr>
                    <td><strong><?= $orden['num_orden'] ?></strong></td>
                    <td><?= $orden['fecha'] ?></td>
                    <td><?= $orden['producto'] ?></td>
                    <td><?= $orden['proveedor'] ?></td>
                    <td><?= $orden['cantidad'] ?></td>
                    <td><?= $orden['destino'] ?></td>
                    
                    <?php $total = ($orden['cantidad'] * $orden['precio']) + $orden['costo_envio']; ?>
                    <td class="col-total">S/ <?= number_format($total, 2) ?></td>
                    
                    <td>
                        <form action="index.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="rol" value="proveedor">
                            <input type="hidden" name="accion" value="cambiar_estado">
                            <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_orden" value="<?= $orden['id'] ?>">
                            
                            <select name="nuevo_estado" class="select-estado" onchange="this.form.submit()"
                                <?php
                                    if($orden['estado_orden'] == 'Procesando') echo 'style="color: #856404; background: #fff3cd;"';
                                    elseif($orden['estado_orden'] == 'Confirmado') echo 'style="color: #004085; background: #cce5ff;"';
                                    elseif($orden['estado_orden'] == 'Enviado') echo 'style="color: #155724; background: #d4edda;"';
                                    elseif($orden['estado_orden'] == 'Cancelado') echo 'style="color: #721c24; background: #f8d7da;"';
                                ?>>
                                <option value="Procesando" <?= $orden['estado_orden'] == 'Procesando' ? 'selected' : '' ?>>Procesando</option>
                                <option value="Confirmado" <?= $orden['estado_orden'] == 'Confirmado' ? 'selected' : '' ?>>Confirmado</option>
                                <option value="Enviado" <?= $orden['estado_orden'] == 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                                <option value="Cancelado" <?= $orden['estado_orden'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============================================================ -->
    <!-- LIBRERÍAS DE DATATABLES Y EXPORTACIÓN                        -->
    <!-- ============================================================ -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <!-- ARCHIVO CENTRALIZADO -->
    <script src="assets/js/exportador.js"></script>

    <!-- INICIALIZACIÓN Y LÓGICA DE LOS FILTROS -->
    <script>
        $(document).ready(function() {
            // 1. Cargamos la tabla usando nuestro archivo centralizado
            let tabla = activarExportacion('#tablaOrdenes', 'Reporte de Órdenes - Proveedor', [7], true);

            if (tabla) {
                // 2. Creamos la regla de filtrado personalizado
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        // Obtenemos lo que el usuario seleccionó en el panel
                        let desde = $('#filtro_desde').val();
                        let hasta = $('#filtro_hasta').val();
                        let producto = $('#filtro_producto').val().toLowerCase(); // AGREGADO
                        let proveedor = $('#filtro_proveedor').val().toLowerCase();
                        let destino = $('#filtro_destino').val().toLowerCase();
                        let estado = $('#filtro_estado').val().toLowerCase();

                        // Obtenemos los textos de la fila actual (El índice corresponde a la columna, ej: data[2] es Producto)
                        let rowFecha = data[1]; 
                        let rowProducto = data[2].toLowerCase(); // AGREGADO
                        let rowProveedor = data[3].toLowerCase(); 
                        let rowDestino = data[5].toLowerCase(); 
                        
                        let rowEstado = $(settings.aoData[dataIndex].nTr).find('.select-estado').val().toLowerCase();

                        // Validaciones lógicas
                        if (desde && rowFecha < desde) return false;
                        if (hasta && rowFecha > hasta) return false;
                        if (producto && rowProducto !== producto) return false; // AGREGADO
                        if (proveedor && rowProveedor !== proveedor) return false;
                        if (destino && rowDestino !== destino) return false;
                        if (estado && rowEstado !== estado) return false;

                        return true; 
                    }
                );

                // 3. Activador Automático (Actualizado para incluir filtro_producto)
                $('#filtro_desde, #filtro_hasta, #filtro_producto, #filtro_proveedor, #filtro_destino, #filtro_estado').on('change', function() {
                    tabla.draw();
                });

                // 4. Botón Limpiar (Actualizado para incluir filtro_producto)
                $('#btn_limpiar_filtros').on('click', function() {
                    $('#filtro_desde, #filtro_hasta, #filtro_producto, #filtro_proveedor, #filtro_destino, #filtro_estado').val('');
                    tabla.draw();
                });
            }
        });
    </script>
</body>
</html>