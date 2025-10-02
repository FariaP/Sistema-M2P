<?php
require_once __DIR__ . '/../app/config.php';

session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php?err=' . urlencode('Faça login para continuar.'));
    exit;
}

$rows = $pdo->query('SELECT id,nome,telefone,cpf_usuario,placa,criado_em FROM usuarios ORDER BY id DESC')->fetchAll();
?>


<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuários cadastrados</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <header>
                <img src="../assets/logo.png" alt="logo" />
            </header>
            <h1>Usuários cadastrados</h1>
            <div class="helper">
                Logado como <strong><?= htmlspecialchars($_SESSION['user']['cpf_usuario']) ?></strong> — <a
                    href="logout.php">Sair</a>
            </div>
            <a href="index.php" class="button"
                style="display:inline-block;text-align:center;margin:8px 0 12px;">Voltar</a>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['id']) ?></td>
                                <td><?= htmlspecialchars($r['nome']) ?></td>
                                <td><?= htmlspecialchars($r['telefone']) ?></td>
                                <td><?= htmlspecialchars($r['cpf_usuario']) ?></td>
                                <td><?= htmlspecialchars($r['placa']) ?></td>
                                <td><?= htmlspecialchars($r['criado_em']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>