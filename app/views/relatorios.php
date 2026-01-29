<?php
// Arquivo: app/views/relatorios.php
$pageTitle = "Relatórios Financeiros";
require_once('layout/header.php');
require_once('layout/sidebar.php');
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h2 class="text-primary"><i class="fas fa-chart-line me-2"></i>Dashboard & Relatórios</h2>

        <div class="d-flex align-items-end gap-2">
            <button id="btnExportarPDF" class="btn btn-danger btn-sm px-3">
                <i class="fas fa-file-pdf me-1"></i> Exportar Fechamento
            </button>
            <div>
                <label class="small fw-bold">Início:</label>
                <input type="date" id="filtroInicio" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>
            <div>
                <label class="small fw-bold">Fim:</label>
                <input type="date" id="filtroFim" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
            </div>
            <button id="btnFiltrar" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-filter me-1"></i> Filtrar
            </button>
        </div>
    </div>

    <div class="row px-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Faturamento Total</h6>
                    <h3 id="cardFaturamento">R$ 0,00</h3>
                    <i class="fas fa-money-bill-wave fa-2x position-absolute end-0 bottom-0 m-3 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Hospedagens Encerradas</h6>
                    <h3 id="cardTotalHosp">0</h3>
                    <i class="fas fa-door-closed fa-2x position-absolute end-0 bottom-0 m-3 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Ticket Médio</h6>
                    <h3 id="cardTicketMedio">R$ 0,00</h3>
                    <i class="fas fa-user-tag fa-2x position-absolute end-0 bottom-0 m-3 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Ocupação Atual</h6>
                    <h3 id="cardOcupacao">0%</h3>
                    <i class="fas fa-bed fa-2x position-absolute end-0 bottom-0 m-3 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row px-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold"><i class="fas fa-list me-2 text-primary"></i>Movimentação do Período</div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Checkout</th>
                                    <th>Hóspede / Quarto</th>
                                    <th class="text-end">Consumo</th>
                                    <th class="text-end">Total Geral</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaMovimentacao"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold"><i class="fas fa-trophy me-2 text-warning"></i>Produtos Mais Vendidos</div>
                <div class="card-body">
                    <div id="listaRanking"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('layout/footer.php'); ?>
<script src="../../public/js/relatorios.js"></script>