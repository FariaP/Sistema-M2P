<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);
session_start();

// só admins podem acessar
session_regenerate_id(true);
if (!isset($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: index.php?err=' . urlencode('Acesso não permitido.'));
    exit;
}

require_once __DIR__ . '/../app/config.php';

$errors = [];
$success = null;

// ações: add, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $cpf = trim($_POST['cpf_usuario'] ?? '');
        $placa = trim($_POST['placa'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $ano = intval($_POST['ano'] ?? 0) ?: null;

        if ($cpf === '' || $placa === '') {
            $errors[] = 'CPF do usuário e placa são obrigatórios.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO veiculo (cpf_usuario, placa, modelo, ano) VALUES (?, ?, ?, ?)');
            $stmt->execute([$cpf, $placa, $modelo ?: null, $ano]);
            $success = 'Veículo cadastrado com sucesso.';
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $cpf = trim($_POST['cpf_usuario'] ?? '');
        $placa = trim($_POST['placa'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $ano = intval($_POST['ano'] ?? 0) ?: null;

        if ($id <= 0 || $cpf === '' || $placa === '') {
            $errors[] = 'Dados inválidos para atualização.';
        } else {
            $stmt = $pdo->prepare('UPDATE veiculo SET cpf_usuario = ?, placa = ?, modelo = ?, ano = ? WHERE id = ?');
            $stmt->execute([$cpf, $placa, $modelo ?: null, $ano, $id]);
            $success = 'Veículo atualizado com sucesso.';
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            $errors[] = 'ID inválido para exclusão.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM veiculo WHERE id = ?');
            $stmt->execute([$id]);
            $success = 'Veículo excluído.';
        }
    }
}

// buscar veículos
$veiculos = $pdo->query('SELECT v.*, u.nome as usuario_nome FROM veiculo v LEFT JOIN usuarios u ON v.cpf_usuario = u.cpf_usuario ORDER BY v.id DESC')->fetchAll();

// buscar usuários para o select
$usuarios = $pdo->query('SELECT id, nome, cpf_usuario FROM usuarios ORDER BY nome')->fetchAll();

// editar: carregar dados
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM veiculo WHERE id = ?');
        $stmt->execute([$id]);
        $edit = $stmt->fetch();
    }
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veículos - AutoTech</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_admin.css">
    <link rel="stylesheet" href="../assets/css/style_user.css">
</head>

<body>
    <div class="container" style="flex-direction:column;align-items:stretch;min-height:0vh;">
        <div class="user-header">
            <img src="../assets/logo.png" alt="logo" style="height:38px;">
            <span class="brand">AutoTech</span>
            <span style="font-size:0.95em;color:var(--muted);margin-left:8px;">Cadastro de Veículos</span>
            <a href="logout.php" class="button logout" style="width:auto;padding:8px 18px;font-size:1em;">⤴ Sair</a>
        </div>

        <div class="card">
            <h2 style="margin-top:0;"><?= $edit ? 'Editar Veículo' : 'Novo Veículo' ?></h2>

            <?php if ($errors): ?>
                <div class="alert error">
                    <?php foreach ($errors as $e)
                        echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
                <?php if ($edit): ?><input type="hidden" name="id" value="<?= intval($edit['id']) ?>"><?php endif; ?>

                <label>
                    Usuário (CPF)
                    <select name="cpf_usuario" required>
                        <option value="">selecione:</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= htmlspecialchars($u['cpf_usuario']) ?>" <?= ($edit && $edit['cpf_usuario'] === $u['cpf_usuario']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['nome'] . ' — ' . $u['cpf_usuario']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Placa
                    <input type="text" name="placa" value="<?= $edit ? htmlspecialchars($edit['placa']) : '' ?>"
                        required>
                </label>

                <label>
                    Modelo
                    <input type="text" name="modelo" value="<?= $edit ? htmlspecialchars($edit['modelo']) : '' ?>">
                </label>

                <label>
                    Ano
                    <input type="number" name="ano" value="<?= $edit && $edit['ano'] ? intval($edit['ano']) : '' ?>">
                </label>

                <div style="grid-column:1 / -1;display:flex;gap:10px;justify-content:flex-end;margin-top:6px;">
                    <?php if ($edit): ?>
                        <a href="veiculo_crud.php" class="button">Cancelar</a>
                    <?php endif; ?>
                    <button class="button primary" type="submit"><?= $edit ? 'Atualizar' : 'Cadastrar' ?></button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Veículos Cadastrados</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Placa</th>
                        <th>Modelo</th>
                        <th>Ano</th>
                        <th>Usuário</th>
                        <th style="width:170px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($veiculos as $v): ?>
                        <tr>
                            <td><?= intval($v['id']) ?></td>
                            <td><?= htmlspecialchars($v['placa']) ?></td>
                            <td><?= htmlspecialchars($v['modelo']) ?></td>
                            <td><?= $v['ano'] ? intval($v['ano']) : '-' ?></td>
                            <td><?= htmlspecialchars($v['usuario_nome'] ?: $v['cpf_usuario']) ?></td>
                            <td>
                                <a class="btn-edit" href="?edit=<?= intval($v['id']) ?>">Editar</a>
                                <form method="post" style="display:inline-block;margin:0;"
                                    onsubmit="return confirm('Excluir este veículo?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= intval($v['id']) ?>">
                                    <button class="btn-delete" type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
    
    <script>
    // Máscara Placa (AAA-0A00 ou AAA-0000)
    const placaInput = document.querySelector('input[name="placa"]');
    placaInput.addEventListener('input', function (e) {
        
        let v = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        // Limita a 7 caracteres
        v = v.slice(0, 7);
        // Formatação AAA-0A00 ou AAA-0000
        if (v.length > 3) v = v.slice(0, 3) + '-' + v.slice(3);
        e.target.value = v;
    
    });
    </script>

</body>

</html>