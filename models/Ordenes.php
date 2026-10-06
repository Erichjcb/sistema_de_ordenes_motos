<?php
// 1. CLASE PADRE 
class OrdenCompra {
    
    protected $fecha;
    protected $num_orden;
    protected $producto;
    protected $proveedor;
    protected $cantidad;
    protected $precio;
    protected $num_cliente;
    protected $destino;
    
    // NUEVOS CAMPOS AÑADIDOS
    protected $estado_orden;
    protected $solicitud_cliente;

    // Constructor actualizado con los nuevos campos (con valores por defecto)
    public function __construct($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino, $estado_orden = 'Procesando', $solicitud_cliente = '') {
        $this->fecha = $fecha;
        $this->num_orden = $num_orden;
        $this->producto = $producto;
        $this->proveedor = $proveedor;
        $this->cantidad = $cantidad;
        $this->precio = $precio;
        $this->num_cliente = $num_cliente;
        $this->destino = $destino;
        
        $this->estado_orden = $estado_orden;
        $this->solicitud_cliente = $solicitud_cliente;
    }

    // Getters originales
    public function getFecha() { return $this->fecha; }
    public function getNumOrden() { return $this->num_orden; }
    public function getProducto() { return $this->producto; }
    public function getProveedor() { return $this->proveedor; }
    public function getCantidad() { return $this->cantidad; }
    public function getPrecio() { return $this->precio; }
    public function getNumCliente() { return $this->num_cliente; }
    public function getDestino() { return $this->destino; }
    
    // Nuevos Getters
    public function getEstadoOrden() { return $this->estado_orden; }
    public function getSolicitudCliente() { return $this->solicitud_cliente; }

    // Método
    public function calcularCostoEnvio() {
        return 0; 
    }
}

// 2. CLASE HIJA
class OrdenLocal extends OrdenCompra {
    // Polimorfismo
    public function calcularCostoEnvio() {
        return 10.00;
    }
}

// 3. CLASE HIJA
class OrdenNacional extends OrdenCompra {
    // Polimorfismo
    public function calcularCostoEnvio() {
        return 50.00;
    }
}
?>