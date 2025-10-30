<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);
session_start();

// acesso apenas para admin
session_regenerate_id(true);
if (!isset($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: index.php?err=' . urlencode('Acesso não permitido.'));
    exit;
}

require_once __DIR__ . '/../app/config.php';

$errors = [];
$success = null;

// ações: add_pedido, update_pedido, delete_pedido, delete_item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_pedido') {
        $id_veiculo = intval($_POST['id_veiculo'] ?? 0);
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = trim($_POST['status'] ?? 'Em andamento');
        $descricoes = $_POST['descricao'] ?? [];
        $valores = $_POST['valor'] ?? [];

        if ($id_veiculo <= 0) {
            $errors[] = 'Veículo é obrigatório.';
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('INSERT INTO pedido_servico (id_veiculo, observacoes, status) VALUES (?, ?, ?)');
                $stmt->execute([$id_veiculo, $observacoes ?: null, $status]);
                $pedidoId = $pdo->lastInsertId();

                $itStmt = $pdo->prepare('INSERT INTO item_servico (id_pedido, descricao, valor) VALUES (?, ?, ?)');
                for ($i = 0; $i < count($descricoes); $i++) {
                    $d = trim($descricoes[$i]);
                    $v = str_replace(',', '.', trim($valores[$i] ?? ''));
                    if ($d === '' || $v === '')
                        continue;
                    $itStmt->execute([$pedidoId, $d, floatval($v)]);
                }

                $pdo->commit();
                $success = 'Pedido criado com sucesso.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Erro ao salvar pedido: ' . $e->getMessage();
            }
        }

    } elseif ($action === 'update_pedido') {
        $id = intval($_POST['id'] ?? 0);
        $id_veiculo = intval($_POST['id_veiculo'] ?? 0);
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = trim($_POST['status'] ?? 'Em andamento');
        $descricoes = $_POST['descricao'] ?? [];
        $valores = $_POST['valor'] ?? [];

        if ($id <= 0 || $id_veiculo <= 0) {
            $errors[] = 'Dados inválidos para atualização.';
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('UPDATE pedido_servico SET id_veiculo = ?, observacoes = ?, status = ? WHERE id = ?');
                $stmt->execute([$id_veiculo, $observacoes ?: null, $status, $id]);

                // simplificação: remover todos os itens antigos e reinserir os enviados
                $pdo->prepare('DELETE FROM item_servico WHERE id_pedido = ?')->execute([$id]);
                $itStmt = $pdo->prepare('INSERT INTO item_servico (id_pedido, descricao, valor) VALUES (?, ?, ?)');
                for ($i = 0; $i < count($descricoes); $i++) {
                    $d = trim($descricoes[$i]);
                    $v = str_replace(',', '.', trim($valores[$i] ?? ''));
                    if ($d === '' || $v === '')
                        continue;
                    $itStmt->execute([$id, $d, floatval($v)]);
                }

                $pdo->commit();
                $success = 'Pedido atualizado com sucesso.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Erro ao atualizar pedido: ' . $e->getMessage();
            }
        }

    } elseif ($action === 'delete_pedido') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            $errors[] = 'ID inválido para exclusão.';
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare('DELETE FROM item_servico WHERE id_pedido = ?')->execute([$id]);
                $pdo->prepare('DELETE FROM pedido_servico WHERE id = ?')->execute([$id]);
                $pdo->commit();
                $success = 'Pedido excluído.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Erro ao excluir pedido: ' . $e->getMessage();
            }
        }

    } elseif ($action === 'delete_item') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM item_servico WHERE id = ?')->execute([$id]);
            $success = 'Item excluído.';
        }
    }
}

// buscar pedidos com info do veículo e usuário
$pedidos = $pdo->query('SELECT p.*, v.placa, u.nome as usuario_nome FROM pedido_servico p LEFT JOIN veiculo v ON p.id_veiculo = v.id LEFT JOIN usuarios u ON v.cpf_usuario = u.cpf_usuario ORDER BY p.id DESC')->fetchAll();

// buscar veículos para select
$veiculos = $pdo->query('SELECT v.id, v.placa, u.nome as usuario_nome FROM veiculo v LEFT JOIN usuarios u ON v.cpf_usuario = u.cpf_usuario ORDER BY v.placa')->fetchAll();

// editar: carregar pedido e itens
$edit = null;
$itens = [];
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM pedido_servico WHERE id = ?');
        $stmt->execute([$id]);
        $edit = $stmt->fetch();

        $itens = $pdo->prepare('SELECT * FROM item_servico WHERE id_pedido = ? ORDER BY id');
        $itens->execute([$id]);
        $itens = $itens->fetchAll();
    }
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos de Serviço - AutoTech</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_admin.css">
    <link rel="stylesheet" href="../assets/css/style_user.css">
</head>

<body>
    <div class="container" style="flex-direction:column;align-items:stretch;min-height:100vh;">
        <div class="user-header">
            <img src="../assets/logo.png" alt="logo" style="height:38px;">
            <span class="brand">AutoTech</span>
            <span style="font-size:0.95em;color:var(--muted);margin-left:8px;">Cadastro de Pedidos de Serviço</span>
            <a href="logout.php" class="button logout" style="width:auto;padding:8px 18px;font-size:1em;">⤴ Sair</a>
        </div>

        <div class="card">
            <h2 style="margin-top:0;"><?= $edit ? 'Editar Pedido' : 'Novo Pedido' ?></h2>

            <?php if ($errors): ?>
                <div class="alert error">
                    <?php foreach ($errors as $e)
                        echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" id="pedido-form">
                <input type="hidden" name="action" value="<?= $edit ? 'update_pedido' : 'add_pedido' ?>">
                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= intval($edit['id']) ?>">
                <?php endif; ?>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <label>
                        Veículo
                        <select name="id_veiculo" required>
                            <option value="">-- selecione --</option>
                            <?php foreach ($veiculos as $v): ?>
                                <option value="<?= intval($v['id']) ?>" <?= ($edit && $edit['id_veiculo'] == $v['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($v['placa'] . ' — ' . ($v['usuario_nome'] ?: '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        Status
                        <select name="status">
                            <?php $statuses = ['Em andamento', 'Concluído', 'Pausado', 'Cancelado']; ?>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>" <?= ($edit && $edit['status'] === $s) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <label style="margin-top:12px;">Observações
                    <textarea name="observacoes"
                        rows="3"><?= $edit ? htmlspecialchars($edit['observacoes']) : '' ?></textarea>
                </label>

                <h3 style="margin:12px 0 6px;">Itens do Pedido</h3>

                <table class="table items-table">
                    <thead>
                        <tr>
                            <th style="width:60%;">Descrição</th>
                            <th style="width:140px;text-align:right;">Valor (R$)</th>
                            <th style="width:80px;text-align:center;">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <?php if ($edit && $itens): ?>
                            <?php foreach ($itens as $it): ?>
                                <tr data-item-id="<?= intval($it['id']) ?>">
                                    <td><input type="text" name="descricao[]" value="<?= htmlspecialchars($it['descricao']) ?>">
                                    </td>
                                    <td><input type="number" name="valor[]" step="0.01"
                                            value="<?= number_format($it['valor'], 2, '.', '') ?>"></td>
                                    <td style="text-align:center;">
                                        <button type="button" class="action-btn delete" data-action="remove-existing"
                                            data-item-id="<?= intval($it['id']) ?>">
                                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path d="M8 6v12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V6" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M9 6V4h6v2" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="text-align:right;">Total</td>
                            <td style="text-align:right;padding-right:12px;" id="total-cell">R$ 0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <div style="display:flex;gap:10px;justify-content:space-between;margin-top:10px;">
                    <div>
                        <button type="button" id="add-item" class="button">+ Adicionar Item</button>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <?php if ($edit): ?>
                            <a href="pedido_crud.php" class="button">Cancelar</a>
                        <?php endif; ?>
                        <button type="submit"
                            class="button primary"><?= $edit ? 'Atualizar Pedido' : 'Criar Pedido' ?></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Pedidos Cadastrados</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Veículo</th>
                        <th>Usuário</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th style="width:200px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td><?= intval($p['id']) ?></td>
                            <td><?= htmlspecialchars($p['placa'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($p['usuario_nome'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($p['data_criacao']) ?></td>
                            <td><?= htmlspecialchars($p['status']) ?></td>
                            <td>
                                <a class="action-btn edit" href="?edit=<?= intval($p['id']) ?>" title="Editar pedido">
                                    Editar
                                </a>
                                <form method="post" style="display:inline-block;margin:0;"
                                    onsubmit="return confirm('Excluir este pedido?');">
                                    <input type="hidden" name="action" value="delete_pedido">
                                    <input type="hidden" name="id" value="<?= intval($p['id']) ?>">
                                    <button class="action-btn delete" type="submit"
                                        style="border:none; background:none; color:red; font-size: 15px; cursor: pointer;"
                                        title="Excluir pedido">Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="js/function_pedidos.js"></script>
</body>

</html>