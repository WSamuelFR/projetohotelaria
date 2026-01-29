<?php
// Arquivo: app/controllers/gerar_ficha_cliente_pdf.php

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../models/cadastroModel.php');

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

$id = $_GET['id'] ?? null;
if (!$id) die("ID do cliente não fornecido.");

$model = new CadastroModel();
$c = $model->getClientDataById((int)$id);

if (!$c) die("Cliente não encontrado.");

// Formatação de datas
$nascimento = !empty($c['birth_date']) ? date("d/m/Y", strtotime($c['birth_date'])) : 'N/A';
$criado_em = date("d/m/Y H:i", strtotime($c['created_at']));

$html = '
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #0056b3; text-transform: uppercase; }
        .section { background: #f4f4f4; padding: 5px 10px; font-weight: bold; border-left: 5px solid #0056b3; margin-top: 20px; }
        .row { margin: 10px 0; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .label { font-weight: bold; color: #555; width: 150px; display: inline-block; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #888; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Ficha Cadastral do Cliente</div>
        <div>WS-HOTELARIA | Sistema de Gestão</div>
    </div>

    <div class="section">1. INFORMAÇÕES PESSOAIS</div>
    <div class="row"><span class="label">Nome/Razão Social:</span> ' . htmlspecialchars($c['full_name']) . '</div>
    <div class="row"><span class="label">CPF/CNPJ:</span> ' . htmlspecialchars($c['cpf_cnpj']) . '</div>';

if ($c['tipo'] === 'hospede') {
    $html .= '
        <div class="row"><span class="label">RG:</span> ' . ($c['rg'] ?? 'N/A') . '</div>
        <div class="row"><span class="label">Data Nascimento:</span> ' . $nascimento . '</div>
        <div class="row"><span class="label">Gênero:</span> ' . ucfirst($c['gender'] ?? 'N/A') . '</div>';
} else {
    $html .= '
        <div class="row"><span class="label">Data de Fundação:</span> ' . ($c['data_fundacao'] ?? 'N/A') . '</div>
        <div class="row"><span class="label">Tel. Comercial:</span> ' . ($c['telefone_comercial'] ?? 'N/A') . '</div>';
}

$html .= '
    <div class="section">2. CONTATO</div>
    <div class="row"><span class="label">E-mail:</span> ' . htmlspecialchars($c['email']) . '</div>
    <div class="row"><span class="label">Telefone:</span> ' . htmlspecialchars($c['phone']) . '</div>

    <div class="section">3. ENDEREÇO</div>
    <div class="row"><span class="label">Rua:</span> ' . htmlspecialchars($c['street']) . ', Nº ' . htmlspecialchars($c['address_number']) . '</div>
    <div class="row"><span class="label">Bairro:</span> ' . htmlspecialchars($c['neighborhood']) . '</div>
    <div class="row"><span class="label">Cidade/Estado:</span> ' . htmlspecialchars($c['city']) . ' / ' . htmlspecialchars($c['state']) . '</div>
    <div class="row"><span class="label">CEP / País:</span> ' . ($c['cep'] ?? 'N/A') . ' - ' . htmlspecialchars($c['current_country']) . '</div>

    <div class="footer">
        Ficha gerada em ' . date("d/m/Y H:i:s") . ' | Cadastro realizado em: ' . $criado_em . '
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Ficha_Cliente_{$id}.pdf", ["Attachment" => false]);
