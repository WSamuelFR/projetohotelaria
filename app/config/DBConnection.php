<?php

/**
 * Função simples para carregar variáveis de um arquivo .env para o ambiente PHP.
 * @param string $path Caminho completo para o arquivo .env.
 * @return bool Retorna true em sucesso, false em falha.
 */
function loadEnv(string $path): bool
{
    if (!file_exists($path)) {
        // Loga um erro se o arquivo não for encontrado (IMPORTANTE)
        error_log(".env file not found at: " . $path);
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Divide a linha em chave e valor
        list($name, $value) = explode('=', $line, 2);

        // Limpa espaços e aspas
        $name = trim($name);
        $value = trim($value, " \n\r\t\v\x00\"'");

        // Define a variável de ambiente. Use $_ENV, $_SERVER ou putenv().
        if (!array_key_exists($name, $_SERVER)) {
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

/**
 * Função de Conexão com o Banco de Dados, utilizando variáveis de ambiente.
 * @return mysqli Uma instância de conexão mysqli.
 */
function Connect(): mysqli
{
    // 1. Carregar as variáveis de ambiente
    // Ajuste o caminho conforme a localização do seu arquivo .env
    $env_file_path = __DIR__ . '/.env'; 
    loadEnv($env_file_path);

    // 2. Obter credenciais das variáveis de ambiente
    $servername = $_ENV['DB_HOST']; 
    $username = $_ENV['DB_USER']; 
    $password = $_ENV['DB_PASS']; 
    $dbname = $_ENV['DB_NAME']; 

    // 3. Criar a conexão
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 4. Verificar conexão
    if ($conn->connect_error) {
        // Em um sistema real, você registraria o erro e não exporia detalhes ao usuário.
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
    
    // Define o charset para garantir o suporte a caracteres especiais
    $conn->set_charset("utf8mb4");

    return $conn;
}

// Exemplo de como usar em outros arquivos:
// require_once('caminho/para/DBConnection.php');
// $db = Connect();
// if ($db) { echo "Conexão bem-sucedida!"; }
