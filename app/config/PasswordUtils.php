<?php

/**
 * Classe utilitária para manuseio seguro de senhas.
 * Fornece a função estática para gerar o hash seguro de uma senha.
 */
class PasswordUtils {

    /**
     * Gera um hash seguro para uma senha.
     * Deve ser usada ao CADASTRAR ou ATUALIZAR a senha de um usuário.
     * * @param string $password A senha em texto puro fornecida pelo usuário.
     * @return string O hash da senha gerado (seguro para armazenamento no banco de dados).
     */
    public static function hashPassword(string $password): string
    {
        // PASSWORD_DEFAULT utiliza o algoritmo de hashing mais forte disponível (atualmente Argon2id, ou Bcrypt).
        // Este é o método padrão e mais seguro para hashing de senhas no PHP.
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
// Para verificar a senha no login, você deve usar a função nativa do PHP:
// password_verify($senha_digitada, $hash_armazenado_no_db)
