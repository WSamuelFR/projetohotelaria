<?php
// Arquivo: app/Views/Layout/footer.php
// Inclui o Footer, scripts e fecha o <body> e <html>.
?>
</main>

<footer class="bg-light text-center py-3 mt-5">
    <p class="mb-0">© 2025 HOTELARIA. Todos os direitos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('appSidebar');
        const toggleButton = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('mainContent');
        const logoutButton = document.getElementById('logoutButton');

        // --- Menu Lateral (Gaveta) ---
        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            // Adiciona margem ao conteúdo principal quando o menu está aberto
            mainContent.classList.toggle('sidebar-opened-margin');
        });

        // --- Logout Real ---
        logoutButton.addEventListener('click', () => {
            // Em um ambiente PHP real, você deve destruir a sessão antes de redirecionar.
            // O caminho abaixo sobe dois níveis para sair de app/views e chegar em public/index.php.
            alert('Sessão encerrada com sucesso! Redirecionando para a tela de login...');
            window.location.href = '../../public/index.php'; 
        });
    });
</script>
</body>

</html>