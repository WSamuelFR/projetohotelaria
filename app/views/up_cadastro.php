<?php
// Arquivo: app/Views/edicao.php (Página de Edição de Cliente)

$pageTitle = "Edição de Cliente";

// 1. Inclua o cabeçalho e o menu lateral do Layout Global
require_once('layout/header.php');
require_once('layout/sidebar.php');

// Tenta obter o ID do cadastro da URL
$cadastro_id = $_GET['id'] ?? null;

if (!$cadastro_id) {
    // Redireciona ou exibe erro se o ID não for fornecido
    echo '<div class="container mt-5"><div class="alert alert-danger" role="alert">ID de cadastro não fornecido.</div></div>';
    require_once('layout/footer.php');
    exit;
}

?>

<div class="container mt-4">

    <h1 class="mb-4 text-primary"><?php echo $pageTitle; ?> ID: <?php echo htmlspecialchars($cadastro_id); ?></h1>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="form-check form-switch d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tipo de Cadastro: <span class="badge bg-primary" id="currentTypeBadge">Carregando...</span></h5>
                <div>
                    <input class="form-check-input" type="checkbox" role="switch" id="cadastroTypeSwitch" disabled>
                    <label class="form-check-label" for="cadastroTypeSwitch">Empresa (PJ) / Hóspede (PF)</label>
                </div>
            </div>
        </div>

        <div class="card-body">

            <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

            <div id="loading-spinner" class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando dados...</span>
                </div>
                <p class="mt-3">Carregando dados do cliente...</p>
            </div>

            <form id="edicaoForm" method="POST" class="d-none">
                <input type="hidden" id="cadastroId" name="cadastro_id" value="<?php echo htmlspecialchars($cadastro_id); ?>">
                <input type="hidden" id="cadastroType" name="tipo" value="">

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 fs-6 text-primary fw-bold">1. Dados de Identificação</legend>

                    <div id="hospedeFields" class="row">
                        <div class="col-md-4 mb-3"><label for="full_name" class="form-label">Nome completo</label><input type="text" class="form-control" id="full_name" name="full_name"></div>
                        <div class="col-md-4 mb-3"><label for="cpf" class="form-label">CPF/CNPJ</label><input type="text" class="form-control" id="cpf" name="cpf" disabled></div>
                        <div class="col-md-4 mb-3"><label for="rg" class="form-label">RG</label><input type="text" class="form-control" id="rg" name="rg"></div>
                        <div class="col-md-4 mb-3"><label for="birth_date" class="form-label">Data de Nascimento</label><input type="date" class="form-control" id="birth_date" name="birth_date"></div>
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">Gênero</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="" selected>Selecione</option>
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ethnicity" class="form-label">Etnia</label>
                            <select class="form-select" id="ethnicity" name="ethnicity">
                                <option value="" selected>Selecione</option>
                                <option value="branco">Branco</option>
                                <option value="negro">Negro</option>
                                <option value="pardo">Pardo</option>
                                <option value="indigena">Indígena</option>
                            </select>
                        </div>
                    </div>

                    <div id="empresaFields" class="row d-none">
                        <div class="col-md-6 mb-3"><label for="company_name" class="form-label">Nome da Empresa</label><input type="text" class="form-control" id="company_name" name="company_name"></div>
                        <div class="col-md-6 mb-3"><label for="cnpj" class="form-label">CNPJ</label><input type="text" class="form-control" id="cnpj_pj" name="cnpj_pj" disabled></div>
                        <div class="col-md-6 mb-3"><label for="founding_date" class="form-label">Data de Fundação</label><input type="date" class="form-control" id="founding_date" name="data_fundacao"></div>
                        <div class="col-md-6 mb-3"><label for="commercial_phone" class="form-label">Telefone Comercial</label><input type="text" class="form-control" id="commercial_phone" name="telefone_comercial"></div>
                        <input type="hidden" id="idEmpresa" name="id_empresa" value="">
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 fs-6 text-primary fw-bold">2. Contato e Acesso</legend>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label for="email" class="form-label">E-mail</label><input type="email" class="form-control" id="email" name="email" required></div>
                        <div class="col-md-4 mb-3"><label for="phone" class="form-label">Telefone</label><input type="text" class="form-control" id="phone" name="phone" required></div>
                        <div class="col-md-4 mb-3"><label for="senha" class="form-label">Nova Senha de Acesso (Deixe em branco para manter a antiga)</label><input type="password" class="form-control" id="senha" name="senha"></div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 fs-6 text-primary fw-bold">3. Dados de Endereço</legend>
                    <input type="hidden" id="enderecoId" name="endereco_id" value="">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label for="current_country" class="form-label">País Atual</label><input type="text" class="form-control" id="current_country" name="current_country" required></div>
                        <div class="col-md-3 mb-3"><label for="state" class="form-label">Estado</label><input type="text" class="form-control" id="state" name="state" required></div>
                        <div class="col-md-3 mb-3"><label for="city" class="form-label">Cidade</label><input type="text" class="form-control" id="city" name="city" required></div>
                        <div class="col-md-3 mb-3"><label for="cep" class="form-label">CEP</label><input type="text" class="form-control" id="cep" name="cep"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label for="neighborhood" class="form-label">Bairro</label><input type="text" class="form-control" id="neighborhood" name="neighborhood" required></div>
                        <div class="col-md-4 mb-3"><label for="street" class="form-label">Rua</label><input type="text" class="form-control" id="street" name="street" required></div>
                        <div class="col-md-2 mb-3"><label for="address_number" class="form-label">Número</label><input type="text" class="form-control" id="address_number" name="address_number" required></div>
                        <div class="col-md-2 mb-3"><label for="tipo_endereco" class="form-label">Tipo (e.g. Principal)</label><input type="text" class="form-control" id="tipo_endereco" name="tipo_endereco"></div>
                    </div>
                </fieldset>

                <div class="text-center mt-4">
                    <button type="submit" id="submitButton" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i> Atualizar Cadastro
                    </button>
                    <a href="lobby.php" class="btn btn-secondary btn-lg ms-3">
                        <i class="fas fa-arrow-left me-2"></i> Voltar ao Lobby
                    </a>
                    
                </div>

            </form>
        </div>
    </div>
</div>
<?php
// 4. Inclua o rodapé e finalize a página
require_once('layout/footer.php');
?>

<script src="../../public/js/up_cadastro.js"></script>