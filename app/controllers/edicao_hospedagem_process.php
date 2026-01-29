<?php
// Arquivo: app/controllers/edicao_hospedagem_process.php
session_start();
require_once(__DIR__ . '/../models/hospedagemModel.php');

header('Content-Type: application/json');

// 1. Receber e decodificar a requisição JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dados de edição não recebidos.']);
    exit;
}

// 2. Validação básica de campos obrigatórios
if (empty($data['id']) || empty($data['titular_id']) || empty($data['quarto_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro: Dados incompletos para a atualização.'
    ]);
    exit;
}

try {
    $model = new HospedagemModel();
    
    // 3. Executa a atualização diretamente (Sem travas de status)
    $sucesso = $model->updateHospedagem($data);

    if ($sucesso) {
        echo json_encode([
            'success' => true, 
            'message' => 'Hospedagem atualizada com sucesso!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Nenhuma alteração detectada ou erro ao salvar no banco.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno: ' . $e->getMessage()
    ]);
}