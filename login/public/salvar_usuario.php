<?php
require_once __DIR__ . '/../app/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}
$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$cpf_usuario = trim($_POST['cpf_usuario'] ?? '');
$placa_hash = trim($_POST['placa_hash'] ?? '');

if ($nome === '' || $telefone === '' || $cpf_usuario === '' || $placa_hash === '') {
  header('Location: register.php?err=Preencha todos os campos.');
  exit;
}
// if ($placa_hash !== $confirmar) {
//   header('Location: register.php?err=As senhas não conferem.');
//   exit;
// }
try {
  $hash = password_hash($placa_hash, PASSWORD_BCRYPT);
  $stmt = $pdo->prepare('INSERT INTO usuarios (nome, telefone, cpf_usuario, placa_hash, placa) VALUES (:nome,:telefone,:cpf_usuario,:hash, :placa)');
  $stmt->bindValue(':nome', $nome);
  $stmt->bindValue(':telefone', $telefone);
  $stmt->bindValue(':cpf_usuario', $cpf_usuario);
  $stmt->bindValue(':hash', $hash);
  $stmt->bindValue(':placa', $placa_hash);
  $stmt->execute();
  header('Location: index.php?ok=Cadastro realizado! Faça login.');
} catch (PDOException $e) {
  if (($e->errorInfo[1] ?? 0) == 1062) {
    header('Location: register.php?err=Usuário já existe.');
  } else {
    header('Location: register.php?err=Erro ao salvar.');
  }
}