<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /Views/login.php"); 
    exit();
}

include '../config/conexao.php'; 

$usuario_id = $_SESSION['usuario_id'];

// Consultar informações do aluno
$query = "SELECT a.nome, a.cpf, a.email, a.curso, a.moedas, i.nome AS instituicao 
          FROM alunos a 
          JOIN instituicoes i ON a.instituicao_id = i.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $aluno = $result->fetch_assoc();
} else {
    die("Aluno não encontrado.");
}

// Buscar recompensas disponíveis
$queryRecompensas = "SELECT * FROM recompensas";
$resultRecompensas = $conn->query($queryRecompensas);

// Buscar recompensas resgatadas pelo aluno
$queryResgatadas = "
    SELECT r.nome, r.descricao, r.preco, rr.data_resgate 
    FROM recompensas_resgatadas rr
    JOIN recompensas r ON rr.recompensa_id = r.id
    WHERE rr.aluno_id = ?";
$stmtResgatadas = $conn->prepare($queryResgatadas);
$stmtResgatadas->bind_param("i", $usuario_id);
$stmtResgatadas->execute();
$resultResgatadas = $stmtResgatadas->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Aluno</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos básicos */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; }
        .container { max-width: 600px; margin: 2em auto; padding: 2em; background: white; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); text-align: left; }
        .info p strong, h2 { color: #333; }
        .moedas { font-size: 1.5em; color: black; }
        .moedas i { color: gold; }
        .btn { padding: 0.7em 1em; border: none; border-radius: 5px; color: white; font-size: 16px; cursor: pointer; transition: background-color 0.3s; margin: 0.5em 0; }
        .logout-btn { background-color: red; }
        .card { border: 1px solid #007bff; border-radius: 8px; padding: 1em; margin: 1em 0; text-align: center; background: #f9f9f9; }
        .recompensa-card { border: 1px solid #007bff; border-radius: 8px; padding: 1em; margin: 1em 0; text-align: left; background: #f1f1f1; }
        .recompensa-card h5 { margin-top: 0; }
        .recompensa-card p { margin: 0.5em 0; }
        .btn-resgatar { background-color: green; }
        .btn-resgatar:hover { background-color: darkgreen; }
    </style>
    <script>
        function confirmarResgate(recompensaId, recompensaNome) {
            if (confirm("Tem certeza de que deseja resgatar a recompensa: " + recompensaNome + "?")) {
                document.getElementById("resgatar-form-" + recompensaId).submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Perfil do Aluno</h2>
        <div class="info">
            <p><strong>Nome:</strong> <?= htmlspecialchars($aluno['nome']) ?></p>
            <p><strong>CPF:</strong> <?= htmlspecialchars($aluno['cpf']) ?></p>
            <p><strong>E-mail:</strong> <?= htmlspecialchars($aluno['email']) ?></p>
            <p><strong>Curso:</strong> <?= htmlspecialchars($aluno['curso']) ?></p>
            <p><strong>Instituição:</strong> <?= htmlspecialchars($aluno['instituicao']) ?></p>
        </div>

        <div class="card">
            <h3>Moedas</h3>
            <div class="moedas">
                <i class="fas fa-coins"></i>
                <?= htmlspecialchars($aluno['moedas']) ?> Moedas
            </div>
        </div>

        <h3>Recompensas Disponíveis</h3>
        <?php while ($recompensa = $resultRecompensas->fetch_assoc()): ?>
            <div class="recompensa-card">
                <h5><?= htmlspecialchars($recompensa['nome']) ?></h5>
                <p><?= htmlspecialchars($recompensa['descricao']) ?></p>
                <p><strong>Preço:</strong> <?= htmlspecialchars($recompensa['preco']) ?> moedas</p>
                <form id="resgatar-form-<?= $recompensa['id'] ?>" method="POST" action="resgatar_recompensa.php" style="display: none;">
                    <input type="hidden" name="recompensa_id" value="<?= $recompensa['id'] ?>">
                </form>
                <button class="btn btn-resgatar" onclick="confirmarResgate(<?= $recompensa['id'] ?>, '<?= htmlspecialchars($recompensa['nome']) ?>')">Resgatar</button>
            </div>
        <?php endwhile; ?>

        <h3>Recompensas Resgatadas</h3>
        <?php if ($resultResgatadas->num_rows > 0): ?>
            <ul>
                <?php while ($resgatada = $resultResgatadas->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($resgatada['nome']) ?> - Resgatada em <?= htmlspecialchars($resgatada['data_resgate']) ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Nenhuma recompensa resgatada.</p>
        <?php endif; ?>

        <form action="../Views/login.php" method="POST">
            <button type="submit" class="btn logout-btn">Sair</button>
        </form>
    </div>
</body>
</html>
