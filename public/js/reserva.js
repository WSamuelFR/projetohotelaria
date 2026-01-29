/**
 * Lógica da Reserva (Frontend)
 * Gerencia a busca de dados para Datalists e o envio do formulário.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservaForm');
    const messageBox = document.getElementById('message-box');
    const submitButton = document.getElementById('submitButton');
    
    // Elementos do Cliente
    const clienteSearchInput = document.getElementById('cliente_search');
    const clienteDataList = document.getElementById('clienteDataListOptions');
    const cadastroCpfCnpjInput = document.getElementById('cadastro_cpf_cnpj');
    const selectedClientInfo = document.getElementById('selectedClientInfo');

    // Elementos do Quarto
    const quartoSearchInput = document.getElementById('quarto_search');
    const quartoDataList = document.getElementById('quartoDataListOptions');
    const quartoNumeroInput = document.getElementById('quarto_numero');
    const selectedQuartoInfo = document.getElementById('selectedQuartoInfo');

    // --- Funções Auxiliares ---

    const showMessage = (message, isError = false) => {
        messageBox.textContent = message;
        messageBox.classList.remove('d-none', 'alert-danger', 'alert-success');
        
        if (isError) {
            messageBox.classList.add('alert-danger');
        } else {
            messageBox.classList.add('alert-success');
        }
    };
    
    // --- Lógica do Datalist (Clientes e Quartos) ---

    // Função genérica para buscar e preencher o datalist
    const fetchDatalistData = async (type, search, datalist) => {
        // Caminho relativo para o controller de dados
        const url = `../../app/controllers/reserva_data.php?type=${type}&search=${encodeURIComponent(search)}`;
        try {
            const response = await fetch(url);
            const result = await response.json();
            
            datalist.innerHTML = '';
            
            if (result.success) {
                result.data.forEach(item => {
                    const option = document.createElement('option');
                    
                    if (type === 'clientes') {
                        // Formato: Nome (CPF/CNPJ)
                        option.value = `${item.full_name} (${item.cpf_cnpj})`;
                        option.dataset.value = item.cpf_cnpj; // Armazena o CPF/CNPJ como valor real
                    } else if (type === 'quartos') {
                        // Formato: Quarto NNN - TIPO (STATUS)
                        option.value = `Quarto ${item.numero} - ${item.tipo} (${item.status_display})`;
                        option.dataset.value = item.numero; // Armazena o número do quarto
                    }
                    datalist.appendChild(option);
                });
            }
        } catch (error) {
            console.error(`Erro ao buscar ${type}:`, error);
        }
    };

    // Listener para busca de Clientes
    clienteSearchInput.addEventListener('input', () => {
        const searchTerm = clienteSearchInput.value.trim();
        // Dispara a busca apenas após digitar 3 caracteres
        if (searchTerm.length >= 3) {
            fetchDatalistData('clientes', searchTerm, clienteDataList);
        }
        // Limpar o campo oculto e info quando o usuário digita
        cadastroCpfCnpjInput.value = '';
        selectedClientInfo.textContent = 'Cliente selecionado: N/A';
    });

    // Listener para busca de Quartos
    quartoSearchInput.addEventListener('input', () => {
        const searchTerm = quartoSearchInput.value.trim();
        // Dispara a busca para todos se vazio, ou após 2 caracteres
        if (searchTerm.length >= 2 || !searchTerm) { 
            fetchDatalistData('quartos', searchTerm, quartoDataList);
        }
        // Limpar o campo oculto e info quando o usuário digita
        quartoNumeroInput.value = '';
        selectedQuartoInfo.textContent = 'Quarto selecionado: N/A';
    });
    
    // --- Lógica de Seleção (Cliente) ---
    clienteSearchInput.addEventListener('change', () => {
        const selectedOption = clienteDataList.querySelector(`option[value="${clienteSearchInput.value}"]`);
        
        if (selectedOption) {
            // Se encontrou uma opção válida no datalist
            const cpfCnpj = selectedOption.dataset.value;
            cadastroCpfCnpjInput.value = cpfCnpj;
            selectedClientInfo.textContent = `Cliente selecionado: ${selectedOption.value}`;
        } else {
            // Caso o usuário digite algo que não está na lista ou limpe
            cadastroCpfCnpjInput.value = '';
            selectedClientInfo.textContent = 'Cliente selecionado: N/A (Selecione um da lista)';
        }
    });

    // --- Lógica de Seleção (Quarto) ---
    quartoSearchInput.addEventListener('change', () => {
        const selectedOption = quartoDataList.querySelector(`option[value="${quartoSearchInput.value}"]`);
        
        if (selectedOption) {
            // Se encontrou uma opção válida no datalist
            const numero = selectedOption.dataset.value;
            quartoNumeroInput.value = numero;
            selectedQuartoInfo.textContent = `Quarto selecionado: ${selectedOption.value}`;
        } else {
            // Caso o usuário digite algo que não está na lista ou limpe
            quartoNumeroInput.value = '';
            selectedQuartoInfo.textContent = 'Quarto selecionado: N/A (Selecione um da lista)';
        }
    });

    // --- Lógica de Envio do Formulário (JSON) ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // 1. Re-validação crítica dos campos ocultos
        if (!cadastroCpfCnpjInput.value || !quartoNumeroInput.value) {
            showMessage('Erro: Selecione um Cliente e um Quarto válidos da lista.', true);
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Reservando...';
        messageBox.classList.add('d-none');
        
        // Coleta os dados que serão enviados ao controller
        const payload = {
            cadastro: cadastroCpfCnpjInput.value,
            quarto: parseInt(quartoNumeroInput.value),
            data_checkin: document.getElementById('data_checkin').value,
            data_checkout: document.getElementById('data_checkout').value
        };

        try {
            // Caminho relativo para o controller de processamento
            const response = await fetch('../../app/controllers/reserva_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                showMessage(result.message, false);
                // Limpar o formulário após sucesso
                form.reset(); 
                cadastroCpfCnpjInput.value = '';
                quartoNumeroInput.value = '';
                selectedClientInfo.textContent = 'Cliente selecionado: N/A';
                selectedQuartoInfo.textContent = 'Quarto selecionado: N/A';

            } else {
                showMessage('Falha na Reserva: ' + result.message, true);
            }

        } catch (error) {
            console.error('Erro de conexão:', error);
            showMessage('Erro de comunicação com o servidor. Tente novamente.', true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Confirmar Reserva';
        }
    });

});