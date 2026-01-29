<?php
// Arquivo: app/models/produtoModel.php
require_once(__DIR__ . '/../config/DBConnection.php');

class ProdutoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Connect();
    }

    /**
     * Cadastra um novo produto no estoque.
     */
    public function insertProduto($data)
    {
        $sql = "INSERT INTO produto (nome, preco_venda, estoque_atual) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdi", $data['nome'], $data['preco'], $data['estoque']);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Produto cadastrado com sucesso!", "id" => $this->conn->insert_id];
        } else {
            return ["success" => false, "message" => "Erro ao cadastrar: " . $this->conn->error];
        }
    }

    /**
     * Lista todos os produtos cadastrados.
     */
    public function listarTodos()
    {
        $sql = "SELECT * FROM produto ORDER BY nome ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Exclui um produto do banco de dados.
     * @param int $id ID do produto a ser removido.
     * @return bool Retorna true em caso de sucesso.
     */
    public function deleteProduto($id)
    {
        $sql = "DELETE FROM produto WHERE produto_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
