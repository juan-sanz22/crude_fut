<?php

// $servername = "localhost";
// $username = "root";
// $password = "root";
// $dbname = "futebol_db";


// $conn = new mysqli($servername,$username,$password,$dbname);

// if ($conn->connect_error) {
//     die("Conexao Falhoukkkk: " . $conn -> connect_error);
// }



// Configuração do banco de dados
$host = 'localhost';
$db = 'futebol_db';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro de conexão555: " . $e->getMessage());
}

?>