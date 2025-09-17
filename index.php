<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $termosAceitos = isset($_POST['termos']);
    
    if ($acao === 'cadastro') {
        if (!empty($nome) && !empty($email) && !empty($senha)) {
            if (registrarUsuario($nome, $email, $senha, $conn)) {
                $_SESSION['sucesso'] = 'Cadastro realizado com sucesso! Faça login.';
                header("Location: index.php");
                exit();
            } else {
                $erro = "Erro ao cadastrar. Email já existe.";
            }
        } else {
            $erro = "Preencha todos os campos.";
        }
        if (!$termosAceitos) {
            $erro = "Você deve aceitar os termos para se cadastrar.";
        } elseif (!empty($nome) && !empty($email) && !empty($senha)) {
            // resto do código de cadastro...
        } else {
            $erro = "Preencha todos os campos.";
        }
    } elseif ($acao === 'login') {
        if (!empty($email) && !empty($senha)) {
            if (fazerLogin($email, $senha, $conn)) {
                header("Location: dashboard.php");
                exit();
            } else {
                $erro = "Email ou senha incorretos.";
            }
        } else {
            $erro = "Preencha todos os campos.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="login-container">
  <div class="text-center mb-4">
    <h2 class="text-primary"><i class="bi bi-check-circle"></i> Todo List</h2>
    <p class="text-muted">Organize suas tarefas de forma simples</p>
  </div>

  <?php if (isset($_SESSION['sucesso'])): ?>
  <div class="alert alert-success">
    <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
  </div>
  <?php endif; ?>

  <?php if ($erro): ?>
  <div class="alert alert-danger"><?php echo $erro; ?></div>
  <?php endif; ?>

  <div class="form-container">
    <!-- Login -->
    <div class="mb-4">
      <h5 class="text-center mb-3">Login</h5>
      <form method="POST">
        <input type="hidden" name="acao" value="login" />
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input type="password" name="senha" class="form-control" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
      </form>
    </div>

    <hr />

    <!-- Cadastro -->
    <div>
      <h5 class="text-center mb-3">Cadastrar</h5>
      <form method="POST">
        <input type="hidden" name="acao" value="cadastro" />
        <div class="mb-3">
          <label class="form-label">Nome</label>
          <input type="text" name="nome" class="form-control" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input type="password" name="senha" class="form-control" required />
        </div>
        <div class="mb-3 form-check">
          <input
            type="checkbox"
            class="form-check-input"
            id="termos"
            name="termos"
            required
          />
          <label class="form-check-label" for="termos">
            Aceito os <a href="#" target="_blank">Termos de Uso</a> e a
            <a href="#" target="_blank">Política de Privacidade</a>.
          </label>
        </div>
        <button type="submit" class="btn btn-outline-primary w-100">
          Cadastrar
        </button>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
