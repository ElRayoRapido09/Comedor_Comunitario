<?php
class Database {
    private $host = 'ep-dry-bonus-aaew9wqb-pooler.westus3.azure.neon.tech';
    private $db_name = 'neondb';
    private $username = 'neondb_owner';
    private $password = 'npg_XimlV9vFBoN6';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>