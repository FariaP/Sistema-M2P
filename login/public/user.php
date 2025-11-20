<?php

session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

session_start();

// só entra se estiver logado e for usuário comum
session_regenerate_id(true);
if (!isset($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'user') {
    header('Location: index.php?err=' . urlencode('Acesso não permitido.'));
    exit;
}

// carregar configuração do banco para buscar veículos
require_once __DIR__ . '/../app/config.php';

// verificar se o usuário tem veículos cadastrados
$cpfSess = $_SESSION['user']['cpf_usuario'] ?? null;
$veiculos = [];
if ($cpfSess) {
    try {
        $vstmt = $pdo->prepare('SELECT id, placa, modelo, ano FROM veiculo WHERE cpf_usuario = :cpf ORDER BY id DESC');
        $vstmt->bindValue(':cpf', $cpfSess);
        $vstmt->execute();
        $veiculos = $vstmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $veiculos = [];
    }
}

// Se não houver veículos cadastrados, mostrar tela inicial informando isso
if (empty($veiculos)) {
    ?>
    <!doctype html>
    <html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Área do Cliente - AutoTech</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="../assets/css/style_user.css">
    </head>
    <body>
        <div class="container" style="flex-direction:column;align-items:stretch;min-height:100vh;">
            <div class="user-header">
                <img src="../assets/logo.png" alt="logo" style="height:38px;">
                <span class="brand">AutoTech</span>
                <span style="font-size:0.95em;color:var(--muted);margin-left:8px;">Área do Cliente</span>
                <a href="logout.php" class="button logout" style="width:auto;padding:8px 18px;font-size:1em;">⤴ Sair</a>
            </div>
            <div class="card" style="margin:24px auto;max-width:720px;text-align:center;">
                <h2>Nenhum veículo cadastrado</h2>
                <p style="color:var(--muted);">Não encontramos veículos vinculados ao seu CPF. Para agendar um serviço, por favor, contate a administração ou cadastre um veículo através do suporte.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Determinar veículo escolhido por: query `?veiculo=ID`, sessão `veiculo_id`, ou pela placa em sessão
$selected = null;
// 1) se foi passado ?veiculo=ID (seleção rápida)
if (isset($_GET['veiculo'])) {
    $vid = intval($_GET['veiculo']);
    foreach ($veiculos as $v) {
        if (intval($v['id']) === $vid) {
            $selected = $v;
            $_SESSION['user']['veiculo_id'] = $v['id'];
            break;
        }
    }
}

// 2) se não veio pela query, tentar por veiculo_id em sessão
if (!$selected && isset($_SESSION['user']['veiculo_id'])) {
    $vid = intval($_SESSION['user']['veiculo_id']);
    foreach ($veiculos as $v) {
        if (intval($v['id']) === $vid) {
            $selected = $v;
            break;
        }
    }
}

// 3) se ainda não tiver, tentar localizar por placa armazenada em sessão
if (!$selected && !empty($_SESSION['user']['placa'])) {
    $sessPlaca = trim($_SESSION['user']['placa']);
    foreach ($veiculos as $v) {
        if (strcasecmp(trim($v['placa']), $sessPlaca) === 0) {
            $selected = $v;
            $_SESSION['user']['veiculo_id'] = $v['id'];
            break;
        }
    }
}

// 4) se houver apenas 1 veículo, escolher por padrão
if (!$selected && count($veiculos) === 1) {
    $selected = $veiculos[0];
    $_SESSION['user']['veiculo_id'] = $selected['id'];
}

// Construir dados do usuário/veículo para exibição (substitui o carro genérico)
$placaExib = $selected['placa'] ?? ($_SESSION['user']['placa'] ?? 'ABC-1234');
$carroExib = $selected ? trim(($selected['modelo'] ?? '') . ($selected['ano'] ? ' ' . $selected['ano'] : '')) : ($_SESSION['user']['carro'] ?? 'Veículo não especificado');

$user = [
    'nome' => $_SESSION['user']['nome'] ?? 'Usuário',
    'placa' => $placaExib,
    'carro' => $carroExib,
    'km' => '45.000 km',
    'status' => 'Em Procedimento',
    'previsao' => '15/01/2025',
    'atualizacao' => '14/01/2025 14:30',
];

$servicos = [
    ['nome' => 'Troca de óleo', 'obrigatorio' => true, 'feito' => true],
    ['nome' => 'Filtro de ar', 'obrigatorio' => true, 'feito' => false],
    ['nome' => 'Revisão de freios', 'obrigatorio' => true, 'feito' => false],
    ['nome' => 'Alinhamento', 'obrigatorio' => false, 'feito' => false],
];

$orcamento = [
    ['nome' => 'Troca de óleo + filtro', 'valor' => 120, 'obrigatorio' => true],
    ['nome' => 'Pastilhas de freio', 'valor' => 280, 'obrigatorio' => true],
    ['nome' => 'Alinhamento', 'valor' => 80, 'obrigatorio' => false],
];

// Tentar carregar último pedido e itens do veículo para preencher progresso/orçamento
$total = 0;
$ultimoPedido = null;
$itensPedido = [];
if (!empty($selected['id'])) {
    try {
        $pstmt = $pdo->prepare('SELECT * FROM pedido_servico WHERE id_veiculo = :id_veiculo ORDER BY data_criacao DESC LIMIT 1');
        $pstmt->bindValue(':id_veiculo', $selected['id'], PDO::PARAM_INT);
        $pstmt->execute();
        $ultimoPedido = $pstmt->fetch(PDO::FETCH_ASSOC);
        if ($ultimoPedido) {
            $ipstmt = $pdo->prepare('SELECT * FROM item_servico WHERE id_pedido = :id_pedido');
            $ipstmt->bindValue(':id_pedido', $ultimoPedido['id'], PDO::PARAM_INT);
            $ipstmt->execute();
            $itensPedido = $ipstmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // em caso de erro, manter os dados estáticos
        $ultimoPedido = null;
        $itensPedido = [];
    }
}

if ($itensPedido) {
    // construir servicos/orcamento a partir dos itens do pedido
    $servicos = [];
    $orcamento = [];
    foreach ($itensPedido as $it) {
        $statusItem = $it['status_item'] ?? '';
        $feito = false;
        $sNorm = mb_strtolower(trim($statusItem));
        if ($sNorm === 'concluido' || $sNorm === 'concluído' || $sNorm === 'finalizado' || $sNorm === 'feito') {
            $feito = true;
        }
        $servicos[] = ['nome' => $it['descricao'], 'obrigatorio' => true, 'feito' => $feito];
        $orcamento[] = ['nome' => $it['descricao'], 'valor' => floatval($it['valor']), 'obrigatorio' => true];
        $total += floatval($it['valor']);
    }
    // atualizar status/previsao/atualizacao com dados do pedido
    $user['status'] = $ultimoPedido['status'] ?? $user['status'];
    $user['atualizacao'] = $ultimoPedido['data_criacao'] ?? $user['atualizacao'];
    // previsao não existe na tabela; manter placeholder
} else {
    foreach ($orcamento as $item) $total += $item['valor'];
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Área do Cliente - AutoTech</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style_user.css">

</head>

<body>
    <div class="container" style="flex-direction:column;align-items:stretch;min-height:100vh;">
        <div class="user-header">
            <img src="../assets/logo.png" alt="logo" style="height:38px;">
            <span class="brand">AutoTech</span>
            <span style="font-size:0.95em;color:var(--muted);margin-left:8px;">Área do Cliente</span>
            <a href="logout.php" class="button logout" style="width:auto;padding:8px 18px;font-size:1em;">⤴ Sair</a>
        </div>
        <div class="card-user">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:1.2em;">🚗 <b><?= htmlspecialchars($user['placa']) ?></b></span>
                <span class="status-badge"><?= htmlspecialchars($user['status']) ?></span>
            </div>
            <?php if (count($veiculos) > 1): ?>
                <ul class="vehicle-list">
                    <?php foreach ($veiculos as $v):
                        $isActive = (isset($selected) && $selected && intval($selected['id']) === intval($v['id']));
                        $label = htmlspecialchars($v['placa']);
                        $sub = htmlspecialchars(trim(($v['modelo'] ?? '') . ($v['ano'] ? ' ' . $v['ano'] : '')));
                    ?>
                        <li>
                            <a href="?veiculo=<?= intval($v['id']) ?>" class="btn-vehicle<?= $isActive ? ' active' : '' ?>">
                                <strong><?= $label ?></strong>
                                <span style="margin-left:8px;font-size:0.85em;color:var(--muted);"><?= $sub ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div style="color:var(--muted);margin-bottom:6px;">
                <?= htmlspecialchars($user['carro']) ?> • <?= htmlspecialchars($user['km']) ?>
            </div>
            <div style="font-size:0.98em;margin-bottom:8px;">Nossos técnicos estão trabalhando no seu veículo</div>
            <div style="display:flex;gap:18px;font-size:0.95em;color:var(--muted);">
                <span>📅 Previsão de entrega: <b><?= $user['previsao'] ?></b></span>
                <span>🕒 Última atualização: <?= $user['atualizacao'] ?></span>
            </div>
        </div>
        <div class="cards-container">
            <div class="card" style="margin-bottom:18px;">
                <h2 style="font-size:1.1em;margin:0 0 10px;">Progresso dos Serviços</h2>
                <div style="font-size:0.98em;color:var(--muted);margin-bottom:8px;">Lista de Serviços</div>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($servicos as $s): ?>
                        <li style="display:flex;align-items:center;gap:10px;margin-bottom:7px;">
                            <?php if ($s['feito']): ?>
                                <span style="color:#22C55E;font-size:1.2em;">●</span>
                                <span class="servico-feito" style="flex:1;"> <s><?= htmlspecialchars($s['nome']) ?></s></span>
                            <?php else: ?>
                                <span style="color:var(--muted);font-size:1.2em;">○</span>
                                <span class="servico-pendente" style="flex:1;"> <?= htmlspecialchars($s['nome']) ?></span>
                            <?php endif; ?>
                            <span class="orcamento-badge obrigatorio">
                                <?= $s['obrigatorio'] ? 'Obrigatório' : 'Opcional' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card" style="margin-bottom:18px;">
                <h2 style="font-size:1.1em;margin:0 0 10px;">Orçamento Detalhado</h2>
                <div style="font-size:0.98em;color:var(--muted);margin-bottom:8px;">Valores dos serviços e peças</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th style="width:120px;">&nbsp;</th>
                            <th style="width:100px;text-align:right;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orcamento as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nome']) ?></td>
                                <td>
                                    <span class="orcamento-badge <?= $item['obrigatorio'] ? 'obrigatorio' : 'opcional' ?>">
                                        <?= $item['obrigatorio'] ? 'Obrigatório' : 'Opcional' ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">R$ <?= number_format($item['valor'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:right;">Total</th>
                            <th style="text-align:right;">R$ <?= number_format($total, 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card">
                <h2 style="font-size:1.1em;margin:0 0 10px;">Histórico de Serviços</h2>
                <div style="font-size:0.98em;color:var(--muted);margin-bottom:8px;">
                    Consulte todos os serviços realizados anteriormente neste veículo
                </div>
                <!-- Aqui pode ser listado o histórico real -->
                <div class="card card-historico">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="font-size:1.2em;">🚗 <b><?= htmlspecialchars($user['placa']) ?></b></span>
                        <span class="status-badge"><?= htmlspecialchars($user['status']) ?></span>
                    </div>
                    <div style="color:var(--muted);margin-bottom:6px;">
                        <?= htmlspecialchars($user['carro']) ?> • <?= htmlspecialchars($user['km']) ?>
                    </div>
                    <div style="font-size:0.98em;margin-bottom:8px;">Nossos técnicos estão trabalhando no seu veículo
                    </div>
                    <div style="display:flex;gap:18px;font-size:0.95em;color:var(--muted);">
                        <span>📅 Previsão de entrega: <b><?= $user['previsao'] ?></b></span>
                        <span>🕒 Última atualização: <?= $user['atualizacao'] ?></span>
                    </div>
                </div>
            </div>
</body>

</html>