<?php
function getTarefas($usuarioId, $conn, $filtro = null) {
    $sql = "SELECT * FROM tarefas WHERE usuario_id = ?";
    
    if ($filtro === 'concluidas') {
        $sql .= " AND status = 'concluida'";
    } elseif ($filtro === 'pendentes') {
        $sql .= " AND status = 'pendente'";
    }
    
    $sql .= " ORDER BY 
        CASE prioridade 
            WHEN 'alta' THEN 1 
            WHEN 'media' THEN 2 
            WHEN 'baixa' THEN 3 
        END, 
        data_criacao DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    return $stmt->get_result();
}

function criarTarefa($usuarioId, $titulo, $descricao, $prioridade, $conn) {
    $stmt = $conn->prepare("INSERT INTO tarefas (usuario_id, titulo, descricao, prioridade) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $usuarioId, $titulo, $descricao, $prioridade);
    return $stmt->execute();
}

function atualizarTarefa($id, $titulo, $descricao, $prioridade, $status, $conn) {
    $dataConclusao = $status === 'concluida' ? date('Y-m-d H:i:s') : null;
    
    $stmt = $conn->prepare("UPDATE tarefas SET titulo = ?, descricao = ?, prioridade = ?, status = ?, data_conclusao = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $titulo, $descricao, $prioridade, $status, $dataConclusao, $id);
    return $stmt->execute();
}

function excluirTarefa($id, $conn) {
    $stmt = $conn->prepare("DELETE FROM tarefas WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getTarefaPorId($id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM tarefas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>