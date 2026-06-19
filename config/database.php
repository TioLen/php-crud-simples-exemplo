<?php
// =============================================================================
// CONFIGURAÇÃO DE CONEXÃO COM O BANCO DE DADOS
// =============================================================================
// Este arquivo é responsável por criar e fornecer a conexão com o MySQL.
//
// CONCEITO: Em MVC, a configuração do banco fica separada dos Models.
// Os Models USAM esta conexão, mas não se preocupam em COMO ela é criada.
// Isso é o princípio da "Separação de Responsabilidades".
// =============================================================================

/**
 * Classe Database
 * 
 * Responsável por criar e gerenciar a conexão com o banco de dados MySQL.
 * Usa o padrão Singleton simplificado (uma única conexão reutilizada).
 * 
 * CONCEITO — PDO (PHP Data Objects):
 * PDO é uma interface de acesso a banco de dados no PHP. Diferente da extensão
 * mysqli (que só funciona com MySQL), o PDO suporta 12+ bancos de dados diferentes
 * (MySQL, PostgreSQL, SQLite, etc.) usando a MESMA interface.
 * 
 * Por que usar PDO ao invés de mysqli?
 * 1. Portabilidade: se trocar de banco, o código muda muito pouco
 * 2. Prepared Statements nativos: proteção contra SQL Injection
 * 3. Interface orientada a objetos mais moderna e limpa
 * 4. Melhor tratamento de erros com exceções (Exceptions)
 */
class Database
{
    // =========================================================================
    // CONFIGURAÇÕES DE CONEXÃO
    // =========================================================================
    // Em um projeto real, essas informações ficariam em um arquivo .env
    // (variáveis de ambiente) por segurança. Aqui deixamos direto para simplificar.

    /** @var string Host do banco — 'localhost' significa que o MySQL roda na mesma máquina */
    private static $host = 'localhost';

    /** @var string Nome do banco de dados que criamos no database.sql */
    private static $dbName = 'crud_php_mvc';

    /** @var string Usuário padrão do MySQL no XAMPP */
    private static $username = 'root';

    /** @var string Senha padrão do XAMPP (vazia) — em produção NUNCA deixe assim! */
    private static $password = '';

    /** @var PDO|null Armazena a conexão para reutilização */
    private static $connection = null;

    /**
     * Retorna uma conexão PDO com o banco de dados.
     * 
     * Se a conexão já foi criada antes, reutiliza a mesma (padrão Singleton).
     * Isso evita abrir múltiplas conexões desnecessárias com o banco.
     * 
     * @return PDO Objeto de conexão com o banco de dados
     * @throws PDOException Se não conseguir conectar ao banco
     */
    public static function getConnection(): PDO
    {
        // Se ainda não existe uma conexão, cria uma nova
        if (self::$connection === null) {

            // DSN (Data Source Name): string que define COMO conectar ao banco
            // Formato: "driver:host=SERVIDOR;dbname=BANCO;charset=CODIFICAÇÃO"
            //
            // charset=utf8mb4: garante que caracteres especiais (acentos, emojis)
            // sejam transmitidos corretamente entre PHP e MySQL
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbName . ";charset=utf8mb4";

            // try-catch: estrutura de tratamento de erros (exceções)
            // Se algo der errado dentro do "try", o código do "catch" é executado
            try {
                // Cria a conexão PDO passando: DSN, usuário e senha
                self::$connection = new PDO($dsn, self::$username, self::$password);

                // =========================================================
                // CONFIGURAÇÕES DO PDO (Atributos)
                // =========================================================

                // ATTR_ERRMODE → ERRMODE_EXCEPTION:
                // Faz o PDO LANÇAR EXCEÇÕES quando ocorrer um erro SQL.
                // Sem isso, erros seriam silenciosos e difíceis de debugar.
                // Outras opções (NÃO recomendadas):
                //   - ERRMODE_SILENT: ignora erros (perigoso!)
                //   - ERRMODE_WARNING: exibe warnings mas continua executando
                self::$connection->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                // ATTR_DEFAULT_FETCH_MODE → FETCH_ASSOC:
                // Define que os resultados das queries sejam retornados como
                // arrays associativos (chave => valor), exemplo:
                //   ['id' => 1, 'name' => 'João', 'email' => 'joao@email.com']
                // Outras opções:
                //   - FETCH_NUM: array numérico [0 => 1, 1 => 'João']
                //   - FETCH_OBJ: objeto stdClass ($row->name)
                //   - FETCH_BOTH: associativo + numérico (padrão, desperdiça memória)
                self::$connection->setAttribute(
                    PDO::ATTR_DEFAULT_FETCH_MODE,
                    PDO::FETCH_ASSOC
                );

                // ATTR_EMULATE_PREPARES → false:
                // Desativa a emulação de prepared statements.
                // Com false, o MySQL REAL faz a preparação da query,
                // garantindo proteção REAL contra SQL Injection.
                // Com true (padrão), o PHP simula — menos seguro.
                self::$connection->setAttribute(
                    PDO::ATTR_EMULATE_PREPARES,
                    false
                );

            } catch (PDOException $e) {
                // Se a conexão falhar, exibe uma mensagem amigável e para a execução
                // Em produção, você logaria o erro em um arquivo e mostraria
                // uma página genérica de erro (sem expor detalhes técnicos)
                die("Erro ao conectar com o banco de dados: " . $e->getMessage());
            }
        }

        // Retorna a conexão (nova ou reutilizada)
        return self::$connection;
    }
}
