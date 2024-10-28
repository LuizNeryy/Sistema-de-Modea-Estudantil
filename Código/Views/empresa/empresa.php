<?php
session_start(); // Inicia a sessão

// Inclua a conexão ao banco de dados
include '../../config/conexao.php'; // Ajuste o caminho conforme sua estrutura

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] != 'empresa') {
    header("Location: ../index.php"); // Redireciona para a página de login se não estiver autenticado
    exit();
}

// Armazena o nome da empresa logada
$empresa = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Empresa Desconhecida'; // Verificação

// Mensagem de sucesso ou erro
$mensagemSucesso = '';
$mensagemErro = '';

// Lógica para cadastrar recompensa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    
    // Inserindo a nova recompensa no banco de dados
    $queryInsert = "INSERT INTO recompensas (nome, descricao, preco, empresa) VALUES (?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($queryInsert);
    $stmtInsert->bind_param("ssds", $nome, $descricao, $preco, $empresa);
    
    try {
        if ($stmtInsert->execute()) {
            $mensagemSucesso = "Recompensa cadastrada com sucesso!";
        } else {
            $mensagemErro = "Erro ao cadastrar recompensa.";
        }
    } catch (mysqli_sql_exception $e) {
        $mensagemErro = "Erro: " . $e->getMessage();
    }
}

// Lógica para excluir recompensa
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $queryDelete = "DELETE FROM recompensas WHERE id = ? AND empresa = ?";
    $stmtDelete = $conn->prepare($queryDelete);
    $stmtDelete->bind_param("is", $id, $empresa);
    
    if ($stmtDelete->execute()) {
        $mensagemSucesso = "Recompensa excluída com sucesso!";
    } else {
        $mensagemErro = "Erro ao excluir recompensa.";
    }
}

// Busca as recompensas cadastradas pela empresa
$queryRecompensas = "SELECT * FROM recompensas WHERE empresa = ?";
$stmtRecompensas = $conn->prepare($queryRecompensas);
$stmtRecompensas->bind_param("s", $empresa);
$stmtRecompensas->execute();
$resultRecompensas = $stmtRecompensas->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Empresa</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Inclua seu CSS -->
    <style>
               body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: auto;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2em;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
            margin: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1em;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 1em;
        }
        .error {
            color: red;
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
        .input-group input, .input-group textarea {
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
        .recompensas-list {
            margin-top: 2em;
            border-top: 1px solid #ccc;
            padding-top: 1em;
        }
        .recompensa {
            padding: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Painel da Empresa</h2>

        <h3>Empresa parceira: <?php echo htmlspecialchars($empresa); ?></h3>

        <!-- Exibe mensagem de sucesso se existir -->
        <?php if ($mensagemSucesso): ?>
            <div class="success"><?= htmlspecialchars($mensagemSucesso); ?></div>
        <?php endif; ?>

        <!-- Exibe mensagem de erro se existir -->
        <?php if ($mensagemErro): ?>
            <div class="error"><?= htmlspecialchars($mensagemErro); ?></div>
        <?php endif; ?>

        <h4>Cadastrar Recompensa</h4>
        <form method="POST">
            <div class="input-group">
                <label for="nome">Nome da Recompensa:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="input-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" required></textarea>
            </div>
            <div class="input-group">
                <label for="preco">Preço (em moedas):</label>
                <input type="number" id="preco" name="preco" required min="1">
            </div>
            <input type="hidden" name="acao" value="cadastrar">
            <button type="submit" class="btn">Cadastrar Recompensa</button>
        </form>

        <!-- Lista de Recompensas -->
        <div class="recompensas-list">
            <h4>Recompensas Cadastradas:</h4>
            <?php if ($resultRecompensas->num_rows > 0): ?>
                <?php while ($recompensa = $resultRecompensas->fetch_assoc()): ?>
                    <div class="recompensa">
                        <h5><?= htmlspecialchars($recompensa['nome']); ?></h5>
                        <p><?= htmlspecialchars($recompensa['descricao']); ?></p>
                        <p><strong>Preço: <?= htmlspecialchars($recompensa['preco']); ?> moedas</strong></p>
                        <div>
                            <a href="editar_recompensa.php?id=<?= $recompensa['id']; ?>" class="btn" style="background-color: green;">Editar</a>
                            <a href="?acao=excluir&id=<?= $recompensa['id']; ?>" class="btn" style="background-color: red;">Excluir</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhuma recompensa cadastrada.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
