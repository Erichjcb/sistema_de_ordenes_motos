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

        /* Estilos extra para los botones de exportar */
        .btn-exportar { background-color: #007bff !important; color: white !important; border-radius: 4px !important; border: none !important; font-weight: bold !important; margin-bottom: 15px !important; }
        .btn-exportar:hover { background-color: #0056b3 !important; }
    </style>

    <!-- CSS de DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>

    <a href="index.php" class="volver">⬅ Cambiar de Perfil</a>
    <h2>📦 Panel Administrativo - Proveedor (Supplier)</h2>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alerta alerta-<?= $_SESSION['tipo_mensaje'] ?>">
            <?= $_SESSION['mensaje'] ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ccc; overflow-x: auto;">
        <h3>Todas las órdenes del sistema</h3>
        
        <!-- AQUÍ SE AGREGÓ EL ID "tablaOrdenes" -->
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
                        <!-- Formulario incrustado para cambiar el estado con un solo clic -->
                        <form action="index.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="rol" value="proveedor">
                            <input type="hidden" name="accion" value="cambiar_estado">
                            <input type="hidden" name="id_orden" value="<?= $orden['id'] ?>">
                            
                            <select name="nuevo_estado" class="select-estado" onchange="this.form.submit()"
                                <?php
                                    // Colorear el select según el estado
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
    <!-- SCRIPTS DE JAVASCRIPT PARA EXPORTAR A EXCEL Y PDF            -->
    <!-- ============================================================ -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <!-- Scripts para los Botones -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> <!-- Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script> <!-- PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <!-- Inicializar la tabla mágica -->
    <script>
        $(document).ready(function() {
            $('#tablaOrdenes').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '📊 Exportar a Excel',
                        className: 'btn-exportar',
                        title: 'Reporte de Órdenes - Repuestos de Motos'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '📄 Exportar a PDF',
                        className: 'btn-exportar',
                        title: 'Reporte de Órdenes - Repuestos de Motos',
                        orientation: 'landscape', // Horizontal para que todo encaje
                        pageSize: 'A4'
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' // Lo pone en español
                }
            });
        });
    </script>

</body>
</html>