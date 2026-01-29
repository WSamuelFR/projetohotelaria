document.addEventListener('DOMContentLoaded', () => {
    // Referências das tabelas
    const cadastroTableBody = document.getElementById('cadastroTableBody');
    const quartoTableBody = document.getElementById('quartoTableBody');
    const reservaTableBody = document.getElementById('reservaTableBody');
    const hospTableBody = document.getElementById('hospedagemTableBody');

    // Filtros globais
    let currentHospFilter = 'ativa';
    let currentQuartoFilter = 'todos';
    let currentReservaFilter = 'pendente';
    let idHospedagemParaFinalizar = null;

    /**
     * Função principal de busca de dados
     */
    const fetchDataAndRender = async () => {
        const activeTab = document.querySelector('.nav-link.active')?.id;
        let searchTerm = '';

        if (activeTab === 'cadastros-tab') searchTerm = document.getElementById('cadastroSearchInput')?.value || '';
        else if (activeTab === 'quartos-tab') searchTerm = document.getElementById('quartoSearchInput')?.value || '';
        else if (activeTab === 'reservas-tab') searchTerm = document.getElementById('reservaSearchInput')?.value || '';
        else if (activeTab === 'hospedagens-tab') searchTerm = document.getElementById('hospedagemSearchInput')?.value || '';

        [cadastroTableBody, quartoTableBody, reservaTableBody, hospTableBody].forEach(el => {
            if (el) el.innerHTML = `<tr><td colspan="10" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>`;
        });

        try {
            const url = `../../app/models/lobbyModel.php?search=${encodeURIComponent(searchTerm)}&status=${currentHospFilter}&reservaStatus=${currentReservaFilter}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                renderCadastros(data.cadastros);
                renderQuartos(data.quartos);
                renderReservas(data.reservas);
                renderHospedagens(data.hospedagens);
            }
        } catch (error) { console.error('Erro ao carregar lobby:', error); }
    };

    const renderCadastros = (cadastros) => {
        if (!cadastroTableBody) return;
        cadastroTableBody.innerHTML = cadastros.length ? '' : '<tr><td colspan="4" class="text-center">Nenhum cadastro encontrado.</td></tr>';
        cadastros.forEach(c => {
            cadastroTableBody.innerHTML += `
            <tr>
                <td>${c.nome_cliente}</td>
                <td>${c.cpf_cnpj}</td>
                <td>${c.tipo}</td>
                <td class="text-center">
                    <a href="up_cadastro.php?id=${c.cadastro_id}" class="btn btn-sm btn-primary" title="Editar"><i class="fas fa-user-edit"></i></a>
                    <button onclick="gerarPDFCliente(${c.cadastro_id})" class="btn btn-sm btn-danger ms-1" title="Gerar Ficha PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                </td>
            </tr>`;
        });
    };

    const renderQuartos = (quartos) => {
        if (!quartoTableBody) return;
        quartoTableBody.innerHTML = '';
        const filtrados = quartos.filter(q => {
            if (currentQuartoFilter === 'todos') return true;
            if (currentQuartoFilter === 'livre') return q.status_principal === 'livre' && q.clean_status === 'limpo';
            if (currentQuartoFilter === 'ocupado') return q.status_principal === 'ocupado';
            if (currentQuartoFilter === 'sujo') return q.clean_status === 'sujo';
            return true;
        });
        filtrados.forEach(q => {
            const rowClass = q.status_principal === 'ocupado' ? 'status-ocupado' : (q.clean_status === 'sujo' ? 'status-sujo' : 'status-livre');
            const btnDisable = (q.status_principal === 'ocupado' || q.clean_status === 'limpo') ? 'disabled' : '';
            quartoTableBody.innerHTML += `<tr class="${rowClass}"><td>#${q.numero}</td><td>${q.tipo}</td><td><span class="badge ${q.status_principal === 'ocupado' ? 'badge-ocupado' : 'badge-limpo'}">${q.status_display.toUpperCase()}</span></td><td><span class="badge ${q.clean_status === 'sujo' ? 'badge-sujo' : 'badge-limpo'}">${q.clean_status.toUpperCase()}</span></td><td>${q.cliente_atual || '---'}</td><td class="text-center"><button onclick="realizarLimpeza(${q.id})" class="btn btn-sm btn-outline-success" ${btnDisable}><i class="fas fa-broom"></i> Limpeza</button><a href="up_quarto.php?id=${q.id}" class="btn btn-sm btn-primary ms-1"><i class="fas fa-cog"></i></a></td></tr>`;
        });
    };

    const renderReservas = (reservas) => {
        if (!reservaTableBody) return;
        reservaTableBody.innerHTML = reservas.length ? '' : '<tr><td colspan="6" class="text-center">Nenhuma reserva encontrada.</td></tr>';
        reservas.forEach(r => {
            let badgeClass = r.situacao === 'cancelado' ? 'bg-danger' : (r.situacao === 'concluida' ? 'bg-success' : 'bg-primary');
            reservaTableBody.innerHTML += `<tr><td>${r.nome_cliente}</td><td>${r.cpf_cnpj}</td><td>${r.data_checkin}</td><td>${r.data_checkout}</td><td><span class="badge ${badgeClass}">${r.situacao.toUpperCase()}</span></td><td class="text-center">${r.situacao === 'pendente' ? `<a href="hospedagem.php?reserva_id=${r.reserva_id}" class="btn btn-sm btn-success" title="Check-in"><i class="fas fa-sign-in-alt"></i></a><button onclick="cancelarReserva(${r.reserva_id})" class="btn btn-sm btn-danger ms-1" title="Cancelar"><i class="fas fa-ban"></i></button>` : '<span class="text-muted">---</span>'}</td></tr>`;
        });
    };

    const renderHospedagens = (hospedagens) => {
        if (!hospTableBody) return;
        hospTableBody.innerHTML = hospedagens.length ? '' : '<tr><td colspan="6" class="text-center">Nenhuma hospedagem encontrada.</td></tr>';
        hospedagens.forEach(h => {
            const btnFinalizar = h.situacao === 'ativa' ? `<button onclick="finalizarEstadia(${h.hospedagem_id})" class="btn btn-sm btn-danger ms-1" title="Check-out"><i class="fas fa-sign-out-alt"></i></button>` : '';

            hospTableBody.innerHTML += `
            <tr>
                <td>${h.nome_hospede}</td>
                <td>Quarto ${h.numero_quarto}</td>
                <td>${h.data_checkin} a ${h.data_checkout}</td>
                <td>R$ ${h.total}</td>
                <td><span class="badge ${h.situacao === 'ativa' ? 'bg-success' : 'bg-secondary'}">${h.situacao.toUpperCase()}</span></td>
                <td class="text-center">
                    <a href="up_hospedagem.php?id=${h.hospedagem_id}" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                    
                    <button onclick="gerarPDF(${h.hospedagem_id})" class="btn btn-sm btn-info ms-1" title="Imprimir Extrato">
                        <i class="fas fa-print"></i>
                    </button>

                    ${btnFinalizar}
                </td>
            </tr>`;
        });
    };

    // --- FUNÇÕES DE AÇÃO ---

    window.realizarLimpeza = async (id) => {
        if (!confirm("Confirmar limpeza deste quarto?")) return;
        const res = await fetch('../../app/controllers/quarto_process.php?action=limpar_quarto', { method: 'POST', body: JSON.stringify({ id }) });
        if ((await res.json()).success) fetchDataAndRender();
    };

    window.cancelarReserva = async (id) => {
        if (!confirm("Deseja cancelar esta reserva?")) return;
        const res = await fetch('../../app/controllers/excluir_reserva_process.php', { method: 'POST', body: JSON.stringify({ id }) });
        if ((await res.json()).success) fetchDataAndRender();
    };

    /**
     * REFINADO: Agora mostra acompanhantes e lista detalhada de produtos
     */
    window.finalizarEstadia = async (id) => {
        idHospedagemParaFinalizar = id;

        try {
            const res = await fetch(`../../app/controllers/leitura_hospedagem.php?id=${id}`);
            const result = await res.json();

            if (result.success) {
                const h = result.data;

                document.getElementById('checkoutNome').innerText = h.nome_titular;

                let nomesAcompanhantes = h.acompanhantes && h.acompanhantes.length > 0
                    ? h.acompanhantes.map(a => a.full_name).join(', ')
                    : 'Nenhum acompanhante.';

                document.getElementById('checkoutNome').innerHTML = `${h.nome_titular} <br><small class="text-muted">Acomp: ${nomesAcompanhantes}</small>`;
                document.getElementById('checkoutQuarto').innerText = h.numero_quarto;
                document.getElementById('checkoutPeriodo').innerText = `${h.data_checkin} até ${h.data_checkout}`;
                document.getElementById('checkoutTotalDiarias').innerText = `R$ ${parseFloat(h.total).toFixed(2)}`;

                const resConsumo = await fetch(`../../app/controllers/leitura_hospedagem.php?action=get_consumo_total&hospedagem_id=${id}`);
                const resultConsumo = await resConsumo.json();

                let htmlConsumo = '';
                let totalConsumoVal = 0;

                if (resultConsumo.success && resultConsumo.data.length > 0) {
                    resultConsumo.data.forEach(c => {
                        const sub = c.quantidade * c.preco_unitario_pago;
                        totalConsumoVal += sub;
                        htmlConsumo += `<tr>
                            <td>${c.nome_produto} <br><small class="text-muted">(${c.nome_cliente})</small></td>
                            <td>${c.quantidade}</td>
                            <td>R$ ${parseFloat(c.preco_unitario_pago).toFixed(2)}</td>
                            <td class="text-end">R$ ${sub.toFixed(2)}</td>
                        </tr>`;
                    });
                } else {
                    htmlConsumo = '<tr><td colspan="4" class="text-center text-muted">Nenhum consumo registrado.</td></tr>';
                }

                document.getElementById('checkoutListaConsumo').innerHTML = htmlConsumo;
                document.getElementById('checkoutTotalConsumo').innerText = `R$ ${totalConsumoVal.toFixed(2)}`;
                document.getElementById('checkoutTotalGeral').innerText = `R$ ${(parseFloat(h.total) + totalConsumoVal).toFixed(2)}`;

                const myModal = new bootstrap.Modal(document.getElementById('modalCheckout'));
                myModal.show();
            }
        } catch (e) {
            console.error(e);
            alert("Erro ao carregar dados financeiros.");
        }
    };

    // --- NOVA LÓGICA: BOTÃO DE IMPRESSÃO ---
    document.getElementById('btnImprimirCheckout')?.addEventListener('click', () => {
        if (idHospedagemParaFinalizar) {
            window.open(`../../app/controllers/gerar_extrato_pdf.php?id=${idHospedagemParaFinalizar}`, '_blank');
        }
    });

    document.getElementById('btnConfirmarCheckout')?.addEventListener('click', async () => {
        if (!idHospedagemParaFinalizar) return;
        const res = await fetch('../../app/controllers/finalizar_hospedagem_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idHospedagemParaFinalizar })
        });
        const result = await res.json();
        alert(result.message);
        if (result.success) {
            const modalEl = document.getElementById('modalCheckout');
            bootstrap.Modal.getInstance(modalEl).hide();
            fetchDataAndRender();
        }
    });

    // LISTENERS DE FILTRO E BUSCA
    document.querySelectorAll('.filter-reserva, .filter-hosp, .filter-quarto').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (this.classList.contains('filter-reserva')) currentReservaFilter = this.dataset.status;
            else if (this.classList.contains('filter-hosp')) currentHospFilter = this.dataset.status;
            else if (this.classList.contains('filter-quarto')) currentQuartoFilter = this.dataset.filter;
            fetchDataAndRender();
        });
    });

    ['cadastroSearchButton', 'quartoSearchButton', 'reservaSearchButton', 'hospedagemSearchButton'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.onclick = () => fetchDataAndRender();
    });

    fetchDataAndRender();
});

// FUNÇÃO GLOBAL PARA USO NA EDIÇÃO (up_hospedagem.php)
window.gerarPDF = (id) => {
    window.open(`../../app/controllers/gerar_extrato_pdf.php?id=${id}`, '_blank');
};

window.gerarPDFCliente = (id) => {
    window.open(`../../app/controllers/gerar_ficha_cliente_pdf.php?id=${id}`, '_blank');
};