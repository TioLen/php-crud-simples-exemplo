-- =============================================================================
-- SCRIPT DE CRIAÇÃO DO BANCO DE DADOS — CRUD PHP MVC
-- =============================================================================
-- Este arquivo cria o banco de dados e as tabelas necessárias para o projeto.
-- Execute este script no phpMyAdmin (http://localhost/phpmyadmin) ou via terminal MySQL.
--
-- CONCEITO: O banco de dados é onde os dados são PERSISTIDOS (salvos permanentemente).
-- Diferente de variáveis PHP que existem apenas durante a execução do script,
-- os dados no banco sobrevivem ao reinício do servidor.
-- =============================================================================

-- Cria o banco de dados se ele não existir
-- CHARACTER SET utf8mb4: suporta emojis e caracteres especiais (acentos, ç, etc.)
-- COLLATE utf8mb4_unicode_ci: define como o MySQL compara e ordena textos
--   (ci = case-insensitive, ou seja, "João" e "joão" são considerados iguais em buscas)
CREATE DATABASE IF NOT EXISTS crud_php_mvc
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco para uso
USE crud_php_mvc;

-- =============================================================================
-- TABELA: users (Usuários)
-- =============================================================================
-- Armazena os dados de autenticação dos usuários (cadastro e login).
--
-- CONCEITO: Cada tabela representa uma "entidade" do sistema.
-- As colunas são os "atributos" dessa entidade.
CREATE TABLE IF NOT EXISTS users (
    -- AUTO_INCREMENT: o MySQL gera automaticamente um número único para cada registro
    -- PRIMARY KEY: identificador único de cada linha (nunca se repete)
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- VARCHAR(100): texto com no máximo 100 caracteres
    -- NOT NULL: campo obrigatório (não pode ficar vazio)
    name VARCHAR(100) NOT NULL,

    -- UNIQUE: garante que não existam dois usuários com o mesmo e-mail
    -- Isso é importante pois o e-mail é usado para login
    email VARCHAR(150) NOT NULL UNIQUE,

    -- VARCHAR(255): armazena o HASH da senha, não a senha real!
    -- A função password_hash() do PHP gera um hash de ~60 caracteres,
    -- mas usamos 255 por segurança caso o algoritmo mude no futuro
    password VARCHAR(255) NOT NULL,

    -- TIMESTAMP: armazena data e hora
    -- DEFAULT CURRENT_TIMESTAMP: preenche automaticamente com a data/hora atual
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- ENGINE=InnoDB: motor de armazenamento do MySQL que suporta:
--   - Transações (operações atômicas)
--   - Chaves estrangeiras (relacionamentos entre tabelas)
--   - Bloqueio em nível de linha (melhor performance com múltiplos acessos)

-- =============================================================================
-- TABELA: tasks (Tarefas)
-- =============================================================================
-- Armazena as tarefas criadas pelos usuários. Esta é a tabela principal do CRUD.
-- Cada tarefa pertence a um usuário (relacionamento 1:N — um usuário tem muitas tarefas).
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- user_id: referência ao usuário que criou a tarefa
    -- Este campo cria o RELACIONAMENTO entre as tabelas users e tasks
    user_id INT NOT NULL,

    title VARCHAR(200) NOT NULL,

    -- TEXT: tipo para textos longos (sem limite prático de caracteres)
    -- Diferente de VARCHAR que tem limite definido
    description TEXT,

    -- ENUM: restringe os valores possíveis a uma lista predefinida
    -- Só aceita 'pendente' ou 'concluida', qualquer outro valor será rejeitado
    -- DEFAULT 'pendente': se não informado, a tarefa começa como pendente
    status ENUM('pendente', 'concluida') DEFAULT 'pendente',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- ON UPDATE CURRENT_TIMESTAMP: atualiza automaticamente quando o registro é modificado
    -- Útil para saber quando a tarefa foi editada pela última vez
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- FOREIGN KEY (Chave Estrangeira):
    -- Cria um vínculo entre tasks.user_id e users.id
    -- ON DELETE CASCADE: se um usuário for excluído, TODAS as suas tarefas também serão
    -- Isso evita tarefas "órfãs" (sem dono) no banco de dados
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
