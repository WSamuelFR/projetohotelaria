<?php
// Arquivo: app/Views/Layout/header.php
// Inclui o início do HTML, cabeçalho e a Navbar.

// Inicia a sessão para ler os dados reais gravados no loginModel.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Recupera os dados reais da sessão definidos no loginModel.php
$userName = $_SESSION['user_name'] ?? "Administrador";
$userId = $_SESSION['user_id'] ?? "1"; 
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'WS-HOTELARIA'; ?> | Gerenciamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

<body>

    <header class="navbar navbar-expand-lg fixed-top app-header">
        <div class="container-fluid">
            <button class="btn btn-primary me-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <a class="navbar-brand" href="lobby.php">
                HOTELARIA
            </a>

            <div class="ms-auto d-flex align-items-center">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span id="userName"><?php echo htmlspecialchars($userName); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="up_cadastro.php?id=<?php echo $userId; ?>">
                                <i class="fas fa-address-card me-2"></i> Meu Cadastro
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <button class="dropdown-item text-danger" id="logoutButton">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

<?php 
// Fecha o bloco PHP para continuar no _sidebar.php e, depois, no arquivo de conteúdo.
?>