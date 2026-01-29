<?php
// Arquivo: app/models/quartoModel.php

require_once('../config/DBConnection.php');

class QuartoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Connect();
        if ($this->conn->connect_error) {
            throw new Exception("Falha na conexão com o banco de dados: " . $this->conn->connect_error);
        }
    }

    /**
     * NOVA FUNÇÃO: Atualiza o status de limpeza de um quarto para 'limpo'.
     * @param int $quartoId
     * @return bool
     */
    public function marcarComoLimpo(int $quartoId): bool
    {
        $sql = "UPDATE quarto SET clean_status = 'limpo' WHERE quarto_id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) return false;

        $stmt->bind_param("i", $quartoId);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Insere um novo registro na tabela quarto.
     */
    public function insertQuarto(array $data): int|string
    {
        $this->conn->begin_transaction();
        try {
            $sql = "INSERT INTO quarto (numero, room_type, room_status, clean_status, bed_quantity) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "issss",
                $data['numero'],
                $data['room_type'],
                $data['room_status'],
                $data['clean_status'],
                $data['bed_quantity']
            );

            if (!$stmt->execute()) {
                if ($stmt->errno == 1062) {
                    throw new Exception("Quarto já cadastrado. O número " . $data['numero'] . " já existe.");
                }
                throw new Exception("Erro ao inserir quarto: " . $stmt->error);
            }
            $quartoId = $this->conn->insert_id;
            $stmt->close();
            $this->conn->commit();
            return $quartoId;
        } catch (Exception $e) {
            $this->conn->rollback();
            return "Falha na transação: " . $e->getMessage();
        }
    }

    /**
     * Busca dados de um quarto pelo ID.
     */
    public function getQuartoDataById(int $quartoId): ?array
    {
        $sql = "SELECT quarto_id, numero, room_type, room_status, clean_status, bed_quantity 
                FROM quarto WHERE quarto_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quartoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    /**
     * Atualiza os dados de um quarto.
     */
    public function updateQuarto(array $data): bool|string
    {
        $this->conn->begin_transaction();
        try {
            $quartoId = $data['quarto_id'];
            $sql = "UPDATE quarto SET room_type=?, room_status=?, clean_status=?, bed_quantity=? WHERE quarto_id=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "ssssi",
                $data['room_type'],
                $data['room_status'],
                $data['clean_status'],
                $data['bed_quantity'],
                $quartoId
            );
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar quarto: " . $stmt->error);
            }
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return "Falha na transação: " . $e->getMessage();
        }
    }

    /**
     * Lista quartos disponíveis e opcionalmente um específico ocupado.
     */
    public function listarTodos($idOcupado = null)
    {
        if ($idOcupado) {
            $sql = "SELECT quarto_id, numero FROM quarto 
                    WHERE room_status = 'livre' OR quarto_id = ? 
                    ORDER BY numero";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $idOcupado);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $sql = "SELECT quarto_id, numero FROM quarto 
                    WHERE room_status = 'livre' 
                    ORDER BY numero";
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Nota: Removi o fechamento da conexão no try/catch para manter a conexão ativa
    // para outros processos caso necessário, seguindo o padrão da sua aplicação.
}