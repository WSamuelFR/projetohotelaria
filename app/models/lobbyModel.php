<?php
// Arquivo: app/models/lobbyModel.php

// Define que a resposta será JSON
header('Content-Type: application/json');

// Importa a conexão com o banco de dados
require_once('../config/DBConnection.php');

$response = [
    'success' => false,
    'message' => 'Erro desconhecido.',
    'cadastros' => [],
    'quartos' => [],
    'reservas' => [],
    'hospedagens' => [] 
];

try {
    $conn = Connect();

    if ($conn->connect_error) {
        throw new Exception("Falha na conexão com o DB.");
    }

    // Determina os termos de busca e filtros vindos do lobby.js
    $searchTerm = $_GET['search'] ?? '';
    $statusFilter = $_GET['status'] ?? 'todas';
    // NOVA CAPTURA: Filtro específico para a aba de reservas
    $reservaStatus = $_GET['reservaStatus'] ?? 'pendente'; 

    $searchParam = !empty($searchTerm) ? "%" . $searchTerm . "%" : null;

    // ------------------------------------------
    // 1. CONSULTA DE CADASTROS (MANTIDA)
    // ------------------------------------------
    $sqlCadastros = "
        SELECT 
            c.cadastro_id,
            c.full_name as nome_cliente,
            c.cpf_cnpj,
            CASE 
                WHEN e.id_empresa IS NOT NULL THEN 'Empresa (CNPJ)'
                WHEN CHAR_LENGTH(c.cpf_cnpj) = 14 THEN 'Empresa (CNPJ)'
                WHEN CHAR_LENGTH(c.cpf_cnpj) = 11 THEN 'Hóspede (CPF)'
                ELSE 'Desconhecido'
            END as tipo
        FROM cadastro c
        LEFT JOIN empresa e ON c.cpf_cnpj = e.cadastro
    ";

    if ($searchParam) {
        $sqlCadastros .= " WHERE c.full_name LIKE ? OR c.cpf_cnpj LIKE ?";
    }
    $sqlCadastros .= " ORDER BY nome_cliente";

    $stmtCadastros = $conn->prepare($sqlCadastros);
    if ($searchParam) {
        $stmtCadastros->bind_param("ss", $searchParam, $searchParam);
    }
    $stmtCadastros->execute();
    $response['cadastros'] = $stmtCadastros->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtCadastros->close();

    // ------------------------------------------
    // 2. CONSULTA DE QUARTOS (CORREÇÃO DE CHAVE PARA JS)
    // ------------------------------------------
    $sqlQuartos = "
        SELECT 
            q.quarto_id AS id,
            q.numero,
            q.room_type AS tipo,
            q.bed_quantity AS camas,
            q.room_status AS status_principal,
            q.clean_status, 
            c.full_name AS cliente_atual,
            h.hospedagem_id AS hospedagem_ativa_id
        FROM quarto q
        LEFT JOIN hospedagem h ON q.quarto_id = h.quarto AND h.situacao = 'ativa'
        LEFT JOIN cadastro c ON h.hospedes = c.cadastro_id
    ";

    if ($searchParam) {
        $sqlQuartos .= " WHERE q.numero LIKE ? OR q.room_type LIKE ?";
    }
    $sqlQuartos .= " ORDER BY q.numero";

    $stmtQuartos = $conn->prepare($sqlQuartos);
    if ($searchParam) {
        $stmtQuartos->bind_param("ss", $searchParam, $searchParam);
    }
    $stmtQuartos->execute();
    
    $resultQuartos = $stmtQuartos->get_result();
    while ($row = $resultQuartos->fetch_assoc()) {
        // Sincroniza o status_display com a lógica de limpeza para o badge visual
        $row['status_display'] = ($row['status_principal'] == 'livre' && $row['clean_status'] == 'sujo') ? 'limpeza' : $row['status_principal'];
        $row['cliente_atual'] = $row['cliente_atual'] ?? 'Vazio';
        $response['quartos'][] = $row;
    }
    $stmtQuartos->close();

    // ------------------------------------------
    // 3. CONSULTA DE RESERVAS (ATUALIZADA COM FILTROS)
    // ------------------------------------------
    $sqlReservas = "
        SELECT r.reserva_id, r.data_checkin, r.data_checkout, r.situacao, c.full_name AS nome_cliente, c.cpf_cnpj
        FROM reserva r
        JOIN cadastro c ON r.cadastro = c.cpf_cnpj
        WHERE 1=1
    ";

    $paramsReservas = [];
    $typesReservas = "";

    // Aplica filtro de situação se não for 'todas'
    if ($reservaStatus !== 'todas') {
        $sqlReservas .= " AND r.situacao = ?";
        $paramsReservas[] = $reservaStatus;
        $typesReservas .= "s";
    }

    if ($searchParam) {
        $sqlReservas .= " AND (c.full_name LIKE ? OR c.cpf_cnpj LIKE ?)";
        $paramsReservas[] = $searchParam;
        $paramsReservas[] = $searchParam;
        $typesReservas .= "ss";
    }

    $sqlReservas .= " ORDER BY r.data_checkin ASC";

    $stmtReservas = $conn->prepare($sqlReservas);
    if (!empty($paramsReservas)) {
        $stmtReservas->bind_param($typesReservas, ...$paramsReservas);
    }
    $stmtReservas->execute();
    $response['reservas'] = $stmtReservas->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtReservas->close();

    // ------------------------------------------
    // 4. CONSULTA DE HOSPEDAGENS (MANTIDA)
    // ------------------------------------------
    $sqlHosp = "
        SELECT 
            h.hospedagem_id, 
            h.data_checkin, 
            h.data_checkout, 
            h.total, 
            h.situacao,
            c.full_name AS nome_hospede, 
            q.numero AS numero_quarto
        FROM hospedagem h
        JOIN cadastro c ON h.hospedes = c.cadastro_id
        JOIN quarto q ON h.quarto = q.quarto_id
    ";

    $whereHosp = [];
    $paramsHosp = [];
    $typesHosp = "";

    if ($statusFilter !== 'todas') {
        $whereHosp[] = "h.situacao = ?";
        $paramsHosp[] = $statusFilter;
        $typesHosp .= "s";
    }

    if ($searchParam) {
        $whereHosp[] = "(c.full_name LIKE ? OR q.numero LIKE ?)";
        $paramsHosp[] = $searchParam;
        $paramsHosp[] = $searchParam;
        $typesHosp .= "ss";
    }

    if ($whereHosp) {
        $sqlHosp .= " WHERE " . implode(" AND ", $whereHosp);
    }
    $sqlHosp .= " ORDER BY h.hospedagem_id DESC";

    $stmtHosp = $conn->prepare($sqlHosp);
    if ($paramsHosp) {
        $stmtHosp->bind_param($typesHosp, ...$paramsHosp);
    }
    $stmtHosp->execute();
    $response['hospedagens'] = $stmtHosp->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtHosp->close();

    $response['success'] = true;
    $response['message'] = 'Dados carregados com sucesso.';

} catch (Exception $e) {
    $response['message'] = "Erro: " . $e->getMessage();
} finally {
    if (isset($conn) && $conn->ping()) { 
        $conn->close();
    }
}

echo json_encode($response);