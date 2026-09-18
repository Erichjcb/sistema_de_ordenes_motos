    <?php
// << Archivos
require_once 'Conexion.php';
require_once 'Ordenes.php';

echo "<h2>Pruebas del Sistema de Órdenes</h2>";

// PRUEBA 1: Verificar la Conexión a la Base de Datos
echo "<h3>1. Prueba de Conexión:</h3>";
$conexion = new Conexion();
if ($conexion->conectar()) {
    echo "<p style='color:green;'>✅ ¡Conexión exitosa a la base de datos 'sistema_ordenes'!</p>";
} else {
    echo "<p style='color:red;'>❌ Error de conexión.</p>";
}

// PRUEBA 2: Verificar Clases y Polimorfismo
echo "<h3>2. Prueba de Clases y Polimorfismo:</h3>";

// Creamos una orden local
$orden1 = new OrdenLocal("2026-09-15", "ORD-001", "Bujía", "Repuestos XYZ", 2, 15.50, "CLI-01", "Lima");
echo "<p>El costo de envío para la orden Local es: <b>S/ " . $orden1->calcularCostoEnvio() . "</b></p>";

// Creamos una orden nacional
$orden2 = new OrdenNacional("2026-09-15", "ORD-002", "Llanta Trasera", "MotoLlantas", 1, 120.00, "CLI-02", "Arequipa");
echo "<p>El costo de envío para la orden Nacional es: <b>S/ " . $orden2->calcularCostoEnvio() . "</b></p>";
?>