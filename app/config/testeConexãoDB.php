<?php
// Arquivo: app/Models/CadastroModel.php

// Incluir o arquivo de conexão e a classe de utilidades
require_once('DBConnection.php');

echo "<h1>Teste de Conexão com o Banco de Dados</h1>";

try {
    // Tenta chamar a função Connect()
    $conn = Connect();

    if ($conn instanceof mysqli && $conn->connect_error) {
        // Esta condição geralmente é tratada pelo 'die' dentro da função Connect(), 
        // mas é uma boa prática verificar.
        echo "<div style='color: white; background-color: #dc3545; padding: 15px; border-radius: 5px;'>";
        echo "❌ FALHA NA CONEXÃO: " . $conn->connect_error;
        echo "</div>";
    } elseif ($conn instanceof mysqli && $conn->ping()) {
        // A função ping() verifica se a conexão está ativa
        echo "<div style='color: white; background-color: #28a745; padding: 15px; border-radius: 5px;'>";
        echo "✅ CONEXÃO ESTABELECIDA COM SUCESSO!";
        echo "</div>";
        echo "<p>Banco de dados conectado: <strong>" . ($_ENV['DB_NAME'] ?? 'N/A') . "</strong></p>";
        
        // Opcional: Testa uma consulta simples para garantir que o banco de dados 'hotel' existe
        $testQuery = $conn->query("SELECT 1 FROM cadastro LIMIT 1");
        if ($testQuery === false) {
             echo "<p style='color: orange;'>⚠️ Aviso: A conexão foi bem-sucedida, mas a tabela 'cadastro' não foi encontrada. Certifique-se de que o SQL foi executado.</p>";
        } else {
             echo "<p style='color: #28a745;'>✅ Tabela 'cadastro' acessível.</p>";
        }

        $conn->close();
    } else {
        echo "<div style='color: white; background-color: #ffc107; padding: 15px; border-radius: 5px;'>";
        echo "❓ ERRO DESCONHECIDO: Não foi possível verificar o estado da conexão.";
        echo "</div>";
    }

} catch (Exception $e) {
    // Captura qualquer exceção, como a falha ao carregar o .env
    echo "<div style='color: white; background-color: #dc3545; padding: 15px; border-radius: 5px;'>";
    echo "❌ ERRO DE EXECUÇÃO: " . $e->getMessage();
    echo "<p>Verifique o caminho do arquivo .env e suas permissões.</p>";
    echo "</div>";
}
?>