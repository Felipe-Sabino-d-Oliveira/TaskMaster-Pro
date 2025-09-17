<?php
session_start();

function verificarAutenticacao() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: index.php");
        exit();
    }
}

function registrarUsuario($nome, $email, $senha, $conn) {
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $email, $senhaHash);
    
    return $stmt->execute();
}

function fazerLogin($email, $senha, $conn) {
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            return true;
        }
    }
    return false;
}
?>