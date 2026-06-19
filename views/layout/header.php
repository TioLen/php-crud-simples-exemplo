<?php
// =============================================================================
// VIEW: Layout — Header (Cabeçalho)
// =============================================================================
// No padrão MVC, as VIEWS são responsáveis pela APRESENTAÇÃO (HTML/CSS).
// Elas recebem dados prontos do Controller e apenas os EXIBEM.
//
// Este arquivo é um LAYOUT PARCIAL (partial): um pedaço de HTML reutilizado
// em todas as páginas. Em vez de repetir o <head>, menu e <body> em cada View,
// centralizamos aqui. Isso segue o princípio DRY (Don't Repeat Yourself).
//
// CONCEITO — htmlspecialchars():
// Converte caracteres especiais em entidades HTML para prevenir XSS.
// XSS (Cross-Site Scripting): ataque onde alguém injeta código JavaScript
// malicioso nos dados que são exibidos na página.
//
// Exemplo sem proteção:
//   $name = '<script>alert("Hackeado!")</script>';
//   echo $name; ← o JavaScript seria EXECUTADO no navegador!
//
// Com htmlspecialchars():
//   echo htmlspecialchars($name);
//   → '&lt;script&gt;alert("Hackeado!")&lt;/script&gt;'
//   → o navegador EXIBE o texto, não executa
//
// ENT_QUOTES: converte aspas simples E duplas (proteção extra)
// 'UTF-8': define a codificação do texto
// =============================================================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <!-- viewport: ESSENCIAL para responsividade em dispositivos móveis -->
    <!-- Sem isso, o celular exibiria a versão desktop "encolhida" -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta description para SEO -->
    <meta name="description" content="Exemplo educacional de CRUD em PHP com padrão MVC">

    <title><?= htmlspecialchars($pageTitle ?? 'CRUD PHP MVC', ENT_QUOTES, 'UTF-8') ?> — CRUD PHP MVC</title>

    <!-- Google Fonts: tipografia moderna (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

    <!-- ===================================================================
         NAVEGAÇÃO
         ===================================================================
         isset(): verifica se $_SESSION['user_id'] existe.
         Se existir, o usuário está logado e mostramos o menu completo.
         Se não, mostramos apenas Login e Cadastro.
    -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-logo">
                <span class="logo-icon">📋</span>
                <span class="logo-text">CRUD PHP</span>
            </a>

            <div class="navbar-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Menu para usuários LOGADOS -->
                    <span class="navbar-greeting">
                        Olá, <strong><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </span>
                    <a href="index.php?route=tasks" class="nav-link">Minhas Tarefas</a>
                    <a href="index.php?route=task_create" class="nav-link nav-link--accent">+ Nova Tarefa</a>
                    <a href="index.php?route=logout" class="nav-link nav-link--danger">Sair</a>
                <?php else: ?>
                    <!-- Menu para visitantes (NÃO logados) -->
                    <a href="index.php?route=login" class="nav-link">Login</a>
                    <a href="index.php?route=register" class="nav-link nav-link--accent">Cadastre-se</a>
                <?php endif; ?>
                <!-- 
                    CONCEITO — Sintaxe alternativa do PHP para templates:
                    if(): / endif; é equivalente a if() { } 
                    A versão com : e endif é preferida em Views porque
                    fica mais fácil de ler quando misturada com HTML.
                    
                    Também vale para: foreach(): endforeach; while(): endwhile; for(): endfor;
                -->
            </div>
        </div>
    </nav>

    <!-- Container principal do conteúdo -->
    <main class="main-content">
        <div class="container">
