<?php
// =============================================================================
// MODEL: Task (Tarefa) — O CORAÇÃO DO CRUD
// =============================================================================
// Este Model implementa as 4 operações do CRUD:
//   C — Create (Criar)    → método create()
//   R — Read (Ler)        → métodos getAllByUser() e getById()
//   U — Update (Atualizar)→ método update()
//   D — Delete (Excluir)  → método delete()
//
// CRUD é o acrônimo para as 4 operações básicas de persistência de dados.
// Praticamente TODO sistema web implementa CRUD de alguma forma:
//   - Rede social: CRUD de posts
//   - E-commerce: CRUD de produtos
//   - Blog: CRUD de artigos
//   - Este projeto: CRUD de tarefas
//
// No SQL, o CRUD se traduz em:
//   C → INSERT INTO
//   R → SELECT
//   U → UPDATE ... SET
//   D → DELETE FROM
// =============================================================================

require_once __DIR__ . '/../config/database.php';

class Task
{
    /** @var PDO Conexão com o banco de dados */
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // C — CREATE (Criar)
    // =========================================================================
    /**
     * Cria uma nova tarefa no banco de dados.
     * 
     * @param int    $userId      ID do usuário logado (dono da tarefa)
     * @param string $title       Título da tarefa
     * @param string $description Descrição detalhada (opcional)
     * @return bool True se criou com sucesso
     */
    public function create(int $userId, string $title, string $description): bool
    {
        // INSERT INTO: comando SQL para INSERIR um novo registro na tabela
        // Especificamos as colunas (user_id, title, description) e os valores (:placeholders)
        // As colunas id, status, created_at e updated_at são preenchidas automaticamente
        // pelo banco (AUTO_INCREMENT, DEFAULT, TIMESTAMP)
        $stmt = $this->db->prepare(
            "INSERT INTO tasks (user_id, title, description) 
             VALUES (:user_id, :title, :description)"
        );

        return $stmt->execute([
            ':user_id'     => $userId,
            ':title'       => $title,
            ':description' => $description
        ]);
    }

    // =========================================================================
    // R — READ (Ler) — Listar todas as tarefas do usuário
    // =========================================================================
    /**
     * Retorna todas as tarefas de um usuário específico.
     * 
     * SEGURANÇA: Filtramos por user_id para que cada usuário veja APENAS
     * suas próprias tarefas. Sem esse filtro, qualquer pessoa veria tudo!
     * 
     * @param int $userId ID do usuário logado
     * @return array Lista de tarefas (array de arrays associativos)
     * 
     * Exemplo de retorno:
     *   [
     *     ['id' => 1, 'title' => 'Estudar PHP', 'status' => 'pendente', ...],
     *     ['id' => 2, 'title' => 'Fazer exercícios', 'status' => 'concluida', ...],
     *   ]
     */
    public function getAllByUser(int $userId): array
    {
        // SELECT *: seleciona TODAS as colunas da tabela
        // WHERE user_id = :user_id: filtra apenas as tarefas do usuário
        // ORDER BY created_at DESC: ordena da mais recente para a mais antiga
        //   DESC = descendente (maior → menor)
        //   ASC  = ascendente  (menor → maior) — é o padrão se não especificado
        $stmt = $this->db->prepare(
            "SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC"
        );

        $stmt->execute([':user_id' => $userId]);

        // fetchAll(): retorna TODAS as linhas do resultado como um array de arrays.
        // Diferente de fetch() que retorna apenas UMA linha.
        // Se não houver resultados, retorna um array VAZIO [] (não false).
        return $stmt->fetchAll();
    }

    // =========================================================================
    // R — READ (Ler) — Buscar uma tarefa específica
    // =========================================================================
    /**
     * Busca uma tarefa pelo ID, verificando se pertence ao usuário.
     * 
     * Usado para preencher o formulário de edição e para verificar
     * permissão antes de editar/excluir.
     * 
     * @param int $id     ID da tarefa
     * @param int $userId ID do usuário (para verificar se é o dono)
     * @return array|false Dados da tarefa ou false se não encontrada
     */
    public function getById(int $id, int $userId)
    {
        // Usamos duas condições no WHERE (AND):
        // - id = :id → encontra a tarefa específica
        // - user_id = :user_id → garante que pertence ao usuário logado
        // Sem a segunda condição, um usuário poderia editar tarefas dos outros
        // simplesmente mudando o ID na URL!
        $stmt = $this->db->prepare(
            "SELECT * FROM tasks WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId
        ]);

        return $stmt->fetch();
    }

    // =========================================================================
    // U — UPDATE (Atualizar)
    // =========================================================================
    /**
     * Atualiza os dados de uma tarefa existente.
     * 
     * @param int    $id          ID da tarefa a atualizar
     * @param int    $userId      ID do usuário (verificação de permissão)
     * @param string $title       Novo título
     * @param string $description Nova descrição
     * @param string $status      Novo status ('pendente' ou 'concluida')
     * @return bool True se atualizou com sucesso
     */
    public function update(int $id, int $userId, string $title, string $description, string $status): bool
    {
        // UPDATE: comando SQL para MODIFICAR registros existentes
        // SET: define quais colunas serão alteradas e seus novos valores
        // WHERE: FUNDAMENTAL! Sem WHERE, TODOS os registros seriam alterados!
        //
        // A coluna updated_at é atualizada automaticamente pelo MySQL
        // (graças ao ON UPDATE CURRENT_TIMESTAMP que definimos no database.sql)
        $stmt = $this->db->prepare(
            "UPDATE tasks 
             SET title = :title, description = :description, status = :status 
             WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute([
            ':title'       => $title,
            ':description' => $description,
            ':status'      => $status,
            ':id'          => $id,
            ':user_id'     => $userId
        ]);

        // rowCount(): retorna o número de linhas AFETADAS pela query.
        // Se retornar 0, significa que:
        //   - O ID não existe, OU
        //   - A tarefa não pertence ao usuário, OU
        //   - Os dados são idênticos aos existentes (nada mudou)
        return $stmt->rowCount() > 0;
    }

    // =========================================================================
    // D — DELETE (Excluir)
    // =========================================================================
    /**
     * Exclui uma tarefa do banco de dados.
     * 
     * ATENÇÃO: DELETE é PERMANENTE! Uma vez excluído, não há como recuperar.
     * Em sistemas reais, é comum usar "soft delete" (marcar como excluído
     * sem remover do banco), mas aqui usamos delete real para fins didáticos.
     * 
     * @param int $id     ID da tarefa a excluir
     * @param int $userId ID do usuário (verificação de permissão)
     * @return bool True se excluiu com sucesso
     */
    public function delete(int $id, int $userId): bool
    {
        // DELETE FROM: comando SQL para REMOVER registros da tabela
        // Novamente, o WHERE com user_id garante que um usuário
        // não consiga excluir tarefas de outros usuários
        $stmt = $this->db->prepare(
            "DELETE FROM tasks WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }
}
