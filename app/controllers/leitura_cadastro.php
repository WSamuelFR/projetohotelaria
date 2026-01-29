<?php
// Arquivo: app/controllers/leitura_cadastro.php

require_once('../models/cadastroModel.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $cadastro_id = $_GET['id'] ?? null;

    if (empty($cadastro_id) || !is_numeric($cadastro_id)) {
        $response['message'] = 'ID de cadastro inválido ou ausente.';
        echo json_encode($response);
        exit;
    }

    try {
        $model = new CadastroModel();
        // Chama o novo método para buscar todos os dados do cliente
        $clientData = $model->getClientDataById((int)$cadastro_id);

        if ($clientData) {
            $response['success'] = true;
            $response['message'] = 'Dados carregados com sucesso.';
            $response['data'] = $clientData;
        } else {
            $response['message'] = 'Nenhum cliente encontrado com o ID fornecido.';
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro interno: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>