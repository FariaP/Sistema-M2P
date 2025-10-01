<?php
session_start();
// só entra se estiver logado e for usuário comum
session_regenerate_id(true);
if (!isset($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'user') {
    header('Location: index.php?err=' . urlencode('Acesso não permitido.'));
    exit;
}

// Simulação de dados do usuário e serviços (ajuste para dados reais do seu sistema)
$user = [
    'nome' => $_SESSION['user']['nome'] ?? 'Usuário',
    'placa' => $_SESSION['user']['placa'] ?? 'ABC-1234',
    'carro' => 'Honda Civic 2020',
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
$total = 0;
foreach ($orcamento as $item)
    $total += $item['valor'];
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