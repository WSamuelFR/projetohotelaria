document.addEventListener('DOMContentLoaded', () => {
    const btnFiltrar = document.getElementById('btnFiltrar');
    const inputInicio = document.getElementById('filtroInicio');
    const inputFim = document.getElementById('filtroFim');

    // Referências dos Cards
    const cardFaturamento = document.getElementById('cardFaturamento');
    const cardTotalHosp = document.getElementById('cardTotalHosp');
    const cardTicketMedio = document.getElementById('cardTicketMedio');
    const cardOcupacao = document.getElementById('cardOcupacao');

    // Referências das Tabelas/Listas
    const tabelaMovimentacao = document.getElementById('tabelaMovimentacao');
    const listaRanking = document.getElementById('listaRanking');

    /**
     * Função principal que procura os dados no Controller
     */
    const carregarDadosRelatorio = async () => {
        const inicio = inputInicio.value;
        const fim = inputFim.value;

        // Feedback visual de carregamento
        tabelaMovimentacao.innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        try {
            const res = await fetch(`../../app/controllers/relatorio_process.php?inicio=${inicio}&fim=${fim}`);
            const result = await res.json();

            if (result.success) {
                const { resumo, movimentacao, ranking } = result.data;

                // 1. Atualizar Cards
                cardFaturamento.innerText = `R$ ${parseFloat(resumo.faturamento_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                cardTotalHosp.innerText = resumo.total_hospedagens;
                cardTicketMedio.innerText = `R$ ${parseFloat(resumo.ticket_medio).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                cardOcupacao.innerText = `${Math.round(resumo.ocupacao_porcentagem)}%`;

                // 2. Renderizar Tabela de Movimentação
                renderizarTabela(movimentacao);

                // 3. Renderizar Ranking de Produtos
                renderizarRanking(ranking);
            }
        } catch (error) {
            console.error("Erro ao carregar relatório:", error);
        }
    };

    const renderizarTabela = (dados) => {
        if (dados.length === 0) {
            tabelaMovimentacao.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhuma movimentação no período.</td></tr>';
            return;
        }

        tabelaMovimentacao.innerHTML = dados.map(item => `
            <tr>
                <td class="small">${new Date(item.data_checkout).toLocaleDateString('pt-BR')}</td>
                <td>
                    <span class="fw-bold">${item.hospede}</span><br>
                    <small class="text-muted">Quarto ${item.quarto}</small>
                </td>
                <td class="text-end text-info">R$ ${parseFloat(item.total_consumo || 0).toFixed(2)}</td>
                <td class="text-end fw-bold">R$ ${parseFloat(item.total).toFixed(2)}</td>
            </tr>
        `).join('');
    };

    const renderizarRanking = (dados) => {
        if (dados.length === 0) {
            listaRanking.innerHTML = '<p class="text-center text-muted small">Sem vendas registradas.</p>';
            return;
        }

        listaRanking.innerHTML = dados.map((prod, index) => `
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark border me-2">${index + 1}º</span>
                    <div>
                        <div class="fw-bold text-dark">${prod.nome}</div>
                        <small class="text-muted">${prod.qtd_vendida} unidades vendidas</small>
                    </div>
                </div>
                <div class="text-success fw-bold">R$ ${parseFloat(prod.total_arrecadado).toFixed(2)}</div>
            </div>
        `).join('');
    };

    // Eventos
    btnFiltrar.addEventListener('click', carregarDadosRelatorio);

    document.getElementById('btnExportarPDF')?.addEventListener('click', () => {
        const inicio = inputInicio.value;
        const fim = inputFim.value;
        window.open(`../../app/controllers/gerar_relatorio_pdf.php?inicio=${inicio}&fim=${fim}`, '_blank');
    });

    // Carregamento Inicial
    carregarDadosRelatorio();
});