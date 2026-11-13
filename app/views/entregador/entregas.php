<?php require __DIR__ . '/../layout/header.php'; ?>
<h1>Minhas entregas</h1>
<p class="nota">Atualiza sozinha a cada 30 segundos.</p>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>
<?php if ($avisos): ?>
  <div class="bloco"><h2>Avisos</h2><ul class="notifs">
    <?php foreach ($avisos as $a): ?><li><?= e($a['mensagem']) ?> <small><?= e(data_br($a['criada_em'])) ?></small></li><?php endforeach; ?>
  </ul></div>
<?php endif; ?>
<?php if (!$entregas): ?><p class="card">Nenhuma entrega atribuída a você agora.</p><?php endif; ?>
<div class="grade-op">
<?php foreach ($entregas as $p): ?>
  <article class="bloco op-card">
    <h2>Pedido #<?= (int)$p['id'] ?> <span class="status st-<?= e($p['status']) ?>"><?= e(STATUS_PEDIDO[$p['status']]) ?></span></h2>
    <p><strong><?= e($p['cliente']) ?></strong><?= $p['telefone'] ? ' &middot; ' . e($p['telefone']) : '' ?><br>
      <?= e(endereco_texto($p)) ?>
      <?php if ($p['instrucoes']): ?><br><small>Instruções: <?= e($p['instrucoes']) ?></small><?php endif; ?></p>
    <p><small>
      <?= $p['tipo_entrega'] === 'agendada' ? 'Agendado para ' . e(data_br($p['data_agendada'])) : 'Entrega imediata' ?> &middot;
      Pagamento: <?= $p['metodo'] === 'pix' ? 'Pix' : 'Cartão' ?> (<?= e(STATUS_PAGAMENTO[$p['pagamento_status']] ?? '-') ?>) &middot; <?= moeda($p['total']) ?></small></p>
    <p><a class="btn-link" href="<?= e(url_mapa_rota($p)) ?>" target="_blank" rel="noopener">&#128205; Abrir rota no Google Maps</a></p>
    <?php if ($p['status'] === 'a_caminho'): ?>
      <iframe class="mapa" title="Mapa do endereço do pedido <?= (int)$p['id'] ?>" loading="lazy" src="<?= e(url_mapa_embed($p)) ?>"></iframe>
    <?php endif; ?>
    <?php if ($p['status'] === 'pronto' || $p['status'] === 'a_caminho'): ?>
      <form method="post" action="index.php?r=entregador/avancar">
        <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="de" value="<?= e($p['status']) ?>">
        <button type="submit"><?= $p['status'] === 'pronto' ? 'Retirei, saí para entrega' : 'Confirmar entrega' ?></button>
      </form>
    <?php else: ?>
      <p class="nota">Aguardando o preparo ficar pronto.</p>
    <?php endif; ?>
  </article>
<?php endforeach; ?>
</div>
<?php if ($feitas): ?>
<div class="bloco"><h2>Últimas concluídas</h2><ul class="mini-lista">
  <?php foreach ($feitas as $f): ?><li>#<?= (int)$f['id'] ?> &middot; <?= e($f['cliente']) ?> &middot; <?= e(data_br($f['entregue_em'])) ?> &middot; <?= $f['nota_cliente'] ? 'nota ' . (int)$f['nota_cliente'] . '/5' : 'sem avaliação' ?></li><?php endforeach; ?>
</ul></div>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
