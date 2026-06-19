<?php
// =============================================================================
// VIEW: Lista de Tarefas (index)
// =============================================================================
// Exibe todas as tarefas do usuário em formato de cards.
// Esta é a view principal do CRUD — mostra o "R" (Read) em ação.
// Recebe do Controller: $pageTitle, $tasks (array), $success
// =============================================================================

require __DIR__ . '/../layout/header.php';
?>

<div class="tasks-header">
    <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="index.php?route=task_create" class="btn btn--primary">
        + Nova Tarefa
    </a>
</div>

<!-- Mensagem flash (sucesso ao criar/editar/excluir) -->
<?php if (!empty($success)): ?>
    <div class="alert alert--success">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (empty($tasks)): ?>
    <!-- Estado vazio: quando o usuário não tem tarefas -->
    <div class="empty-state">
        <div class="empty-state-icon">📝</div>
        <h2>Nenhuma tarefa ainda</h2>
        <p>Crie sua primeira tarefa para começar a organizar suas atividades.</p>
        <a href="index.php?route=task_create" class="btn btn--primary">
            Criar Primeira Tarefa
        </a>
    </div>
<?php else: ?>
    <!--
        CONCEITO — foreach:
        Percorre cada elemento de um array, um por um.
        
        foreach ($tasks as $task):
        A cada iteração, $task contém UMA tarefa (um array associativo):
          $task = ['id' => 1, 'title' => 'Estudar PHP', 'status' => 'pendente', ...]
        
        O loop repete o HTML abaixo para CADA tarefa na lista.
    -->
    <div class="tasks-grid">
        <?php foreach ($tasks as $task): ?>
            <div class="task-card <?= $task['status'] === 'concluida' ? 'task-card--done' : '' ?>">
                <!--
                    Operador ternário (? :):
                    condição ? valor_se_verdadeiro : valor_se_falso
                    
                    É uma versão compacta do if/else:
                    if ($task['status'] === 'concluida') {
                        echo 'task-card--done';
                    } else {
                        echo '';
                    }
                -->
                
                <div class="task-card-header">
                    <h3 class="task-title">
                        <?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>
                    <span class="task-badge <?= $task['status'] === 'concluida' ? 'task-badge--done' : 'task-badge--pending' ?>">
                        <?= $task['status'] === 'concluida' ? '✅ Concluída' : '⏳ Pendente' ?>
                    </span>
                </div>

                <?php if (!empty($task['description'])): ?>
                    <p class="task-description">
                        <?= htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <div class="task-meta">
                    <!--
                        date(): formata uma data/hora.
                        strtotime(): converte uma string de data em timestamp Unix.
                        
                        date('d/m/Y H:i', strtotime($task['created_at']))
                        1. strtotime('2026-06-19 14:30:00') → 1781957400 (timestamp)
                        2. date('d/m/Y H:i', 1781957400) → '19/06/2026 14:30'
                        
                        Formatos mais usados:
                        d = dia (01-31)    m = mês (01-12)    Y = ano (4 dígitos)
                        H = hora (00-23)   i = minutos (00-59) s = segundos (00-59)
                    -->
                    <span class="task-date">
                        Criada em <?= date('d/m/Y H:i', strtotime($task['created_at'])) ?>
                    </span>
                </div>

                <div class="task-actions">
                    <!-- Link para editar: passa o ID da tarefa pela URL ($_GET) -->
                    <a href="index.php?route=task_edit&id=<?= $task['id'] ?>" class="btn btn--small btn--secondary">
                        ✏️ Editar
                    </a>

                    <!--
                        FORMULÁRIO DE EXCLUSÃO:
                        Usamos um <form> com method="POST" para exclusão em vez de um link <a>.
                        
                        Por quê?
                        1. Links usam GET — bots e crawlers poderiam excluir dados acidentalmente
                        2. POST é mais seguro para ações que MODIFICAM dados
                        3. O campo hidden "id" envia o ID da tarefa sem mostrá-lo ao usuário
                        
                        onclick="return confirm(...)":
                        Exibe uma caixa de confirmação do navegador.
                        Se o usuário clicar "OK", retorna true e o form é enviado.
                        Se clicar "Cancelar", retorna false e o form NÃO é enviado.
                    -->
                    <form action="index.php?route=task_delete" method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button type="submit" class="btn btn--small btn--danger" 
                                onclick="return confirm('Tem certeza que deseja excluir esta tarefa?')">
                            🗑️ Excluir
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
