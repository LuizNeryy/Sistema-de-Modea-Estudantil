<?php
session_start();

// Inclua a conexão ao banco de dados
include '../../config/conexao.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] != 'empresa') {
    header("Location: ../index.php");
    exit();
}

// Mensagem de sucesso ou erro
$mensagemSucesso = '';
$mensagemErro = '';

// Lógica para editar recompensa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    
    // Atualizando a recompensa no banco de dados
    $queryUpdate = "UPDATE recompensas SET nome = ?, descricao = ?, preco = ? WHERE id = ? AND empresa = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    $stmtUpdate->bind_param("ssdsi", $nome, $descricao, $preco, $id, $_SESSION['usuario_id']);
    
    try {
        if ($stmtUpdate->execute()) {
            $mensagemSucesso = "Recompensa editada com sucesso!";
        } else {
            $mensagemErro = "Erro ao editar recompensa.";
        }
    } catch (mysqli_sql_exception $e) {
        $mensagemErro = "Erro: " . $e->getMessage();
    }
}

// Verifica se o ID da recompensa foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Busca a recompensa a ser editada
    $queryRecompensa = "SELECT * FROM recompensas WHERE id = ? AND empresa = ?";
    $stmtRecompensa = $conn->prepare($queryRecompensa);
    $stmtRecompensa->bind_param("is", $id, $_SESSION['usuario_id']);
    $stmtRecompensa->execute();
    $resultRecompensa = $stmtRecompensa->get_result();
    
    // Verifica se a recompensa foi encontrada
    if ($resultRecompensa->num_rows === 0) {
        header("Location: ../../models/empresa.php"); // Redireciona se não encontrar
        exit();
    }

    $recompensa = $resultRecompensa->fetch_assoc();
} else {
    header("Location: empresa.php"); // Redireciona se não houver ID
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Recompensa</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Inclua seu CSS -->
    <style>
        /* Adicione seu estilo CSS aqui se necessário */
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Recompensa</h2>

        <!-- Exibe mensagem de sucesso se existir -->
        <?php if ($mensagemSucesso): ?>
            <div class="success"><?= htmlspecialchars($mensagemSucesso); ?></div>
        <?php endif; ?>

        <!-- Exibe mensagem de erro se existir -->
        <?php if ($mensagemErro): ?>
            <div class="error"><?= htmlspecialchars($mensagemErro); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label for="nome">Nome da Recompensa:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($recompensa['nome']); ?>" required>
            </div>
            <div class="input-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" required><?= htmlspecialchars($recompensa['descricao']); ?></textarea>
            </div>
            <div class="input-group">
                <label for="preco">Preço (em moedas):</label>
                <input type="number" id="preco" name="preco" value="<?= htmlspecialchars($recompensa['preco']); ?>" required min="1">
            </div>
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?= htmlspecialchars($recompensa['id']); ?>">
            <button type="submit" class="btn">Salvar Alterações</button>
        </form>

        <!-- Botão de Voltar -->
        <a href="empresa.php" class="btn" style="background-color: gray;">Voltar</a>
    </div>
</body>
</html>
             