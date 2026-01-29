<?php
// Arquivo: app/controllers/gerar_extrato_pdf.php

// 1. Carregar dependências (Autoload do Composer e Model de Hospedagem)
require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../models/hospedagemModel.php');

use Dompdf\Dompdf;
use Dompdf\Options;

// 2. Configurações de renderização do PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// 3. Captura e validação do ID da Hospedagem
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Erro: ID da hospedagem não fornecido.");
}

// 4. Busca de dados no Banco de Dados
$model = new HospedagemModel();
$h = $model->getHospedagemById((int)$id);

if (!$h) {
    die("Erro: Hospedagem não encontrada.");
}

// Busca o consumo consolidado de todos os hóspedes do quarto
$conn = Connect();
$sqlConsumo = "SELECT hc.*, p.nome as nome_produto, c.full_name as nome_cliente 
               FROM hospedagem_consumo hc 
               JOIN produto p ON hc.produto_id = p.produto_id 
               JOIN cadastro c ON hc.hospede_id = c.cadastro_id
               WHERE hc.hospedagem_id = ? 
               ORDER BY hc.data_consumo DESC";

$stmt = $conn->prepare($sqlConsumo);
$stmt->bind_param("i", $id);
$stmt->execute();
$consumos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 5. Cálculos Financeiros
$totalConsumo = 0;
foreach ($consumos as $c) {
    $totalConsumo += ($c['quantidade'] * $c['preco_unitario_pago']);
}
$valorDiarias = floatval($h['total']);
$valorGeral = $valorDiarias + $totalConsumo;

// 6. Lista de Acompanhantes para o cabeçalho
$nomesAcompanhantes = (isset($h['acompanhantes']) && !empty($h['acompanhantes']))
    ? implode(', ', array_column($h['acompanhantes'], 'full_name'))
    : 'Nenhum';

// 7. Montagem do Layout HTML/CSS (Nota Fiscal Profissional)
$html = '
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Helvetica", "Arial", sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #0056b3; padding: 10px 0; margin-bottom: 20px; }
        .hotel-name { font-size: 24px; font-weight: bold; color: #0056b3; margin-bottom: 5px; }
        .invoice-title { font-size: 16px; font-weight: bold; text-transform: uppercase; color: #555; }
        .section-title { background: #0056b3; color: white; padding: 6px 10px; font-weight: bold; margin: 20px 0 10px 0; border-radius: 3px; }
        .info-table, .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .data-table th { background: #f2f2f2; border: 1px solid #ddd; padding: 8px; text-align: left; color: #333; }
        .data-table td { border: 1px solid #ddd; padding: 8px; }
        .text-right { text-align: right; }
        .total-container { margin-top: 30px; width: 40%; margin-left: 60%; }
        .total-row { display: table; width: 100%; border-bottom: 1px solid #ddd; padding: 5px 0; }
        .total-label { display: table-cell; font-weight: bold; }
        .total-value { display: table-cell; text-align: right; }
        .grand-total { font-size: 16px; color: #d9534f; border-bottom: 2px solid #d9534f; margin-top: 5px; }
        .footer { margin-top: 60px; text-align: center; }
        .signature-line { width: 300px; border-top: 1px solid #333; margin: 0 auto; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="hotel-name">WS-HOTELARIA</div>
        <div style="font-size: 12px;">CNPJ: 00.000.000/0001-00 | João Pessoa - PB</div>
        <div class="invoice-title">Extrato de Conta Detalhado #' . str_pad($id, 6, "0", STR_PAD_LEFT) . '</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="60%">
                <strong>Hóspede Titular:</strong> ' . htmlspecialchars($h['nome_titular']) . '<br>
                <strong>CPF/CNPJ:</strong> ' . htmlspecialchars($h['cpf_titular']) . '<br>
                <strong>Acompanhantes:</strong> ' . htmlspecialchars($nomesAcompanhantes) . '
            </td>
            <td width="40%" class="text-right">
                <strong>Quarto:</strong> #' . $h['numero_quarto'] . '<br>
                <strong>Período:</strong> ' . date("d/m/Y", strtotime($h['data_checkin'])) . ' - ' . date("d/m/Y", strtotime($h['data_checkout'])) . '<br>
                <strong>Status:</strong> ' . strtoupper($h['situacao']) . '
            </td>
        </tr>
    </table>

    <div class="section-title">DETALHAMENTO DE DIÁRIAS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="text-right">Total das Diárias</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Hospedagem em Quarto ' . $h['numero_quarto'] . ' (Calculado por hóspede)</td>
                <td class="text-right">R$ ' . number_format($valorDiarias, 2, ",", ".") . '</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">DETALHAMENTO DE CONSUMO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Hóspede</th>
                <th>Produto / Serviço</th>
                <th>Qtd</th>
                <th>Unitário</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>';

if (empty($consumos)) {
    $html .= '<tr><td colspan="5" style="text-align:center; color: #888;">Nenhum consumo registrado nesta estadia.</td></tr>';
} else {
    foreach ($consumos as $c) {
        $sub = $c['quantidade'] * $c['preco_unitario_pago'];
        $html .= '<tr>
                    <td>' . htmlspecialchars($c['nome_cliente']) . '</td>
                    <td>' . htmlspecialchars($c['nome_produto']) . '</td>
                    <td>' . $c['quantidade'] . '</td>
                    <td>R$ ' . number_format($c['preco_unitario_pago'], 2, ",", ".") . '</td>
                    <td class="text-right">R$ ' . number_format($sub, 2, ",", ".") . '</td>
                </tr>';
    }
}

$html .= '
        </tbody>
    </table>

    <div class="total-container">
        <div class="total-row">
            <span class="total-label">Subtotal Diárias:</span>
            <span class="total-value">R$ ' . number_format($valorDiarias, 2, ",", ".") . '</span>
        </div>
        <div class="total-row">
            <span class="total-label">Subtotal Consumo:</span>
            <span class="total-value">R$ ' . number_format($totalConsumo, 2, ",", ".") . '</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">TOTAL GERAL:</span>
            <span class="total-value">R$ ' . number_format($valorGeral, 2, ",", ".") . '</span>
        </div>
    </div>

    <div class="footer">
        <p>Documento emitido em ' . date("d/m/Y H:i:s") . ' por ' . htmlspecialchars($h['usuario_responsavel']) . '</p>
        <br><br><br>
        <div class="signature-line">Assinatura do Hóspede</div>
        <p style="margin-top: 15px; font-size: 10px;">Obrigado por escolher a WS-HOTELARIA!</p>
    </div>
</body>
</html>';

// 8. Renderização Final e Saída
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Envia o PDF para o navegador (Attachment: false permite abrir em nova aba para imprimir)
$dompdf->stream("Nota_Fiscal_Hospedagem_{$id}.pdf", ["Attachment" => false]);
