<?php require __DIR__ . '/../layout/header.php'; ?>
<div class="barra-topo">
  <h1>Usuários</h1>
  <a class="btn" href="index.php?r=adminusuario/novo">+ Nova conta</a>
</div>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>
<form method="get" class="inline-form filtros">
  <input type="hidden" name="r" value="adminusuario/index">
  <label class="sr" for="q">Buscar</label>
  <input type="search" id="q" name="q" placeholder="Nome ou e-mail" value="<?= e($q) ?>">
  <label class="sr" for="perfil">Perfil</label>
  <select id="perfil" name="perfil">
    <option value="">Todos os perfis</option>
    <?php foreach (Usuario::PERFIS as $pf): ?><option value="<?= e($pf) ?>" <?= $perfil === $pf ? 'selected' : '' ?>><?= e(ucfirst($pf)) ?></option><?php endforeach; ?>
  </select>
  <button type="submit">Filtrar</button>
</form>
<div class="tabela-wrap">
<table class="tabela">
  <thead><tr><th>Nome</th><th>E-mail / telefone</th><th>Perfil</th><th>Atividade</th><th>Situação</th><th>Ações</th></tr></thead>
  <tbody>
  <?php foreach ($usuarios as $x): $pode = in_array($x['perfil'], $gerenciaveis, true); ?>
    <tr class="<?= $x['ativo'] ? '' : 'inativo' ?>">
      <td><?= e($x['nome']) ?></td>
      <td><?= e($x['email']) ?><?= $x['telefone'] ? '<br><small>' . e($x['telefone']) . '</small>' : '' ?></td>
      <td><?= e($x['perfil']) ?></td>
      <td><small><?= $x['perfil'] === 'cliente' ? (int)$x['qtd_pedidos'] . ' pedido(s)' : ($x['perfil'] === 'entregador' ? (int)$x['qtd_entregas'] . ' entrega(s)' : '-') ?></small></td>
      <td><?= $x['ativo'] ? 'Ativa' : 'Desativada' ?></td>
      <td class="acoes">
      <?php if ($pode && (int)$x['id'] !== (int)$u['id']): ?>
        <form method="post" action="index.php?r=adminusuario/alternar" onsubmit="return confirm('Confirmar?');">
          <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
          <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
          <button type="submit" class="link"><?= $x['ativo'] ? 'Desativar' : 'Reativar' ?></button>
        </form>
        <form method="post" action="index.php?r=adminusuario/senha" class="inline-form">
          <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
          <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
          <label class="sr" for="s<?= (int)$x['id'] ?>">Nova senha de <?= e($x['nome']) ?></label>
          <input type="password" id="s<?= (int)$x['id'] ?>" name="senha" minlength="8" placeholder="Nova senha" autocomplete="new-password" required>
          <button type="submit" class="link">Redefinir</button>
        </form>
      <?php else: ?><small>&mdash;</small><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$usuarios): ?><tr><td colspan="6">Nenhuma conta encontrada.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
