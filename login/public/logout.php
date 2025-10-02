<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

session_start();
session_unset();
session_destroy();
// Inicia nova sessão para mensagem
session_start();
$_SESSION['mensagem_logout'] = 'Você saiu com sucesso.';
header('Location: index.php');
exit;