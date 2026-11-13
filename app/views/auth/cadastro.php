<?php $titulo = 'Criar conta'; require __DIR__ . '/../layout/header.php'; ?>
<section class="card">
  <h1>Criar conta</h1>
  <?php if ($erro): ?><p class="erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
    <label for="nome">Nome</label>
    <input id="nome" name="nome" required>
    <label for="email">E-mail</label>
    <input id="email" type="email" name="email" required>
    <label for="telefone">Telefone</label>
    <input id="telefone" name="telefone">
    <label for="senha">Senha (minimo 6 caracteres)</label>
    <input id="senha" type="password" name="senha" minlength="6" required>
    <button type="submit">Cadastrar</button>
  </form>
  <p><a href="index.php?r=auth/login">Ja tenho conta</a></p>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
