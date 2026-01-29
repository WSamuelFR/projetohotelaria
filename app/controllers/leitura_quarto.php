<?php
// Arquivo: app/controllers/leitura_quarto.php

require_once('../models/quartoModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $quarto_id = $_GET['id'] ?? null;

    if (empty($quarto_id) || !is_numeric($quarto_id)) {
        $response['message'] = 'ID do quarto inválido ou ausente.';
        echo json_encode($response);
        exit;
    }

    try {
        $model = new QuartoModel();
        $quartoData = $model->getQuartoDataById((int)$quarto_id);

        if ($quartoData) {
            $response['success'] = true;
            $response['message'] = 'Dados carregados com sucesso.';
            $response['data'] = $quartoData;
        } else {
            $response['message'] = 'Nenhum quarto encontrado com o ID fornecido.';
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro interno: ' . $e->getMessage();
    }
}

echo json_encode($response);