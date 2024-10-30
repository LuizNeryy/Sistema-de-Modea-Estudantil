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

// Lógica para resgatar a recompensa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recompensa_id'])) {
    $recompensa_id = $_POST['recompensa_id'];
    
    // Verificar se o aluno tem moedas suficientes
    $queryVerificaMoedas = "SELECT moedas FROM alunos WHERE id = ?";
    $stmtVerificaMoedas = $conn->prepare($queryVerificaMoedas);
    $stmtVerificaMoedas->bind_param("i", $usuario_id);
    $stmtVerificaMoedas->execute();
    $resultVerificaMoedas = $stmtVerificaMoedas->get_result();
    $alunoData = $resultVerificaMoedas->fetch_assoc();

    if ($alunoData['moedas'] >= $recompensa['preco']) {
        // Registrar o resgate da recompensa
        $queryResgate = "INSERT INTO recompensas_resgatadas (aluno_id, recompensa_id, data_resgate) VALUES (?, ?, NOW())";
        $stmtResgate = $conn->prepare($queryResgate);
        $stmtResgate->bind_param("ii", $usuario_id, $recompensa_id);
        $stmtResgate->execute();

        // Atualizar as moedas do aluno
        $novaQuantidade = $alunoData['moedas'] - $recompensa['preco'];
        $queryAtualizaMoedas = "UPDATE alunos SET moedas = ? WHERE id = ?";
        $stmtAtualizaMoedas = $conn->prepare($queryAtualizaMoedas);
        $stmtAtualizaMoedas->bind_param("ii", $novaQuantidade, $usuario_id);
        $stmtAtualizaMoedas->execute();

        // Redirecionar para evitar o reenvio do formulário
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Você não tem moedas suficientes para resgatar essa recompensa.');</script>";
    }
}
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
        .card { border: none; border-radius: 8px; margin: 1em 0; text-align: center; }
        
        /* Estilos para recompensas */
        .recompensa-card, .resgatada-card { 
            border: 1px solid #333; 
            border-radius: 8px; 
            padding: 1em; 
            margin: 1em 0; 
            text-align: left; 
            background: #f9f9f9; 
            transition: transform 0.3s, box-shadow 0.3s; 
        }
        .recompensa-card:hover, .resgatada-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); 
        }
        .recompensa-card h5, .resgatada-card h5 { 
            margin-top: 0; 
            color: #333; 
            font-size: 1.2em; 
        }
        .recompensa-card p, .resgatada-card p { 
            margin: 0.5em 0; 
            color: #666; 
        }
        .btn-resgatar { 
            background-color: #333; 
            color: white; 
        }
        .btn-resgatar:hover { 
            background-color: #555; 
        }
        .resgatadas-container { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 1em; 
        }
        .resgatada-card { 
            flex: 1 1 calc(50% - 1em); 
            box-shadow: 0 0 5px rgba(0,0,0,0.2); 
            transition: transform 0.3s; 
        }
        .resgatada-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); 
        }

        /* Estilos para seções */
        .section {
            border: 1px solid #ccc; 
            border-radius: 8px; 
            padding: 1.5em; 
            margin: 2em 0; 
            background-color: #fefefe; 
        }
        .section h3 { 
            margin: 0; 
            color: #333; 
            text-align: center; 
        }
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

        <div class="section">
            <h3>Recompensas Disponíveis</h3>
            <?php while ($recompensa = $resultRecompensas->fetch_assoc()): ?>
                <div class="recompensa-card">
                    <h5><?= htmlspecialchars($recompensa['nome']) ?></h5>
                    <p><?= htmlspecialchars($recompensa['descricao']) ?></p>
                    <p><strong>Preço:</strong> <?= htmlspecialchars($recompensa['preco']) ?> moedas</p>
                    <form action="" method="POST" id="resgatar-form-<?= htmlspecialchars($recompensa['id']) ?>">
                        <input type="hidden" name="recompensa_id" value="<?= htmlspecialchars($recompensa['id']) ?>">
                        <button type="button" class="btn btn-resgatar" onclick="confirmarResgate(<?= htmlspecialchars($recompensa['id']) ?>, '<?= htmlspecialchars($recompensa['nome']) ?>')">Resgatar</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="section">
            <h3>Recompensas Resgatadas</h3>
            <div class="resgatadas-container">
                <?php if ($resultResgatadas->num_rows > 0): ?>
                    <?php while ($resgatada = $resultResgatadas->fetch_assoc()): ?>
                        <div class="resgatada-card">
                            <h5><?= htmlspecialchars($resgatada['nome']) ?></h5>
                            <p><strong>Descrição:</strong> <?= htmlspecialchars($resgatada['descricao']) ?></p>
                            <p><strong>Preço:</strong> <?= htmlspecialchars($resgatada['preco']) ?> moedas</p>
                            <p><strong>Resgatada em:</strong> <?= htmlspecialchars($resgatada['data_resgate']) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Ainda não há recompensas resgatadas.</p>
                <?php endif; ?>
            </div>
        </div>

        <form action="../Views/login.php" method="POST">
            <button type="submit" class="btn logout-btn">Sair</button>
        </form>
    </div>
</body>
</html>
