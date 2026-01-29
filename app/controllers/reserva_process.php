<?php
// Arquivo: app/controllers/reserva_process.php

require_once('../models/reservaModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $cadastro = $data['cadastro'] ?? '';
    $quarto = $data['quarto'] ?? '';
    $checkin = $data['data_checkin'] ?? '';
    $checkout = $data['data_checkout'] ?? '';

    // 1. Validação básica
    if (empty($cadastro) || empty($quarto) || empty($checkin) || empty($checkout)) {
        $response['message'] = 'Todos os campos de cliente, quarto e datas são obrigatórios.';
        echo json_encode($response);
        exit;
    }
    
    // Validação de datas
    if (strtotime($checkin) >= strtotime($checkout)) {
        $response['message'] = 'A Data de Check-out deve ser posterior à Data de Check-in.';
        echo json_encode($response);
        exit;
    }

    // 2. Chamar o Model para inserção
    try {
        $model = new ReservaModel();
        $result = $model->insertReserva($data);

        if (is_numeric($result)) {
            $response['success'] = true;
            $response['message'] = 'Reserva ID ' . $result . ' realizada com sucesso!';
            $response['reserva_id'] = $result;
        } else {
            // Captura a mensagem de erro detalhada do Model
            $response['message'] = $result;
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro interno ao processar a reserva: ' . $e->getMessage();
    }
}

echo json_encode($response);