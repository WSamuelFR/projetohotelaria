<?php
// Arquivo: app/Controllers/cadastro_process.php

// Inicia a sessão para capturar o ID do funcionário logado
session_start();

// Importa os Models necessários
require_once('../models/cadastroModel.php');
require_once('../models/LoggerModel.php');

// Define que a resposta será JSON
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Receber e Decodificar a Requisição JSON
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // 2. Validação básica (dependente do tipo de cadastro)
    $tipo = $data['tipo'] ?? '';
    
    // Validação dos campos de contato (obrigatórios para PF e PJ)
    if (empty($data['email']) || empty($data['phone'])) {
        $response['message'] = 'E-mail e telefone são obrigatórios para qualquer tipo de cadastro.';
        echo json_encode($response);
        exit;
    }
    
    // Validação de campos obrigatórios para Hóspede (PF)
    if ($tipo == 'hospede' && (empty($data['full_name']) || empty($data['cpf']))) {
         $response['message'] = 'Para hóspede, Nome e CPF são obrigatórios.';
         echo json_encode($response);
         exit;
    }

    // Validação de campos obrigatórios para Empresa (PJ)
    // Coerente com o formulário simplificado, exigindo apenas nome da empresa e CNPJ.
    if ($tipo == 'empresa' && (empty($data['company_name']) || empty($data['cnpj']))) {
         $response['message'] = 'Para empresa, Nome da Empresa e CNPJ são obrigatórios.';
         echo json_encode($response);
         exit;
    }
    
    // 3. Chamar o Model para inserção
    try {
        $model = new CadastroModel();
        $result = $model->insertCadastro($data);

        if (is_numeric($result)) {
            $response['success'] = true;
            $response['message'] = 'Cadastro ID ' . $result . ' realizado com sucesso!';
            $response['cadastro_id'] = $result;

            // --- NOVA FUNÇÃO: REGISTRO DE AUDITORIA (LOG) ---
            $usuario_id = $_SESSION['user_id'] ?? 0;
            $nome_registro = ($tipo == 'hospede') ? $data['full_name'] : $data['company_name'];
            $acao_log = "NOVO_" . strtoupper($tipo);
            $detalhes_log = "Cadastrou um novo " . $tipo . ": " . $nome_registro . " (ID: " . $result . ")";
            
            // Registra a ação de forma silenciosa
            LoggerModel::registrar($usuario_id, $acao_log, $detalhes_log);

        } else {
            // Captura a mensagem de erro detalhada do Model
            $response['message'] = $result;
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro interno ao processar o cadastro: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>