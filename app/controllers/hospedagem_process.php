<?php
// Arquivo: app/controllers/hospedagem_process.php
session_start();
require_once('../models/hospedagemModel.php');

header('Content-Type: application/json');

// 1. Receber e decodificar a requisição JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Determina a ação via parâmetro URL
$action = $_GET['action'] ?? '';

if (!$data && $action !== 'get_consumo') {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos ou não recebidos.']);
    exit;
}

try {
    $model = new HospedagemModel();

    // ---------------------------------------------------------
    // AÇÃO: LANÇAR CONSUMO INDIVIDUAL
    // ---------------------------------------------------------
    if ($action === 'lancar_consumo') {
        if (empty($data['hospedagem_id']) || empty($data['hospede_id']) || empty($data['produto_id'])) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos para lançar consumo.']);
            exit;
        }

        $sucesso = $model->registrarConsumo($data);
        echo json_encode([
            'success' => $sucesso,
            'message' => $sucesso ? 'Consumo registrado com sucesso!' : 'Erro ao registrar consumo no banco.'
        ]);
        exit;
    }

    // ---------------------------------------------------------
    // AÇÃO: SALVAR (NOVO CHECK-IN)
    // ---------------------------------------------------------
    if ($action === 'salvar') {
        // Validação híbrida: Aceita 'hospedes' (novo cadastro) ou 'titular_id' (legado/edição)
        $temTitular = !empty($data['titular_id']) || (!empty($data['hospedes']) && is_array($data['hospedes']));

        if (!$temTitular || empty($data['quarto_id'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erro: Selecione um Hóspede Titular e um Quarto válido.'
            ]);
            exit;
        }

        $data['usuario'] = $_SESSION['user_name'] ?? 'Sistema'; 
        echo json_encode($model->registrarHospedagem($data));
        exit;
    }

    // ---------------------------------------------------------
    // AÇÃO PADRÃO: EDIÇÃO (Mantida para não quebrar up_hospedagem.php)
    // ---------------------------------------------------------
    if (!empty($data['id'])) {
        if (empty($data['titular_id']) || empty($data['quarto_id'])) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos para atualização.']);
            exit;
        }

        $sucesso = $model->updateHospedagem($data);
        echo json_encode([
            'success' => $sucesso, 
            'message' => $sucesso ? 'Hospedagem atualizada com sucesso!' : 'Erro ao atualizar hospedagem no banco de dados.'
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Ação não identificada.']);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno no servidor: ' . $e->getMessage()
    ]);
}