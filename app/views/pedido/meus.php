<?php $titulo = 'Meus pedidos'; require __DIR__ . '/../layout/header.php'; ?>
<h1>Meus pedidos</h1>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>

<?php if ($notificacoes): ?>
<section class="bloco">
  <h2>Notificações</h2>
  <ul class="notifs">
    <?php foreach ($notificacoes as $n): ?>
      <li class="<?= $n['lida'] ? '' : 'nova' ?>"><?= e($n['mensagem']) ?> <small><?= e(data_br($n['criada_em'])) ?></small></li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<?php if (!$pedidos): ?>
  <p class="vazio">Você ainda não fez pedidos. <a href="index.php?r=produto/index">Ver a vitrine</a>.</p>
<?php else: ?>
<div class="tabela-wrap">
<table class="tabela">
  <thead><tr><th>Pedido</th><th>Data</th><th>Status</th><th>Pagamento</th><th>Total</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($pedidos as $p): ?>
    <tr>
      <td>#<?= (int)$p['id'] ?></td>
      <td><?= e(data_br($p['criado_em'])) ?></td>
      <td><span class="status st-<?= e($p['status']) ?>"><?= e(STATUS_PEDIDO[$p['status']] ?? $p['status']) ?></span></td>
      <td><?= $p['metodo'] === 'pix' ? 'Pix' : 'Cartão' ?> &middot; <?= e(STATUS_PAGAMENTO[$p['pagamento_status']] ?? '-') ?></td>
      <td><?= moeda($p['total']) ?></td>
      <td><a href="index.php?r=pedido/ver&amp;id=<?= (int)$p['id'] ?>">Detalhes</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
