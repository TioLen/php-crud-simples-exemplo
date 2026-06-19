<?php
// =============================================================================
// VIEW: Criar Tarefa (Create)
// =============================================================================
// Formulário para criar uma nova tarefa.
// Recebe do Controller: $pageTitle, $error
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

        <form action="index.php?route=task_create" method="POST" class="task-form">

            <div class="form-group">
                <label for="title">Título da Tarefa *</label>
                <input type="text" id="title" name="title" placeholder="Ex: Estudar PHP, Fazer compras..." required
                    maxlength="200" value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <!-- maxlength="200": limita a 200 caracteres (mesmo limite do banco VARCHAR(200)) -->
            </div>

            <div class="form-group">
                <label for="description">Descrição (opcional)</label>
                <!--
                    <textarea>: campo de texto multilinha.
                    Diferente de <input>, o valor do textarea vai ENTRE as tags,
                    não no atributo value.
                    
                    rows="5": define a altura inicial (5 linhas visíveis)
                -->
                <textarea id="description" name="description" rows="5"
                    placeholder="Descreva os detalhes da tarefa..."><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php /* ATENÇÃO: sem espaço entre > e <?= espaços extras apareceriam no campo */ ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">
                    Criar Tarefa
                </button>
                <a href="index.php?route=tasks" class="btn btn--ghost">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>