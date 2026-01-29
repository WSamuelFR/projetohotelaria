<?php
// Arquivo: app/views/zeladoria.php
$pageTitle = "Monitor de Zeladoria";
require_once('layout/header.php');
require_once('layout/sidebar.php');
?>

<div class="container-fluid mt-4">
    <div class="px-3 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-primary fw-bold mb-0">Monitor de Zeladoria</h2>
            <p class="text-muted">Gestão de limpeza e prontidão de quartos</p>
        </div>
        <button onclick="location.reload()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-sync"></i>
        </button>
    </div>

    <div class="row px-3" id="containerZeladoria">
        </div>
</div>

<?php require_once('layout/footer.php'); ?>
<script src="../../public/js/zeladoria.js"></script>