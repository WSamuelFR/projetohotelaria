<?php
// Inicia a sessão para armazenar informações do usuário após o login
session_start();

// Importa a função de conexão ao banco de dados
require_once('../config/DBConnection.php');

// Importa a classe de utilitários de senha
if (file_exists('../config/PasswordUtils.php')) {
    require_once('../config/PasswordUtils.php');
}

// Cabeçalho para garantir que o navegador entenda a resposta como JSON
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

// 2. Receber e Decodificar a Requisição JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $email = $data['email'] ?? '';
    $senha_digitada = $data['senha'] ?? '';

    if (empty($email) || empty($senha_digitada)) {
        $response['message'] = 'E-mail e senha são obrigatórios.';
        echo json_encode($response);
        exit;
    }

    $conn = Connect();
    if ($conn->connect_error) {
        $response['message'] = 'Falha na conexão com o banco.';
        echo json_encode($response);
        exit;
    }

    // 3. Preparar e Executar a Consulta (Busca o hash, CPF/CNPJ e Nome)
    $sql = "SELECT 
                c.cadastro_id, 
                c.cpf_cnpj, 
                c.full_name, 
                COALESCE(l_pf.senha, l_pj.senha) AS senha,
                COALESCE(l_pf.id, l_pj.id) AS login_id
            FROM cadastro c
            LEFT JOIN login l_pf ON c.cpf_cnpj = l_pf.cadastro AND l_pf.empresa IS NULL
            LEFT JOIN empresa e ON c.cpf_cnpj = e.cadastro
            LEFT JOIN login l_pj ON e.id_empresa = l_pj.empresa AND l_pj.cadastro IS NULL
            WHERE c.email = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = 'Erro interno do servidor.';
        error_log("SQL Prepare Error: " . $conn->error);
        $conn->close();
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // 4. Lógica de Autenticação
    if ($user && !empty($user['senha'])) {
        // VERIFICAÇÃO SEGURA: Usa password_verify()
        if (password_verify($senha_digitada, $user['senha'])) {
            $response['success'] = true;
            $response['message'] = 'Login bem-sucedido!';

            // Armazena informações essenciais na sessão
            $_SESSION['user_id'] = $user['cadastro_id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $user['full_name']; // AQUI: Define o nome para o Header
            
            echo json_encode($response);
            exit; // Interrompe para garantir JSON limpo
        } else {
            $response['message'] = 'E-mail ou senha incorretos.';
        }
    } else {
        $response['message'] = 'E-mail ou senha incorretos.';
    }
}

echo json_encode($response);
exit;