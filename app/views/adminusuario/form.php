<?php require __DIR__ . '/../layout/header.php'; ?>
<p><a href="index.php?r=adminusuario/index">&larr; Voltar</a></p>
<h1>Nova conta</h1>
<?php if ($erro): ?><p class="erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
<form method="post" class="card-form" autocomplete="off">
  <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
  <label for="nome">Nome</label>
  <input id="nome" name="nome" required maxlength="120" value="<?= e($d['nome']) ?>">
  <label for="email">E-mail</label>
  <input id="email" type="email" name="email" required maxlength="150" value="<?= e($d['email']) ?>">
  <label for="telefone">Telefone</label>
  <input id="telefone" name="telefone" maxlength="20" value="<?= e($d['telefone']) ?>">
  <label for="perfil">Perfil</label>
  <select id="perfil" name="perfil">
    <?php foreach ($perm as $pf): ?><option value="<?= e($pf) ?>" <?= $d['perfil'] === $pf ? 'selected' : '' ?>><?= e(ucfirst($pf)) ?></option><?php endforeach; ?>
  </select>
  <label for="senha">Senha inicial (mín. 8 caracteres)</label>
  <input id="senha" type="password" name="senha" minlength="8" required autocomplete="new-password">
  <button type="submit">Criar conta</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>
