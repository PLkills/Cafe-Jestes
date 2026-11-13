<?php require __DIR__ . '/../layout/header.php'; ?>
<h1>Fila de preparo</h1>
<p class="nota">Somente pedidos com pagamento aprovado. A página atualiza sozinha a cada 30 segundos.</p>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>
<?php if (!$fila): ?><p class="card">Nenhum pedido na fila no momento. &#9749;</p><?php endif; ?>
<div class="grade-op">
<?php foreach ($fila as $p): ?>
  <article class="bloco op-card">
    <h2>Pedido #<?= (int)$p['id'] ?> <span class="status st-<?= e($p['status']) ?>"><?= e(STATUS_PEDIDO[$p['status']]) ?></span></h2>
    <p><small><?= e($p['cliente']) ?> &middot; feito às <?= e(data_br($p['criado_em'])) ?><br>
      <?= $p['tipo_entrega'] === 'agendada' ? '<strong>Agendado para ' . e(data_br($p['data_agendada'])) . '</strong>' : 'Entrega imediata' ?></small></p>
    <ul class="mini-lista">
      <?php foreach ($itens[$p['id']] ?? [] as $i): ?>
        <li><strong><?= (int)$i['quantidade'] ?>x <?= e($i['nome']) ?></strong>
          <?php if ($d = desc_personalizacao($i)): ?><br><small class="pers"><?= e($d) ?></small><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
    <form method="post" action="index.php?r=barista/avancar">
      <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <input type="hidden" name="de" value="<?= e($p['status']) ?>">
      <button type="submit"><?= $p['status'] === 'aguardando_preparo' ? 'Iniciar preparo' : 'Marcar como pronto' ?></button>
    </form>
  </article>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
