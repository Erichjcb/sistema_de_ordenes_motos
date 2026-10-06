<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .volver { display: inline-block; margin-bottom: 20px; text-decoration: none; background: #6c757d; color: white; padding: 8px 15px; border-radius: 4px;}
        .menu-opciones { display: flex; gap: 20px; margin-bottom: 30px; }
        .btn-menu { padding: 15px 30px; font-size: 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold; }
        .btn-menu:hover { opacity: 0.8; }
        .btn-menu.activo { background-color: #0056b3; box-shadow: inset 0 3px 5px rgba(0,0,0,0.2); }
        
        .formulario { background: white; padding: 20px; border-radius: 8px; width: 400px; border: 1px solid #ccc; margin: 0 auto; }
        .buscador-caja { background: white; padding: 20px; border-radius: 8px; border: 1px solid #ccc; text-align: center; margin-bottom: 20px; }
        .buscador-caja input { width: 250px; padding: 10px; font-size: 16px; }
        .buscador-caja button { background-color: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-size: 16px; }
        .btn-borrar { background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-size: 16px; margin-left: 5px;}
        
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        .btn-guardar { background-color: #28a745; color: white; padding: 10px; border: none; width: 100%; cursor: pointer; border-radius: 4px; font-weight:bold;}
        .readonly-input { background-color: #e9ecef; cursor: not-allowed; font-weight: bold;}
        
        table { width: 100%; border-collapse: collapse; font-size: 14px; background: white;}
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn-eliminar { background-color: #dc3545; color: white; padding: 5px 8px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-editar { background-color: #ffc107; color: black; padding: 5px 8px; text-decoration: none; border-radius: 4px; font-size: 12px; margin-right: 5px; }
        .col-total { font-weight: bold; color: #28a745; }
        
        .alerta { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .alerta-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alerta-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Estilo para el botón PDF */
        .btn-imprimir { background-color: #17a2b8 !important; color: white !important; border-radius: 4px !important; border: none !important; font-weight: bold !important; margin-bottom: 15px !important; font-size: 14px !important;}
        .btn-imprimir:hover { background-color: #138496 !important; }
    </style>

    <!-- CSS de DataTables para los botones -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>

    <a href="index.php" class="volver">⬅ Cambiar de Perfil</a>
    <h2>👤 Portal del Cliente (Customer)</h2>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alerta alerta-<?= $_SESSION['tipo_mensaje'] ?>">
            <?= $_SESSION['mensaje'] ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- MENÚ DE OPCIONES -->
    <div class="menu-opciones">
        <a href="index.php?rol=cliente&vista=nueva" class="btn-menu <?= $vista_cliente == 'nueva' ? 'activo' : '' ?>">📝 Nueva Orden</a>
        <a href="index.php?rol=cliente&vista=seguimiento" class="btn-menu <?= $vista_cliente == 'seguimiento' ? 'activo' : '' ?>">🔍 Seguimiento</a>
    </div>

    <!-- PANTALLA: NUEVA ORDEN -->
    <?php if ($vista_cliente === 'nueva'): ?>
        <div class="formulario">
            <h3><?= $ordenEditar ? 'Editar Orden' : 'Formulario de Nueva Orden' ?></h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="rol" value="cliente">
                <input type="hidden" name="vista" value="nueva">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id" value="<?= $ordenEditar['id'] ?? '' ?>">

                <label>N° de Orden:</label>
                <input type="text" name="num_orden" class="readonly-input" value="<?= $numero_orden_mostrar ?>" readonly required>

                <label>Tipo de Envío:</label>
                <select name="tipo" required>
                    <option value="local" <?= (isset($ordenEditar) && $ordenEditar['tipo'] == 'local') ? 'selected' : '' ?>>Local</option>
                    <option value="nacional" <?= (isset($ordenEditar) && $ordenEditar['tipo'] == 'nacional') ? 'selected' : '' ?>>Nacional</option>
                </select>

                <label>Fecha:</label>
                <input type="date" name="fecha" value="<?= $ordenEditar['fecha'] ?? '' ?>" required>

                <label>Producto:</label>
                <select name="producto" required>
                    <option value="">Seleccione...</option>
                    <option value="Llanta delantera" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Llanta delantera') ? 'selected' : '' ?>>Llanta delantera</option>
                    <option value="Llanta trasera" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Llanta trasera') ? 'selected' : '' ?>>Llanta trasera</option>
                    <option value="Bujía NGK" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Bujía NGK') ? 'selected' : '' ?>>Bujía NGK</option>
                    <option value="Filtro de Aceite" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Filtro de Aceite') ? 'selected' : '' ?>>Filtro de Aceite</option>
                    <option value="Pastillas de Freno" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Pastillas de Freno') ? 'selected' : '' ?>>Pastillas de Freno</option>
                    <option value="Batería 12V" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Batería 12V') ? 'selected' : '' ?>>Batería 12V</option>
                    <option value="Kit de Arrastre" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Kit de Arrastre') ? 'selected' : '' ?>>Kit de Arrastre</option>
                    <option value="Aceite de Motor 4T" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Aceite de Motor 4T') ? 'selected' : '' ?>>Aceite de Motor 4T</option>
                    <option value="Cable de Embrague" <?= (isset($ordenEditar) && $ordenEditar['producto'] == 'Cable de Embrague') ? 'selected' : '' ?>>Cable de Embrague</option>
                </select>

                <label>Proveedor:</label>
                <select name="proveedor" required>
                    <option value="">Seleccione...</option>
                    <option value="MotoPartes S.A." <?= (isset($ordenEditar) && $ordenEditar['proveedor'] == 'MotoPartes S.A.') ? 'selected' : '' ?>>MotoPartes S.A.</option>
                    <option value="Repuestos El Motorista" <?= (isset($ordenEditar) && $ordenEditar['proveedor'] == 'Repuestos El Motorista') ? 'selected' : '' ?>>Repuestos El Motorista</option>
                    <option value="Importaciones TodoMoto" <?= (isset($ordenEditar) && $ordenEditar['proveedor'] == 'Importaciones TodoMoto') ? 'selected' : '' ?>>Importaciones TodoMoto</option>
                    <option value="Distribuidora Central" <?= (isset($ordenEditar) && $ordenEditar['proveedor'] == 'Distribuidora Central') ? 'selected' : '' ?>>Distribuidora Central</option>
                    <option value="Yamaha Oficial" <?= (isset($ordenEditar) && $ordenEditar['proveedor'] == 'Yamaha Oficial') ? 'selected' : '' ?>>Yamaha Oficial</option>
                    <option value="Honda Perú Repuestos" <?= (isset($ordenEditar) && $ordenEditar['proveedor'] == 'Honda Perú Repuestos') ? 'selected' : '' ?>>Honda Perú Repuestos</option>
                </select>

                <label>Cantidad:</label>
                <input type="number" name="cantidad" value="<?= $ordenEditar['cantidad'] ?? '1' ?>" required min="1">
                <label>Precio Unitario (S/):</label>
                <input type="number" step="0.01" name="precio" value="<?= $ordenEditar['precio'] ?? '' ?>" required min="0.1">
                <label>N° Cliente:</label>
                <input type="text" name="num_cliente" value="<?= $ordenEditar['num_cliente'] ?? '' ?>" required>

                <label>Destino:</label>
                <select name="destino" required>
                    <option value="">Seleccione...</option>
                    <option value="Lima" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Lima') ? 'selected' : '' ?>>Lima</option>
                    <option value="Oxapampa" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Oxapampa') ? 'selected' : '' ?>>Oxapampa</option>
                    <option value="Huancayo" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Huancayo') ? 'selected' : '' ?>>Huancayo</option>
                    <option value="Huancabamba" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Huancabamba') ? 'selected' : '' ?>>Huancabamba</option>
                    <option value="Arequipa" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Arequipa') ? 'selected' : '' ?>>Arequipa</option>
                    <option value="Cusco" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Cusco') ? 'selected' : '' ?>>Cusco</option>
                    <option value="Trujillo" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Trujillo') ? 'selected' : '' ?>>Trujillo</option>
                    <option value="Piura" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Piura') ? 'selected' : '' ?>>Piura</option>
                    <option value="Iquitos" <?= (isset($ordenEditar) && $ordenEditar['destino'] == 'Iquitos') ? 'selected' : '' ?>>Iquitos</option>
                </select>

                <button type="submit" class="btn-guardar"><?= $ordenEditar ? 'Actualizar Orden' : 'Guardar Orden' ?></button>
            </form>
        </div>
    <?php endif; ?>

    <!-- PANTALLA: SEGUIMIENTO -->
    <?php if ($vista_cliente === 'seguimiento'): ?>
        <div class="buscador-caja">
            <h3>Consultar Estado de Orden</h3>
            <form method="GET" action="index.php">
                <input type="hidden" name="rol" value="cliente">
                <input type="hidden" name="vista" value="seguimiento">
                <input type="text" name="buscar_orden" placeholder="Ingrese el N° de Orden (Ej: ORD-001)" required>
                <button type="submit">Buscar</button>
                <a href="index.php?rol=cliente&vista=seguimiento" class="btn-borrar">Borrar</a>
            </form>
        </div>

        <?php if ($ordenBuscada): ?>
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ccc; overflow-x: auto;">
            <h3>Detalles de la Orden Encontrada</h3>
            
            <!-- AGREGAMOS EL ID A LA TABLA -->
            <table id="tablaSeguimiento">
                <thead>
                    <tr>
                        <th>N° Orden</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Proveedor</th>
                        <th>Cant.</th>
                        <th>Destino</th>
                        <th>Total</th>
                        <th>Estado Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?= $ordenBuscada['num_orden'] ?></strong></td>
                        <td><?= $ordenBuscada['fecha'] ?></td>
                        <td><?= $ordenBuscada['producto'] ?></td>
                        <td><?= $ordenBuscada['proveedor'] ?></td>
                        <td><?= $ordenBuscada['cantidad'] ?></td>
                        <td><?= $ordenBuscada['destino'] ?></td>
                        
                        <?php $total = ($ordenBuscada['cantidad'] * $ordenBuscada['precio']) + $ordenBuscada['costo_envio']; ?>
                        <td class="col-total">S/ <?= number_format($total, 2) ?></td>
                        
                        <td style="color: #007bff; font-weight: bold; font-size: 16px;"><?= $ordenBuscada['estado_orden'] ?></td>
                        
                        <td>
                            <a href="index.php?editar=<?= $ordenBuscada['id'] ?>&rol=cliente&vista=nueva" class="btn-editar">Editar</a>
                            <a href="index.php?eliminar=<?= $ordenBuscada['id'] ?>&rol=cliente&vista=seguimiento" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar?');">Eliminar</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- SCRIPTS PARA GENERAR EL TICKET EN PDF                        -->
    <!-- ============================================================ -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Verificamos que la tabla exista (es decir, que se haya buscado una orden)
            if ($('#tablaSeguimiento').length) {
                $('#tablaSeguimiento').DataTable({
                    dom: 'B', // La "B" significa que SOLO muestre los Botones (sin buscador ni paginación)
                    paging: false,
                    searching: false,
                    info: false,
                    ordering: false, // Desactiva las flechitas de ordenar porque solo hay 1 fila
                    buttons: [
                        {
                            extend: 'pdfHtml5',
                            text: '📄 Descargar Ticket PDF',
                            className: 'btn-imprimir',
                            title: 'Ticket de Orden: <?php echo isset($ordenBuscada) ? $ordenBuscada["num_orden"] : ""; ?>',
                            orientation: 'landscape',
                            exportOptions: {
                                // MÁGIA: Imprime las columnas de la 0 a la 7, ignorando la 8 (Acciones)
                                columns: [0, 1, 2, 3, 4, 5, 6, 7] 
                            }
                        }
                    ]
                });
            }
        });
    </script>

</body>
</html>