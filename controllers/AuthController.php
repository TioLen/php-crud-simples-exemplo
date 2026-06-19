<?php
// =============================================================================
// CONTROLLER: AuthController (Autenticação)
// =============================================================================
// No padrão MVC, o CONTROLLER é o "intermediário" entre o Model e a View.
// Ele:
//   1. RECEBE a requisição do usuário (dados do formulário, URL acessada)
//   2. PROCESSA a lógica (valida dados, chama o Model)
//   3. DECIDE qual View exibir (e quais dados passar para ela)
//
// O Controller NÃO acessa o banco diretamente (isso é trabalho do Model).
// O Controller NÃO gera HTML diretamente (isso é trabalho da View).
//
// Este controller cuida de: Login, Cadastro e Logout.
// =============================================================================

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    /** @var User Instância do Model de usuário */
    private $userModel;

    public function __construct()
    {
        // Cria uma instância do Model User para poder chamar seus métodos
        // (create, findByEmail, emailExists)
        $this->userModel = new User();
    }

    // =========================================================================
    // EXIBIR FORMULÁRIO DE LOGIN
    // =========================================================================
    /**
     * Exibe a página de login.
     * 
     * CONCEITO — require vs include:
     * Usamos require para carregar a View. Se o arquivo não existir,
     * o PHP para imediatamente (erro fatal), o que é o comportamento
     * desejado — não faz sentido continuar sem a página.
     */
    public function showLogin(): void
    {
        // Se o usuário já está logado, redireciona para as tarefas
        if ($this->isLoggedIn()) {
            $this->redirect('tasks');
        }

        // Variáveis que a View precisa para funcionar
        $pageTitle = 'Login';
        $error = '';

        // Carrega a View de login
        // A View terá acesso às variáveis definidas acima ($pageTitle, $error)
        // porque o require executa o arquivo no MESMO ESCOPO
        require __DIR__ . '/../views/auth/login.php';
    }

    // =========================================================================
    // PROCESSAR LOGIN
    // =========================================================================
    /**
     * Processa o formulário de login enviado pelo usuário.
     * 
     * CONCEITO — $_POST:
     * $_POST é uma SUPERGLOBAL do PHP — um array associativo que contém
     * os dados enviados por um formulário HTML com method="POST".
     * 
     * Exemplo: se o formulário tem <input name="email">, acessamos via $_POST['email']
     * 
     * Superglobais do PHP:
     *   $_GET     → dados da URL (?chave=valor)
     *   $_POST    → dados do formulário (method="POST")
     *   $_SESSION → dados da sessão do usuário
     *   $_COOKIE  → cookies armazenados no navegador
     *   $_SERVER  → informações do servidor e da requisição
     *   $_FILES   → arquivos enviados via upload
     *   $_REQUEST → combina $_GET, $_POST e $_COOKIE (evite usar — ambíguo)
     * 
     * CONCEITO — POST vs GET:
     *   GET:  dados ficam visíveis na URL (http://site.com?email=joao@email.com)
     *         → Bom para: buscas, filtros, links compartilháveis
     *         → Ruim para: senhas, dados sensíveis (ficam no histórico do navegador)
     * 
     *   POST: dados são enviados no CORPO da requisição (invisíveis na URL)
     *         → Bom para: login, cadastro, qualquer dado sensível
     *         → Ruim para: links compartilháveis (não dá pra copiar a URL e compartilhar)
     */
    public function login(): void
    {
        $pageTitle = 'Login';
        $error = '';

        // trim(): remove espaços em branco do início e fim de uma string.
        // Exemplo: "  joao@email.com  " → "joao@email.com"
        // Isso evita problemas quando o usuário acidentalmente digita espaços.
        //
        // O operador ?? é o "null coalescing operator" (operador de coalescência nula).
        // Se $_POST['email'] existir, usa seu valor. Se não, usa '' (string vazia).
        // É uma forma segura de acessar chaves que podem não existir no array.
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validação básica: verifica se os campos foram preenchidos
        if (empty($email) || empty($password)) {
            $error = 'Preencha todos os campos.';
            require __DIR__ . '/../views/auth/login.php';
            return; // Para a execução aqui — não continua para o próximo código
        }

        // Busca o usuário no banco pelo e-mail
        $user = $this->userModel->findByEmail($email);

        // =====================================================================
        // CONCEITO — password_verify():
        // =====================================================================
        // Compara uma senha em texto puro com um hash gerado por password_hash().
        // 
        // password_verify('minhasenha123', '$2y$10$92IXUNpk...')
        //   → true (se a senha corresponde ao hash)
        //   → false (se não corresponde)
        //
        // Por que não comparar diretamente ($password === $user['password'])?
        // Porque o hash inclui um "salt" (valor aleatório). A mesma senha
        // gera hashes DIFERENTES cada vez que password_hash() é chamado:
        //   password_hash('teste', PASSWORD_DEFAULT) → "$2y$10$abc..."
        //   password_hash('teste', PASSWORD_DEFAULT) → "$2y$10$xyz..."
        // Ambos são válidos para 'teste', mas são strings diferentes!
        // Só password_verify() sabe extrair o salt e comparar corretamente.
        // =====================================================================

        if (!$user || !password_verify($password, $user['password'])) {
            // Mensagem genérica por segurança: NÃO dizemos se o erro é no e-mail ou na senha.
            // Se disséssemos "e-mail não encontrado", um atacante saberia quais e-mails
            // estão cadastrados no sistema (enumeração de usuários).
            $error = 'E-mail ou senha incorretos.';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        // =====================================================================
        // CONCEITO — Sessões PHP ($_SESSION):
        // =====================================================================
        // Sessões permitem manter dados do usuário entre diferentes páginas.
        // 
        // HTTP é "stateless" (sem estado): cada requisição é independente.
        // O servidor não "lembra" quem fez a requisição anterior.
        // 
        // Como sessões resolvem isso:
        //   1. session_start() cria/retoma uma sessão
        //   2. PHP gera um ID único (PHPSESSID) e envia ao navegador como cookie
        //   3. O navegador envia esse cookie em TODA requisição seguinte
        //   4. PHP usa o ID para recuperar os dados da sessão no servidor
        //   5. Os dados ficam disponíveis em $_SESSION
        //
        // Diferença de Cookies:
        //   Cookie: armazenado NO NAVEGADOR (o usuário pode ver e modificar)
        //   Sessão: armazenada NO SERVIDOR (o navegador só tem o ID)
        //   → Sessões são mais SEGURAS para dados sensíveis
        //
        // IMPORTANTE: session_start() já é chamado no index.php (front controller)
        // antes de qualquer output HTML. Se chamar depois de enviar HTML, dá erro!
        // =====================================================================

        // Armazena dados do usuário na sessão
        // A partir de agora, qualquer página pode acessar $_SESSION['user_id']
        // para saber QUEM está logado
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        // Redireciona para a lista de tarefas após login bem-sucedido
        $this->redirect('tasks');
    }

    // =========================================================================
    // EXIBIR FORMULÁRIO DE CADASTRO
    // =========================================================================
    public function showRegister(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('tasks');
        }

        $pageTitle = 'Cadastro';
        $error = '';
        $success = '';
        require __DIR__ . '/../views/auth/register.php';
    }

    // =========================================================================
    // PROCESSAR CADASTRO
    // =========================================================================
    /**
     * Processa o formulário de cadastro.
     * Valida os dados, verifica duplicatas e cria o usuário.
     */
    public function register(): void
    {
        $pageTitle = 'Cadastro';
        $error = '';
        $success = '';

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        // =====================================================================
        // VALIDAÇÃO DE DADOS
        // =====================================================================
        // SEMPRE valide dados no SERVIDOR (backend), mesmo que tenha validação
        // no frontend (JavaScript/HTML5). O frontend pode ser burlado facilmente
        // (basta abrir o DevTools do navegador e modificar o HTML).
        // A validação no servidor é a sua ÚLTIMA linha de defesa.
        // =====================================================================

        if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
            $error = 'Preencha todos os campos.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        // filter_var(): função do PHP para validar e sanitizar dados.
        // FILTER_VALIDATE_EMAIL: verifica se a string tem formato de e-mail válido.
        // Retorna o e-mail se válido, ou false se inválido.
        // Exemplo: "joao@email.com" → "joao@email.com" (válido)
        //          "joao@"          → false (inválido)
        //          "não é email"    → false (inválido)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'E-mail inválido.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        // strlen(): retorna o número de caracteres de uma string
        // Verificamos se a senha tem pelo menos 6 caracteres
        if (strlen($password) < 6) {
            $error = 'A senha deve ter pelo menos 6 caracteres.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        if ($password !== $confirm) {
            $error = 'As senhas não coincidem.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        // Verifica se o e-mail já está cadastrado
        if ($this->userModel->emailExists($email)) {
            $error = 'Este e-mail já está cadastrado.';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        // Tenta criar o usuário no banco de dados
        if ($this->userModel->create($name, $email, $password)) {
            $success = 'Conta criada com sucesso! Faça login para continuar.';
        } else {
            $error = 'Erro ao criar conta. Tente novamente.';
        }

        require __DIR__ . '/../views/auth/register.php';
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================
    /**
     * Encerra a sessão do usuário.
     * 
     * CONCEITO — session_destroy():
     * Remove TODOS os dados da sessão no servidor.
     * Após chamar essa função, $_SESSION fica vazio.
     * 
     * Mas antes de destruir, é boa prática:
     *   1. Limpar o array $_SESSION (= [])
     *   2. Remover o cookie da sessão
     *   3. Aí sim destruir
     * 
     * Isso garante uma limpeza COMPLETA.
     */
    public function logout(): void
    {
        // Limpa todas as variáveis de sessão
        $_SESSION = [];

        // Remove o cookie de sessão do navegador
        // session_name(): retorna o nome do cookie de sessão (geralmente 'PHPSESSID')
        // setcookie(): cria/modifica/remove um cookie
        // Para REMOVER um cookie, definimos seu tempo de expiração no PASSADO
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),        // Nome do cookie
                '',                    // Valor vazio
                time() - 42000,        // Expirado (no passado)
                $params["path"],       // Caminho
                $params["domain"],     // Domínio
                $params["secure"],     // Apenas HTTPS?
                $params["httponly"]    // Inacessível via JavaScript?
            );
        }

        // Destrói a sessão no servidor
        session_destroy();

        // Redireciona para a página de login
        $this->redirect('login');
    }

    // =========================================================================
    // MÉTODOS AUXILIARES (helpers)
    // =========================================================================

    /**
     * Verifica se o usuário está logado.
     * 
     * isset(): verifica se uma variável existe E não é null.
     * Diferente de empty():
     *   isset($x)  → true se $x existe e não é null
     *   empty($x)  → true se $x não existe, ou é null, 0, "", false, []
     * 
     * @return bool True se logado, False se não
     */
    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Redireciona o navegador para outra rota.
     * 
     * CONCEITO — header():
     * Envia um cabeçalho HTTP cru (raw header) para o navegador.
     * "Location: URL" é um cabeçalho especial que instrui o navegador
     * a ir para outra página (redirecionamento HTTP 302).
     * 
     * REGRA IMPORTANTE: header() DEVE ser chamada ANTES de qualquer output
     * (echo, HTML, espaço em branco). Se algo já foi enviado ao navegador,
     * dá erro "Headers already sent". Por isso session_start() e header()
     * devem estar no TOPO do código, antes de qualquer HTML.
     * 
     * exit: para a execução do script IMEDIATAMENTE após o redirecionamento.
     * Sem exit, o PHP continuaria executando o resto do código (desperdiçando
     * recursos e potencialmente causando comportamento inesperado).
     * 
     * @param string $route Nome da rota para redirecionar
     */
    private function redirect(string $route): void
    {
        header("Location: index.php?route=" . $route);
        exit;
    }
}
