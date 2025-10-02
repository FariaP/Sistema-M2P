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
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador</title>
</head>

<body>
    <h1>Bem-vindo, administrador!</h1>
    <p>CPF: <?= htmlspecialchars($_SESSION['user']['cpf_usuario']) ?></p>
    <p>ID: <?= htmlspecialchars($_SESSION['user']['id']) ?></p>

    <a href="logout.php">Sair</a>
</body>

</html>