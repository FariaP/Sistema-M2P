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

// Não permitir excluir o próprio usuário
if ($_SESSION['user']['id'] == $id) {
    header('Location: admin.php?err=' . urlencode('Você não pode excluir seu próprio usuário.'));
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: admin.php?success=' . urlencode('Usuário excluído com sucesso.'));
    } else {
        header('Location: admin.php?err=' . urlencode('Usuário não encontrado.'));
    }
} catch (PDOException $e) {
    header('Location: admin.php?err=' . urlencode('Erro ao excluir usuário.'));
}
exit;