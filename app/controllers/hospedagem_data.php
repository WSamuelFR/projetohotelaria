<?php
// app/controllers/hospedagem_data.php
require_once('../config/DBConnection.php');
header('Content-Type: application/json');

$reserva_id = $_GET['reserva_id'] ?? null;

if ($reserva_id) {
    // Usa a função Connect() definida em DBConnection.php
    $conn = Connect(); 
    
    // SQL ajustado para trazer o cadastro_id e vincular o quarto_id real para o select
    $sql = "SELECT 
                r.reserva_id, 
                r.data_checkin, 
                r.data_checkout, 
                c.full_name, 
                c.cpf_cnpj, 
                c.cadastro_id, 
                q.quarto_id
            FROM reserva r 
            JOIN cadastro c ON r.cadastro = c.cpf_cnpj 
            JOIN quarto q ON r.quarto = q.numero
            WHERE r.reserva_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reserva_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserva = $result->fetch_assoc();

    if ($reserva) {
        // Retorna os dados para preenchimento automático no hospedagem.js
        echo json_encode(["success" => true, "data" => $reserva]);
    } else {
        echo json_encode(["success" => false, "message" => "Reserva não encontrada."]);
    }
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "ID da reserva não fornecido."]);
}