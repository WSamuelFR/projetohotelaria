<?php
// Arquivo: app/Views/Layout/_sidebar.php
// Inclui a gaveta lateral (Sidebar).
?>

<div class="sidebar" id="appSidebar">
    <h5 class="text-white text-center mb-4">Menu de Navegação</h5>
    <nav class="nav flex-column">
        <a class="sidebar-link" href="lobby.php">
            <i class="fas fa-th-large me-2"></i> Dashboard (Início)
        </a>
        <a class="sidebar-link" href="produto.php">
            <i class="fas fa-store me-2"></i> Loja
        </a>
        <a class="sidebar-link" href="cadastro.php">
            <i class="fas fa-user-plus me-2"></i> Novo Cadastro
        </a>
        <a class="sidebar-link" href="reserva.php">
            <i class="fas fa-calendar-plus me-2"></i> Nova Reserva
        </a>
        <a class="sidebar-link" href="hospedagem.php">
            <i class="fas fa-concierge-bell me-2"></i> Check-in (Hospedagem)
        </a>
        <a class="sidebar-link" href="quarto.php">
            <i class="fas fa-bed me-2"></i> Novo Quarto
        </a>
        <a class="sidebar-link" href="zeladoria.php">
            <i class="fas fa-spray-can me-2"></i> Zeladoria (Limpeza)
        </a>
        <hr class="mx-3 border-light opacity-25">
        <a class="sidebar-link" href="relatorios.php">
            <i class="fas fa-chart-line me-2"></i> Relatórios
        </a>
        <a class="sidebar-link" href="logs.php">
            <i class="fas fa-history me-2"></i> Auditoria (Logs)
        </a>
        <a class="sidebar-link" href="/configuracoes/geral">
            <i class="fas fa-cogs me-2"></i> Configurações
        </a>
    </nav>
</div>

<main id="mainContent">
    <?php
    // O corpo do arquivo de conteúdo (lobby.php, por exemplo) será injetado aqui.
    // O arquivo de conteúdo DEVE fechar a div <main> e a div <div class="container-fluid"> se usá-la.
    ?>