<?php
class Conexion {
    private $host = "localhost";
    private $db = "sistema_ordenes"; 
    private $user = "root";          
    private $pass = "";              
    protected $pdo = null;

    public function conectar() {
        if ($this->pdo === null) {
            try {
                // Preparamos la conexión
                $ruta = "mysql:host=" . $this->host . ";port=3307;dbname=" . $this->db . ";charset=utf8";
                // Conexión PDO
                $this->pdo = new PDO($ruta, $this->user, $this->pass);
                // Manejo de errores
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Error al conectar: " . $e->getMessage();
            }
        }
        return $this->pdo;
    }
}
?>