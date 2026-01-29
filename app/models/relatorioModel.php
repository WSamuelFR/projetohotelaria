<?php
// Arquivo: app/models/relatorioModel.php
require_once(__DIR__ . '/../config/DBConnection.php');

class RelatorioModel {
    private $conn;

    public function __construct() {
        $this->conn = Connect();
    }

    /**
     * Retorna os números resumidos para os Cards do Dashboard
     */
    public function getCardsResumo($dataInicio, $dataFim) {
        $resumo = [];

        // 1. Faturamento Total (Diárias + Consumo já somados na coluna 'total' ao encerrar)
        $sqlFin = "SELECT SUM(total) as faturamento, COUNT(hospedagem_id) as total_estadias 
                   FROM hospedagem 
                   WHERE situacao = 'encerrada' 
                   AND data_checkout BETWEEN ? AND ?";
        
        $stmt = $this->conn->prepare($sqlFin);
        $stmt->bind_param("ss", $dataInicio, $dataFim);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        $resumo['faturamento_total'] = $res['faturamento'] ?? 0;
        $resumo['total_hospedagens'] = $res['total_estadias'] ?? 0;
        $resumo['ticket_medio'] = ($res['total_estadias'] > 0) ? ($res['faturamento'] / $res['total_estadias']) : 0;

        // 2. Taxa de Ocupação Atual (Quartos Ocupados / Total de Quartos)
        $sqlOcup = "SELECT 
                    (SELECT COUNT(*) FROM quarto WHERE room_status = 'ocupado') as ocupados,
                    (SELECT COUNT(*) FROM quarto) as total_quartos";
        $resOcup = $this->conn->query($sqlOcup)->fetch_assoc();
        
        $resumo['ocupacao_porcentagem'] = ($resOcup['total_quartos'] > 0) 
            ? ($resOcup['ocupados'] / $resOcup['total_quartos']) * 100 
            : 0;

        return $resumo;
    }

    /**
     * Retorna a lista detalhada de movimentações para a tabela
     */
    public function getMovimentacaoDetalhada($dataInicio, $dataFim) {
        $sql = "SELECT h.hospedagem_id, h.data_checkout, h.total, c.full_name as hospede, q.numero as quarto,
                (SELECT SUM(hc.quantidade * hc.preco_unitario_pago) 
                 FROM hospedagem_consumo hc 
                 WHERE hc.hospedagem_id = h.hospedagem_id) as total_consumo
                FROM hospedagem h
                JOIN cadastro c ON h.hospedes = c.cadastro_id
                JOIN quarto q ON h.quarto = q.quarto_id
                WHERE h.situacao = 'encerrada' 
                AND h.data_checkout BETWEEN ? AND ?
                ORDER BY h.data_checkout DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $dataInicio, $dataFim);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Ranking de produtos mais vendidos
     */
    public function getRankingProdutos($dataInicio, $dataFim) {
        $sql = "SELECT p.nome, SUM(hc.quantidade) as qtd_vendida, SUM(hc.quantidade * hc.preco_unitario_pago) as total_arrecadado
                FROM hospedagem_consumo hc
                JOIN produto p ON hc.produto_id = p.produto_id
                WHERE hc.data_consumo BETWEEN ? AND ?
                GROUP BY hc.produto_id
                ORDER BY qtd_vendida DESC LIMIT 5";

        $stmt = $this->conn->prepare($sql);
        // Adicionamos o horário para pegar o dia inteiro
        $ini = $dataInicio . " 00:00:00";
        $fim = $dataFim . " 23:59:59";
        $stmt->bind_param("ss", $ini, $fim);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}