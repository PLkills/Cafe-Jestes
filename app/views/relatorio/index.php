<?php require __DIR__ . '/../layout/header.php';
$qs = 'de=' . urlencode($de) . '&ate=' . urlencode($ate); ?>
<div class="barra-topo no-print">
  <h1>Relatórios</h1>
  <button type="button" onclick="window.print()">Imprimir / salvar em PDF</button>
</div>
<h1 class="so-print">Café Jester &mdash; Relatório de vendas</h1>
<form method="get" class="inline-form filtros no-print">
  <input type="hidden" name="r" value="relatorio/index">
  <label for="de">De</label><input type="date" id="de" name="de" value="<?= e($de) ?>" required>
  <label for="ate">Até</label><input type="date" id="ate" name="ate" value="<?= e($ate) ?>" required>
  <button type="submit">Aplicar</button>
</form>
<p class="nota">Período: <?= e(date('d/m/Y', strtotime($de))) ?> a <?= e(date('d/m/Y', strtotime($ate))) ?> &middot; considera pedidos com pagamento aprovado, exceto cancelados.</p>

<div class="kpis">
  <div class="kpi"><span><?= (int)$tot['pedidos'] ?></span>Pedidos</div>
  <div class="kpi"><span><?= moeda($tot['faturamento']) ?></span>Faturamento</div>
  <div class="kpi"><span><?= moeda($tot['ticket']) ?></span>Ticket médio</div>
  <div class="kpi"><span><?= moeda($tot['taxas']) ?></span>Taxas de entrega</div>
</div>

<div class="bloco tabela-wrap"><h2>Vendas por dia <a class="no-print peq" href="index.php?r=relatorio/csv&amp;tipo=vendas&amp;<?= $qs ?>">CSV</a></h2>
<?php if (!$dias): ?><p>Sem vendas no período.</p><?php else: ?>
  <table class="tabela"><thead><tr><th>Dia</th><th>Pedidos</th><th>Total</th><th class="no-print"></th></tr></thead><tbody>
  <?php foreach ($dias as $d): ?>
    <tr><td><?= e(date('d/m/Y', strtotime($d['dia']))) ?></td><td><?= (int)$d['pedidos'] ?></td><td><?= moeda($d['total']) ?></td>
        <td class="no-print"><span class="barra" style="width:<?= $max > 0 ? round($d['total'] / $max * 100) : 0 ?>%"></span></td></tr>
  <?php endforeach; ?></tbody></table>
<?php endif; ?></div>

<div class="bloco tabela-wrap"><h2>Mais vendidos <a class="no-print peq" href="index.php?r=relatorio/csv&amp;tipo=produtos&amp;<?= $qs ?>">CSV</a></h2>
<?php if (!$top): ?><p>Sem vendas no período.</p><?php else: ?>
  <table class="tabela"><thead><tr><th>#</th><th>Produto</th><th>Qtd.</th><th>Total</th></tr></thead><tbody>
  <?php foreach ($top as $k => $t): ?><tr><td><?= $k + 1 ?></td><td><?= e($t['nome']) ?></td><td><?= (int)$t['qtd'] ?></td><td><?= moeda($t['total']) ?></td></tr><?php endforeach; ?>
  </tbody></table>
<?php endif; ?></div>

<div class="bloco tabela-wrap"><h2>Formas de pagamento <a class="no-print peq" href="index.php?r=relatorio/csv&amp;tipo=pagamentos&amp;<?= $qs ?>">CSV</a></h2>
<?php if (!$pags): ?><p>Sem vendas no período.</p><?php else: ?>
  <table class="tabela"><thead><tr><th>Forma</th><th>Pedidos</th><th>Total</th></tr></thead><tbody>
  <?php foreach ($pags as $g): ?><tr><td><?= $g['metodo'] === 'pix' ? 'Pix' : 'Cartão' ?></td><td><?= (int)$g['pedidos'] ?></td><td><?= moeda($g['total']) ?></td></tr><?php endforeach; ?>
  </tbody></table>
<?php endif; ?></div>

<div class="bloco tabela-wrap"><h2>Entregadores</h2>
<?php if (!$ents): ?><p>Sem entregas concluídas no período.</p><?php else: ?>
  <table class="tabela"><thead><tr><th>Entregador</th><th>Entregas</th><th>Nota média</th></tr></thead><tbody>
  <?php foreach ($ents as $en): ?><tr><td><?= e($en['nome']) ?></td><td><?= (int)$en['entregas'] ?></td><td><?= $en['nota'] !== null ? number_format((float)$en['nota'], 1, ',', '') . '/5' : '<small>sem avaliações</small>' ?></td></tr><?php endforeach; ?>
  </tbody></table>
<?php endif; ?></div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
