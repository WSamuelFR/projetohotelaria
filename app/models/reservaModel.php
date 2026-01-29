<?php
// Arquivo: app/models/reservaModel.php

require_once('../config/DBConnection.php');

class ReservaModel {
    private $conn;

    public function __construct() {
        // A conexão é feita pela função Connect() no DBConnection.php.
        $this->conn = Connect();
        if ($this->conn->connect_error) {
            throw new Exception("Falha na conexão com o banco de dados: " . $this->conn->connect_error);
        }
    }

    /**
     * Busca clientes (PF ou PJ) para o Datalist.
     */
    public function getClientesForDatalist(string $searchTerm = ''): array {
        $searchParam = "%" . $searchTerm . "%";
        $sql = "
            SELECT 
                full_name,
                cpf_cnpj
            FROM cadastro
            WHERE full_name LIKE ? OR cpf_cnpj LIKE ?
            ORDER BY full_name
        ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
        $stmt->close();
        return $clientes;
    }

    /**
     * Busca quartos para o Datalist.
     */
    public function getQuartosForDatalist(string $searchTerm = ''): array {
        $searchParam = "%" . $searchTerm . "%";
        $sql = "
            SELECT 
                numero,
                room_type,
                room_status,
                clean_status
            FROM quarto
            WHERE numero LIKE ? OR room_type LIKE ? OR room_status LIKE ?
            ORDER BY numero
        ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $quartos = [];
        while ($row = $result->fetch_assoc()) {
            $status = ($row['room_status'] == 'livre' && $row['clean_status'] == 'sujo') 
                        ? 'LIMPEZA' 
                        : strtoupper($row['room_status']);
            
            $quartos[] = [
                'numero' => $row['numero'],
                'tipo' => $row['room_type'],
                'status_display' => $status
            ];
        }
        $stmt->close();
        return $quartos;
    }

    /**
     * Insere uma nova reserva no banco de dados.
     * Ajustado para definir situação inicial como 'pendente'.
     */
    public function insertReserva(array $data): int|string {
        $this->conn->begin_transaction();
        
        try {
            // Adicionado campo 'situacao' com valor padrão 'pendente'
            $sql = "INSERT INTO reserva (cadastro, quarto, data_checkin, data_checkout, situacao) 
                    VALUES (?, ?, ?, ?, 'pendente')";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("siss",
                $data['cadastro'],
                $data['quarto'],
                $data['data_checkin'],
                $data['data_checkout']
            );

            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir reserva: " . $stmt->error);
            }
            $reservaId = $this->conn->insert_id;
            $stmt->close();

            $this->conn->commit();
            return $reservaId;

        } catch (Exception $e) {
            $this->conn->rollback();
            return "Falha na transação: " . $e->getMessage();
        }
    }

    /**
     * Busca os dados de uma reserva específica para edição.
     */
    public function getReservaById($id) {
        $sql = "SELECT r.*, c.full_name as nome_cliente 
                FROM reserva r 
                JOIN cadastro c ON r.cadastro = c.cpf_cnpj 
                WHERE r.reserva_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }
    
    /**
     * Atualiza os dados de uma reserva existente.
     */
    public function updateReserva($id, $quarto, $checkin, $checkout) {
        $sql = "UPDATE reserva SET quarto = ?, data_checkin = ?, data_checkout = ? WHERE reserva_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $quarto, $checkin, $checkout, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Cancela uma reserva (Exclusão Lógica).
     * Altera a situação para 'cancelado' em vez de deletar o registro.
     */
    public function cancelarReserva($id) {
        $sql = "UPDATE reserva SET situacao = 'cancelado' WHERE reserva_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function __destruct() {
        if (isset($this->conn) && $this->conn->ping()) {
             $this->conn->close();
        }
    }
}