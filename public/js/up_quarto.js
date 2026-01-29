document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('edicaoQuartoForm'); // ID sincronizado com a View
    const messageBox = document.getElementById('message-box');
    const submitButton = document.getElementById('submitButton');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    const quartoId = document.getElementById('quartoId').value;

    const showMessage = (message, isError = false) => {
        messageBox.textContent = message;
        messageBox.classList.remove('d-none', 'alert-danger', 'alert-success');
        messageBox.classList.add(isError ? 'alert-danger' : 'alert-success');
    };
    
    // 1. Busca os Dados do Quarto
    const fetchQuartoData = async (id) => {
        try {
            const response = await fetch(`../../app/controllers/leitura_quarto.php?id=${id}`);
            const result = await response.json();

            if (result.success) {
                // Preenchimento dos campos
                document.getElementById('numeroQuartoDisplay').textContent = result.data.numero;
                document.getElementById('numero').value = result.data.numero;
                document.getElementById('room_type').value = result.data.room_type;
                document.getElementById('bed_quantity').value = result.data.bed_quantity;
                document.getElementById('room_status').value = result.data.room_status;
                document.getElementById('clean_status').value = result.data.clean_status;
                
                loadingSpinner.classList.add('d-none');
                form.classList.remove('d-none');
            } else {
                showMessage('Erro: ' + result.message, true);
            }
        } catch (error) {
            showMessage('Erro de comunicação ao buscar dados.', true);
        }
    };

    // 2. Envio da Edição
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitButton.disabled = true;
        
        const payload = {
            quarto_id: quartoId,
            room_type: document.getElementById('room_type').value,
            bed_quantity: document.getElementById('bed_quantity').value,
            room_status: document.getElementById('room_status').value,
            clean_status: document.getElementById('clean_status').value
        };

        try {
            const response = await fetch('../../app/controllers/edicao_quarto_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (result.success) {
                showMessage(result.message, false);
                submitButton.textContent = 'Atualizado!';
            } else {
                showMessage(result.message, true);
            }
        } catch (error) {
            showMessage('Erro ao processar atualização.', true);
        } finally {
            submitButton.disabled = false;
        }
    });

    if (quartoId) fetchQuartoData(quartoId);
});