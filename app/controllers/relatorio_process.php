<?php
// Arquivo: app/controllers/relatorio_process.php

require_once(__DIR__ . '/../models/relatorioModel.php');

header('Content-Type: application/json');

try {
    $model = new RelatorioModel();

    // 1. Captura as datas do filtro (Padrão: mês atual se não informado)
    $dataInicio = $_GET['inicio'] ?? date('Y-m-01');
    $dataFim = $_GET['fim'] ?? date('Y-m-t');

    // 2. Coleta as diferentes partes do relatório
    $resumo = $model->getCardsResumo($dataInicio, $dataFim);
    $movimentacao = $model->getMovimentacaoDetalhada($dataInicio, $dataFim);
    $ranking = $model->getRankingProdutos($dataInicio, $dataFim);

    // 3. Devolve tudo num único pacote JSON
    echo json_encode([
        'success' => true,
        'data' => [
            'resumo' => $resumo,
            'movimentacao' => $movimentacao,
            'ranking' => $ranking,
            'periodo' => [
                'inicio' => date('d/m/Y', strtotime($dataInicio)),
                'fim' => date('d/m/Y', strtotime($dataFim))
            ]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar relatório: ' . $e->getMessage()
    ]);
}