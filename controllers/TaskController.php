<?php
// =============================================================================
// CONTROLLER: TaskController (Tarefas — CRUD)
// =============================================================================
// Este controller gerencia todas as ações relacionadas a tarefas:
//   - Listar tarefas (index)
//   - Criar nova tarefa (create)
//   - Editar tarefa existente (edit)
//   - Excluir tarefa (delete)
//
// CONCEITO — Fluxo MVC:
//   1. Usuário acessa uma URL → Front Controller (index.php) identifica a rota
//   2. Front Controller instancia o Controller correto e chama o método
//   3. O Controller chama o Model para obter/modificar dados
//   4. O Controller passa os dados para a View
//   5. A View gera o HTML que o usuário vê no navegador
//
//   Usuário → URL → Controller → Model → Controller → View → HTML → Usuário
// =============================================================================

require_once __DIR__ . '/../models/Task.php';

class TaskController
{
    /** @var Task Instância do Model de tarefas */
    private $taskModel;

    /** @var int ID do usuário logado (obtido da sessão) */
    private $userId;

    public function __construct()
    {
        $this->taskModel = new Task();

        // Obtém o ID do usuário da sessão.
        // O front controller (index.php) já verificou que o usuário está logado
        // antes de chegar aqui, então $_SESSION['user_id'] SEMPRE existe neste ponto.
        $this->userId = $_SESSION['user_id'];
    }

    // =========================================================================
    // INDEX — Listar todas as tarefas
    // =========================================================================
    /**
     * Exibe a lista de tarefas do usuário logado.
     * Este é o "R" (Read) do CRUD em ação na camada Controller.
     */
    public function index(): void
    {
        $pageTitle = 'Minhas Tarefas';

        // Chama o Model para buscar TODAS as tarefas do usuário logado
        // O Model retorna um array de tarefas (ou array vazio se não houver)
        $tasks = $this->taskModel->getAllByUser($this->userId);

        // Verifica se existe uma mensagem de sucesso na sessão
        // (definida após criar, editar ou excluir uma tarefa)
        $success = $_SESSION['flash_success'] ?? '';

        // unset(): remove uma variável ou chave de um array.
        // Removemos a mensagem flash após lê-la para que ela apareça APENAS UMA VEZ.
        // Esse padrão é chamado de "flash message" — mensagem temporária.
        unset($_SESSION['flash_success']);

        // Carrega a View, que terá acesso a $pageTitle, $tasks e $success
        require __DIR__ . '/../views/tasks/index.php';
    }

    // =========================================================================
    // CREATE — Criar nova tarefa
    // =========================================================================
    /**
     * Exibe o formulário de criação (GET) ou processa o envio (POST).
     * 
     * CONCEITO — $_SERVER['REQUEST_METHOD']:
     * Identifica o MÉTODO HTTP da requisição atual.
     * 
     * Quando o usuário CLICA no link "Nova Tarefa" → método GET
     *   → Exibimos o formulário vazio
     * 
     * Quando o usuário ENVIA o formulário → método POST
     *   → Processamos os dados e salvamos no banco
     * 
     * Essa verificação permite usar o MESMO método do controller
     * para duas ações diferentes (exibir e processar), o que é um
     * padrão comum em aplicações web.
     */
    public function create(): void
    {
        $pageTitle = 'Nova Tarefa';
        $error = '';

        // Verifica se é uma requisição POST (formulário enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            // Validação simples: título é obrigatório
            if (empty($title)) {
                $error = 'O título é obrigatório.';
                require __DIR__ . '/../views/tasks/create.php';
                return;
            }

            // Chama o Model para inserir a tarefa no banco
            if ($this->taskModel->create($this->userId, $title, $description)) {
                // Define uma "flash message" na sessão antes de redirecionar
                $_SESSION['flash_success'] = 'Tarefa criada com sucesso!';
                $this->redirect('tasks');
            } else {
                $error = 'Erro ao criar tarefa. Tente novamente.';
            }
        }

        // Se for GET ou se houve erro no POST, exibe o formulário
        require __DIR__ . '/../views/tasks/create.php';
    }

    // =========================================================================
    // EDIT — Editar tarefa existente
    // =========================================================================
    /**
     * Exibe o formulário de edição preenchido (GET) ou processa a atualização (POST).
     * 
     * CONCEITO — $_GET:
     * $_GET é uma superglobal que contém os parâmetros da URL.
     * Se a URL é: index.php?route=task_edit&id=5
     * Então: $_GET['route'] = 'task_edit' e $_GET['id'] = '5'
     * 
     * CONCEITO — intval():
     * Converte uma string para inteiro (int).
     * "5" → 5, "abc" → 0, "3.7" → 3, "" → 0
     * Usamos para garantir que o ID é um número válido, evitando erros.
     * 
     * Alternativa: (int) — casting manual. Exemplo: (int) $_GET['id']
     * intval() e (int) são praticamente equivalentes.
     */
    public function edit(): void
    {
        $pageTitle = 'Editar Tarefa';
        $error = '';

        // Obtém o ID da tarefa da URL
        $id = intval($_GET['id'] ?? 0);

        // Busca a tarefa no banco (verificando se pertence ao usuário)
        $task = $this->taskModel->getById($id, $this->userId);

        // Se a tarefa não existe ou não pertence ao usuário, redireciona
        if (!$task) {
            $_SESSION['flash_success'] = 'Tarefa não encontrada.';
            $this->redirect('tasks');
            return; // O redirect já chama exit, mas return deixa a intenção clara
        }

        // Se é POST (formulário de edição enviado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status      = $_POST['status'] ?? 'pendente';

            if (empty($title)) {
                $error = 'O título é obrigatório.';
                // Atualiza $task com os dados do formulário para não perder o que o usuário digitou
                $task['title'] = $title;
                $task['description'] = $description;
                $task['status'] = $status;
                require __DIR__ . '/../views/tasks/edit.php';
                return;
            }

            // Valida o status (só aceita os valores permitidos pelo ENUM no banco)
            // in_array(): verifica se um valor existe dentro de um array
            // Exemplo: in_array('pendente', ['pendente', 'concluida']) → true
            //          in_array('invalido', ['pendente', 'concluida']) → false
            if (!in_array($status, ['pendente', 'concluida'])) {
                $status = 'pendente'; // Valor padrão se algo inválido for enviado
            }

            if ($this->taskModel->update($id, $this->userId, $title, $description, $status)) {
                $_SESSION['flash_success'] = 'Tarefa atualizada com sucesso!';
                $this->redirect('tasks');
            } else {
                $error = 'Erro ao atualizar tarefa.';
            }
        }

        // Exibe o formulário preenchido com os dados atuais da tarefa
        require __DIR__ . '/../views/tasks/edit.php';
    }

    // =========================================================================
    // DELETE — Excluir tarefa
    // =========================================================================
    /**
     * Exclui uma tarefa.
     * 
     * SEGURANÇA: Idealmente, ações de exclusão deveriam usar POST (não GET).
     * Com GET, um link malicioso poderia excluir tarefas:
     *   <img src="index.php?route=task_delete&id=5"> ← exclui sem clicar!
     * 
     * Aqui usamos POST (via formulário com botão) para maior segurança.
     * Em projetos maiores, adicionaríamos um token CSRF para proteção extra.
     */
    public function delete(): void
    {
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0 && $this->taskModel->delete($id, $this->userId)) {
            $_SESSION['flash_success'] = 'Tarefa excluída com sucesso!';
        } else {
            $_SESSION['flash_success'] = 'Erro ao excluir tarefa.';
        }

        $this->redirect('tasks');
    }

    /**
     * Redireciona para outra rota.
     */
    private function redirect(string $route): void
    {
        header("Location: index.php?route=" . $route);
        exit;
    }
}
