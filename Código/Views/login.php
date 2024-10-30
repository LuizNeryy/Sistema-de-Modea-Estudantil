<?php
session_start(); // Inicia a sessão

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Inclua a conexão ao banco de dados
    include '../config/conexao.php'; // Ajuste o caminho conforme sua estrutura

    // Verifica se os campos foram preenchidos
    if (isset($_POST['login']) && isset($_POST['senha'])) {
        $login = $_POST['login']; // Aqui pode ser CPF (aluno/professor) ou CNPJ (empresa)
        $senha = $_POST['senha'];

        // Tenta autenticar como aluno
        $queryAluno = "SELECT id, senha FROM alunos WHERE cpf = ?";
        $stmtAluno = $conn->prepare($queryAluno);
        $stmtAluno->bind_param("s", $login);
        $stmtAluno->execute();
        $resultAluno = $stmtAluno->get_result();

        if ($resultAluno->num_rows > 0) {
            $user = $resultAluno->fetch_assoc();
            if (password_verify($senha, $user['senha'])) {
                // Autentica o aluno
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['tipo_usuario'] = 'aluno'; // Define o tipo de usuário como aluno
                header("Location: ../models/aluno.php"); // Redireciona para a página do aluno
                exit();
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            // Se não encontrou um aluno, tenta buscar um professor
            $queryProfessor = "SELECT id, senha FROM professores WHERE cpf = ?";
            $stmtProfessor = $conn->prepare($queryProfessor);
            $stmtProfessor->bind_param("s", $login);
            $stmtProfessor->execute();
            $resultProfessor = $stmtProfessor->get_result();

            if ($resultProfessor->num_rows > 0) {
                $user = $resultProfessor->fetch_assoc();
                if (password_verify($senha, $user['senha'])) {
                    // Autentica o professor
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['tipo_usuario'] = 'professor'; // Define o tipo de usuário como professor
                    header("Location: ../models/professor.php"); // Redireciona para a página do professor
                    exit();
                } else {
                    $erro = "Senha incorreta.";
                }
            } else {
                // Se não encontrou um professor, tenta autenticar como empresa
                $queryEmpresa = "SELECT id, senha FROM empresas WHERE cnpj = ?";
                $stmtEmpresa = $conn->prepare($queryEmpresa);
                $stmtEmpresa->bind_param("s", $login);
                $stmtEmpresa->execute();
                $resultEmpresa = $stmtEmpresa->get_result();

                if ($resultEmpresa->num_rows > 0) {
                    $user = $resultEmpresa->fetch_assoc();
                    if (password_verify($senha, $user['senha'])) {
                        // Autentica a empresa
                        $_SESSION['usuario_id'] = $user['id'];
                        $_SESSION['tipo_usuario'] = 'empresa'; // Define o tipo de usuário como empresa
                        header("Location: ../models/empresa.php"); // Redireciona para a página da empresa
                        exit();
                    } else {
                        $erro = "Senha incorreta.";
                    }
                } else {
                    $erro = "Usuário ou empresa não encontrado.";
                }
            }
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Moeda Estudantil</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Inclua seu CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2em;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1em;
        }
        .input-group {
            margin-bottom: 1em;
        }
        .input-group label {
            display: block;
            margin-bottom: 0.5em;
        }
        .input-group input {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .btn {
            width: 100%;
            padding: 0.7em;
            border: none;
            border-radius: 3px;
            background-color: #000;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #333;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 1em;
        }
        .link {
            text-align: center;
            margin-top: 1em;
        }
        .link a {
            color: #007bff;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($erro)) { ?>
            <div class="error"><?= $erro; ?></div>
        <?php } ?>
        <form action="" method="POST">
            <div class="input-group">
                <label for="login">CPF ou CNPJ:</label> <!-- Agora aceita CPF e CNPJ -->
                <input type="text" id="login" name="login" required>
            </div>
            <div class="input-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <div class="link">
            <p>Não possui conta? <a href="aluno/cadastro_aluno.php">Cadastre-se aqui!</a></p>
        </div>
    </div>
</body>
</html>
