<?php

session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

session_start();

// Inicializa tentativas e bloqueio
if (!isset($_SESSION['tentativas'])) {
    $_SESSION['tentativas'] = 0;
}
if (!isset($_SESSION['bloqueio_login'])) {
    $_SESSION['bloqueio_login'] = 0;
}
require_once __DIR__ . '/../app/config.php';


// Verifica bloqueio
if ($_SESSION['bloqueio_login'] > time()) {
    $restante = $_SESSION['bloqueio_login'] - time();
    $msg = 'Login bloqueado por ' . $restante . ' segundos. Aguarde.';
    $_SESSION['err'] = $msg;
    session_write_close();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$cpf = trim($_POST['cpf_usuario'] ?? '');
$senha = trim($_POST['senha_hash'] ?? '');






// validação básica
if ($cpf === '' || $senha === '') {
    $_SESSION['err'] = 'Informe usuário e senha.';
    header('Location: index.php');
    exit;
}

try {
    // busca usuário
    $stmt = $pdo->prepare("SELECT id, cpf_usuario, senha_hash, tipo FROM usuarios WHERE cpf_usuario = :cpf LIMIT 1");
    $stmt->bindValue(':cpf', $cpf);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        // Login bem-sucedido: zera tentativas
        $_SESSION['tentativas'] = 0;
        $_SESSION['bloqueio_login'] = 0;
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $usuario['id'],
            'cpf_usuario' => $usuario['cpf_usuario'],
            'tipo' => $usuario['tipo']
        ];
        // Buscar veículos associados ao CPF do usuário e popular sessão
        try {
            $vstmt = $pdo->prepare('SELECT id, placa, modelo, ano FROM veiculo WHERE cpf_usuario = :cpf ORDER BY id DESC');
            $vstmt->bindValue(':cpf', $usuario['cpf_usuario']);
            $vstmt->execute();
            $veiculos = $vstmt->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['user']['veiculos'] = $veiculos;

            if (count($veiculos) === 1) {
                // se houver apenas um veículo, seleciona automaticamente
                $v = $veiculos[0];
                $_SESSION['user']['veiculo_id'] = intval($v['id']);
                $_SESSION['user']['placa'] = $v['placa'] ?? null;
                $modelo = $v['modelo'] ?? '';
                $ano = $v['ano'] ? ' ' . $v['ano'] : '';
                $_SESSION['user']['carro'] = trim($modelo . $ano);
            } elseif (count($veiculos) > 1) {
                // se houver vários, não escolhe nenhum por padrão (user.php permitirá a escolha)
                $_SESSION['user']['veiculo_id'] = $_SESSION['user']['veiculo_id'] ?? null;
                // opcional: preencher placa/carro com o primeiro para exibição inicial
                $first = $veiculos[0];
                $_SESSION['user']['placa'] = $first['placa'] ?? null;
                $_SESSION['user']['carro'] = trim(($first['modelo'] ?? '') . ($first['ano'] ? ' ' . $first['ano'] : ''));
            } else {
                // sem veículos
                $_SESSION['user']['veiculo_id'] = null;
                $_SESSION['user']['placa'] = null;
                $_SESSION['user']['carro'] = null;
            }
        } catch (PDOException $e) {
            // não interrompe o login por causa de falha na leitura do veículo
            $_SESSION['user']['veiculos'] = [];
            $_SESSION['user']['veiculo_id'] = null;
            $_SESSION['user']['placa'] = null;
            $_SESSION['user']['carro'] = null;
        }

        // redireciona de acordo com o tipo
        if ($usuario['tipo'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
        exit;
    } else {
        // Incrementa tentativas
        $_SESSION['tentativas']++;
        if ($_SESSION['tentativas'] >= 5) {
            $_SESSION['bloqueio_login'] = time() + 30;
            $_SESSION['tentativas'] = 0;
        } else {
            $msg = 'Credenciais inválidas.';
            $_SESSION['err'] = $msg;
        }
        session_write_close();
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    // em produção, não exibir o erro diretamente
    die("Erro no banco: " . $e->getMessage());
}
