<?php
// app/views/hospedagem.php
require_once('layout/header.php');
require_once('layout/sidebar.php');
require_once('../models/cadastroModel.php');
require_once('../models/quartoModel.php');

$cadModel = new CadastroModel();
$clientes = $cadModel->listarTodos();

$qModel = new QuartoModel();
$quartos = $qModel->listarTodos(); 
?>

<div class="container-fluid mt-4">
    <h2 class="text-primary mb-4 px-3"><i class="fas fa-file-invoice me-2"></i>Check-in de Hospedagem</h2>

    <form id="hospedagemForm" class="px-3">
        <input type="hidden" id="reserva_id" value="<?= $_GET['reserva_id'] ?? '' ?>">
        <input type="hidden" id="titular_id">

        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-header bg-primary text-white fw-bold">Dados da Estadia</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="fw-bold">Hóspede Titular (Busca por Nome/CPF)</label>
                        <input type="text" list="listaClientes" id="busca_titular" class="form-control" placeholder="Digite para buscar e selecione...">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Quarto</label>
                        <select id="quarto_id" class="form-select" required>
                            <option value="" selected disabled>Selecione um quarto disponível...</option>
                            <?php foreach ($quartos as $q): ?>
                                <option value="<?= $q['quarto_id'] ?>">Quarto <?= $q['numero'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="fw-bold text-success">Preço por Pessoa (R$)</label>
                        <input type="number" id="preco_unitario" class="form-control" value="0.00" step="0.01">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3"><label>Data de Check-in</label><input type="date" id="checkin" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-3 mb-3"><label>Data de Check-out</label><input type="date" id="checkout" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label>Observações</label><input type="text" id="obs" class="form-control" placeholder="Ex: Cama extra, restrição alimentar..."></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2"></i>Acompanhantes no Quarto</span>
                        <div class="w-50">
                            <input type="text" list="listaClientes" id="busca_acompanhante" class="form-control form-control-sm" placeholder="Adicionar outro hóspede...">
                        </div>
                    </div>
                    <div class="table-responsive" style="min-height: 200px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Documento</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="gridHospedes">
                                </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light text-end">
                        <h4 class="mb-0 text-primary">Valor Total Diárias: R$ <span id="labelTotal">0.00</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg w-100 mt-2 shadow">
            <i class="fas fa-check-circle me-2"></i>Confirmar Check-in e Abrir Hospedagem
        </button>
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