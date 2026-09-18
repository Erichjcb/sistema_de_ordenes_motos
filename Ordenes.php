<?php
// 1. CLASE PADRE 
class OrdenCompra {
    
// .......
    protected $fecha;
    protected $num_orden;
    protected $producto;
    protected $proveedor;
    protected $cantidad;
    protected $precio;
    protected $num_cliente;
    protected $destino;

    // El constructor 
    public function __construct($fecha, $num_orden, $producto, $proveedor, $cantidad, $precio, $num_cliente, $destino) {
        $this->fecha = $fecha;
        $this->num_orden = $num_orden;
        $this->producto = $producto;
        $this->proveedor = $proveedor;
        $this->cantidad = $cantidad;
        $this->precio = $precio;
        $this->num_cliente = $num_cliente;
        $this->destino = $destino;
    }

    // Getters 
    public function getFecha() { return $this->fecha; }
    public function getNumOrden() { return $this->num_orden; }
    public function getProducto() { return $this->producto; }
    public function getProveedor() { return $this->proveedor; }
    public function getCantidad() { return $this->cantidad; }
    public function getPrecio() { return $this->precio; }
    public function getNumCliente() { return $this->num_cliente; }
    public function getDestino() { return $this->destino; }

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