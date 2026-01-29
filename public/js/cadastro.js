/**
 * Lógica do Formulário de Cadastro (Frontend)
 * Alterna entre PF e PJ e envia os dados via JSON.
 */
document.addEventListener('DOMContentLoaded', () => {
    const typeSwitch = document.getElementById('cadastroTypeSwitch');
    const hospedeFields = document.getElementById('hospedeFields');
    const empresaFields = document.getElementById('empresaFields');
    const form = document.getElementById('cadastroForm');
    const messageBox = document.getElementById('message-box');
    const submitButton = document.getElementById('submitButton');
    const typeBadge = document.getElementById('currentTypeBadge'); // Adiciona a referência ao badge

    // --- Função para atualizar o Badge de Tipo ---
    const updateBadge = (isHospede) => {
        if (typeBadge) {
            typeBadge.textContent = isHospede ? 'Hóspede (Pessoa Física)' : 'Empresa (Pessoa Jurídica)';
        }
    };
    
    // --- Função para alternar visibilidade e atributos ---
    const toggleFields = (isHospede) => {
        const h_inputs = hospedeFields.querySelectorAll('input, select');
        const e_inputs = empresaFields.querySelectorAll('input, select');

        if (isHospede) {
            // Mostrar PF, Esconder PJ
            hospedeFields.classList.remove('d-none');
            empresaFields.classList.add('d-none');
            h_inputs.forEach(input => input.setAttribute('required', 'required'));
            e_inputs.forEach(input => input.removeAttribute('required'));
            
            // Ajustar o campo 'Nome/Empresa' no formulário final
            document.getElementById('cadastroType').value = 'hospede';

        } else {
            // Mostrar PJ, Esconder PF
            empresaFields.classList.remove('d-none');
            hospedeFields.classList.add('d-none');
            // Apenas os campos presentes no PJ (company_name, cnpj) receberão o required
            e_inputs.forEach(input => input.setAttribute('required', 'required'));
            h_inputs.forEach(input => input.removeAttribute('required'));

            // Ajustar o campo 'Nome/Empresa' no formulário final
            document.getElementById('cadastroType').value = 'empresa';
        }

        updateBadge(isHospede); // Chamada para atualizar o badge
    };
    
    // Inicializa o estado (Pessoa Física por padrão)
    toggleFields(typeSwitch.checked);

    // Listener para o switch
    typeSwitch.addEventListener('change', () => {
        toggleFields(typeSwitch.checked);
    });

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
            payload[key] = value;
        }

        try {
            const response = await fetch('../../app/controllers/cadastro_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                showMessage(result.message, false);
                // Opcional: Limpar formulário ou redirecionar
                // form.reset(); 
                console.log('Novo ID de Cadastro:', result.cadastro_id);

            } else {
                showMessage('Falha no Cadastro: ' + result.message, true);
            }

        } catch (error) {
            console.error('Erro de conexão:', error);
            showMessage('Erro de comunicação com o servidor. Tente novamente.', true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Cadastrar Cliente';
        }
    });

});