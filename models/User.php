<?php
// =============================================================================
// MODEL: User (Usuário)
// =============================================================================
// No padrão MVC, o MODEL é responsável por:
//   1. Representar os dados (entidade "Usuário")
//   2. Interagir com o banco de dados (queries SQL)
//   3. Aplicar regras de negócio relacionadas aos dados
//
// O Model NÃO sabe nada sobre HTML, formulários ou URLs.
// Ele só se preocupa com DADOS.
//
// IMPORTANTE: O Model recebe e retorna dados "puros" (arrays, strings, booleanos).
// Quem decide o que FAZER com esses dados é o Controller.
// =============================================================================

// require_once: inclui um arquivo PHP APENAS UMA VEZ.
// Se o arquivo já foi incluído antes, não inclui de novo (evita erros de redeclaração).
// Diferença entre require e include:
//   - require: se o arquivo não existir, PARA a execução (erro fatal)
//   - include: se o arquivo não existir, CONTINUA executando (só warning)
//   - require_once / include_once: mesma coisa, mas evita inclusão duplicada
//
// __DIR__: constante mágica do PHP que retorna o diretório do arquivo ATUAL.
// Exemplo: se este arquivo está em /models/, __DIR__ = "/models"
// Usamos isso para criar caminhos RELATIVOS ao arquivo, não ao diretório de trabalho.
require_once __DIR__ . '/../config/database.php';

class User
{
    /** @var PDO Conexão com o banco de dados */
    private $db;

    /**
     * Construtor: chamado automaticamente quando fazemos "new User()"
     * 
     * Obtém a conexão com o banco de dados da classe Database.
     * Assim, toda vez que o User precisar fazer uma query, usa $this->db.
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Cadastra um novo usuário no banco de dados.
     * 
     * CONCEITO — password_hash():
     * NUNCA armazene senhas em texto puro no banco de dados!
     * A função password_hash() transforma a senha em um HASH irreversível.
     * 
     * Exemplo:
     *   Senha: "minhasenha123"
     *   Hash:  "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"
     * 
     * Por que NÃO usar md5() ou sha1()?
     *   - São funções de hash GENÉRICAS, não feitas para senhas
     *   - São RÁPIDAS demais (facilita ataques de força bruta)
     *   - Não usam "salt" (texto aleatório adicionado à senha antes do hash)
     *   - password_hash() usa bcrypt: lento de propósito + salt automático
     * 
     * PASSWORD_DEFAULT: usa o melhor algoritmo disponível na versão do PHP.
     * Atualmente é bcrypt, mas se um melhor surgir, o PHP atualiza automaticamente.
     * 
     * @param string $name  Nome completo do usuário
     * @param string $email E-mail (será usado como login)
     * @param string $password Senha em texto puro (será transformada em hash)
     * @return bool True se cadastrou com sucesso, False se falhou
     */
    public function create(string $name, string $email, string $password): bool
    {
        // =====================================================================
        // CONCEITO — Prepared Statements (Consultas Preparadas)
        // =====================================================================
        // Prepared Statements são a forma SEGURA de inserir dados no banco.
        // Em vez de colocar os valores diretamente na query SQL, usamos
        // "placeholders" (marcadores) representados por "?" ou ":nome".
        //
        // EXEMPLO DO QUE NUNCA FAZER (vulnerável a SQL Injection):
        //   $sql = "INSERT INTO users (name) VALUES ('$name')";
        //   Se $name = "'; DROP TABLE users; --", a tabela seria apagada!
        //
        // COM Prepared Statements:
        //   1. A query é ENVIADA ao MySQL com placeholders (sem dados)
        //   2. O MySQL COMPILA a query e entende sua ESTRUTURA
        //   3. Os dados são enviados SEPARADAMENTE
        //   4. O MySQL trata os dados como VALORES, nunca como CÓDIGO SQL
        //   → SQL Injection se torna IMPOSSÍVEL
        // =====================================================================

        // prepare(): compila a query SQL com placeholders (:name, :email, :password)
        // Retorna um objeto PDOStatement que representa a query preparada
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)"
        );

        // Gera o hash da senha ANTES de salvar no banco
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // execute(): executa a query preparada, substituindo os placeholders pelos valores reais.
        // O array associativo mapeia cada placeholder ao seu valor:
        //   :name     → $name
        //   :email    → $email
        //   :password → $hashedPassword (hash, NÃO a senha original!)
        //
        // Retorna true se a query foi executada com sucesso, false caso contrário.
        return $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $hashedPassword
        ]);
    }

    /**
     * Busca um usuário pelo e-mail.
     * 
     * Usado no processo de login para verificar se o e-mail existe
     * e depois comparar a senha fornecida com o hash armazenado.
     * 
     * @param string $email E-mail a ser buscado
     * @return array|false Dados do usuário como array associativo, ou false se não encontrado
     * 
     * Exemplo de retorno (sucesso):
     *   ['id' => 1, 'name' => 'João', 'email' => 'joao@email.com', 'password' => '$2y$10$...']
     * 
     * Exemplo de retorno (não encontrado):
     *   false
     */
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");

        // Aqui usamos execute com apenas um placeholder
        $stmt->execute([':email' => $email]);

        // fetch(): retorna UMA ÚNICA linha do resultado.
        // Se não encontrar nenhuma linha, retorna false.
        // Diferença entre fetch() e fetchAll():
        //   - fetch(): retorna a PRÓXIMA linha (uma só)
        //   - fetchAll(): retorna TODAS as linhas como um array de arrays
        // Usamos fetch() aqui porque e-mail é UNIQUE — só pode haver um resultado.
        return $stmt->fetch();
    }

    /**
     * Verifica se um e-mail já está cadastrado no sistema.
     * 
     * Usado antes do cadastro para evitar duplicatas e dar feedback ao usuário.
     * 
     * @param string $email E-mail a verificar
     * @return bool True se o e-mail já existe, False se está disponível
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        $result = $stmt->fetch();

        // COUNT(*) retorna o número de linhas que correspondem à condição.
        // Se total > 0, significa que já existe um usuário com esse e-mail.
        return $result['total'] > 0;
    }
}
