# Walkthrough — CRUD PHP MVC Educacional

## O que foi feito

Projeto completo de CRUD em PHP puro com padrão MVC, sistema de autenticação e persistência de dados via MySQL (XAMPP). Todos os arquivos estão extensamente comentados em português, explicando cada conceito, função nativa e decisão de design.

## Estrutura do Projeto

```
php-crud-simples-exemplo/
├── config/database.php          ← Conexão PDO com MySQL
├── controllers/
│   ├── AuthController.php       ← Login, cadastro, logout
│   └── TaskController.php       ← CRUD de tarefas
├── models/
│   ├── User.php                 ← Operações no banco (users)
│   └── Task.php                 ← CRUD no banco (tasks)
├── views/
│   ├── layout/
│   │   ├── header.php           ← Cabeçalho reutilizável
│   │   └── footer.php           ← Rodapé reutilizável
│   ├── auth/
│   │   ├── login.php            ← Formulário de login
│   │   └── register.php         ← Formulário de cadastro
│   └── tasks/
│       ├── index.php            ← Lista de tarefas
│       ├── create.php           ← Criar tarefa
│       └── edit.php             ← Editar tarefa
├── public/css/style.css         ← Estilos visuais
├── database.sql                 ← Script SQL
├── index.php                    ← Front Controller
└── .htaccess                    ← Proteção de diretórios
```

## Como Configurar e Testar

### 1. Iniciar o XAMPP
- Abra o **XAMPP Control Panel**
- Inicie o **Apache** e o **MySQL**

### 2. Criar o Banco de Dados
- Acesse **http://localhost/phpmyadmin**
- Clique em **"SQL"** no menu superior
- Cole o conteúdo de [database.sql](file:///c:/xampp/htdocs/php-crud-simples-exemplo/database.sql) e clique em **"Executar"**

### 3. Acessar a Aplicação
- Abra o navegador em: **http://localhost/php-crud-simples-exemplo/**
- Será redirecionado para a tela de login

### 4. Testar o Fluxo Completo
1. **Cadastrar** uma nova conta (preencher nome, e-mail, senha)
2. **Login** com o e-mail e senha cadastrados
3. **Criar** uma tarefa (botão "+ Nova Tarefa")
4. **Listar** as tarefas (página principal)
5. **Editar** uma tarefa (botão "✏️ Editar")
6. **Marcar como concluída** (alterar status na edição)
7. **Excluir** uma tarefa (botão "🗑️ Excluir")
8. **Logout** (botão "Sair")

## Conceitos Explicados nos Comentários

| Conceito | Arquivo |
|---|---|
| **PDO** (o que é, por que usar) | [database.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/config/database.php) |
| **DSN, charset, atributos PDO** | [database.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/config/database.php) |
| **Prepared Statements** (SQL Injection) | [User.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/models/User.php) |
| **password_hash / password_verify** | [User.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/models/User.php), [AuthController.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/controllers/AuthController.php) |
| **CRUD completo** (INSERT, SELECT, UPDATE, DELETE) | [Task.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/models/Task.php) |
| **fetch() vs fetchAll() vs rowCount()** | [Task.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/models/Task.php) |
| **$_SESSION** (sessões PHP) | [AuthController.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/controllers/AuthController.php) |
| **$_POST, $_GET** (superglobais) | [AuthController.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/controllers/AuthController.php), [TaskController.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/controllers/TaskController.php) |
| **session_start / session_destroy** | [AuthController.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/controllers/AuthController.php), [index.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/index.php) |
| **header() e redirecionamentos** | [AuthController.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/controllers/AuthController.php) |
| **htmlspecialchars() (prevenção XSS)** | [header.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/views/layout/header.php) |
| **filter_var() (validação)** | [AuthController.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/controllers/AuthController.php) |
| **Padrão MVC** | Comentários em cada camada |
| **Front Controller pattern** | [index.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/index.php) |
| **require vs include vs require_once** | [User.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/models/User.php) |
| **Operador ternário, ??, foreach** | Views de tarefas |
| **HTTP status codes** | [index.php](file:///c:/xampp/htdocs/php-crud-simples-exemplo/index.php) |
| **SQL: PRIMARY KEY, FOREIGN KEY, ENUM, AUTO_INCREMENT** | [database.sql](file:///c:/xampp/htdocs/php-crud-simples-exemplo/database.sql) |
