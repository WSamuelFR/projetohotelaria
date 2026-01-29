<?php
// Arquivo: app/controllers/leitura_hospedagem.php
require_once(__DIR__ . '/../models/hospedagemModel.php');

header('Content-Type: application/json');

// Define a ação (leitura padrão, consumo individual ou consumo total para checkout)
$action = $_GET['action'] ?? 'read';

try {
    $model = new HospedagemModel();

    switch ($action) {
        case 'get_consumo_total':
            // NOVA ROTA: Busca todo o consumo da hospedagem (todos os hóspedes) para o Modal de Check-out
            $hospedagem_id = $_GET['hospedagem_id'] ?? null;
            if (!$hospedagem_id) {
                echo json_encode(['success' => false, 'message' => 'ID da hospedagem ausente.']);
                exit;
            }

            // SQL para buscar todos os itens consumidos nesta estadia específica
            $sql = "SELECT hc.*, p.nome as nome_produto, c.full_name as nome_cliente 
                    FROM hospedagem_consumo hc 
                    JOIN produto p ON hc.produto_id = p.produto_id 
                    JOIN cadastro c ON hc.hospede_id = c.cadastro_id
                    WHERE hc.hospedagem_id = ? 
                    ORDER BY hc.data_consumo DESC";
            
            $conn = Connect();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $hospedagem_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $res]);
            break;

        case 'get_consumo':
            // Rota utilizada pelo JS para carregar a tabela de consumo individual (Mantida)
            $hospedagem_id = $_GET['hospedagem_id'] ?? null;
            $hospede_id = $_GET['hospede_id'] ?? null;

            if (!$hospedagem_id || !$hospede_id) {
                echo json_encode(['success' => false, 'message' => 'Parâmetros de consumo ausentes.']);
                exit;
            }

            $consumo = $model->getConsumoPorHospede((int)$hospedagem_id, (int)$hospede_id);
            echo json_encode(['success' => true, 'data' => $consumo]);
            break;

        case 'read':
        default:
            // Rota padrão para carregar os dados da hospedagem para edição (Mantida)
            $id = $_GET['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                echo json_encode(['success' => false, 'message' => 'ID de hospedagem inválido.']);
                exit;
            }

            $dados = $model->getHospedagemById((int)$id);

            if ($dados) {
                echo json_encode(['success' => true, 'data' => $dados]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Hospedagem não encontrada.']);
            }
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no controlador: ' . $e->getMessage()]);
}