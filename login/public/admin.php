<?php
// Configurações precisam vir antes do session_start
session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

session_start();


// só entra se estiver logado e for admin
session_regenerate_id(true);
if (!isset($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: index.php?err=' . urlencode('Acesso não permitido.'));
    exit;
}

require_once __DIR__ . '/../app/config.php';
// buscar apenas para contar usuários na visão resumida
$rows = $pdo->query('SELECT id,nome,telefone,cpf_usuario,placa,criado_em FROM usuarios ORDER BY id DESC')->fetchAll();

// nome do admin com fallback caso não exista no array de sessão
$adminName = $_SESSION['user']['nome'] ?? $_SESSION['user']['cpf_usuario'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Área do Administrador</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/style_user.css">
</head>

<body>
  <div class="container" style="flex-direction:column;align-items:stretch;min-height:100vh;">
    <div class="user-header">
      <img src="../assets/logo.png" alt="logo" style="height:38px;">
      <span class="brand">Sistema M2P</span>
      <span style="font-size:0.95em;color:var(--muted);margin-left:8px;">Área do Administrador</span>
      <a href="logout.php" class="button logout" style="width:auto;padding:8px 18px;font-size:1em;">⤴ Sair</a>
    </div>

    <div class="card-user">
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:1.1em;"><b><?= htmlspecialchars($adminName) ?></b></span>
        <span class="status-badge">Admin</span>
      </div>
      <div style="color:var(--muted);margin-bottom:6px;">Gerencie os usuários cadastrados no sistema</div>
      <div style="font-size:0.98em;margin-top:10px;color:var(--muted);">Total de usuários: <b><?= count($rows) ?></b></div>
    </div>

    <div class="cards-container">
      <div class="card" style="margin-bottom:18px;">
        <h2 style="font-size:1.1em;margin:0 0 10px;">Ações</h2>
        <a href="admin_list.php" class="btn-primary">Gerenciar Usuários (Editar / Excluir)</a>
        <a href="register.php" class="btn-primary" style="display:inline-block;margin-left:10px;">Cadastrar Novo Usuário</a>
      </div>
    </div>
  </div>
</body>

</html>