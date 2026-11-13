<?php require __DIR__ . '/../layout/header.php'; ?>
<h1>Gestão de entregas</h1>
<p class="nota">Atualiza sozinha a cada 30 segundos.</p>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>
<div class="bloco tabela-wrap">
  <h2>Pedidos em aberto</h2>
  <?php if (!$pedidos): ?><p>Nenhum pedido em aberto.</p><?php else: ?>
  <table class="tabela">
    <thead><tr><th>#</th><th>Cliente</th><th>Status</th><th>Pagamento</th><th>Total</th><th>Entregador</th></tr></thead>
    <tbody>
    <?php foreach ($pedidos as $p): $pago = $p['pagamento_status'] === 'aprovado'; $livre = in_array($p['status'], ['aguardando_preparo', 'em_preparo', 'pronto'], true); ?>
      <tr>
        <td><a href="index.php?r=pedido/ver&id=<?= (int)$p['id'] ?>">#<?= (int)$p['id'] ?></a></td>
        <td><?= e($p['cliente']) ?><br><small><?= $p['tipo_entrega'] === 'agendada' ? 'Agend. ' . e(data_br($p['data_agendada'])) : 'Imediata' ?></small></td>
        <td><span class="status st-<?= e($p['status']) ?>"><?= e(STATUS_PEDIDO[$p['status']]) ?></span></td>
        <td><?= e(STATUS_PAGAMENTO[$p['pagamento_status']] ?? '-') ?></td>
        <td><?= moeda($p['total']) ?></td>
        <td>
          <?php if ($pago && $livre): ?>
            <form method="post" action="index.php?r=gestao/atribuir" class="inline-form">
              <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <label class="sr" for="ent<?= (int)$p['id'] ?>">Entregador do pedido <?= (int)$p['id'] ?></label>
              <select name="entregador_id" id="ent<?= (int)$p['id'] ?>" required>
                <option value="">Escolher...</option>
                <?php foreach ($entregadores as $en): ?>
                  <option value="<?= (int)$en['id'] ?>" <?= (int)$en['id'] === (int)$p['entregador_id'] ? 'selected' : '' ?>><?= e($en['nome']) ?> (<?= (int)$en['em_andamento'] ?> em andamento)</option>
                <?php endforeach; ?>
              </select>
              <button type="submit"><?= $p['entregador_id'] ? 'Trocar' : 'Atribuir' ?></button>
            </form>
          <?php else: ?>
            <?= $p['entregador'] ? e($p['entregador']) : '<small>' . ($pago ? '-' : 'Aguardando pagamento') . '</small>' ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<div class="bloco tabela-wrap">
  <h2>Últimas entregas concluídas</h2>
  <?php if (!$recentes): ?><p>Ainda não há entregas concluídas.</p><?php else: ?>
  <table class="tabela">
    <thead><tr><th>#</th><th>Cliente</th><th>Entregador</th><th>Entregue em</th><th>Nota</th></tr></thead>
    <tbody>
    <?php foreach ($recentes as $r): ?>
      <tr><td>#<?= (int)$r['id'] ?></td><td><?= e($r['cliente']) ?></td><td><?= e($r['entregador'] ?? '-') ?></td>
          <td><?= e(data_br($r['entregue_em'])) ?></td>
          <td><?= $r['nota_cliente'] ? (int)$r['nota_cliente'] . '/5' : '<small>sem avaliação</small>' ?><?= $r['comentario_cliente'] ? '<br><small>' . e($r['comentario_cliente']) . '</small>' : '' ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
