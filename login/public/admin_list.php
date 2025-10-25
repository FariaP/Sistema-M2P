<?php
require_once __DIR__ . '/../app/config.php';

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

$rows = $pdo->query('SELECT id,nome,telefone,cpf_usuario,placa,criado_em FROM usuarios ORDER BY id DESC')->fetchAll();
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_user.css">
</head>

<body>
    <div class="container" style="flex-direction:column;align-items:stretch;min-height:100vh;">
        <div class="user-header">
            <img src="../assets/logo.png" alt="logo" style="height:38px;">
            <span class="brand">Sistema M2P</span>
            <span style="font-size:0.95em;color:var(--muted);margin-left:8px;">Gerenciar Usuários</span>
            <a href="logout.php" class="button logout" style="width:auto;padding:8px 18px;font-size:1em;">⤴ Sair</a>
        </div>

        <div class="cards-container">
            <div class="card" style="margin-bottom:18px;">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success alert"><?= htmlspecialchars($_GET['success']) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['err'])): ?>
                    <div class="alert"><?= htmlspecialchars($_GET['err']) ?></div>
                <?php endif; ?>

                <h2 style="font-size:1.1em;margin:0 0 10px;">Usuários cadastrados</h2>
                <div class="client-list">
                    <?php if (!$rows): ?>
                        <div class="alert">Nenhum usuário cadastrado ainda.</div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <th>CPF</th>
                                    <th>Placa</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td><?= htmlspecialchars($user['nome']) ?></td>
                                        <td><?= htmlspecialchars($user['telefone']) ?></td>
                                        <td><?= htmlspecialchars($user['cpf_usuario']) ?></td>
                                        <td><?= htmlspecialchars($user['placa']) ?></td>
                                        <td><?= htmlspecialchars($user['criado_em']) ?></td>
                                        <td>
                                            <a href="editar_usuario.php?id=<?= htmlspecialchars($user['id']) ?>" class="btn-edit">Editar</a>
                                            <a href="excluir_usuario.php?id=<?= htmlspecialchars($user['id']) ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="actions" style="margin-top:12px;">
                    <a href="register.php" class="btn-primary">Cadastrar Novo Usuário</a>
                    <a href="admin.php" class="button" style="display:inline-block;margin-left:10px;">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>