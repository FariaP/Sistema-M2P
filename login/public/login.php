<?php
session_start();
require_once __DIR__ . '/../app/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:index.php');
    exit;
}

$cpf = trim($_POST['cpf_usuario'] ?? '');
$senha = trim($_POST['placa_hash'] ?? '');

// validação básica
if ($cpf === '' || $senha === '') {
    header('Location:index.php?err=Informe usuário e senha.');
    exit;
}

try {
    // busca usuário
    $stmt = $pdo->prepare("SELECT id, cpf_usuario, placa_hash, tipo FROM usuarios WHERE cpf_usuario = :cpf LIMIT 1");
    $stmt->bindValue(':cpf', $cpf);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['placa_hash'])) {
        // cria sessão
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
        header('Location:index.php?err=Credenciais inválidas.');
        exit;
    }

} catch (PDOException $e) {
    // em produção, não exibir o erro diretamente
    die("Erro no banco: " . $e->getMessage());
}
