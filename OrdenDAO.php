<?php
require_once 'Conexion.php';
require_once 'Ordenes.php';

class OrdenDAO extends Conexion {
    
    // 1. GUARDAR (Create)
    public function registrar($orden, $tipo) {
        $conexion = $this->conectar();
        $sql = "INSERT INTO ordenes (tipo, fecha, num_orden, producto, proveedor, cantidad, precio, num_cliente, destino, costo_envio) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            $tipo, $orden->getFecha(), $orden->getNumOrden(), $orden->getProducto(), 
            $orden->getProveedor(), $orden->getCantidad(), $orden->getPrecio(), 
            $orden->getNumCliente(), $orden->getDestino(), $orden->calcularCostoEnvio()
        ]);
    }

    // 2. LISTAR (Read)
    public function listar() {
        $conexion = $this->conectar();
        $sql = "SELECT * FROM ordenes ORDER BY id DESC";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. ELIMINAR (Delete)
    public function eliminar($id) {
        $conexion = $this->conectar();
        $sql = "DELETE FROM ordenes WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$id]);
    }

    // 4. BUSCAR UNA ORDEN (Para llenar el formulario)
    public function obtenerPorId($id) {
        $conexion = $this->conectar();
        $sql = "SELECT * FROM ordenes WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }

    // 5. ACTUALIZAR (Update)
    public function actualizar($id, $orden, $tipo) {
        $conexion = $this->conectar();
        $sql = "UPDATE ordenes SET tipo=?, fecha=?, num_orden=?, producto=?, proveedor=?, cantidad=?, precio=?, num_cliente=?, destino=?, costo_envio=? WHERE id=?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            $tipo, $orden->getFecha(), $orden->getNumOrden(), $orden->getProducto(), 
            $orden->getProveedor(), $orden->getCantidad(), $orden->getPrecio(), 
            $orden->getNumCliente(), $orden->getDestino(), $orden->calcularCostoEnvio(),
            $id
        ]);
    }

    // =======================================================
    // NUEVO: 6. GENERADOR AUTOMÁTICO DE N° DE ORDEN
    // =======================================================
    public function obtenerSiguienteNumeroOrden() {
        $conexion = $this->conectar();
        // Preguntamos cuál es el ID más alto registrado
        $sql = "SELECT MAX(id) as max_id FROM ordenes";
        $stmt = $conexion->query($sql);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no hay nada, empieza en 0. Le sumamos 1.
        $siguiente = ($resultado['max_id'] ?? 0) + 1;
        
        // str_pad le pone ceros a la izquierda (Ej: 001, 002)
        return "ORD-" . str_pad($siguiente, 3, "0", STR_PAD_LEFT);
    }
}
?>