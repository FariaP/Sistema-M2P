<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true
]);

session_start();

// Mensagens de feedback via sessão
$ok = $_SESSION['mensagem_logout'] ?? $_SESSION['ok'] ?? '';
$err = $_SESSION['err'] ?? '';
unset($_SESSION['mensagem_logout'], $_SESSION['ok'], $_SESSION['err']);


// if (isset($_SESSION['user'])) {
//   header('Location: listar_usuarios.php');
//   exit;
// }

// Verifica bloqueio
$bloqueado = false;
$tempo_restante = 0;
if (isset($_SESSION['bloqueio_login']) && $_SESSION['bloqueio_login'] > time()) {
  $bloqueado = true;
  $tempo_restante = $_SESSION['bloqueio_login'] - time();
}

?>



<!doctype html>
<html lang="pt-br">


<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Login</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>


<body>
  <div class="container">
    <div class="card">
      <header>
        <img src="../assets/logo.png" alt="logo" />
      </header>
      <h1>Sistema de Login</h1>
      <?php if ($ok): ?>
        <div class="success alert"><?= htmlspecialchars($ok) ?></div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="alert"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>
      <?php if ($bloqueado): ?>
        <div class="alert">Login bloqueado por <?php echo $tempo_restante; ?> segundos. Aguarde.</div>
      <?php endif; ?>
      <form class="form" method="post" action="login.php" autocomplete="off">
        <input class="input" type="text" name="cpf_usuario" placeholder="CPF" required minlength="3" maxlength="80"
          <?php echo $bloqueado ? 'disabled' : ''; ?>>
        <input class="input" type="text" name="senha_hash" placeholder="Telefone" required minlength="4" maxlength="64"
          <?php echo $bloqueado ? 'disabled' : ''; ?>>
        <button class="button" type="submit" <?php echo $bloqueado ? 'disabled' : ''; ?>>ENTRAR</button>
      </form>
      <div class="helper">Ainda não é Cadastrado? <a href="register.php">Cadastre-se!</a></div>
    </div>
  </div>

  <script>// Máscara CPF
    const cpfInput = document.querySelector('input[name="cpf_usuario"]');
    cpfInput.addEventListener('input', function (e) {
      let v = e.target.value.replace(/\D/g, '');
      if (v.length > 11) v = v.slice(0, 11);
      if (v.length > 9) v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
      else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
      else if (v.length > 3) v = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
      e.target.value = v;
    });

    // Máscara Placa (AAA-0A00 ou AAA-0000)
    const senhaInput = document.querySelector('input[name="senha_hash"]');

    // Máscara telefone
    senhaInput.addEventListener('input', function (e) {
      let v = e.target.value.replace(/\D/g, '');
      if (v.length > 11) v = v.slice(0, 11);
      if (v.length > 0) v = '(' + v;
      if (v.length > 3) v = v.slice(0, 3) + ') ' + v.slice(3);
      if (v.length > 10) v = v.slice(0, 10) + '-' + v.slice(10);
      else if (v.length > 9) v = v.slice(0, 9) + '-' + v.slice(9);
      e.target.value = v;
    });
  </script>
  
  <?php if ($bloqueado): ?>
    <script>
      setTimeout(function () {
        window.location.reload();
      }, <?php echo $tempo_restante * 1000; ?>);
    </script>
  <?php endif; ?>

</body>

</html>