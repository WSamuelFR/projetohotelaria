<?php
// Arquivo: app/controllers/edicao_process.php

session_start();
require_once('../models/cadastroModel.php');
require_once('../models/loggerModel.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $cadastro_id = $data['cadastro_id'] ?? null;
    $tipo = $data['tipo'] ?? '';
    
    // Validações originais mantidas...
    if (empty($cadastro_id) || !is_numeric($cadastro_id)) { /*...*/ }

    try {
        $model = new CadastroModel();
        $result = $model->updateCadastro($data);

        if ($result === true) {
            // --- LOG DE AUDITORIA ---
            $usuario_id = $_SESSION['user_id'] ?? 0;
            $nome = ($tipo == 'hospede') ? ($data['full_name'] ?? 'PF') : ($data['company_name'] ?? 'PJ');
            loggerModel::registrar($usuario_id, 'EDITAR_CADASTRO', "Alterou dados de $tipo: $nome (ID: $cadastro_id)");

            echo json_encode(['success' => true, 'message' => 'Cadastro atualizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => $result]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
    }
}