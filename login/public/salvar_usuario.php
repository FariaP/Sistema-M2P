<?php

require_once __DIR__ . '/../app/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$cpf_usuario = trim($_POST['cpf_usuario'] ?? '');
$placa_hash = trim($_POST['placa_hash'] ?? '');


if ($nome === '' || $telefone === '' || $cpf_usuario === '' || $placa_hash === '') {
  session_start();
  $_SESSION['err'] = 'Preencha todos os campos.';
  header('Location: register.php');
  exit;
}

try {
  $hash = password_hash($placa_hash, PASSWORD_BCRYPT);
  $hash_senha = password_hash($telefone, PASSWORD_BCRYPT);
  $stmt = $pdo->prepare('INSERT INTO usuarios (nome, telefone, cpf_usuario, placa_hash, placa, senha_hash) VALUES (:nome,:telefone,:cpf_usuario,:hash, :placa, :senha_hash)');
  $stmt->bindValue(':nome', $nome);
  $stmt->bindValue(':telefone', $telefone);
  $stmt->bindValue(':cpf_usuario', $cpf_usuario);
  $stmt->bindValue(':hash', $hash);
  $stmt->bindValue(':placa', $placa_hash);
  $stmt->bindValue(':senha_hash', $hash_senha);
  $stmt->execute();
  session_start();
  // redireciona para a área do admin com mensagem de sucesso
  header('Location: admin.php?success=' . urlencode('Usuário cadastrado com sucesso.'));
} catch (PDOException $e) {
  if (($e->errorInfo[1] ?? 0) == 1062) {
    session_start();
    $_SESSION['err'] = 'Usuário já existe.';
    header('Location: register.php');
  } else {
    session_start();
    $_SESSION['err'] = 'Erro ao salvar.';
    header('Location: register.php');
  }
}