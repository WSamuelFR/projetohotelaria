<?php
// Arquivo: app/models/hospedagemModel.php
require_once(__DIR__ . '/../config/DBConnection.php');

class HospedagemModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Connect();
    }

    /**
     * REGISTRA NOVA HOSPEDAGEM (CHECK-IN)
     * Ajustado para processar o array de hóspedes dinâmico.
     */
    public function registrarHospedagem($dados)
    {
        $this->conn->begin_transaction();
        try {
            // 1. Identificar Titular e Acompanhantes dentro do array 'hospedes'
            $titular_id = null;
            $acompanhantes_ids = [];

            if (!empty($dados['hospedes']) && is_array($dados['hospedes'])) {
                foreach ($dados['hospedes'] as $h) {
                    if ($h['tipo'] === 'Titular') {
                        $titular_id = $h['id'];
                    } else {
                        $acompanhantes_ids[] = $h['id'];
                    }
                }
            }

            // Validação de segurança
            if (!$titular_id) {
                throw new Exception("Hóspede titular não encontrado na lista.");
            }

            // 2. Calcular o Valor Total (Preço Unitário * Quantidade de Pessoas)
            // Resolve o erro: Column 'total' cannot be null
            $qtdPessoas = count($dados['hospedes']);
            $precoUnitario = floatval($dados['preco_unitario'] ?? 0);
            $valorTotalDiarias = $precoUnitario * $qtdPessoas;

            // 3. Insere a hospedagem principal
            $sql = "INSERT INTO hospedagem (reserva, hospedes, quarto, data_checkin, data_checkout, total, observacoes, situacao, usuario_responsavel) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'ativa', ?)";

            $stmt = $this->conn->prepare($sql);
            $reserva = !empty($dados['reserva_id']) ? $dados['reserva_id'] : null;
            $obs = $dados['obs'] ?? ''; // O JS envia como 'obs'

            $stmt->bind_param(
                "iiissdss",
                $reserva,
                $titular_id,
                $dados['quarto_id'],
                $dados['checkin'],
                $dados['checkout'],
                $valorTotalDiarias,
                $obs,
                $dados['usuario']
            );

            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir hospedagem: " . $stmt->error);
            }

            $hospedagemId = $this->conn->insert_id;

            // 4. Insere os acompanhantes na tabela de ligação
            if (!empty($acompanhantes_ids)) {
                $sqlAcomp = "INSERT INTO hospedagem_acompanhantes (hospedagem_id, cadastro_id) VALUES (?, ?)";
                $stmtA = $this->conn->prepare($sqlAcomp);
                foreach ($acompanhantes_ids as $acompId) {
                    $stmtA->bind_param("ii", $hospedagemId, $acompId);
                    $stmtA->execute();
                }
            }

            // 5. Atualiza status do quarto para ocupado
            $sqlQuarto = "UPDATE quarto SET room_status = 'ocupado' WHERE quarto_id = ?";
            $stmtQ = $this->conn->prepare($sqlQuarto);
            $stmtQ->bind_param("i", $dados['quarto_id']);
            $stmtQ->execute();

            $this->conn->commit();
            return ["success" => true, "message" => "Check-in realizado com sucesso!"];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Erro no Model: " . $e->getMessage()];
        }
    }

    /**
     * ATUALIZA HOSPEDAGEM (EDIÇÃO)
     * Corrigido para gerenciar a troca de quartos e status de limpeza/ocupação.
     */
    public function updateHospedagem($dados)
    {
        $this->conn->begin_transaction();
        try {
            // 1. Busca o ID do quarto que estava registrado antes da alteração
            $sqlBuscaAntigo = "SELECT quarto FROM hospedagem WHERE hospedagem_id = ?";
            $stmtAntigo = $this->conn->prepare($sqlBuscaAntigo);
            $stmtAntigo->bind_param("i", $dados['id']);
            $stmtAntigo->execute();
            $resultadoAntigo = $stmtAntigo->get_result()->fetch_assoc();
            $quartoAntigoId = $resultadoAntigo['quarto'] ?? null;

            // 2. Atualiza os dados principais da hospedagem
            $sql = "UPDATE hospedagem SET 
                    hospedes = ?, quarto = ?, data_checkin = ?, 
                    data_checkout = ?, total = ?, observacoes = ? 
                    WHERE hospedagem_id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "iissdsi",
                $dados['titular_id'],
                $dados['quarto_id'],
                $dados['checkin'],
                $dados['checkout'],
                $dados['total'],
                $dados['observacoes'],
                $dados['id']
            );
            $stmt->execute();

            // 3. Gerenciamento de Status dos Quartos (CASO TENHA MUDADO O QUARTO)
            if ($quartoAntigoId && $quartoAntigoId != $dados['quarto_id']) {
                // Liberar o quarto antigo e marcar como sujo
                $sqlLivre = "UPDATE quarto SET room_status = 'livre', clean_status = 'sujo' WHERE quarto_id = ?";
                $stmtL = $this->conn->prepare($sqlLivre);
                $stmtL->bind_param("i", $quartoAntigoId);
                $stmtL->execute();

                // Ocupar o novo quarto
                $sqlOcupado = "UPDATE quarto SET room_status = 'ocupado' WHERE quarto_id = ?";
                $stmtO = $this->conn->prepare($sqlOcupado);
                $stmtO->bind_param("i", $dados['quarto_id']);
                $stmtO->execute();
            }

            // 4. Sincroniza acompanhantes (Mantendo o que já funcionava)
            $sqlDel = "DELETE FROM hospedagem_acompanhantes WHERE hospedagem_id = ?";
            $stmtD = $this->conn->prepare($sqlDel);
            $stmtD->bind_param("i", $dados['id']);
            $stmtD->execute();

            if (!empty($dados['acompanhantes']) && is_array($dados['acompanhantes'])) {
                $sqlIns = "INSERT INTO hospedagem_acompanhantes (hospedagem_id, cadastro_id) VALUES (?, ?)";
                $stmtI = $this->conn->prepare($sqlIns);
                foreach ($dados['acompanhantes'] as $acompId) {
                    $stmtI->bind_param("ii", $dados['id'], $acompId);
                    $stmtI->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Erro na edição de hospedagem: " . $e->getMessage());
            return false;
        }
    }

    public function getHospedagemById($id)
    {
        $sql = "SELECT h.*, c.full_name as nome_titular, c.cpf_cnpj as cpf_titular, q.numero as numero_quarto, c.cadastro_id,
                ((SELECT COUNT(*) FROM hospedagem_acompanhantes ha WHERE ha.hospedagem_id = h.hospedagem_id) + 1) as qtd_total_hospedes
                FROM hospedagem h
                JOIN cadastro c ON h.hospedes = c.cadastro_id
                JOIN quarto q ON h.quarto = q.quarto_id
                WHERE h.hospedagem_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $hospedagem = $stmt->get_result()->fetch_assoc();

        if ($hospedagem) {
            $sqlAcomp = "SELECT c.cadastro_id, c.full_name, c.cpf_cnpj 
                         FROM hospedagem_acompanhantes ha
                         JOIN cadastro c ON ha.cadastro_id = c.cadastro_id
                         WHERE ha.hospedagem_id = ?";
            $stmtA = $this->conn->prepare($sqlAcomp);
            $stmtA->bind_param("i", $id);
            $stmtA->execute();
            $hospedagem['acompanhantes'] = $stmtA->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return $hospedagem;
    }

    public function getConsumoPorHospede($hospedagem_id, $hospede_id)
    {
        $sql = "SELECT hc.*, p.nome as nome_produto 
                FROM hospedagem_consumo hc
                JOIN produto p ON hc.produto_id = p.produto_id
                WHERE hc.hospedagem_id = ? AND hc.hospede_id = ?
                ORDER BY hc.data_consumo DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $hospedagem_id, $hospede_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Registra o consumo de um hóspede e realiza a baixa automática no estoque.
     * Prioridade Máxima: Segurança dos dados e integridade do estoque.
     */
    public function registrarConsumo($dados)
    {
        // Iniciamos uma transação para garantir que o registro do consumo 
        // e a baixa no estoque aconteçam juntos ou não aconteçam.
        $this->conn->begin_transaction();

        try {
            // 1. Inserir o registro na tabela de consumo
            $sqlConsumo = "INSERT INTO hospedagem_consumo (hospedagem_id, hospede_id, produto_id, quantidade, preco_unitario_pago) 
                           VALUES (?, ?, ?, ?, ?)";

            $stmtConsumo = $this->conn->prepare($sqlConsumo);
            $stmtConsumo->bind_param(
                "iiiid",
                $dados['hospedagem_id'],
                $dados['hospede_id'],
                $dados['produto_id'],
                $dados['quantidade'],
                $dados['preco_unitario']
            );

            if (!$stmtConsumo->execute()) {
                throw new Exception("Erro ao registrar consumo: " . $stmtConsumo->error);
            }

            // 2. Realizar a baixa automática no estoque do produto
            // Subtrai a quantidade vendida do estoque_atual na tabela produto
            $sqlEstoque = "UPDATE produto SET estoque_atual = estoque_atual - ? WHERE produto_id = ?";
            $stmtEstoque = $this->conn->prepare($sqlEstoque);
            $stmtEstoque->bind_param(
                "ii",
                $dados['quantidade'],
                $dados['produto_id']
            );

            if (!$stmtEstoque->execute()) {
                throw new Exception("Erro ao atualizar estoque: " . $stmtEstoque->error);
            }

            // Se tudo correu bem, confirma as duas operações no banco
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Se houver qualquer erro, desfaz tudo (não registra consumo nem baixa estoque)
            $this->conn->rollback();
            error_log("Falha na venda/estoque: " . $e->getMessage());
            return false;
        }
    }

    public function finalizarHospedagem($id)
    {
        $this->conn->begin_transaction();
        try {
            $sqlBusca = "SELECT quarto, total FROM hospedagem WHERE hospedagem_id = ?";
            $stmtB = $this->conn->prepare($sqlBusca);
            $stmtB->bind_param("i", $id);
            $stmtB->execute();
            $result = $stmtB->get_result()->fetch_assoc();

            if (!$result) throw new Exception("Hospedagem não encontrada.");

            $quartoId = $result['quarto'];
            $totalDiarias = $result['total'];

            $sqlConsumo = "SELECT SUM(quantidade * preco_unitario_pago) as total_consumo 
                            FROM hospedagem_consumo 
                            WHERE hospedagem_id = ?";
            $stmtC = $this->conn->prepare($sqlConsumo);
            $stmtC->bind_param("i", $id);
            $stmtC->execute();
            $resConsumo = $stmtC->get_result()->fetch_assoc();
            $totalConsumo = $resConsumo['total_consumo'] ?? 0;

            $valorFinal = $totalDiarias + $totalConsumo;

            $sqlHosp = "UPDATE hospedagem SET situacao = 'encerrada', total = ? WHERE hospedagem_id = ?";
            $stmtH = $this->conn->prepare($sqlHosp);
            $stmtH->bind_param("di", $valorFinal, $id);
            $stmtH->execute();

            $sqlQuarto = "UPDATE quarto SET room_status = 'livre', clean_status = 'sujo' WHERE quarto_id = ?";
            $stmtQ = $this->conn->prepare($sqlQuarto);
            $stmtQ->bind_param("i", $quartoId);
            $stmtQ->execute();

            $this->conn->commit();

            return [
                "success" => true,
                "message" => "Check-out finalizado! Total: R$ $valorFinal."
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Erro ao finalizar: " . $e->getMessage()];
        }
    }
}
