<?php
// Arquivo: app/views/logs.php
$pageTitle = "Auditoria de Sistema";
require_once('layout/header.php');
require_once('layout/sidebar.php');
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h2 class="text-primary"><i class="fas fa-history me-2"></i>Logs de Auditoria</h2>
        <button onclick="carregarLogs()" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-sync-alt me-1"></i> Atualizar
        </button>
    </div>

    <div class="card shadow-sm mx-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Detalhes</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        <tr>
                            <td colspan="5" class="text-center p-5">
                                <div class="spinner-border text-primary" role="status"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('layout/footer.php'); ?>

<script>
async function carregarLogs() {
    const tbody = document.getElementById('logTableBody');
    try {
        const response = await fetch('../controllers/log_data.php');
        const result = await response.json();

        if (result.success) {
            tbody.innerHTML = '';
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum registro encontrado.</td></tr>';
                return;
            }

            result.data.forEach(log => {
                const dataFormatada = new Date(log.created_at).toLocaleString('pt-BR');
                const badgeClass = getBadgeClass(log.acao);
                
                tbody.innerHTML += `
                    <tr>
                        <td class="small fw-bold">${dataFormatada}</td>
                        <td><i class="fas fa-user-circle me-1 text-muted"></i> ${log.nome_usuario || 'Sistema'}</td>
                        <td><span class="badge ${badgeClass}">${log.acao}</span></td>
                        <td class="small text-muted">${log.detalhes}</td>
                        <td class="small text-secondary">${log.ip_origem}</td>
                    </tr>
                `;
            });
        }
    } catch (error) {
        console.error('Erro:', error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar dados.</td></tr>';
    }
}

function getBadgeClass(acao) {
    if (acao.includes('EXCLUIR') || acao.includes('CANCELAR')) return 'bg-danger';
    if (acao.includes('NOVO') || acao.includes('CHECK-OUT')) return 'bg-success';
    if (acao.includes('EDITAR')) return 'bg-warning text-dark';
    return 'bg-info';
}

document.addEventListener('DOMContentLoaded', carregarLogs);
</script>