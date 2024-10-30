<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /Views/login.php"); 
    exit();
}

include '../../config/conexao.php'; 

$usuario_id = $_SESSION['usuario_id'];

// Consultar informações do professor
$query = "SELECT nome, cpf, departamento, instituicao, moedas FROM professores WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $professor = $result->fetch_assoc();
} else {
    die("Professor não encontrado.");
}

// Processar a distribuição de moedas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aluno_id']) && isset($_POST['quantidade'])) {
    $aluno_id = $_POST['aluno_id'];
    $quantidade = $_POST['quantidade'];

    // Verificar se o aluno existe
    $queryAluno = "SELECT nome FROM alunos WHERE id = ?";
    $stmtAluno = $conn->prepare($queryAluno);
    $stmtAluno->bind_param("i", $aluno_id);
    $stmtAluno->execute();
    $resultAluno = $stmtAluno->get_result();

    if ($resultAluno->num_rows > 0) {
        // Verificar se o professor tem moedas suficientes
        if ($professor['moedas'] >= $quantidade) {
            // Atualizar moedas do aluno
            $queryDistribuir = "UPDATE alunos SET moedas = moedas + ? WHERE id = ?";
            $stmtDistribuir = $conn->prepare($queryDistribuir);
            $stmtDistribuir->bind_param("ii", $quantidade, $aluno_id);
            $stmtDistribuir->execute();

            // Atualizar moedas do professor
            $novaQuantidade = $professor['moedas'] - $quantidade;
            $queryAtualizaMoedas = "UPDATE professores SET moedas = ? WHERE id = ?";
            $stmtAtualizaMoedas = $conn->prepare($queryAtualizaMoedas);
            $stmtAtualizaMoedas->bind_param("ii", $novaQuantidade, $usuario_id);
            $stmtAtualizaMoedas->execute();

            echo "<script>alert('Moedas distribuídas com sucesso!');</script>";
            // Redirecionar para evitar reenvio
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "<script>alert('Você não tem moedas suficientes para distribuir.');</script>";
        }
    } else {
        echo "<script>alert('Não há um aluno com esse ID.');</script>";
    }
}

// Buscar informações do aluno pelo ID
$nome_aluno = '';
if (isset($_POST['aluno_id'])) {
    $aluno_id = $_POST['aluno_id'];
    $queryAluno = "SELECT nome FROM alunos WHERE id = ?";
    $stmtAluno = $conn->prepare($queryAluno);
    $stmtAluno->bind_param("i", $aluno_id);
    $stmtAluno->execute();
    $resultAluno = $stmtAluno->get_result();

    if ($resultAluno->num_rows > 0) {
        $aluno = $resultAluno->fetch_assoc();
        $nome_aluno = $aluno['nome'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribuir Moedas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: 2em auto;
            padding: 2em;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: left;
        }

        h2 {
            margin-bottom: 0.5em;
            color: #333;
        }

        .info {
            margin: 1em 0;
        }

        .moedas {
            font-size: 1.5em;
            color: black; 
        }

        .moedas i {
            color: gold; 
        }

        .btn {
            padding: 0.7em 1em;
            border: none;
            border-radius: 5px;
            background-color: orange; 
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 0.5em 0;
        }

        .btn:hover {
            background-color: blueviolet; 
        }

        .form-group {
            margin-bottom: 1em;
        }

        label {
            display: block;
            margin-bottom: 0.5em;
            color: #333;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .nome-aluno {
            margin-top: 1em;
            padding: 0.5em;
            background-color: #e7f3fe;
            border: 1px solid #b3d4fc;
            border-radius: 5px;
            color: #31708f;
        }

        .voltar-btn {
            background-color: black;
            margin-top: 2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Distribuir Moedas</h2>
        <div class="info">
            <p><strong>Professor:</strong> <?= htmlspecialchars($professor['nome']) ?></p>
            <p><span class="moedas"><i class="fas fa-coins"></i> <?= htmlspecialchars($professor['moedas']) ?> Moedas</span></p>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label for="aluno_id">ID do Aluno:</label>
                <input type="text" id="aluno_id" name="aluno_id" value="<?= htmlspecialchars($aluno_id ?? '') ?>" required oninput="fetchNomeAluno(this.value)">
            </div>

            <div id="nome_aluno_display" class="nome-aluno">
                <?php if ($nome_aluno): ?>
                    <strong>Nome do Aluno:</strong> <?= htmlspecialchars($nome_aluno) ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="quantidade">Quantidade de Moedas:</label>
                <input type="number" id="quantidade" name="quantidade" min="1" max="<?= htmlspecialchars($professor['moedas']) ?>" required>
            </div>

            <button type="submit" class="btn">Distribuir Moedas</button>
        </form>

        <form action="../../models/Professor.php" method="POST"> <!-- Altere o caminho para a página anterior -->
            <button type="submit" class="btn voltar-btn">Voltar</button>
        </form>
    </div>

    <script>
        function fetchNomeAluno(aluno_id) {
            if (aluno_id) {
                fetch('../../config/fetch_nome_aluno.php?aluno_id=' + aluno_id)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('nome_aluno_display').innerHTML = data ? `<strong>Nome do Aluno:</strong> ${data}` : '';
                    });
            } else {
                document.getElementById('nome_aluno_display').innerHTML = '';
            }
        }
    </script>
</body>
</html>
