<?php
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
    $_SESSION['flash_err'] = $msg;
    session_write_close();
    header('Location: index.php?err=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$cpf = trim($_POST['cpf_usuario'] ?? '');
$senha = trim($_POST['placa_hash'] ?? '');






// validação básica
if ($cpf === '' || $senha === '') {
    header('Location: index.php?err=' . urlencode('Informe usuário e senha.'));
    exit;
}

try {
    // busca usuário
    $stmt = $pdo->prepare("SELECT id, cpf_usuario, placa_hash, tipo FROM usuarios WHERE cpf_usuario = :cpf LIMIT 1");
    $stmt->bindValue(':cpf', $cpf);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($usuario && password_verify($senha, $usuario['placa_hash'])) {
        // Login bem-sucedido: zera tentativas
        $_SESSION['tentativas'] = 0;
        $_SESSION['bloqueio_login'] = 0;
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $usuario['id'],
            'cpf_usuario' => $usuario['cpf_usuario'],
            'tipo' => $usuario['tipo']
        ];

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
            // $msg = 'Login bloqueado por 30 segundos após 5 tentativas erradas.';
        } else {
            $msg = 'Credenciais inválidas.';
        }
        // define flash para garantir que a mensagem chegue ao index mesmo que a querystring se perca
        $_SESSION['flash_err'] = $msg;
        session_write_close();
        header('Location: index.php?err=' . urlencode($msg));
        exit;
    }

} catch (PDOException $e) {
    // em produção, não exibir o erro diretamente
    die("Erro no banco: " . $e->getMessage());
}
