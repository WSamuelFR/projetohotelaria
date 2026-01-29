<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WS-HOTELARIA | Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Estilos personalizados inspirados nos arquivos originais */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa; /* Light gray background */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-container {
            width: 100%;
            height: 100vh;
        }

        .carousel-container {
            height: 100%;
        }

        .carousel-item img {
            height: 100vh;
            object-fit: cover;
            filter: brightness(0.7); /* Escurece um pouco para o texto aparecer melhor */
        }

        .login-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Cores primárias baseadas em seus arquivos (Azul e Amarelo) */
        .btn-primary {
            background-color: #0056b3;
            border-color: #0056b3;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #004494;
            border-color: #004494;
        }
    </style>
</head>
<body>

    <div class="main-container d-flex flex-row">
        
        <!-- Lado Esquerdo: Carrossel (Visível apenas em telas médias ou maiores) -->
        <div class="d-none d-md-block col-md-7 carousel-container">
            <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner h-100">
                    
                    <!-- Item 1 -->
                    <div class="carousel-item active">
                        <!-- Imagem Placeholder 1 -->
                        <img src="https://placehold.co/1920x1080/0056b3/fff?text=Hotel+Da+Serra" class="d-block w-100" alt="Imagem 1: Hotel da Serra" onerror="this.onerror=null;this.src='https://placehold.co/1920x1080/0056b3/fff?text=Hotel+Da+Serra';">
                        <div class="carousel-caption d-none d-md-block">
                            <h5 class="fw-bold fs-3">Bem-Vindo ao Sistema Hotelaria</h5>
                            <p>Gerencie reservas, clientes e quartos de forma eficiente.</p>
                        </div>
                    </div>
                    
                    <!-- Item 2 -->
                    <div class="carousel-item">
                        <!-- Imagem Placeholder 2 -->
                        <img src="https://placehold.co/1920x1080/ffcc00/000?text=Gerenciamento+de+Reservas" class="d-block w-100" alt="Imagem 2: Gerenciamento de Reservas" onerror="this.onerror=null;this.src='https://placehold.co/1920x1080/ffcc00/000?text=Gerenciamento+de+Reservas';">
                        <div class="carousel-caption d-none d-md-block">
                            <h5 class="fw-bold fs-3">Controle de Hospedagens</h5>
                            <p>Check-in e Check-out rápidos e detalhados.</p>
                        </div>
                    </div>
                    
                    <!-- Item 3 -->
                    <div class="carousel-item">
                        <!-- Imagem Placeholder 3 -->
                        <img src="https://placehold.co/1920x1080/77d7ff/000?text=Cadastro+de+Clientes" class="d-block w-100" alt="Imagem 3: Cadastro de Clientes" onerror="this.onerror=null;this.src='https://placehold.co/1920x1080/77d7ff/000?text=Cadastro+de+Clientes';">
                        <div class="carousel-caption d-none d-md-block">
                            <h5 class="fw-bold fs-3">Cadastro Unificado</h5>
                            <p>Hóspedes e Empresas em um só lugar.</p>
                        </div>
                    </div>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>

        <!-- Lado Direito: Formulário de Login (Card) -->
        <div class="col-12 col-md-5 d-flex align-items-center justify-content-center py-5">
            <div class="login-card p-4 p-md-5 w-100 mx-3" style="max-width: 400px;">
                <h2 class="text-center mb-4 text-primary fw-bold">Acesso ao Sistema</h2>

                <!-- Área de Mensagem (erro ou sucesso) -->
                <div id="message-box" class="d-none p-3 mb-3" role="alert"></div>

                <form id="loginForm" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>

                    <button type="submit" id="loginButton" class="btn btn-primary w-100 mb-3">
                        Entrar
                    </button>
                </form>

                <div class="text-center small">
                    <p class="mb-1">
                        <a href="esqueci_senha.html" class="text-muted">Esqueci minha senha</a>
                    </p>
                    <p class="mb-0">
                        Não tem conta? 
                        <a href="/app/views/new_cadastro.php" class="text-primary fw-bold">Cadastre-se</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Popper e Bundle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Script JS dedicado -->
    <script src="/public/js/login.js"></script>

</body>
</html>