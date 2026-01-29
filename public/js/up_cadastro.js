/**
 * Lógica do Formulário de Edição de Cadastro (Frontend)
 * 1. Busca os dados do cliente pelo ID (GET)
 * 2. Preenche o formulário
 * 3. Envia os dados atualizados (POST)
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('edicaoForm');
    const messageBox = document.getElementById('message-box');
    const submitButton = document.getElementById('submitButton');
    const loadingSpinner = document.getElementById('loading-spinner');
    const typeBadge = document.getElementById('currentTypeBadge');
    
    // Pega o ID do cliente do campo oculto na View
    const cadastroId = document.getElementById('cadastroId').value;

    // Elementos de campo
    const hospedeFields = document.getElementById('hospedeFields');
    const empresaFields = document.getElementById('empresaFields');
    const cadastroTypeInput = document.getElementById('cadastroType');


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
    
    const updateBadge = (tipo) => {
        if (typeBadge) {
            if (tipo === 'hospede') {
                typeBadge.textContent = 'Hóspede (Pessoa Física)';
                hospedeFields.classList.remove('d-none');
                empresaFields.classList.add('d-none');
            } else if (tipo === 'empresa') {
                typeBadge.textContent = 'Empresa (Pessoa Jurídica)';
                empresaFields.classList.remove('d-none');
                hospedeFields.classList.add('d-none');
            }
        }
        cadastroTypeInput.value = tipo;
    };


    // --- 1. Busca dos Dados ---

    const fetchClientData = async (id) => {
        try {
            const response = await fetch(`../../app/controllers/leitura_cadastro.php?id=${id}`);
            const result = await response.json();

            if (result.success) {
                fillForm(result.data);
            } else {
                showMessage('Erro ao carregar dados: ' + result.message, true);
            }
        } catch (error) {
            console.error('Erro de conexão:', error);
            showMessage('Erro de comunicação ao buscar dados.', true);
        } finally {
            loadingSpinner.classList.add('d-none');
            form.classList.remove('d-none');
        }
    };

    // --- 2. Preenchimento do Formulário ---

    const fillForm = (data) => {
        // Separa Nome/Sobrenome se for PF
        if (data.tipo === 'hospede') {
            document.getElementById('full_name').value = data.full_name;
            document.getElementById('cpf').value = data.cpf_cnpj;
            document.getElementById('rg').value = data.rg || '';
            document.getElementById('birth_date').value = data.birth_date || '';
            document.getElementById('gender').value = data.gender || '';
            document.getElementById('ethnicity').value = data.ethnicity || '';
        } else { // PJ
            document.getElementById('company_name').value = data.full_name;
            document.getElementById('cnpj_pj').value = data.cpf_cnpj;
            // Campos específicos da tabela 'empresa'
            document.getElementById('founding_date').value = data.data_fundacao || '';
            document.getElementById('commercial_phone').value = data.telefone_comercial || '';
            document.getElementById('idEmpresa').value = data.id_empresa || '';
        }

        // Dados de Contato e Endereço (Comuns)
        document.getElementById('email').value = data.email;
        document.getElementById('phone').value = data.phone;
        
        // Dados de Endereço
        document.getElementById('enderecoId').value = data.endereco_id;
        document.getElementById('tipo_endereco').value = data.tipo_endereco;
        document.getElementById('current_country').value = data.current_country;
        document.getElementById('state').value = data.state;
        document.getElementById('city').value = data.city;
        document.getElementById('neighborhood').value = data.neighborhood;
        document.getElementById('street').value = data.street;
        document.getElementById('address_number').value = data.address_number;
        document.getElementById('cep').value = data.cep || '';
        
        // Atualiza o tipo e visibilidade
        updateBadge(data.tipo);
    };

    // --- 3. Lógica de Envio da Edição (POST) ---

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Atualizando...';
        messageBox.classList.add('d-none');
        
        const formData = new FormData(form);
        const payload = {};
        
        // Apenas coleta dados dos campos ativos (PF ou PJ) e comuns
        for (const [key, value] of formData.entries()) {
            // Ignora campos disabled (cpf e cnpj)
            if (form.elements[key] && form.elements[key].disabled) continue; 
            payload[key] = value;
        }

        try {
            // Envia para o novo controlador de edição
            const response = await fetch('../../app/controllers/edicao_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                showMessage(result.message, false);
                // Atualiza o botão para indicar sucesso, mas não limpa o formulário
                submitButton.textContent = 'Atualização Completa'; 
                // Recarrega os dados (caso haja alterações)
                fetchClientData(cadastroId);

            } else {
                showMessage('Falha na Atualização: ' + result.message, true);
            }

        } catch (error) {
            console.error('Erro de conexão:', error);
            showMessage('Erro de comunicação com o servidor. Tente novamente.', true);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Atualizar Cadastro';
        }
    });

    // Inicia a busca dos dados ao carregar a página
    if (cadastroId) {
        fetchClientData(cadastroId);
    } else {
         loadingSpinner.classList.add('d-none');
         showMessage('ID de cadastro ausente na URL.', true);
    }

});