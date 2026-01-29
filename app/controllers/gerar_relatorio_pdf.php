<?php
// Arquivo: app/controllers/gerar_relatorio_pdf.php

// 1. Limpar qualquer saída anterior para evitar corrupção do PDF
ob_start(); 

require_once(__DIR__ . '/../../vendor/autoload.php'); 
require_once(__DIR__ . '/../models/relatorioModel.php');

use Dompdf\Dompdf;
use Dompdf\Options;

// 2. Configurações rigorosas
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 
$options->set('defaultFont', 'sans-serif');
$dompdf = new Dompdf($options);

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fim = $_GET['fim'] ?? date('Y-m-t');

try {
    $model = new RelatorioModel();
    $resumo = $model->getCardsResumo($inicio, $fim);
    $movimentacao = $model->getMovimentacaoDetalhada($inicio, $fim);

    // 3. O HTML (Certifique-se de que não há espaços antes do <?php)
    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 20px; }
            .header { text-align: center; border-bottom: 2px solid #0056b3; margin-bottom: 20px; padding-bottom: 10px; }
            .card-box { width: 22%; display: inline-block; background: #f8f9fa; padding: 10px; border: 1px solid #ddd; text-align: center; margin-right: 1%; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #0056b3; color: white; padding: 8px; text-align: left; }
            td { border: 1px solid #ddd; padding: 8px; }
            .text-right { text-align: right; }
            .footer { position: fixed; bottom: 10px; width: 100%; text-align: center; font-size: 9px; color: #777; }
        </style>
    </head>
    <body>
        <div class="header">
            <h2 style="color: #0056b3; margin: 0;">FECHAMENTO FINANCEIRO - WS-HOTELARIA</h2>
            <p>Período: '.date("d/m/Y", strtotime($inicio)).' até '.date("d/m/Y", strtotime($fim)).'</p>
        </div>

        <div class="card-box"><strong>Faturamento</strong><br>R$ '.number_format($resumo['faturamento_total'], 2, ',', '.').'</div>
        <div class="card-box"><strong>Estadias</strong><br>'.$resumo['total_hospedagens'].'</div>
        <div class="card-box"><strong>Ticket Médio</strong><br>R$ '.number_format($resumo['ticket_medio'], 2, ',', '.').'</div>
        <div class="card-box"><strong>Ocupação</strong><br>'.round($resumo['ocupacao_porcentagem']).'%</div>

        <h3>Detalhamento de Movimentações</h3>
        <table>
            <thead>
                <tr>
                    <th>Data Checkout</th>
                    <th>Hóspede</th>
                    <th>Quarto</th>
                    <th class="text-right">Consumo</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>';
            foreach($movimentacao as $m) {
                $html .= '<tr>
                    <td>'.date("d/m/Y", strtotime($m['data_checkout'])).'</td>
                    <td>'.htmlspecialchars($m['hospede']).'</td>
                    <td>'.$m['quarto'].'</td>
                    <td class="text-right">R$ '.number_format($m['total_consumo'], 2, ',', '.').'</td>
                    <td class="text-right">R$ '.number_format($m['total'], 2, ',', '.').'</td>
                </tr>';
            }
    $html .= '</tbody>
        </table>
        <div class="footer">Relatório gerado em '.date("d/m/Y H:i:s").'</div>
    </body>
    </html>';

    // 4. Limpar o buffer de saída para garantir PDF limpo
    ob_end_clean();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 5. Forçar o download ou exibição correta
    // Mude "Attachment" para true se quiser que ele baixe direto em vez de abrir
    $dompdf->stream("Fechamento_{$inicio}.pdf", ["Attachment" => false]);
    exit;

} catch (Exception $e) {
    ob_end_clean();
    die("Erro ao gerar PDF: " . $e->getMessage());
}