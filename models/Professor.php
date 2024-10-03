<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /Views/login.php"); 
    exit();
}

include '../config/conexao.php'; 

$usuario_id = $_SESSION['usuario_id'];

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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Professor</title>
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
            text-align: left; /* Alinhando todo o texto à esquerda */
        }

        h2 {
            margin-bottom: 0.5em;
            color: #333;
        }

        .info {
            margin: 1em 0;
        }

        .info strong {
            color: blue;
        }

        .card {
            border: 1px solid #007bff;
            border-radius: 8px;
            padding: 1em;
            margin: 1em 0;
            text-align: center;
            background: #f9f9f9;
        }

        .moedas {
            font-size: 1.5em;
            margin: 1em 0;
            color: black; 
        }

        .moedas i {
            color: gold; 
        }

        .btn {
            padding: 0.7em 1em;
            border: none;
            border-radius: 5px;
            background-color: blue; 
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 0.5em 0; /* Adicionado espaço entre os botões */
        }

        .btn:hover {
            background-color: blueviolet; 
        }

        .logout-btn {
            background-color: red; /* Botão de logout vermelho */
            margin-top: 2em; /* Mais espaço acima do botão de logout */
            /* margin-bottom: 2em; Removido para evitar conflito com o espaço inferior */
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Perfil do Professor</h2>
        <div class="info">
            <p><strong>Nome:</strong> <?= htmlspecialchars($professor['nome']) ?></p>
            <p><strong>CPF:</strong> <?= htmlspecialchars($professor['cpf']) ?></p>
            <p><strong>Departamento:</strong> <?= htmlspecialchars($professor['departamento']) ?></p>
            <p><strong>Instituição:</strong> <?= htmlspecialchars($professor['instituicao']) ?></p> <!-- Adicionando a instituição -->
        </div>

        <div class="card">
            <h3>Distribuir Moedas</h3>
            <div class="moedas">
                <i class="fas fa-coins"></i>
                <?= htmlspecialchars($professor['moedas']) ?> Moedas
            </div>
            <form action="distribuir_moedas.php" method="POST">
                <input type="hidden" name="professor_id" value="<?= htmlspecialchars($usuario_id) ?>">
                <button type="submit" class="btn">Distribuir Moedas</button>
            </form>
        </div>

        <form action="../Views/login.php" method="POST">
            <button type="submit" class="btn logout-btn">Sair</button>
        </form>
    </div>
</body>
</html>
