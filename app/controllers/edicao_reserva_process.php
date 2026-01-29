<?php
// app/controllers/hospedagem_process.php
session_start();
// Garante que o caminho suba um nível e entre em models
require_once(__DIR__ . '/../models/hospedagemModel.php'); 

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dados não recebidos.']);
    exit;
}

// Instanciação correta da classe que contém a função
$model = new HospedagemModel(); 

// Agora a chamada funcionará
$resultado = $model->registrarHospedagem($data);
echo json_encode($resultado);