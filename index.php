<?php
require_once 'Ordenes.php';
require_once 'OrdenDAO.php';

$dao = new OrdenDAO();

// 1. GUARDAR O ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id']; 
    $tipo        = $_POST['tipo'];
    $fecha       = $_POST['fecha'];
    $num_orden   = $_POST['num_orden'];
    $producto    = $_POST['producto'];
    $proveedor   = $_POST['proveedor'];
    $cantidad    = $_POST['cantidad'];
    $precio      = $_POST['precio'];
    $num_cliente = $_POST['num_cliente'];
    $destino     = $_POST['destino'];

    if ($tipo === 'local') {
        $ordenObj = new OrdenLocal($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino);
    } else {
        $ordenObj = new OrdenNacional($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino);
    }

    if (!empty($id)) {
        $dao->actualizar($id, $ordenObj, $tipo);
    } else {
        $dao->registrar($ordenObj, $tipo);
    }
    
    header("Location: index.php");
    exit;
}

// 2. ELIMINAR
if (isset($_GET['eliminar'])) {
    $dao->eliminar($_GET['eliminar']);
    header("Location: index.php");
    exit;
}

// 3. OBTENER DATOS PARA EDITAR O CREAR NUEVO
$ordenEditar = null; 
$numero_orden_mostrar = "";

if (isset($_GET['editar'])) {
    $ordenEditar = $dao->obtenerPorId($_GET['editar']);
    $numero_orden_mostrar = $ordenEditar['num_orden']; 
} else {
    $numero_orden_mostrar = $dao->obtenerSiguienteNumeroOrden(); 
}

// 4. LISTAR
$listaOrdenes = $dao->listar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Repuestos de Motos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .contenedor { display: flex; gap: 20px; }
        .formulario { background: white; padding: 20px; border-radius: 8px; width: 300px; border: 1px solid #ccc; }
        .tabla-datos { flex-grow: 1; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ccc; overflow-x: auto; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 10px; border: none; width: 100%; cursor: pointer; border-radius: 4px;}
        button:hover { background-color: #218838; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn-eliminar { background-color: #dc3545; color: white; padding: 5px 8px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-editar { background-color: #ffc107; color: black; padding: 5px 8px; text-decoration: none; border-radius: 4px; font-size: 12px; margin-right: 5px; }
        .readonly-input { background-color: #e9ecef; cursor: not-allowed; font-weight: bold;}
        .col-total { font-weight: bold; color: #28a745; }
    </style>
</head>
<body>

    <h2>🏍️ Sistema de Órdenes - Repuestos de Motos</h2>

    <div class="contenedor">
        
        <div class="formulario">
            <h3><?= $ordenEditar ? 'Editar Orden' : 'Nueva Orden' ?></h3>
            
            <form action="index.php" method="POST">
                <input type="hidden" name="id" value="<?= $ordenEditar['id'] ?? '' ?>">

                <label>N° de Orden (Automático):</label>
                <input type="text" name="num_orden" class="readonly-input" value="<?= $numero_orden_mostrar ?>" readonly required>

                <label>Tipo de Envío:</label>
                <select name="tipo" required>
                    <option value="local" <?= (isset($ordenEditar) && $ordenEditar['tipo'] == 'local') ? 'selected' : '' ?>>Local</option>
                    <option value="nacional" <?= (isset($ordenEditar) && $ordenEditar['tipo'] == 'nacional') ? 'selected' : '' ?>>Nacional</option>
                </select>

                <label>Fecha:</label>
                <input type="date" name="fecha" value="<?= $ordenEditar['fecha'] ?? '' ?>" required>

                <label>Producto (Repuesto):</label>
                <!-- LISTA AMPLIADA DE REPUESTOS -->
                <select name="producto" required>
                    <option value="">Seleccione un repuesto...</option>
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
                <!-- NUEVA LISTA DESPLEGABLE DE PROVEEDORES -->
                <select name="proveedor" required>
                    <option value="">Seleccione un proveedor...</option>
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
                <input type="text" name="num_cliente" value="<?= $ordenEditar['num_cliente'] ?? '' ?>" placeholder="Ej: CLI-001" required>

                <label>Destino (Ciudad):</label>
                <!-- LISTA AMPLIADA DE CIUDADES -->
                <select name="destino" required>
                    <option value="">Seleccione ciudad...</option>
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

                <button type="submit"><?= $ordenEditar ? 'Actualizar Orden' : 'Guardar Orden' ?></button>
                
                <?php if($ordenEditar): ?>
                    <a href="index.php" style="display:block; text-align:center; margin-top:10px; color:gray;">Cancelar edición</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="tabla-datos">
            <h3>Lista de Órdenes</h3>
            <table>
                <thead>
                    <tr>
                        <th>N° Orden</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Proveedor</th>
                        <th>Cant.</th>
                        <th>Precio</th>
                        <th>Envío</th>
                        <th>Destino</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listaOrdenes as $orden): ?>
                    <tr>
                        <td><?= $orden['num_orden'] ?></td>
                        <td><?= $orden['fecha'] ?></td>
                        <td><?= $orden['producto'] ?></td>
                        <td><?= $orden['proveedor'] ?></td> <!-- Mostramos el proveedor en la tabla también -->
                        <td><?= $orden['cantidad'] ?></td>
                        <td>S/ <?= $orden['precio'] ?></td>
                        <td>S/ <?= $orden['costo_envio'] ?></td>
                        <td><?= $orden['destino'] ?></td>
                        
                        <?php $total = ($orden['cantidad'] * $orden['precio']) + $orden['costo_envio']; ?>
                        <td class="col-total">S/ <?= number_format($total, 2) ?></td>
                        
                        <td>
                            <a href="index.php?editar=<?= $orden['id'] ?>" class="btn-editar">Editar</a>
                            <a href="index.php?eliminar=<?= $orden['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar esta orden?');">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>