<?php $titulo = 'Entrar'; require __DIR__ . '/../layout/header.php'; ?>
<section class="card">
  <h1>Entrar</h1>
  <?php if ($erro): ?><p class="erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
    <label for="email">E-mail</label>
    <input id="email" type="email" name="email" required>
    <label for="senha">Senha</label>
    <input id="senha" type="password" name="senha" required>
    <button type="submit">Entrar</button>
  </form>
  <p>Novo por aqui? <a href="index.php?r=auth/cadastro">Criar conta</a></p>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
