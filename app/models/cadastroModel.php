<?php
// Arquivo: app/Models/CadastroModel.php

require_once('../config/DBConnection.php');
require_once('../config/PasswordUtils.php'); // Para fazer o hash da senha

class CadastroModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Connect();
        if ($this->conn->connect_error) {
            throw new Exception("Falha na conexão com o banco de dados: " . $this->conn->connect_error);
        }
    }

    /**
     * Insere um novo registro de cliente, tratando PF e PJ com o novo esquema.
     * @param array $data Dados do cliente (incluindo campos de endereço e senha).
     * @return bool|string Retorna o ID do login inserido (sucesso) ou a mensagem de erro.
     */
    public function insertCadastro(array $data): bool|string
    {

        $this->conn->begin_transaction();

        try {
            // ------------------------------------------
            // 1. INSERIR ENDEREÇO
            // ------------------------------------------
            $sqlEndereco = "INSERT INTO endereco (tipo_endereco, current_country, state, city, neighborhood, street, address_number, cep) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtEndereco = $this->conn->prepare($sqlEndereco);

            $stmtEndereco->bind_param(
                "ssssssss",
                $data['tipo_endereco'],
                $data['current_country'],
                $data['state'],
                $data['city'],
                $data['neighborhood'],
                $data['street'],
                $data['address_number'],
                $data['cep']
            );

            if (!$stmtEndereco->execute()) {
                throw new Exception("Erro ao inserir endereço: " . $stmtEndereco->error);
            }
            $enderecoId = $this->conn->insert_id;
            $stmtEndereco->close();

            // ------------------------------------------
            // 2. PREPARAR DADOS DE CADASTRO GERAL (e PF)
            // ------------------------------------------
            $tipo = $data['tipo'] ?? 'hospede';
            $cpfCnpj = '';
            $fullName = '';

            $rg = null;
            $birthDate = null;
            $gender = null;
            $ethnicity = null;
            $dataFundacao = null;
            $telefoneComercial = null;

            if ($tipo == 'hospede') {
                $fullName = $data['full_name'] ?? '';
                $cpfCnpj = $data['cpf'] ?? '';

                $rg = $data['rg'] ?? null;
                $birthDate = $data['birth_date'] ?? null;
                $gender = $data['gender'] ?? null;
                $ethnicity = $data['ethnicity'] ?? null;
            } else { // empresa
                $fullName = $data['company_name'] ?? '';
                $cpfCnpj = $data['cnpj'] ?? '';

                $dataFundacao = $data['data_fundacao'] ?? null;
                $telefoneComercial = $data['telefone_comercial'] ?? null;
            }

            // ------------------------------------------
            // 3. INSERIR CADASTRO (Tabela Mestra/PF)
            // ------------------------------------------
            // Não insere o campo 'tipo' (coerente com o seu esquema de DB)
            $sqlCadastro = "INSERT INTO cadastro (endereco, email, phone, full_name, cpf_cnpj, rg, birth_date, gender, ethnicity) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtCadastro = $this->conn->prepare($sqlCadastro);

            $stmtCadastro->bind_param(
                "issssssss",
                $enderecoId,
                $data['email'],
                $data['phone'],
                $fullName,
                $cpfCnpj,
                $rg,
                $birthDate,
                $gender,
                $ethnicity
            );

            if (!$stmtCadastro->execute()) {
                throw new Exception("Erro ao inserir cadastro: " . $stmtCadastro->error);
            }
            $stmtCadastro->close();

            $cadastroCpfCnpj = $cpfCnpj;
            $empresaId = null;

            // ------------------------------------------
            // 4. INSERIR EMPRESA (Se for PJ)
            // ------------------------------------------
            if ($tipo == 'empresa') {
                $sqlEmpresa = "INSERT INTO empresa (endereco, cadastro, data_fundacao, telefone_comercial) 
                               VALUES (?, ?, ?, ?)";
                $stmtEmpresa = $this->conn->prepare($sqlEmpresa);

                $stmtEmpresa->bind_param(
                    "isss",
                    $enderecoId,
                    $cadastroCpfCnpj, // Vincula à tabela 'cadastro' pelo CPF/CNPJ
                    $dataFundacao,
                    $telefoneComercial
                );

                if (!$stmtEmpresa->execute()) {
                    throw new Exception("Erro ao inserir empresa: " . $stmtEmpresa->error);
                }
                $empresaId = $this->conn->insert_id;
                $stmtEmpresa->close();
            }

            // ------------------------------------------
            // 5. INSERIR LOGIN (Opcional, se houver senha)
            // ------------------------------------------
            $loginId = 0;
            if (!empty($data['senha'])) {
                $hashedPassword = PasswordUtils::hashPassword($data['senha']);

                // O login agora usa cadastro (CPF/CNPJ) OU empresa (ID da empresa)
                $sqlLogin = "INSERT INTO login (cadastro, empresa, senha) VALUES (?, ?, ?)";
                $stmtLogin = $this->conn->prepare($sqlLogin);

                $loginCadastro = ($tipo == 'hospede') ? $cadastroCpfCnpj : null;
                $loginEmpresaId = ($tipo == 'empresa') ? $empresaId : null;

                $stmtLogin->bind_param("sis", $loginCadastro, $loginEmpresaId, $hashedPassword);

                if (!$stmtLogin->execute()) {
                    throw new Exception("Erro ao inserir login: " . $stmtLogin->error);
                }
                $loginId = $this->conn->insert_id;
                $stmtLogin->close();
            }


            // 6. Confirma a transação e retorna o ID do login inserido (sucesso)
            $this->conn->commit();
            return $loginId;
        } catch (Exception $e) {
            $this->conn->rollback();
            return "Falha na transação: " . $e->getMessage();
        } finally {
            $this->conn->close();
        }
    }
    
    // ----------------------------------------------------
    // FUNÇÕES ADICIONADAS PARA LEITURA E EDIÇÃO
    // ----------------------------------------------------

    /**
     * Busca todos os dados de um cliente (PF ou PJ), incluindo endereço e empresa (se PJ).
     * @param int $cadastroId O ID da tabela 'cadastro'.
     * @return array|null Os dados combinados do cliente ou null se não for encontrado.
     */
    public function getClientDataById(int $cadastroId): ?array
    {
        $sql = "
            SELECT 
                c.*, 
                e.id_empresa, e.data_fundacao, e.telefone_comercial,
                d.*,
                CASE WHEN e.id_empresa IS NOT NULL THEN 'empresa' ELSE 'hospede' END AS tipo
            FROM cadastro c
            LEFT JOIN empresa e ON c.cpf_cnpj = e.cadastro
            JOIN endereco d ON c.endereco = d.endereco_id
            WHERE c.cadastro_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro na preparação da leitura: " . $this->conn->error);
        }

        $stmt->bind_param("i", $cadastroId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data;
    }

    /**
     * Atualiza os dados de um cliente (PF ou PJ), endereço e opcionalmente a senha.
     * @param array $data Dados a serem atualizados.
     * @return bool|string True em caso de sucesso ou a mensagem de erro.
     */
    public function updateCadastro(array $data): bool|string
    {

        $this->conn->begin_transaction();

        try {
            $cadastroId = $data['cadastro_id'];
            $tipo = $data['tipo'];
            $enderecoId = $data['endereco_id'];

            // ------------------------------------------
            // 1. ATUALIZAR ENDEREÇO
            // ------------------------------------------
            $sqlEndereco = "UPDATE endereco SET tipo_endereco=?, current_country=?, state=?, city=?, neighborhood=?, street=?, address_number=?, cep=? WHERE endereco_id=?";

            $stmtEndereco = $this->conn->prepare($sqlEndereco);

            $stmtEndereco->bind_param(
                "ssssssssi",
                $data['tipo_endereco'],
                $data['current_country'],
                $data['state'],
                $data['city'],
                $data['neighborhood'],
                $data['street'],
                $data['address_number'],
                $data['cep'],
                $enderecoId
            );

            if (!$stmtEndereco->execute()) {
                throw new Exception("Erro ao atualizar endereço: " . $stmtEndereco->error);
            }
            $stmtEndereco->close();

            // ------------------------------------------
            // 2. ATUALIZAR CADASTRO (Tabela Mestra/PF)
            // ------------------------------------------
            $rg = $data['rg'] ?? null;
            $birthDate = $data['birth_date'] ?? null;
            $gender = $data['gender'] ?? null;
            $ethnicity = $data['ethnicity'] ?? null;

            if ($tipo == 'hospede') {
                $fullName = ($data['full_name'] ?? '');
            } else { // empresa
                $fullName = $data['company_name'] ?? '';
            }

            $sqlCadastro = "UPDATE cadastro SET email=?, phone=?, full_name=?, rg=?, birth_date=?, gender=?, ethnicity=? WHERE cadastro_id=?";

            $stmtCadastro = $this->conn->prepare($sqlCadastro);

            $stmtCadastro->bind_param(
                "sssssssi",
                $data['email'],
                $data['phone'],
                $fullName,
                $rg,
                $birthDate,
                $gender,
                $ethnicity,
                $cadastroId
            );

            if (!$stmtCadastro->execute()) {
                throw new Exception("Erro ao atualizar cadastro: " . $stmtCadastro->error);
            }
            $stmtCadastro->close();

            // ------------------------------------------
            // 3. ATUALIZAR EMPRESA (Se for PJ)
            // ------------------------------------------
            if ($tipo == 'empresa') {
                $idEmpresa = $data['id_empresa'] ?? null;
                $dataFundacao = $data['data_fundacao'] ?? null;
                $telefoneComercial = $data['telefone_comercial'] ?? null;

                // Assumimos que o registro na tabela 'empresa' já existe (pois o tipo não muda na edição)
                $sqlEmpresa = "UPDATE empresa SET data_fundacao=?, telefone_comercial=? WHERE id_empresa=?";

                $stmtEmpresa = $this->conn->prepare($sqlEmpresa);

                $stmtEmpresa->bind_param(
                    "ssi",
                    $dataFundacao,
                    $telefoneComercial,
                    $idEmpresa
                );

                if (!$stmtEmpresa->execute()) {
                    throw new Exception("Erro ao atualizar empresa: " . $stmtEmpresa->error);
                }
                $stmtEmpresa->close();
            }

            // ------------------------------------------
            // 4. ATUALIZAR LOGIN (Se a senha for fornecida)
            // ------------------------------------------
            if (!empty($data['senha'])) {
                $hashedPassword = PasswordUtils::hashPassword($data['senha']);

                // O login usa 'cadastro' (CPF/CNPJ) ou 'empresa' (id_empresa)
                $loginField = ($tipo == 'hospede') ? 'cadastro' : 'empresa';
                $loginValue = ($tipo == 'hospede') ? ($data['cpf'] ?? null) : ($data['id_empresa'] ?? null); // Usa o CPF/CNPJ ou ID da Empresa que veio no POST

                // Se for PJ, garantimos que o valor é um inteiro (id_empresa)
                if ($tipo == 'empresa' && !is_numeric($loginValue)) {
                    // Se o id_empresa não veio no POST, fazemos uma consulta rápida (caso ideal, viria do JS)
                    $clientInfo = $this->getClientDataById($cadastroId);
                    $loginValue = $clientInfo['id_empresa'] ?? null;
                    if (!$loginValue) {
                        throw new Exception("ID da Empresa não encontrado para a atualização do Login.");
                    }
                }

                // Encontra a entrada na tabela 'login' e atualiza a senha
                $sqlLogin = "UPDATE login SET senha=? WHERE {$loginField}=?";
                $stmtLogin = $this->conn->prepare($sqlLogin);

                if ($tipo == 'hospede') {
                    $stmtLogin->bind_param("ss", $hashedPassword, $loginValue);
                } else { // empresa
                    $stmtLogin->bind_param("si", $hashedPassword, $loginValue);
                }

                if (!$stmtLogin->execute()) {
                    throw new Exception("Erro ao atualizar senha: " . $stmtLogin->error);
                }
                $stmtLogin->close();
            }

            // 5. Confirma a transação
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return "Falha na transação: " . $e->getMessage();
        } finally {
            $this->conn->close();
        }
    }

    public function listarTodos()
    {
        $sql = "SELECT cadastro_id as id, full_name, cpf_cnpj FROM cadastro ORDER BY full_name";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
