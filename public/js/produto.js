document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('produtoForm');
    const tableBody = document.getElementById('produtoTableBody');

    const fetchProdutos = async () => {
        const res = await fetch('../../app/controllers/produto_process.php');
        const result = await res.json();

        if (result.success) {
            tableBody.innerHTML = '';
            result.data.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>#${p.produto_id}</td>
                    <td>${p.nome}</td>
                    <td>R$ ${parseFloat(p.preco_venda).toFixed(2)}</td>
                    <td>${p.estoque_atual} un</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger" onclick="excluirProduto(${p.produto_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            nome: document.getElementById('nome').value,
            preco: parseFloat(document.getElementById('preco').value),
            estoque: parseInt(document.getElementById('estoque').value)
        };

        const res = await fetch('../../app/controllers/produto_process.php', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        alert(result.message);
        if (result.success) {
            form.reset();
            fetchProdutos();
        }
    });

    /**
 * Função global para excluir um produto com confirmação.
 */
    window.excluirProduto = async (id) => {
        if (!confirm("Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.")) {
            return;
        }

        try {
            const response = await fetch(`../../app/controllers/produto_process.php?id=${id}`, {
                method: 'DELETE'
            });

            const result = await response.json();
            alert(result.message);

            if (result.success) {
                // Chama a função de listagem que já existe no seu produto.js para atualizar a tela
                if (typeof fetchProdutos === 'function') {
                    fetchProdutos();
                } else {
                    location.reload(); // Fallback caso a função não esteja no escopo global
                }
            }
        } catch (error) {
            console.error('Erro ao excluir produto:', error);
            alert('Erro de comunicação com o servidor.');
        }
    };

    fetchProdutos();
});