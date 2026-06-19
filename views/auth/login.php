<?php
// =============================================================================
// VIEW: Login
// =============================================================================
// Esta View exibe o formulário de login.
// Ela recebe do Controller as variáveis: $pageTitle e $error
//
// CONCEITO — Separação de View e Controller:
// A View NÃO faz nenhuma lógica de negócio (não verifica senha, não consulta banco).
// Ela apenas EXIBE os dados que o Controller preparou.
// Se houver um erro, o Controller define $error e a View mostra.
// =============================================================================

// Inclui o cabeçalho HTML (menu, <head>, etc.)
require __DIR__ . '/../layout/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Entrar na Conta</h1>
            <p>Acesse suas tarefas</p>
        </div>

        <!-- Exibe mensagem de erro, se houver -->
        <?php if (!empty($error)): ?>
            <div class="alert alert--error">
                <!-- 
                    htmlspecialchars(): proteção contra XSS
                    Sempre use ao exibir dados que PODEM ter vindo do usuário
                -->
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!--
            CONCEITO — Formulários HTML:
            <form> define um formulário que envia dados ao servidor.
            
            action: URL para onde os dados serão enviados
            method: método HTTP (POST para dados sensíveis, GET para buscas)
            
            Cada <input> com atributo "name" cria uma entrada no array $_POST.
            Exemplo: <input name="email"> → $_POST['email']
        -->
        <form action="index.php?route=login" method="POST" class="auth-form">

            <div class="form-group">
                <label for="email">E-mail</label>
                <!-- 
                    type="email": o navegador valida se é um e-mail válido (validação frontend)
                    required: impede envio do formulário se o campo estiver vazio (validação frontend)
                    id: identificador único, usado pelo <label for="email"> para acessibilidade
                    
                    LEMBRE-SE: validação frontend é para UX (experiência do usuário).
                    A validação REAL acontece no Controller (backend).
                -->
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="seu@email.com" 
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <!-- type="password": exibe pontos (•••) em vez do texto real -->
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Sua senha" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn--primary btn--full">
                Entrar
            </button>
        </form>

        <div class="auth-footer">
            <p>Não tem uma conta? <a href="index.php?route=register">Cadastre-se</a></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
