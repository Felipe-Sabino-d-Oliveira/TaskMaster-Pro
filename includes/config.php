<?php
$servername = "localhost";
$username = "root";
$password = "root";
$port = 8889;
$dbname = "todo_list";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Conexão falhou: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>