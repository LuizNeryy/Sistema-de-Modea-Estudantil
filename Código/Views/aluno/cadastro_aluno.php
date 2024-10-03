<?php
require_once '../../config/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $endereco = $_POST['endereco'];
    $instituicao_id = $_POST['instituicao'];
    $curso = $_POST['curso'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); 

    $sql = "INSERT INTO alunos (nome, email, cpf, rg, endereco, instituicao_id, curso, senha) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssississ", $nome, $email, $cpf, $rg, $endereco, $instituicao_id, $curso, $senha);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
}

$instituicoes = [];
$result = $conn->query("SELECT id, nome FROM instituicoes");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instituicoes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Aluno</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        form {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px 0;
            margin-bottom: 15px;
            border: none;
            border-bottom: 1px solid #ccc;
            background: none;
            font-size: 16px;
        }
        select {
            border-bottom: 1px solid #ccc;
        }
        input:focus, select:focus {
            outline: none;
            border-bottom: 1px solid #000;
        }
        input[type="submit"] {
            background-color: #000;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #333;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #000;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Cadastro de Aluno</h1>
    <form action="cadastro_aluno.php" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" required maxlength="11" pattern="\d{11}" title="O CPF deve conter apenas números">

        <label for="rg">RG:</label>
        <input type="text" id="rg" name="rg" required maxlength="13" oninput="aplicarMascaraRG(this)">

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" required>

        <label for="instituicao">Instituição:</label>
        <select id="instituicao" name="instituicao" required>
            <option value="">Selecione uma instituição</option>
            <?php foreach ($instituicoes as $instituicao): ?>
                <option value="<?= $instituicao['id'] ?>"><?= $instituicao['nome'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="curso">Curso:</label>
        <input type="text" id="curso" name="curso" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <input type="submit" value="Cadastrar">
    </form>

    <div class="login-link">
        <p>Já possui conta? <a href="../login.php">Entre aqui!</a></p>
    </div>
</body>
</html>
