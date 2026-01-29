// public/js/hospedagem.js

let hospedesNoQuarto = [];
let hospedeAtivoId = null;

/**
 * Função Global para Gerar o PDF (Nota Fiscal)
 * Abre o extrato em uma nova aba.
 */
window.gerarPDF = (id) => {
    if (!id) {
        alert("ID da hospedagem não identificado.");
        return;
    }
    window.open(`../../app/controllers/gerar_extrato_pdf.php?id=${id}`, '_blank');
};

/**
 * Calcula o valor total multiplicando a quantidade de hóspedes pelo preço unitário
 */
const calcularTotalHospedagem = () => {
    const precoInput = document.getElementById('preco_unitario');
    const labelTotal = document.getElementById('labelTotal');
    if (!precoInput || !labelTotal) return;

    const precoPessoa = parseFloat(precoInput.value) || 0;
    const total = hospedesNoQuarto.length * precoPessoa;
    labelTotal.innerText = total.toFixed(2);
};

/**
 * Renderiza a tabela de hóspedes (Mini Tela 1)
 */
window.renderizarTabela = () => {
    const grid = document.getElementById('gridHospedes');
    if (!grid) return;
    
    grid.innerHTML = '';
    hospedesNoQuarto.forEach((h, index) => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', h.id);
        tr.style.cursor = 'pointer';
        
        if (h.id == hospedeAtivoId) tr.classList.add('table-warning', 'fw-bold');
        else if (h.tipo === 'Titular') tr.classList.add('table-info');
        
        tr.onclick = () => window.selecionarHospedeParaConsumo(h.id, h.nome);
        
        tr.innerHTML = `
            <td>${h.nome}</td>
            <td>${h.doc}</td>
            <td><span class="badge ${h.tipo === 'Titular' ? 'bg-primary' : 'bg-info'}">${h.tipo}</span></td>
            <td class="text-center">
                <button type="button" onclick="event.stopPropagation(); removerHospede(${index})" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;
        grid.appendChild(tr);
    });
    
    calcularTotalHospedagem();
};

/**
 * Adiciona um hóspede ao array global
 */
window.adicionarHospede = (id, nome, doc, tipo) => {
    if (!id || id === "undefined" || hospedesNoQuarto.some(h => h.id == id)) return;
    
    if (tipo === 'Titular') {
        hospedesNoQuarto = hospedesNoQuarto.filter(h => h.tipo !== 'Titular');
        hospedesNoQuarto.unshift({ id, nome, doc, tipo });
        
        const inputTitularId = document.getElementById('titular_id');
        if (inputTitularId) inputTitularId.value = id;
    } else {
        hospedesNoQuarto.push({ id, nome, doc, tipo });
    }
    
    window.renderizarTabela();
};

window.removerHospede = (index) => {
    hospedesNoQuarto.splice(index, 1);
    window.renderizarTabela();
};

// --- FUNÇÃO DE COMPRA CORRIGIDA ---
window.confirmarCompra = async (produtoId, preco) => {
    if (!hospedeAtivoId) { 
        alert("Selecione um hóspede no grid lateral antes de realizar a compra."); 
        return; 
    }
    
    const qtdInput = document.getElementById(`qtd_${produtoId}`);
    const hospIdInput = document.getElementById('hospedagem_id'); // ID da estadia atual
    
    if (!hospIdInput || !hospIdInput.value) {
        alert("Erro: ID da hospedagem não encontrado.");
        return;
    }

    const payload = { 
        hospedagem_id: parseInt(hospIdInput.value), 
        hospede_id: parseInt(hospedeAtivoId), 
        produto_id: parseInt(produtoId), 
        quantidade: parseInt(qtdInput.value), 
        preco_unitario: parseFloat(preco) 
    };

    try {
        const res = await fetch('../../app/controllers/hospedagem_process.php?action=lancar_consumo', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload) 
        });
        
        const result = await res.json();
        
        if (result.success) { 
            alert("Venda realizada com sucesso!");
            fetchConsumoHospede(hospedeAtivoId); 
        } else {
            alert("Erro ao lançar venda: " + result.message);
        }
    } catch (e) { 
        console.error("Erro na venda:", e); 
        alert("Erro de comunicação com o servidor.");
    }
};

window.selecionarHospedeParaConsumo = (id, nome) => {
    const labelNome = document.getElementById('nomeHospedeAtivo');
    if (!labelNome) return;

    hospedeAtivoId = id;
    labelNome.innerText = nome;
    document.querySelectorAll('#gridHospedes tr').forEach(row => row.classList.remove('table-warning', 'fw-bold'));
    const rowAtiva = document.querySelector(`#gridHospedes tr[data-id="${id}"]`);
    if (rowAtiva) rowAtiva.classList.add('table-warning', 'fw-bold');
    fetchConsumoHospede(id);
};

const fetchConsumoHospede = async (hospedeId) => {
    const hospId = document.getElementById('hospedagem_id')?.value;
    const tbody = document.getElementById('listaConsumoHospede');
    if (!hospId || !hospedeId || !tbody) return;

    try {
        const res = await fetch(`../../app/controllers/leitura_hospedagem.php?action=get_consumo&hospedagem_id=${hospId}&hospede_id=${hospedeId}`);
        const result = await res.json();
        tbody.innerHTML = '';
        if (result.success && result.data.length > 0) {
            result.data.forEach(c => {
                const subtotal = c.quantidade * c.preco_unitario_pago;
                tbody.innerHTML += `<tr><td>${c.nome_produto}</td><td>${c.quantidade}</td><td>R$ ${parseFloat(c.preco_unitario_pago).toFixed(2)}</td><td class="text-end">R$ ${subtotal.toFixed(2)}</td></tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sem consumo registrado.</td></tr>';
        }
    } catch (e) { console.error(e); }
};

const carregarCatalogoLoja = async () => {
    const tbody = document.getElementById('catalogoLoja');
    if (!tbody) return;
    try {
        const res = await fetch('../../app/controllers/produto_process.php');
        const result = await res.json();
        tbody.innerHTML = '';
        if (result.success) {
            result.data.forEach(p => {
                tbody.innerHTML += `<tr><td>${p.nome}</td><td>R$ ${parseFloat(p.preco_venda).toFixed(2)}</td><td><input type="number" id="qtd_${p.produto_id}" class="form-control form-control-sm" value="1" min="1"></td><td><button type="button" class="btn btn-sm btn-success" onclick="confirmarCompra(${p.produto_id}, ${p.preco_venda})"><i class="fas fa-cart-plus"></i></button></td></tr>`;
            });
        }
    } catch (e) { console.error(e); }
};

document.addEventListener('DOMContentLoaded', () => {
    carregarCatalogoLoja();

    const inputTitular = document.getElementById('busca_titular');
    const inputAcompanhante = document.getElementById('busca_acompanhante');
    const datalist = document.getElementById('listaClientes');
    const form = document.getElementById('hospedagemForm');

    const watchInput = (input, tipo) => {
        if (!input) return;
        input.addEventListener('input', () => {
            const val = input.value;
            const option = Array.from(datalist.options).find(opt => opt.value === val);
            if (option) {
                window.adicionarHospede(option.dataset.id, option.dataset.nome, option.dataset.doc, tipo);
            }
        });
    };

    watchInput(inputTitular, 'Titular');
    watchInput(inputAcompanhante, 'Acompanhante');

    document.getElementById('preco_unitario')?.addEventListener('input', calcularTotalHospedagem);

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (hospedesNoQuarto.length === 0) {
                alert("Adicione pelo menos o hóspede titular.");
                return;
            }

            const payload = {
                reserva_id: document.getElementById('reserva_id')?.value || '',
                quarto_id: document.getElementById('quarto_id').value,
                preco_unitario: document.getElementById('preco_unitario').value,
                checkin: document.getElementById('checkin').value,
                checkout: document.getElementById('checkout').value,
                obs: document.getElementById('obs').value,
                hospedes: hospedesNoQuarto 
            };

            try {
                const res = await fetch('../../app/controllers/hospedagem_process.php?action=salvar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();
                alert(result.message);

                if (result.success) {
                    window.location.href = 'lobby.php'; 
                }
            } catch (error) {
                console.error("Erro:", error);
                alert("Erro de comunicação com o servidor.");
            }
        });
    }
});