<?php
// =============================================================================
// VIEW: Cadastro (Register)
// =============================================================================
// Formulário de cadastro de novo usuário.
// Recebe do Controller: $pageTitle, $error, $success
// =============================================================================

require __DIR__ . '/../layout/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Criar Conta</h1>
            <p>Cadastre-se para gerenciar suas tarefas</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert--success">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="index.php?route=register" method="POST" class="auth-form">

            <div class="form-group">
                <label for="name">Nome Completo</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Seu nome completo"
                    required
                    autocomplete="name"
                    value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <!--
                    value="...": preenche o campo com o valor anterior.
                    Se o cadastro falhar (ex: senhas não coincidem), o usuário
                    não precisa digitar tudo de novo. Isso melhora a UX.
                    
                    Usamos htmlspecialchars() no value também para evitar XSS.
                    Se o usuário digitou aspas no nome, sem essa proteção
                    o HTML quebraria:  value="João "Hacker" Silva"
                    Com proteção:      value="João &quot;Hacker&quot; Silva"
                -->
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="seu@email.com" 
                    required
                    autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Mínimo 6 caracteres" 
                    required
                    minlength="6"
                    autocomplete="new-password"
                >
                <!-- minlength="6": validação HTML5 — impede envio se tiver menos de 6 caracteres -->
                <!-- Note que NÃO preenchemos o value da senha — por segurança, senhas nunca são reenviadas -->
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmar Senha</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    placeholder="Repita a senha" 
                    required
                    minlength="6"
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn btn--primary btn--full">
                Criar Conta
            </button>
        </form>

        <div class="auth-footer">
            <p>Já tem uma conta? <a href="index.php?route=login">Faça login</a></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
