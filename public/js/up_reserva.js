document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const form = document.getElementById('upReservaForm');

    // Carregar dados atuais
    const response = await fetch(`../../app/controllers/leitura_reserva.php?id=${id}`);
    const reserva = await response.json();

    if (reserva) {
        document.getElementById('nome_cliente').value = reserva.nome_cliente;
        document.getElementById('quarto').value = reserva.quarto;
        document.getElementById('data_checkin').value = reserva.data_checkin;
        document.getElementById('data_checkout').value = reserva.data_checkout;
    }

    // Salvar alteração
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            reserva_id: id,
            quarto: document.getElementById('quarto').value,
            data_checkin: document.getElementById('data_checkin').value,
            data_checkout: document.getElementById('data_checkout').value
        };

        const res = await fetch('../../app/controllers/edicao_reserva_process.php', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        const result = await res.json();
        if(result.success) {
            alert('Reserva atualizada com sucesso!');
            window.location.href = 'lobby.php';
        } else {
            alert('Erro ao atualizar reserva.');
        }
    });
});