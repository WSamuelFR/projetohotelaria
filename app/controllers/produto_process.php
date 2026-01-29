<?php
// Arquivo: app/controllers/produto_process.php

require_once(__DIR__ . '/../models/produtoModel.php');
require_once(__DIR__ . '/../models/loggerModel.php'); 

header('Content-Type: application/json');

// 1. Início seguro da sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- BLOCO DE CADASTRO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nome']) || empty($data['preco'])) {
        echo json_encode(['success' => false, 'message' => 'Nome e Preço são obrigatórios.']);
        exit;
    }

    try {
        $model = new ProdutoModel();
        $resultado = $model->insertProduto($data);
        
        if (isset($resultado['success']) && $resultado['success']) {
            $usuario_id = $_SESSION['user_id'] ?? 0;
            // Uso do novo nome: loggerModel
            loggerModel::registrar($usuario_id, 'NOVO_PRODUTO', "Cadastrou o produto: " . $data['nome']);
        }
        
        echo json_encode($resultado);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit; 
} 

// --- BLOCO DE LISTAGEM (GET) - MANTIDO INTACTO ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $model = new ProdutoModel();
        $produtos = $model->listarTodos();
        echo json_encode(['success' => true, 'data' => $produtos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// --- BLOCO DE EXCLUSÃO (DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID do produto inválido.']);
        exit;
    }

    try {
        $model = new ProdutoModel();
        
        // Executa a exclusão no banco
        $sucesso = $model->deleteProduto((int)$id);

        if ($sucesso) {
            // REGISTRO DE AUDITORIA NO SUCESSO
            $usuario_id = $_SESSION['user_id'] ?? 0;
            loggerModel::registrar($usuario_id, 'EXCLUIR_PRODUTO', "Removeu o produto ID: $id do estoque.");
        }

        echo json_encode([
            'success' => $sucesso,
            'message' => $sucesso ? 'Produto removido com sucesso!' : 'Erro ao remover produto.'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit;
}