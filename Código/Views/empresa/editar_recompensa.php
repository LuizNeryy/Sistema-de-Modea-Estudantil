<?php
session_start();
include '../../config/conexao.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] != 'empresa') {
    header("Location: ../index.php");
    exit();
}

$empresa = $_SESSION['usuario_nome'];
$mensagemSucesso = '';
$mensagemErro = '';

// Verifica se o ID da recompensa foi passado
if (!isset($_GET['id'])) {
    header("Location: painel_empresa.php");
    exit();
}

$id = $_GET['id'];

// Lógica para atualizar a recompensa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    
    $queryUpdate = "UPDATE recompensas SET nome = ?, descricao = ?, preco = ? WHERE id = ? AND empresa = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    $stmtUpdate->bind_param("ssdsi", $nome, $descricao, $preco, $id, $empresa);
    
    if ($stmtUpdate->execute()) {
        $mensagemSucesso = "Recompensa atualizada com sucesso!";
    } else {
        $mensagemErro = "Erro ao atualizar recompensa.";
    }
}

// Busca os dados da recompensa para preencher o formulário
$query = "SELECT nome, descricao, preco FROM recompensas WHERE id = ? AND empresa = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $id, $empresa);
$stmt->execute();
$result = $stmt->get_result();
$recompensa = $result->fetch_assoc();

if (!$recompensa) {
    header("Location: painel_empresa.php");
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
</head>
<body>
    <div class="container">
        <h2>Editar Recompensa</h2>

        <?php if ($mensagemSucesso): ?>
            <div class="success"><?= htmlspecialchars($mensagemSucesso); ?></div>
        <?php endif; ?>
        <?php if ($mensagemErro): ?>
            <div class="error"><?= htmlspecialchars($mensagemErro); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label for="nome">Nome da Recompensa:</label>
                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($recompensa['nome']); ?>">
            </div>
            <div class="input-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" required><?= htmlspecialchars($recompensa['descricao']); ?></textarea>
            </div>
            <div class="input-group">
                <label for="preco">Preço (em moedas):</label>
                <input type="number" id="preco" name="preco" required min="1" value="<?= htmlspecialchars($recompensa['preco']); ?>">
            </div>
            <button type="submit" class="btn">Salvar Alterações</button>
        </form>

        <div style="margin-top: 1em;">
            <a href="painel_empresa.php" class="btn">Voltar</a>
        </div>
    </div>
</body>
</html>
