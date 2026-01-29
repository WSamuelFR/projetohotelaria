<?php
// Arquivo: app/controllers/finalizar_hospedagem_process.php

// Inicia a sessão para capturar o ID do funcionário logado
session_start();

// Importa o Model de Hospedagem e o LoggerModel para auditoria
require_once(__DIR__ . '/../models/hospedagemModel.php');
require_once(__DIR__ . '/../models/loggerModel.php'); 

// Define que a resposta será JSON para comunicação com o Lobby
header('Content-Type: application/json');

// 1. Receber e decodificar a requisição JSON vinda do lobby.js
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$hospedagem_id = $data['id'] ?? null;

// 2. Validação básica do ID
if (empty($hospedagem_id) || !is_numeric($hospedagem_id)) {
    echo json_encode([
        'success' => false, 
        'message' => 'ID de hospedagem inválido ou ausente.'
    ]);
    exit;
}

try {
    // 3. Instanciar o Model e executar a finalização
    $model = new HospedagemModel();
    
    /**
     * O método finalizarHospedagem realiza:
     * - Busca do valor das diárias.
     * - Soma de todos os consumos individuais vinculados.
     * - Atualização do total final da hospedagem.
     * - Encerramento da estadia e liberação do quarto (sujo).
     */
    $resultado = $model->finalizarHospedagem((int)$hospedagem_id);  

    // --- NOVA FUNÇÃO: REGISTRO DE AUDITORIA (LOG) ---
    // Se a finalização no banco deu certo, registramos quem fez a ação.
    if ($resultado['success']) {
        $usuario_id = $_SESSION['user_id'] ?? 0; // Captura ID do funcionário ou 0 se não logado
        $detalhes_log = "Hospedagem #" . $hospedagem_id . " encerrada com sucesso. " . $resultado['message'];
        
        // Chamada estática do LoggerModel (Não quebra o fluxo se falhar)
        loggerModel::registrar($usuario_id, 'CHECK-OUT', $detalhes_log);  
    }

    // Retorna o resultado original (Mantendo compatibilidade com o front-end)
    echo json_encode($resultado);

} catch (Exception $e) {
    // Captura de erros inesperados
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno ao processar o check-out: ' . $e->getMessage()
    ]);
}