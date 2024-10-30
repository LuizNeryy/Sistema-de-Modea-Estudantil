<?php
include 'conexao.php';

if (isset($_GET['aluno_id'])) {
    $aluno_id = $_GET['aluno_id'];

    // Buscar informações do aluno
    $query = "SELECT nome FROM alunos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $aluno_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $aluno = $result->fetch_assoc();
        echo htmlspecialchars($aluno['nome']);
    } else {
        echo '';
    }
}
?>
