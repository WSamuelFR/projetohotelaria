// Arquivo: public/js/zeladoria.js

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('containerZeladoria');

    const carregarQuartosSujos = async () => {
        container.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';

        try {
            // Reutilizamos o lobbyModel que você já validou
            const response = await fetch('../../app/models/lobbyModel.php');
            const data = await response.json();

            if (data.success) {
                const sujos = data.quartos.filter(q => q.clean_status === 'sujo');
                renderizar(sujos);
            }
        } catch (error) {
            container.innerHTML = '<div class="alert alert-danger">Erro ao carregar dados.</div>';
        }
    };

    const renderizar = (quartos) => {
        if (quartos.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-check-double fa-4x text-success mb-3"></i>
                    <h4 class="text-muted">Todos os quartos estão limpos!</h4>
                </div>`;
            return;
        }

        container.innerHTML = quartos.map(q => `
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 border-start border-danger border-5">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <h3 class="fw-bold mb-0">#${q.numero}</h3>
                            <span class="badge bg-danger">SUJO</span>
                        </div>
                        <p class="text-secondary small mt-2">${q.tipo}</p>
                        <button onclick="confirmarLimpeza(${q.id}, '${q.numero}')" class="btn btn-success w-100 mt-3 btn-lg">
                            <i class="fas fa-broom me-2"></i>Concluir Limpeza
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    };

    window.confirmarLimpeza = async (id, numero) => {
        if (!confirm(`Confirmar que o quarto ${numero} está limpo?`)) return;

        try {
            const response = await fetch('../controllers/zeladoria_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const res = await response.json();

            if (res.success) {
                carregarQuartosSujos();
            } else {
                alert("Erro: " + res.message);
            }
        } catch (e) {
            alert("Erro na conexão.");
        }
    };

    carregarQuartosSujos();
});