<?php
// Arquivo: app/controllers/zeladoria_process.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../models/quartoModel.php');
require_once(__DIR__ . '/../models/loggerModel.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $quarto_id = $data['id'] ?? null;

    if (!$quarto_id) {
        echo json_encode(['success' => false, 'message' => 'ID do quarto não fornecido.']);
        exit;
    }

    try {
        $model = new QuartoModel();
        $sucesso = $model->marcarComoLimpo((int)$quarto_id);

        if ($sucesso) {
            // REGISTRO NA AUDITORIA
            $usuario_id = $_SESSION['user_id'] ?? 0;
            loggerModel::registrar($usuario_id, 'LIMPEZA_CONCLUIDA', "Quarto ID $quarto_id marcado como limpo pelo Monitor de Zeladoria.");
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banco de dados.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}