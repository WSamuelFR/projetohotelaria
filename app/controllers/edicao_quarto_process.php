<?php
// Arquivo: app/controllers/edicao_quarto_process.php

require_once('../models/quartoModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $quarto_id = $data['quarto_id'] ?? null;
    $room_type = $data['room_type'] ?? '';
    $bed_quantity = $data['bed_quantity'] ?? '';

    // 1. Validação de ID e campos obrigatórios
    if (empty($quarto_id) || !is_numeric($quarto_id)) {
        $response['message'] = 'ID do quarto inválido.';
        echo json_encode($response);
        exit;
    }
    if (empty($room_type) || empty($bed_quantity)) {
        $response['message'] = 'Tipo de Climatização e Capacidade são obrigatórios.';
        echo json_encode($response);
        exit;
    }
    
    // 2. Chamar o Model para atualização
    try {
        $model = new QuartoModel();
        $result = $model->updateQuarto($data);

        if ($result === true) {
            $response['success'] = true;
            $response['message'] = 'Quarto ID ' . $quarto_id . ' atualizado com sucesso!';
        } else {
            $response['message'] = $result; 
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro interno ao processar a edição: ' . $e->getMessage();
    }
}

echo json_encode($response);