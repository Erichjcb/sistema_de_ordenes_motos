<?php
// 1. CLASE PADRE (Aquí aplicamos Abstracción y Encapsulamiento)
class OrdenCompra {
    // Usamos 'protected' para que las clases hijas puedan usar estas variables (Encapsulamiento)
    protected $fecha;
    protected $num_orden;
    protected $producto;
    protected $proveedor;
    protected $cantidad;
    protected $precio;
    protected $num_cliente;
    protected $destino;

    // El constructor arma el objeto cuando lo creamos
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

    // Getters muy básicos para poder leer los datos protegidos
    public function getFecha() { return $this->fecha; }
    public function getNumOrden() { return $this->num_orden; }
    public function getProducto() { return $this->producto; }
    public function getProveedor() { return $this->proveedor; }
    public function getCantidad() { return $this->cantidad; }
    public function getPrecio() { return $this->precio; }
    public function getNumCliente() { return $this->num_cliente; }
    public function getDestino() { return $this->destino; }

    // Este es el método base que cambiará gracias al Polimorfismo
    public function calcularCostoEnvio() {
        return 0; 
    }
}

// 2. CLASE HIJA: ORDEN LOCAL (Aquí aplicamos Herencia)
class OrdenLocal extends OrdenCompra {
    
    // Polimorfismo: Una orden local tiene envío estándar o gratis (ej. S/ 10.00)
    public function calcularCostoEnvio() {
        return 10.00;
    }
}

// 3. CLASE HIJA: ORDEN NACIONAL (Aquí aplicamos Herencia)
class OrdenNacional extends OrdenCompra {
    
    // Polimorfismo: Una orden nacional tiene un envío más caro por la distancia (ej. S/ 50.00)
    public function calcularCostoEnvio() {
        return 50.00;
    }
}
?>