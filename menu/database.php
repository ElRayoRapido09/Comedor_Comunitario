<?php
class Database {
    private $host = 'bdhfuadlo1hz1ht8dfxp-mysql.services.clever-cloud.com';
    private $db_name = 'bdhfuadlo1hz1ht8dfxp';
    private $username = 'uzcgof6xqfxne5xg';
    private $password = 'NcvzgSUbiBtmGEYnheBv';
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