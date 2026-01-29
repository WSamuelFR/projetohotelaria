<?php
// Arquivo: app/Views/edicao_quarto.php (Página de Edição de Quarto)

$pageTitle = "Edição de Quarto";

// Inclua o cabeçalho e o menu lateral do Layout Global
require_once('layout/header.php'); 
require_once('layout/sidebar.php');

// Tenta obter o ID do quarto da URL
$quarto_id = $_GET['id'] ?? null;

if (!$quarto_id) {
    // Redireciona ou exibe erro se o ID não for fornecido
    echo '<div class="container mt-5"><div class="alert alert-danger" role="alert">ID do quarto não fornecido.</div></div>';
    require_once('layout/footer.php');
    exit;
}

?>

    <div class="container mt-4">

        <h1 class="mb-4 text-primary"><?php echo $pageTitle; ?></h1>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5>Quarto #<span id="numeroQuartoDisplay">...</span> (ID: <?php echo htmlspecialchars($quarto_id); ?>)</h5>
            </div>

            <div class="card-body">
                
                <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

                <div id="loading-spinner" class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando dados...</span>
                    </div>
                    <p class="mt-3">Carregando dados do quarto...</p>
                </div>

                <form id="edicaoQuartoForm" method="POST" class="d-none">
                    <input type="hidden" id="quartoId" name="quarto_id" value="<?php echo htmlspecialchars($quarto_id); ?>">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">Informações Básicas</legend>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="numero" class="form-label">Número do Quarto</label>
                                <input type="number" class="form-control" id="numero" name="numero" required disabled>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="room_type" class="form-label">Tipo de Climatização</label>
                                <select class="form-select" id="room_type" name="room_type" required>
                                    <option value="ar-condicionado">Ar-condicionado</option>
                                    <option value="ventilador">Ventilador</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="bed_quantity" class="form-label">Capacidade (Camas)</label>
                                <select class="form-select" id="bed_quantity" name="bed_quantity" required>
                                    <option value="single">Single (1)</option>
                                    <option value="duplo">Duplo (2)</option>
                                    <option value="triplo">Triplo (3)</option>
                                    <option value="quaduplo">Quádruplo (4+)</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">Status Atual</legend>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="room_status" class="form-label">Status Ocupacional</label>
                                <select class="form-select" id="room_status" name="room_status" required>
                                    <option value="livre">Livre</option>
                                    <option value="ocupado">Ocupado</option>
                                </select>
                                <small class="text-muted">A alteração para 'Ocupado' deve ser feita via Check-in.</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="clean_status" class="form-label">Status de Limpeza</label>
                                <select class="form-select" id="clean_status" name="clean_status" required>
                                    <option value="limpo">Limpo</option>
                                    <option value="sujo">Sujo</option>
                                </select>
                                <small class="text-muted">Mantenha 'Limpo' quando pronto para uso.</small>
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-center mt-4">
                        <button type="submit" id="submitButton" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i> Atualizar Quarto
                        </button>
                        <a href="lobby.php" class="btn btn-secondary btn-lg ms-3">
                            <i class="fas fa-arrow-left me-2"></i> Voltar ao Lobby
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <?php
// 4. Inclua o rodapé e finalize a página
require_once('layout/footer.php');
?>

<script src="../../public/js/up_quarto.js"></script>