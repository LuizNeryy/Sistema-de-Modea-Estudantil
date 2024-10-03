<?php
// config/conexao.php

$servername = "localhost"; // ou o endereço do seu servidor de banco de dados
$username = "root"; // usuário do banco de dados
$password = ""; // senha do banco de dados
$dbname = "SME"; // nome do banco de dados

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
