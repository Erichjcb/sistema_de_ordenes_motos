<?php
require_once 'Conexion.php';
require_once 'Ordenes.php';

class OrdenDAO extends Conexion {
    
    // 1. GUARDAR (Create)
    public function registrar($orden, $tipo, $customerId) {
        $conexion = $this->conectar();
        $sql = "INSERT INTO ordenes (tipo, fecha, num_orden, producto, proveedor, cantidad, precio, num_cliente, destino, costo_envio, estado_orden, solicitud_cliente, customer_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            $tipo, $orden->getFecha(), $orden->getNumOrden(), $orden->getProducto(), 
            $orden->getProveedor(), $orden->getCantidad(), $orden->getPrecio(), 
            $orden->getNumCliente(), $orden->getDestino(), $orden->calcularCostoEnvio(),
            $orden->getEstadoOrden(), $orden->getSolicitudCliente(), $customerId
        ]);
    }

    // 2. LISTAR (Read)
    public function listar($customerId = null) {
        $conexion = $this->conectar();
        if ($customerId === null) {
            $stmt = $conexion->query("SELECT * FROM ordenes ORDER BY id DESC");
        } else {
            $stmt = $conexion->prepare("SELECT * FROM ordenes WHERE customer_id = ? ORDER BY id DESC");
            $stmt->execute([$customerId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. ELIMINAR (Delete)
    public function eliminar($id, $customerId = null) {
        $conexion = $this->conectar();
        $sql = "DELETE FROM ordenes WHERE id = ? AND estado_orden = 'Procesando'";
        $parametros = [$id];
        if ($customerId !== null) {
            $sql .= " AND customer_id = ?";
            $parametros[] = $customerId;
        }
        $stmt = $conexion->prepare($sql);
        return $stmt->execute($parametros) && $stmt->rowCount() === 1;
    }

    // 4. BUSCAR UNA ORDEN POR ID (Para llenar el formulario)
    public function obtenerPorId($id, $customerId = null) {
        $conexion = $this->conectar();
        $sql = "SELECT * FROM ordenes WHERE id = ?";
        $parametros = [$id];
        if ($customerId !== null) {
            $sql .= " AND customer_id = ?";
            $parametros[] = $customerId;
        }
        $stmt = $conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }

    // 5. ACTUALIZAR TODO (Update)
    public function actualizar($id, $orden, $tipo, $customerId) {
        $conexion = $this->conectar();
        $sql = "UPDATE ordenes SET tipo=?, fecha=?, num_orden=?, producto=?, proveedor=?, cantidad=?, precio=?, num_cliente=?, destino=?, costo_envio=?, estado_orden=?, solicitud_cliente=? WHERE id=? AND customer_id=? AND estado_orden='Procesando'";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            $tipo, $orden->getFecha(), $orden->getNumOrden(), $orden->getProducto(), 
            $orden->getProveedor(), $orden->getCantidad(), $orden->getPrecio(), 
            $orden->getNumCliente(), $orden->getDestino(), $orden->calcularCostoEnvio(),
            $orden->getEstadoOrden(), $orden->getSolicitudCliente(),
            $id, $customerId
        ]);
    }

    // 6. GENERADOR AUTOMÁTICO DE N° DE ORDEN
    public function obtenerSiguienteNumeroOrden() {
        $conexion = $this->conectar();
        $sql = "SELECT MAX(id) as max_id FROM ordenes";
        $stmt = $conexion->query($sql);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $siguiente = ($resultado['max_id'] ?? 0) + 1;
        return "ORD-" . str_pad($siguiente, 3, "0", STR_PAD_LEFT);
    }

    // ================= NUEVO PARA ESTA FASE ================= //

    // 7. BUSCAR POR NUMERO DE ORDEN (Para el Seguimiento del Cliente)
    public function buscarPorNumOrden($num_orden, $customerId = null) {
        $conexion = $this->conectar();
        $sql = "SELECT * FROM ordenes WHERE num_orden = ?";
        $parametros = [$num_orden];
        if ($customerId !== null) {
            $sql .= " AND customer_id = ?";
            $parametros[] = $customerId;
        }
        $stmt = $conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 8. ACTUALIZAR ESTADO (Para el menú desplegable del Proveedor)
    public function actualizarEstado($id, $estado) {
        $conexion = $this->conectar();
        $sql = "UPDATE ordenes SET estado_orden = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$estado, $id]);
    }
}
?>