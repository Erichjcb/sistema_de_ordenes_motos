<?php
require_once 'Conexion.php';
require_once 'Ordenes.php';

// Heredamos de Conexion para poder usar la base de datos
class OrdenDAO extends Conexion {
    
    // 1. GUARDAR (Create)
    public function registrar($orden, $tipo) {
        $conexion = $this->conectar();
        
        // Preparamos la instrucción SQL (Los signos '?' son por seguridad)
        $sql = "INSERT INTO ordenes (tipo, fecha, num_orden, producto, proveedor, cantidad, precio, num_cliente, destino, costo_envio) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        
        // Ejecutamos reemplazando los '?' con los datos de nuestro objeto
        return $stmt->execute([
            $tipo,
            $orden->getFecha(),
            $orden->getNumOrden(),
            $orden->getProducto(),
            $orden->getProveedor(),
            $orden->getCantidad(),
            $orden->getPrecio(),
            $orden->getNumCliente(),
            $orden->getDestino(),
            $orden->calcularCostoEnvio() // ¡Aquí se ejecuta tu Polimorfismo!
        ]);
    }

    // 2. LISTAR (Read)
    public function listar() {
        $conexion = $this->conectar();
        // Seleccionamos todo y lo ordenamos del más nuevo al más viejo
        $sql = "SELECT * FROM ordenes ORDER BY id DESC";
        $stmt = $conexion->query($sql);
        
        // Devolvemos todos los registros en forma de arreglo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. ELIMINAR (Delete)
    public function eliminar($id) {
        $conexion = $this->conectar();
        $sql = "DELETE FROM ordenes WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>