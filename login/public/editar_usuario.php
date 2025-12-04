<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

session_start();

// só entra se estiver logado e for admin
if (!isset($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: index.php?err=' . urlencode('Acesso não permitido.'));
    exit;
}

require_once __DIR__ . '/../app/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: admin.php?err=' . urlencode('ID inválido.'));
    exit;
}

// Buscar dados do usuário
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: admin.php?err=' . urlencode('Usuário não encontrado.'));
    exit;
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $placa = filter_input(INPUT_POST, 'placa', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$nome || !$telefone || !$placa) {
        $error = 'Todos os campos são obrigatórios.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, telefone = ?, placa = ? WHERE id = ?');
            $stmt->execute([$nome, $telefone, $placa, $id]);
            
            header('Location: admin.php?success=' . urlencode('Usuário atualizado com sucesso.'));
            exit;
        } catch (PDOException $e) {
            $error = 'Erro ao atualizar usuário.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <header>
                <img src="../assets/logo.png" alt="logo" />
            </header>
            <h1>Editar Usuário</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" class="form">
                <input type="text" 
                       name="nome" 
                       class="input" 
                       placeholder="Nome completo" 
                       value="<?= htmlspecialchars($user['nome']) ?>" 
                       required>
                
                <input type="tel" 
                       name="telefone" 
                       class="input" 
                       placeholder="Telefone" 
                       value="<?= htmlspecialchars($user['telefone']) ?>" 
                       required>
                
                <input type="text" 
                       name="placa" 
                       class="input" 
                       placeholder="Placa do veículo" 
                       value="<?= htmlspecialchars($user['placa']) ?>" 
                       required>

                <button type="submit" class="button">Salvar Alterações</button>
                <a href="admin.php" class="button" style="background:var(--surface);margin-top:10px;text-align:center">Cancelar</a>
            </form>
        </div>
    </div>
</body>
</html>