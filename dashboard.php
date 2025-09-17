<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/tasks.php';

function buscarFraseMotivacional() {
    $url = "https://zenquotes.io/api/today";
    $response = @file_get_contents($url);
    if ($response === FALSE) {
        return "Mantenha o foco e continue!";
    }
    $data = json_decode($response, true);
    if (isset($data[0]['q']) && isset($data[0]['a'])) {
        return $data[0]['q'] . " — " . $data[0]['a'];
    }
    return "Mantenha o foco e continue!";
}

$fraseMotivacional = buscarFraseMotivacional();

verificarAutenticacao();

$filtro = $_GET['filtro'] ?? 'todas';
$tarefas = getTarefas($_SESSION['usuario_id'], $conn, $filtro);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'criar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $prioridade = $_POST['prioridade'] ?? 'media';
        
        if (!empty($titulo)) {
            criarTarefa($_SESSION['usuario_id'], $titulo, $descricao, $prioridade, $conn);
            header("Location: dashboard.php?filtro=$filtro");
            exit();
        }
    }
    elseif ($acao === 'atualizar') {
        $id = $_POST['id'] ?? 0;
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $prioridade = $_POST['prioridade'] ?? 'media';
        $status = $_POST['status'] ?? 'pendente';
        
        if (!empty($titulo)) {
            atualizarTarefa($id, $titulo, $descricao, $prioridade, $status, $conn);
            header("Location: dashboard.php?filtro=$filtro");
            exit();
        }
    }
    elseif ($acao === 'excluir') {
        $id = $_POST['id'] ?? 0;
        excluirTarefa($id, $conn);
        header("Location: dashboard.php?filtro=$filtro");
        exit();
    }
    elseif ($acao === 'toggle_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 'pendente';
        $tarefa = getTarefaPorId($id, $conn);
        
        if ($tarefa) {
            $novoStatus = $tarefa['status'] === 'pendente' ? 'concluida' : 'pendente';
            atualizarTarefa($id, $tarefa['titulo'], $tarefa['descricao'], $tarefa['prioridade'], $novoStatus, $conn);
            header("Location: dashboard.php?filtro=$filtro");
            exit();
        }
    }
}

// Estatísticas
$totalTarefas = $conn->query("SELECT COUNT(*) FROM tarefas WHERE usuario_id = {$_SESSION['usuario_id']}")->fetch_row()[0];
$tarefasConcluidas = $conn->query("SELECT COUNT(*) FROM tarefas WHERE usuario_id = {$_SESSION['usuario_id']} AND status = 'concluida'")->fetch_row()[0];
$tarefasPendentes = $totalTarefas - $tarefasConcluidas;
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <!-- Estatísticas -->
        <div class="card stats-card mb-4">
            <div class="card-body text-center">
                <h5><i class="bi bi-bar-chart"></i> Estatísticas</h5>
                <div class="row mt-3">
                    <div class="col-4">
                        <h4><?php echo $totalTarefas; ?></h4>
                        <small>Total</small>
                    </div>
                    <div class="col-4">
                        <h4><?php echo $tarefasConcluidas; ?></h4>
                        <small>Concluídas</small>
                    </div>
                    <div class="col-4">
                        <h4><?php echo $tarefasPendentes; ?></h4>
                        <small>Pendentes</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <h6><i class="bi bi-funnel"></i> Filtros</h6>
                <select class="form-select" id="filtro">
                    <option value="todas" <?php echo $filtro === 'todas' ? 'selected' : ''; ?>>Todas as tarefas</option>
                    <option value="pendentes" <?php echo $filtro === 'pendentes' ? 'selected' : ''; ?>>Pendentes</option>
                    <option value="concluidas" <?php echo $filtro === 'concluidas' ? 'selected' : ''; ?>>Concluídas</option>
                </select>
            </div>
        </div>

        <!-- Nova Tarefa -->
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-plus-circle"></i> Nova Tarefa</h6>
                <form method="POST">
                    <input type="hidden" name="acao" value="criar">
                    <div class="mb-3">
                        <label class="form-label">Título*</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prioridade</label>
                        <select name="prioridade" class="form-select">
                            <option value="baixa">Baixa</option>
                            <option value="media" selected>Média</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus"></i> Adicionar Tarefa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <!-- Lista de Tarefas -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-task"></i> Tarefas</h5>
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-lightbulb"></i> <?php echo htmlspecialchars($fraseMotivacional); ?>
                </div>
                <span class="badge bg-primary"><?php echo $totalTarefas; ?> tarefas</span>
            </div>
            <div class="card-body">
                <?php if ($tarefas->num_rows === 0): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">Nenhuma tarefa encontrada</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php while ($tarefa = $tarefas->fetch_assoc()): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card task-card <?php echo $tarefa['status']; ?> <?php echo $tarefa['prioridade']; ?>" id="task-<?php echo $tarefa['id']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0 <?php echo $tarefa['status'] === 'concluida' ? 'completed-text' : ''; ?>">
                                                <?php echo htmlspecialchars($tarefa['titulo']); ?>
                                            </h6>
                                            <span class="badge bg-<?php echo $tarefa['prioridade'] === 'alta' ? 'danger' : ($tarefa['prioridade'] === 'media' ? 'warning' : 'success'); ?>">
                                                <?php echo ucfirst($tarefa['prioridade']); ?>
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($tarefa['descricao'])): ?>
                                            <p class="card-text text-muted small <?php echo $tarefa['status'] === 'concluida' ? 'completed-text' : ''; ?>">
                                                <?php echo nl2br(htmlspecialchars($tarefa['descricao'])); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($tarefa['data_criacao'])); ?>
                                            </small>
                                            <div class="btn-group">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="acao" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo $tarefa['id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $tarefa['status']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-<?php echo $tarefa['status'] === 'concluida' ? 'secondary' : 'success'; ?>">
                                                        <i class="bi bi-<?php echo $tarefa['status'] === 'concluida' ? 'arrow-counterclockwise' : 'check'; ?>"></i>
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm btn-outline-primary" onclick="toggleEditForm(<?php echo $tarefa['id']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="acao" value="excluir">
                                                    <input type="hidden" name="id" value="<?php echo $tarefa['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulário de Edição (oculto) -->
                                <div class="card mb-3" id="edit-form-<?php echo $tarefa['id']; ?>" style="display: none;">
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="acao" value="atualizar">
                                            <input type="hidden" name="id" value="<?php echo $tarefa['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Título*</label>
                                                <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($tarefa['titulo']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Descrição</label>
                                                <textarea name="descricao" class="form-control" rows="3"><?php echo htmlspecialchars($tarefa['descricao']); ?></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Prioridade</label>
                                                    <select name="prioridade" class="form-select">
                                                        <option value="baixa" <?php echo $tarefa['prioridade'] === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                                                        <option value="media" <?php echo $tarefa['prioridade'] === 'media' ? 'selected' : ''; ?>>Média</option>
                                                        <option value="alta" <?php echo $tarefa['prioridade'] === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pendente" <?php echo $tarefa['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                                        <option value="concluida" <?php echo $tarefa['status'] === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">Salvar</button>
                                                <button type="button" class="btn btn-secondary" onclick="toggleEditForm(<?php echo $tarefa['id']; ?>)">Cancelar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
<?php include 'includes/footer.php'; ?>