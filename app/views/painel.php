<?php $titulo = 'Painel'; require __DIR__ . '/layout/header.php'; ?>
<section class="card">
  <h1>Olá, <?= e($u['nome']) ?></h1>
  <p>Perfil: <strong><?= e($u['perfil']) ?></strong></p>
  <ul class="atalhos">
    <li><a href="index.php?r=produto/index">Ver a vitrine</a></li>
    <?php if (in_array($u['perfil'], ['gerente', 'administrador'], true)): ?>
      <li><a href="index.php?r=adminproduto/index">Gerenciar produtos</a></li>
      <li><a href="index.php?r=adminusuario/index">Usuários (clientes, equipe e entregadores)</a></li>
      <li><a href="index.php?r=relatorio/index">Relatórios de vendas</a></li>
    <?php endif; ?>
    <?php if ($u['perfil'] === 'administrador'): ?>
      <li><a href="index.php?r=backup/index">Backup dos dados</a></li>
    <?php endif; ?>
    <?php if (in_array($u['perfil'], ['barista', 'gerente', 'administrador'], true)): ?>
      <li><a href="index.php?r=barista/index">Fila de preparo</a></li>
    <?php endif; ?>
    <?php if (in_array($u['perfil'], ['gerente', 'administrador'], true)): ?>
      <li><a href="index.php?r=gestao/index">Gestão de entregas</a></li>
    <?php endif; ?>
    <?php if ($u['perfil'] === 'entregador'): ?>
      <li><a href="index.php?r=entregador/index">Minhas entregas</a></li>
    <?php endif; ?>
  </ul>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
