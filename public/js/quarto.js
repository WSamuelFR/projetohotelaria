/**
 * Lógica do Formulário de Cadastro de Quarto (Frontend)
 * Responsável por coletar dados e enviar via JSON para o back-end.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('quartoForm');
    const messageBox = document.getElementById('message-box');
    const submitButton = document.getElementById('submitButton');

    // --- Função de Exibição de Mensagem ---
    const showMessage = (message, isError = false) => {
        messageBox.textContent = message;
        messageBox.classList.remove('d-none', 'alert-danger', 'alert-success');
        
        if (isError) {
            messageBox.classList.add('alert-danger');
        } else {
            messageBox.classList.add('alert-success');
        }
    };
    
    // --- Lógica de Envio do Formulário (JSON) ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cadastrando...';
        messageBox.classList.add('d-none');
        
        // Coleta todos os dados do formulário
        const formData = new FormData(form);
        const payload = {};
        
        for (const [key, value] of formData.entries()) {
            // Garante que o número do quarto é enviado como INT
            if (key === 'numero') {
                payload[key] = parseInt(value);
            } else {
                payload[key] = value;
            }
        }

        try {
            const response = await fetch('../../app/controllers/quarto_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                showMessage(result.message, false);
                // Limpa o formulário após o sucesso
                form.reset(); 

            } else {
                showMessage('Falha no Cadastro: ' + result.message, true);
            }

        } catch (error) {
            console.error('Erro de conexão:', error);
            showMessage('Erro de comunicação com o servidor. Tente novamente.', true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Cadastrar Quarto';
        }
    });

});