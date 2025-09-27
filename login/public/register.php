<?php $ok = $_GET['ok'] ?? '';
$err = $_GET['err'] ?? ''; ?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cadastre-se</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <header><img src="../assets/logo.png" alt="logo" />
      </header>
      <h1>Cadastre-se</h1>
      <?php if ($ok): ?>
        <div class="success alert"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
      <?php if ($err): ?>
        <div class="alert"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <form class="form" method="post" action="salvar_usuario.php" autocomplete="off">
        <input class="input" type="text" name="nome" placeholder="Nome Completo" required minlength="3" maxlength="120">
        <input class="input" type="tel" name="telefone" id="telefone" placeholder="Telefone" required minlength="8"
          maxlength="15">
        <input class="input" type="text" name="cpf_usuario" placeholder="CPF" required minlength="3" maxlength="80">
        <input class="input" type="text" name="placa_hash" placeholder="Placa" required minlength="4" maxlength="64">
        <button class="button" type="submit">CADASTRAR</button>
      </form>
      <div class="helper"><a href="index.php">Já tem conta? Entrar</a></div>
    </div>
  </div>

  <script>
    // Máscara para telefone: (DDD) 11111-0000
    document.addEventListener('DOMContentLoaded', function () {
      // Máscara telefone
      const telInput = document.getElementById('telefone');
      telInput.addEventListener('input', function (e) {
        let v = e.target.value.replace(/\D/g, '');
        if (v.length > 11) v = v.slice(0, 11);
        if (v.length > 0) v = '(' + v;
        if (v.length > 3) v = v.slice(0, 3) + ') ' + v.slice(3);
        if (v.length > 10) v = v.slice(0, 10) + '-' + v.slice(10);
        else if (v.length > 9) v = v.slice(0, 9) + '-' + v.slice(9);
        e.target.value = v;
      });

      // Máscara CPF
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
      const placaInput = document.querySelector('input[name="placa_hash"]');
      placaInput.addEventListener('input', function (e) {
        let v = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        // Limita a 7 caracteres
        v = v.slice(0, 7);
        // Formatação AAA-0A00 ou AAA-0000
        if (v.length > 3) v = v.slice(0, 3) + '-' + v.slice(3);
        e.target.value = v;
      });
    });
  </script>
</body>

</html>