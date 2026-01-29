<?php
// Arquivo: app/controllers/quarto_process.php

require_once('../models/quartoModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

// Captura a ação via URL (Ex: ?action=limpar_quarto)
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $model = new QuartoModel();

    // --- NOVA AÇÃO: LIMPEZA ---
    if ($action === 'limpar_quarto') {
        $id = $data['id'] ?? null;

        if (!$id) {
            $response['message'] = 'ID do quarto não informado.';
            echo json_encode($response);
            exit;
        }

        try {
            $sucesso = $model->marcarComoLimpo($id); 
            if ($sucesso) {
                $response['success'] = true;
                $response['message'] = 'Quarto liberado e marcado como limpo!';
            } else {
                $response['message'] = 'Erro ao atualizar status de limpeza no banco.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Erro: ' . $e->getMessage();
        }
        
        echo json_encode($response);
        exit;
    }

    // --- AÇÃO ORIGINAL: CADASTRO DE QUARTO ---
    $numero = $data['numero'] ?? null;
    $room_type = $data['room_type'] ?? '';
    $bed_quantity = $data['bed_quantity'] ?? '';

    if (empty($numero) || empty($room_type) || empty($bed_quantity)) {
        $response['message'] = 'O Número, Tipo e Capacidade do quarto são obrigatórios.';
        echo json_encode($response);
        exit;
    }
    
    $data['numero'] = (int)$numero;

    try {
        $result = $model->insertQuarto($data);

        if (is_numeric($result)) {
            $response['success'] = true;
            $response['message'] = 'Quarto ID ' . $result . ' cadastrado com sucesso!';
            $response['quarto_id'] = $result;
        } else {
            $response['message'] = $result;
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro interno ao processar: ' . $e->getMessage();
    }
}

echo json_encode($response);