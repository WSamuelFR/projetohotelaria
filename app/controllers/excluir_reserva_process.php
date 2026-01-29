<?php
// Arquivo: app/controllers/excluir_reserva_process.php

// Inicia a sessão para auditoria
session_start();

// Importa o Model de Reservas e loggerModel
require_once(__DIR__ . '/../models/reservaModel.php');
require_once(__DIR__ . '/../models/loggerModel.php');

header('Content-Type: application/json');

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$reserva_id = $data['id'] ?? null;

if (empty($reserva_id) || !is_numeric($reserva_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da reserva inválido ou ausente.']);
    exit;
}

try {
    $model = new ReservaModel();
    $sucesso = $model->cancelarReserva((int)$reserva_id);

    if ($sucesso) {
        // --- LOG DE AUDITORIA ---
        $usuario_id = $_SESSION['user_id'] ?? 0;
        loggerModel::registrar($usuario_id, 'CANCELAR_RESERVA', "Cancelou a reserva ID: $reserva_id");

        echo json_encode(['success' => true, 'message' => 'Reserva cancelada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar o status da reserva.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}