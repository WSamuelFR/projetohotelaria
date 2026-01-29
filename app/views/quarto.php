<?php
// Arquivo: app/Views/quarto.php (Página de Cadastro de Quarto)

$pageTitle = "Cadastro de Quarto";

require_once('layout/header.php'); 
require_once('layout/sidebar.php');
?>

    <div class="container mt-4">

        <h1 class="mb-4 text-primary"><?php echo $pageTitle; ?></h1>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5>Detalhes do Quarto</h5>
            </div>

            <div class="card-body">
                
                <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

                <form id="quartoForm" method="POST">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">Informações Básicas</legend>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="numero" class="form-label">Número do Quarto</label>
                                <input type="number" class="form-control" id="numero" name="numero" required min="1" placeholder="Ex: 101">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="room_type" class="form-label">Tipo de Climatização</label>
                                <select class="form-select" id="room_type" name="room_type" required>
                                    <option value="" selected>Selecione</option>
                                    <option value="ar-condicionado">Ar-condicionado</option>
                                    <option value="ventilador">Ventilador</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="bed_quantity" class="form-label">Capacidade (Camas)</label>
                                <select class="form-select" id="bed_quantity" name="bed_quantity" required>
                                    <option value="" selected>Selecione</option>
                                    <option value="single">Single (1)</option>
                                    <option value="duplo">Duplo (2)</option>
                                    <option value="triplo">Triplo (3)</option>
                                    <option value="quaduplo">Quádruplo (4+)</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">Status Inicial</legend>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="room_status" class="form-label">Status Ocupacional</label>
                                <select class="form-select" id="room_status" name="room_status" required>
                                    <option value="livre" selected>Livre</option>
                                    <option value="ocupado">Ocupado</option>
                                </select>
                                <small class="text-muted">Geralmente inicia como 'Livre'.</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="clean_status" class="form-label">Status de Limpeza</label>
                                <select class="form-select" id="clean_status" name="clean_status" required>
                                    <option value="limpo" selected>Limpo</option>
                                    <option value="sujo">Sujo</option>
                                </select>
                                <small class="text-muted">Geralmente inicia como 'Limpo'.</small>
                            </div>
                        </div>
                    </fieldset>


                    <div class="text-center mt-4">
                        <button type="submit" id="submitButton" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Cadastrar Quarto
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <?php
require_once('layout/footer.php');
?>

<script src="../../public/js/quarto.js"></script>