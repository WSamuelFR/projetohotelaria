<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Document</title>
    <style>
        /* Estilos CSS adaptados do layout.html */
        :root {
            --hotel-primary: #0056b3;
            --hotel-secondary: #ffcc00;
            --hotel-accent: #77d7ff;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding-top: 56px;
            background-color: #e7f1ff;
        }

        .app-header {
            background-color: var(--hotel-secondary);
            border-bottom: 3px solid var(--hotel-accent);
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--hotel-primary) !important;
            font-size: 1.5rem;
            transition: color 0.2s;
        }

        .sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            width: 250px;
            height: calc(100% - 56px);
            background-color: var(--hotel-primary);
            color: white;
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            z-index: 1030;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .sidebar-link {
            padding: 10px 15px;
            color: white;
            display: block;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px 10px;
            transition: background-color 0.2s;
        }

        main {
            flex-grow: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
            margin-left: 0;
        }

        .sidebar-opened-margin {
            margin-left: 250px;
        }
    </style>
</head>
</head>

<body>
    <div class="container mt-4">

        <h1 class="mb-4 text-primary">Cadastro de Cliente</h1>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="form-check form-switch d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tipo de Cadastro: <span class="badge bg-primary" id="currentTypeBadge">Hóspede (Pessoa Física)</span></h5>
                    <div>
                        <input class="form-check-input" type="checkbox" role="switch" id="cadastroTypeSwitch" checked>
                        <label class="form-check-label" for="cadastroTypeSwitch">Empresa (PJ) / Hóspede (PF)</label>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

                <form id="cadastroForm" method="POST">
                    <input type="hidden" id="cadastroType" name="tipo" value="hospede">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">1. Dados de Identificação</legend>

                        <div id="hospedeFields" class="row">
                            <div class="col-md-4 mb-3"><label for="full_name" class="form-label">Nome completo</label><input type="text" class="form-control" id="full_name" name="full_name"></div>
                            <div class="col-md-4 mb-3"><label for="cpf" class="form-label">CPF/CNPJ</label><input type="text" class="form-control" id="cpf" name="cpf"></div>
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
                            <div class="col-md-6 mb-3"><label for="cnpj" class="form-label">CNPJ</label><input type="text" class="form-control" id="cnpj" name="cnpj"></div>
                            <div class="col-md-6 mb-3"><label for="founding_date" class="form-label">Data de Fundação</label><input type="date" class="form-control" id="founding_date" name="data_fundacao"></div>
                            <div class="col-md-6 mb-3"><label for="commercial_phone" class="form-label">Telefone Comercial</label><input type="text" class="form-control" id="commercial_phone" name="telefone_comercial"></div>
                        </div>
                    </fieldset>

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">2. Contato e Acesso</legend>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label for="email" class="form-label">E-mail</label><input type="email" class="form-control" id="email" name="email" required></div>
                            <div class="col-md-4 mb-3"><label for="phone" class="form-label">Telefone</label><input type="text" class="form-control" id="phone" name="phone" required></div>
                            <div class="col-md-4 mb-3"><label for="senha" class="form-label">Senha de Acesso (Opcional)</label><input type="password" class="form-control" id="senha" name="senha"></div>
                        </div>
                    </fieldset>

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-2 fs-6 text-primary fw-bold">3. Dados de Endereço</legend>
                        <div class="row">
                            <div class="col-md-3 mb-3"><label for="current_country" class="form-label">País Atual</label><input type="text" class="form-control" id="current_country" name="current_country" value="Brasil" required></div>
                            <div class="col-md-3 mb-3"><label for="state" class="form-label">Estado</label><input type="text" class="form-control" id="state" name="state" required></div>
                            <div class="col-md-3 mb-3"><label for="city" class="form-label">Cidade</label><input type="text" class="form-control" id="city" name="city" required></div>
                            <div class="col-md-3 mb-3"><label for="cep" class="form-label">CEP</label><input type="text" class="form-control" id="cep" name="cep"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label for="neighborhood" class="form-label">Bairro</label><input type="text" class="form-control" id="neighborhood" name="neighborhood" required></div>
                            <div class="col-md-4 mb-3"><label for="street" class="form-label">Rua</label><input type="text" class="form-control" id="street" name="street" required></div>
                            <div class="col-md-2 mb-3"><label for="address_number" class="form-label">Número</label><input type="text" class="form-control" id="address_number" name="address_number" required></div>
                            <div class="col-md-2 mb-3"><label for="tipo_endereco" class="form-label">Tipo (e.g. Principal)</label><input type="text" class="form-control" id="tipo_endereco" name="tipo_endereco" value="principal"></div>
                        </div>
                    </fieldset>

                    <div class="text-center mt-4">
                        <button type="submit" id="submitButton" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Cadastrar Cliente
                        </button>
                        <a href="../../public/index.php" class="btn btn-secondary btn-lg">Voltar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="../../public/js/cadastro.js"></script>

</body>

</html>