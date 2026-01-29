<?php
// Arquivo: app/controllers/log_data.php
require_once(__DIR__ . '/../models/loggerModel.php');

header('Content-Type: application/json');

try {
    $logs = loggerModel::listarTodos();
    echo json_encode(['success' => true, 'data' => $logs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}