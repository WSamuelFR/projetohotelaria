<?php
// Arquivo: app/models/Logger.php
require_once(__DIR__ . '/../config/DBConnection.php');

class loggerModel
{
    /**
     * Registra uma ação no banco de dados para fins de auditoria.
     */
    public static function registrar($cadastro_id, $acao, $detalhes)
    {
        try {
            $conn = Connect();
            $sql = "INSERT INTO sistema_logs (cadastro_id, acao, detalhes, ip_origem) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            $stmt->bind_param("isss", $cadastro_id, $acao, $detalhes, $ip);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            // Silencioso: Se o log falhar, o sistema principal não para.
            error_log("Falha ao registrar log: " . $e->getMessage());
        }
    }

    /**
     * Recupera a lista de logs do sistema com o nome do usuário
     */
    public static function listarTodos()
    {
        try {
            $conn = Connect();
            $sql = "SELECT l.*, c.full_name as nome_usuario 
                FROM sistema_logs l
                LEFT JOIN cadastro c ON l.cadastro_id = c.cadastro_id
                ORDER BY l.created_at DESC LIMIT 100";

            $result = $conn->query($sql);
            $logs = $result->fetch_all(MYSQLI_ASSOC);

            $conn->close();
            return $logs;
        } catch (Exception $e) {
            error_log("Erro ao listar logs: " . $e->getMessage());
            return [];
        }
    }
}
