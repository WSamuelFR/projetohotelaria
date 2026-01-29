<?php
// app/views/up_hospedagem.php
$pageTitle = "Editar Hospedagem";
require_once('layout/header.php');
require_once('layout/sidebar.php');
require_once('../models/hospedagemModel.php');
require_once('../models/quartoModel.php');
require_once('../models/cadastroModel.php');

$hospedagem_id = $_GET['id'] ?? null;

$hModel = new HospedagemModel();
$qModel = new QuartoModel();
$cadModel = new CadastroModel();

$dadosHospedagem = $hModel->getHospedagemById($hospedagem_id);
$quartoAtualId = $dadosHospedagem['quarto'] ?? null;
$situacao = $dadosHospedagem['situacao'] ?? 'ativa';

$quartos = $qModel->listarTodos($quartoAtualId);
$clientes = $cadModel->listarTodos();
?>

<style>
    /* Se a hospedagem estiver encerrada, escondemos o botão de excluir e a busca de acompanhantes */
    .modo-leitura .btn-danger,
    .modo-leitura #busca_acompanhante {
        display: none !important;
    }

    /* Removemos a interação com inputs no modo leitura */
    .modo-leitura input,
    .modo-leitura select {
        pointer-events: none;
        background-color: #f8f9fa;
    }
</style>

<div class="container-fluid mt-4">
    <h2 class="text-primary mb-4 px-3"><i class="fas fa-edit me-2"></i>Editar Hospedagem #<?= htmlspecialchars($hospedagem_id) ?></h2>

    <form id="upHospedagemForm" class="px-3 <?= ($situacao === 'encerrada') ? 'modo-leitura' : '' ?>">
        <input type="hidden" id="hospedagem_id" value="<?= htmlspecialchars($hospedagem_id) ?>">

        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning text-dark fw-bold">Configuração da Estadia</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="fw-bold">Titular</label>
                        <input type="text" list="listaClientes" id="busca_titular" class="form-control">
                        <input type="hidden" id="titular_id">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Quarto</label>
                        <select id="quarto_id" class="form-select" required>
                            <?php foreach ($quartos as $q): ?>
                                <option value="<?= $q['quarto_id'] ?>" <?= ($q['quarto_id'] == $quartoAtualId) ? 'selected' : '' ?>>
                                    Quarto <?= htmlspecialchars($q['numero']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold text-success">Preço por Pessoa (R$)</label>
                        <input type="number" id="preco_unitario" class="form-control" step="0.01">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label>Entrada</label><input type="date" id="checkin" class="form-control"></div>
                    <div class="col-md-3 mb-3"><label>Saída</label><input type="date" id="checkout" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label>Notas</label><input type="text" id="obs" class="form-control"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4 h-100">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2"></i>Hóspedes Atuais</span>
                        <input type="text" list="listaClientes" id="busca_acompanhante" class="form-control form-control-sm w-50" placeholder="Adicionar outro...">
                    </div>
                    <div class="table-responsive" style="min-height: 250px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Documento</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="gridHospedes"></tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light text-end">
                        <h4 class="mb-0">Total Diárias: R$ <span id="labelTotal">0.00</span></h4>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm mb-4 h-100 border-primary">
                    <div class="card-header bg-primary text-white p-0">
                        <ul class="nav nav-tabs border-bottom-0" id="consumoTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active text-dark" id="consumo-aba" data-bs-toggle="tab" data-bs-target="#aba-consumo" type="button" role="tab">
                                    <i class="fas fa-list-ul me-2"></i>Consumo: <span id="nomeHospedeAtivo" class="fw-bold">...</span>
                                </button>
                            </li>

                            <?php if ($situacao === 'ativa'): ?>
                                <li class="nav-item">
                                    <button class="nav-link text-dark" id="loja-aba" data-bs-toggle="tab" data-bs-target="#aba-loja" type="button" role="tab">
                                        <i class="fas fa-shopping-cart me-2"></i>Loja
                                    </button>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active p-3" id="aba-consumo" role="tabpanel">
                            <div class="table-responsive" style="max-height: 250px;">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Qtd</th>
                                            <th>Valor</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaConsumoHospede"></tbody>
                                </table>
                            </div>
                        </div>

                        <?php if ($situacao === 'ativa'): ?>
                            <div class="tab-pane fade p-3" id="aba-loja" role="tabpanel">
                                <div class="table-responsive" style="max-height: 250px;">
                                    <table class="table table-sm align-middle">
                                        <tbody id="catalogoLoja"></tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <?php if ($situacao === 'ativa'): ?>
                <button type="submit" class="btn btn-success btn-lg flex-grow-1 shadow">
                    <i class="fas fa-save me-2"></i>Salvar Alterações
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary btn-lg flex-grow-1 shadow" disabled>
                    <i class="fas fa-lock me-2"></i>Hospedagem Encerrada (Consulta)
                </button>
            <?php endif; ?>

            <button type="button" onclick="gerarPDF(<?= $hospedagem_id ?>)" class="btn btn-primary btn-lg shadow">
                <i class="fas fa-print me-2"></i>Imprimir Extrato
            </button>

            <a href="lobby.php" class="btn btn-secondary btn-lg">Voltar</a>
        </div>
    </form>
</div>

<datalist id="listaClientes">
    <?php foreach ($clientes as $c): ?>
        <option value="<?= htmlspecialchars($c['full_name']) ?> | <?= htmlspecialchars($c['cpf_cnpj']) ?>"
            data-id="<?= $c['id'] ?>"
            data-nome="<?= htmlspecialchars($c['full_name']) ?>"
            data-doc="<?= htmlspecialchars($c['cpf_cnpj']) ?>">
        </option>
    <?php endforeach; ?>
</datalist>

<?php require_once('layout/footer.php'); ?>
<script src="../../public/js/hospedagem.js"></script>
<script src="../../public/js/up_hospedagem.js"></script>