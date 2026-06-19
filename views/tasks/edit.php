<?php
// =============================================================================
// VIEW: Editar Tarefa (Edit)
// =============================================================================
// Formulário preenchido com os dados atuais da tarefa para edição.
// Recebe do Controller: $pageTitle, $error, $task (array com dados da tarefa)
//
// CONCEITO — Diferença entre Create e Edit:
// No CREATE, o formulário começa VAZIO e envia INSERT para o banco.
// No EDIT, o formulário começa PREENCHIDO e envia UPDATE para o banco.
// O Controller busca os dados da tarefa (via Model) e passa para esta View.
// =============================================================================

require __DIR__ . '/../layout/header.php';
?>

<div class="form-container">
    <div class="form-card">
        <div class="form-card-header">
            <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <a href="index.php?route=tasks" class="btn btn--secondary btn--small">
                ← Voltar
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!--
            Note que a action inclui o ID da tarefa:
            index.php?route=task_edit&id=5
            Assim o Controller sabe QUAL tarefa está sendo editada.
        -->
        <form action="index.php?route=task_edit&id=<?= $task['id'] ?>" method="POST" class="task-form">

            <div class="form-group">
                <label for="title">Título da Tarefa *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    required
                    maxlength="200"
                    value="<?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>"
                >
                <!-- O value é preenchido com os dados ATUAIS da tarefa ($task['title']) -->
            </div>

            <div class="form-group">
                <label for="description">Descrição (opcional)</label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="5"
                ><?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <!--
                    <select>: campo de seleção (dropdown/combobox)
                    Cada <option> é uma opção disponível.
                    O atributo value define o valor que será enviado no $_POST.
                    O atributo "selected" marca qual opção está selecionada.
                    
                    Usamos o operador ternário para marcar a opção correta:
                    Se $task['status'] === 'pendente', adiciona "selected" nessa opção.
                -->
                <select id="status" name="status" class="form-select">
                    <option value="pendente" <?= $task['status'] === 'pendente' ? 'selected' : '' ?>>
                        ⏳ Pendente
                    </option>
                    <option value="concluida" <?= $task['status'] === 'concluida' ? 'selected' : '' ?>>
                        ✅ Concluída
                    </option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">
                    Salvar Alterações
                </button>
                <a href="index.php?route=tasks" class="btn btn--ghost">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
