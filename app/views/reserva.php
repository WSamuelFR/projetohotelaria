<?php
// Arquivo: app/Views/reserva.php (Página de Cadastro de Reserva)

$pageTitle = "Nova Reserva";

// Inclua o cabeçalho e o menu lateral do Layout Global
require_once('layout/header.php'); 
require_once('layout/sidebar.php');
?>

    <div class="container mt-4">

        <h1 class="mb-4 text-primary"><?php echo $pageTitle; ?></h1>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5>Detalhes da Reserva</h5>
            </div>

            <div class="card-body">
                
                <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

                <form id="reservaForm" method="POST">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">1. Cliente e Quarto</legend>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cliente_search" class="form-label">Buscar Cliente (Nome ou CPF/CNPJ)</label>
                                <input class="form-control" list="clienteDataListOptions" id="cliente_search" placeholder="Digite para buscar..." required>
                                <datalist id="clienteDataListOptions">
                                    </datalist>
                                <input type="hidden" id="cadastro_cpf_cnpj" name="cadastro" required>
                                <small class="text-muted" id="selectedClientInfo">Cliente selecionado: N/A</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quarto_search" class="form-label">Buscar Quarto (Número ou Status)</label>
                                <input class="form-control" list="quartoDataListOptions" id="quarto_search" placeholder="Digite para buscar..." required>
                                <datalist id="quartoDataListOptions">
                                    </datalist>
                                <input type="hidden" id="quarto_numero" name="quarto" required>
                                <small class="text-muted" id="selectedQuartoInfo">Quarto selecionado: N/A</small>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">2. Período</legend>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_checkin" class="form-label">Data de Check-in</label>
                                <input type="date" class="form-control" id="data_checkin" name="data_checkin" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="data_checkout" class="form-label">Data de Check-out</label>
                                <input type="date" class="form-control" id="data_checkout" name="data_checkout" required>
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-center mt-4">
                        <button type="submit" id="submitButton" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check me-2"></i> Confirmar Reserva
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <?php
// 4. Inclua o rodapé e finalize a página
require_once('layout/footer.php');
?>

<script src="../../public/js/reserva.js"></script>