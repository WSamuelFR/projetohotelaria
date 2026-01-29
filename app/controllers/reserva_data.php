<?php
// Arquivo: app/controllers/reserva_data.php

require_once('../models/reservaModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Parâmetro inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? '';
    $searchTerm = $_GET['search'] ?? '';

    try {
        $model = new ReservaModel();

        if ($type === 'clientes') {
            $data = $model->getClientesForDatalist($searchTerm);
            $response['success'] = true;
            $response['data'] = $data;
        } elseif ($type === 'quartos') {
            $data = $model->getQuartosForDatalist($searchTerm);
            $response['success'] = true;
            $response['data'] = $data;
        } else {
            $response['message'] = 'Tipo de dado desconhecido.';
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro ao buscar dados: ' . $e->getMessage();
    }
}

echo json_encode($response);