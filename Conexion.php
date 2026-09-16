<?php
class Conexion {
    private $host = "localhost";
    private $db = "sistema_ordenes"; // El nombre de tu base de datos
    private $user = "root";          // Usuario por defecto en XAMPP
    private $pass = "";              // Contraseña por defecto (vacía)
    protected $pdo = null;

    public function conectar() {
        if ($this->pdo === null) {
            try {
                // Preparamos la conexión
                $ruta = "mysql:host=" . $this->host . ";port=3307;dbname=" . $this->db . ";charset=utf8";
                // Creamos la conexión PDO
                $this->pdo = new PDO($ruta, $this->user, $this->pass);
                // Le decimos que nos muestre los errores si algo falla
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Error al conectar: " . $e->getMessage();
            }
        }
        return $this->pdo;
    }
}
?>