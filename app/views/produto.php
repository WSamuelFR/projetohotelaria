<?php
$pageTitle = "Gestão da Loja";
require_once('layout/header.php'); 
require_once('layout/sidebar.php');
?>

<div class="container mt-4">
    <h1 class="mb-4 text-primary"><i class="fas fa-store me-2"></i>Gestão da Loja (Produtos)</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Cadastrar Novo Produto</h5>
        </div>
        <div class="card-body">
            <form id="produtoForm" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome do Produto</label>
                    <input type="text" id="nome" class="form-control" placeholder="Ex: Água Mineral 500ml" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Preço de Venda (R$)</label>
                    <input type="number" id="preco" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estoque Inicial</label>
                    <input type="number" id="estoque" class="form-control" value="0" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i> Adicionar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title text-secondary">Produtos em Estoque</h5>
            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Estoque Atual</th>
                            <th class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="produtoTableBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('layout/footer.php'); ?>
<script src="../../public/js/produto.js"></script>