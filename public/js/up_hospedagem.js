document.addEventListener('DOMContentLoaded', async () => {
    const idHospedagem = document.getElementById('hospedagem_id').value;
    const form = document.getElementById('upHospedagemForm');

    try {
        const response = await fetch(`../../app/controllers/leitura_hospedagem.php?id=${idHospedagem}`);
        const result = await response.json();
        if (result.success) {
            const h = result.data;
            document.getElementById('titular_id').value = h.cadastro_id;
            document.getElementById('busca_titular').value = `${h.nome_titular} | ${h.cpf_titular}`;
            document.getElementById('quarto_id').value = h.quarto;
            document.getElementById('checkin').value = h.data_checkin;
            document.getElementById('checkout').value = h.data_checkout;
            document.getElementById('obs').value = h.observacoes;
            document.getElementById('preco_unitario').value = (parseFloat(h.total) / (parseInt(h.qtd_total_hospedes) || 1)).toFixed(2);

            // Limpa o array global e preenche com os dados do banco
            hospedesNoQuarto = [];
            window.adicionarHospede(h.cadastro_id, h.nome_titular, h.cpf_titular, 'Titular');
            
            if (h.acompanhantes) {
                h.acompanhantes.forEach(acomp => {
                    window.adicionarHospede(acomp.cadastro_id, acomp.full_name, acomp.cpf_cnpj, 'Acompanhante');
                });
            }
            
            // ATENÇÃO: Define o titular como ativo para consumo automaticamente ao abrir
            setTimeout(() => {
                window.selecionarHospedeParaConsumo(h.cadastro_id, h.nome_titular);
            }, 500);
        }
    } catch (e) { console.error("Erro ao carregar dados da hospedagem:", e); }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            id: idHospedagem,
            titular_id: document.getElementById('titular_id').value,
            quarto_id: document.getElementById('quarto_id').value,
            checkin: document.getElementById('checkin').value,
            checkout: document.getElementById('checkout').value,
            total: document.getElementById('labelTotal').innerText,
            observacoes: document.getElementById('obs').value,
            acompanhantes: hospedesNoQuarto.filter(h => h.tipo === 'Acompanhante').map(h => h.id)
        };
        const res = await fetch('../../app/controllers/edicao_hospedagem_process.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload) 
        });
        const final = await res.json();
        alert(final.message);
        if (final.success) window.location.href = 'lobby.php';
    });
});