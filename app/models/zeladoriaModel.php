<?php
// Arquivo: app/models/ZeladoriaModel.php
require_once(__DIR__ . '/../config/DBConnection.php');

class zeladoriaModel {
    private $conn;

    public function __construct() {
        $this->conn = Connect();
    }

    /**
     * Lista todos os quartos que estão SUJOS ou em LIMPEZA
     */
    public function listarQuartosParaLimpeza() {
        $sql = "SELECT quarto_id, numero, room_type, clean_status, room_status 
                FROM quarto 
                WHERE clean_status = 'sujo' 
                ORDER BY numero ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}