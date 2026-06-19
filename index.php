<?php
// =============================================================================
// FRONT CONTROLLER — Ponto de Entrada da Aplicação
// =============================================================================
// Este é o ÚNICO arquivo que o navegador acessa diretamente.
// Todas as URLs passam por aqui, e ele decide qual Controller/Action executar.
//
// CONCEITO — Front Controller Pattern:
// Em vez de ter dezenas de arquivos PHP acessíveis (login.php, register.php,
// tasks.php, delete.php...), temos UM ÚNICO ponto de entrada.
//
// Vantagens:
//   1. Centraliza configuração (session_start, includes, segurança)
//   2. Facilita controle de acesso (verificar se o usuário está logado)
//   3. Facilita manutenção (só um arquivo para alterar a lógica de roteamento)
//   4. Todas as requisições são tratadas de forma consistente
//
// Como funciona:
//   URL: index.php?route=tasks      → TaskController->index()
//   URL: index.php?route=login      → AuthController->showLogin()
//   URL: index.php?route=task_edit   → TaskController->edit()
//
// O parâmetro "route" na URL define QUAL ação será executada.
// =============================================================================

// =========================================================================
// 1. INICIAR SESSÃO
// =========================================================================
// session_start() DEVE ser a PRIMEIRA coisa do script (antes de qualquer output).
// Ela faz duas coisas:
//   - Se NÃO existe sessão: cria uma nova e gera um ID único (PHPSESSID)
//   - Se JÁ existe sessão: retoma a sessão existente usando o cookie PHPSESSID
//
// IMPORTANTE: se você der echo, print ou até um espaço em branco ANTES de
// session_start(), o PHP dará erro "Headers already sent" porque ele precisa
// enviar o cookie ANTES do conteúdo HTML.
session_start();

// =========================================================================
// 2. CARREGAR OS CONTROLLERS
// =========================================================================
// Incluímos os arquivos dos Controllers para poder instanciá-los.
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/TaskController.php';

// =========================================================================
// 3. IDENTIFICAR A ROTA
// =========================================================================
// Lê o parâmetro "route" da URL.
// Se não existir (ex: acessou só "index.php"), usa 'home' como padrão.
$route = $_GET['route'] ?? 'home';

// =========================================================================
// 4. ROTEAMENTO — Mapear rotas para ações
// =========================================================================
// Usamos switch/case para decidir qual ação executar baseado na rota.
//
// CONCEITO — switch vs if/elseif:
// switch é mais legível quando comparamos UMA variável com MUITOS valores possíveis.
// É equivalente a uma cadeia de if/elseif, mas mais organizado.
//
// Cada "case" é um valor possível da variável.
// "break" impede que o código "caia" para o próximo case (fall-through).
// "default" é executado se NENHUM case corresponder.

switch ($route) {

    // =====================================================================
    // ROTAS PÚBLICAS (não precisam de login)
    // =====================================================================

    case 'home':
        // Página inicial: redireciona para login ou tarefas
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?route=tasks');
        } else {
            header('Location: index.php?route=login');
        }
        exit;

    case 'login':
        $controller = new AuthController();
        // Verifica o método HTTP para decidir se exibe o formulário ou processa
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();       // Processa o login (valida credenciais)
        } else {
            $controller->showLogin();   // Exibe o formulário de login
        }
        break;

    case 'register':
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->register();     // Processa o cadastro (cria usuário)
        } else {
            $controller->showRegister(); // Exibe o formulário de cadastro
        }
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();          // Destrói a sessão e redireciona
        break;

    // =====================================================================
    // ROTAS PROTEGIDAS (precisam de login)
    // =====================================================================
    // Antes de executar qualquer ação de tarefas, verificamos se o usuário
    // está logado. Se não estiver, redirecionamos para o login.
    //
    // Esse é o CONTROLE DE ACESSO (ou middleware de autenticação simplificado).
    // Em frameworks como Laravel ou Symfony, isso é feito com "middlewares".
    // Aqui fazemos manualmente para fins didáticos.
    // =====================================================================

    case 'tasks':
        requireLogin();
        $controller = new TaskController();
        $controller->index();           // Lista todas as tarefas
        break;

    case 'task_create':
        requireLogin();
        $controller = new TaskController();
        $controller->create();          // Exibe form (GET) ou cria tarefa (POST)
        break;

    case 'task_edit':
        requireLogin();
        $controller = new TaskController();
        $controller->edit();            // Exibe form (GET) ou atualiza tarefa (POST)
        break;

    case 'task_delete':
        requireLogin();
        $controller = new TaskController();
        $controller->delete();          // Exclui a tarefa (POST)
        break;

    // =====================================================================
    // ROTA NÃO ENCONTRADA (404)
    // =====================================================================
    default:
        // http_response_code(): define o código de status HTTP da resposta.
        // 404 = "Not Found" (página não encontrada)
        // Outros códigos comuns:
        //   200 = OK (sucesso) — é o padrão
        //   301 = Moved Permanently (redirecionamento permanente)
        //   302 = Found (redirecionamento temporário) — usado pelo header("Location:")
        //   403 = Forbidden (proibido — sem permissão)
        //   500 = Internal Server Error (erro no servidor)
        http_response_code(404);
        echo '<h1>Página não encontrada</h1>';
        echo '<p>A rota "' . htmlspecialchars($route, ENT_QUOTES, 'UTF-8') . '" não existe.</p>';
        echo '<a href="index.php">Voltar ao início</a>';
        break;
}

// =========================================================================
// FUNÇÃO AUXILIAR: Verificar Login
// =========================================================================
/**
 * Verifica se o usuário está logado.
 * Se não estiver, redireciona para a página de login.
 * 
 * Esta função é chamada ANTES de executar qualquer rota protegida.
 * Funciona como um "guarda" (guard) que impede acesso não autorizado.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?route=login');
        exit;
    }
}
