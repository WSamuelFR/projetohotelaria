/**
 * Lógica do Formulário de Login (Front-end)
 * Responsável por coletar dados, enviar via JSON para o back-end e fornecer feedback ao usuário.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const messageBox = document.getElementById('message-box');
    const loginButton = document.getElementById('loginButton');

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

    // --- Processamento do Formulário (JSON POST) ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Desabilitar botão e limpar mensagens
        loginButton.disabled = true;
        loginButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Acessando...';
        messageBox.classList.add('d-none');

        const email = document.getElementById('email').value;
        const senha = document.getElementById('senha').value;
        
        // 2. Montar Payload JSON
        const payload = {
            email: email,
            senha: senha
        };

        try {
            // 3. Enviar requisição POST para o script PHP
            // CORREÇÃO: Ajuste do caminho para subir um nível (de public/ para a raiz) e acessar app/
            const response = await fetch('../app/models/loginModel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            // 4. Processar Resposta
            if (result.success) {
                showMessage(result.message, false);
                
                // Redirecionamento após login bem-sucedido
                setTimeout(() => {
                    // CORREÇÃO: Ajuste da rota para acessar a view do lobby corretamente
                    window.location.href = '../app/views/lobby.php'; 
                }, 1500);
            } else {
                showMessage(result.message, true);
            }

        } catch (error) {
            console.error('Erro de conexão:', error);
            showMessage('Erro de conexão com o servidor. Verifique sua rede.', true);
        } finally {
            // 5. Reabilitar botão e restaurar texto
            loginButton.disabled = false;
            loginButton.textContent = 'Entrar';
        }
    });
});