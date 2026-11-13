<?php $titulo = 'Pedido #' . (int)$p['id']; require __DIR__ . '/../layout/header.php'; ?>
<p><a href="index.php?r=<?= $u['perfil'] === 'cliente' ? 'pedido/meus' : 'home/painel' ?>">&larr; Voltar</a></p>
<h1>Pedido #<?= (int)$p['id'] ?></h1>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>

<div class="carrinho-layout">
  <section>
    <div class="bloco">
      <h2>Status</h2>
      <p><span id="status-rotulo" class="status st-<?= e($p['status']) ?>"><?= e(STATUS_PEDIDO[$p['status']] ?? $p['status']) ?></span>
         &middot; feito em <?= e(data_br($p['criado_em'])) ?></p>
      <?php if ($p['status'] !== 'cancelado'): $__et = (int)array_search($p['status'], ETAPAS, true); ?>
        <div id="acompanhar" data-url="index.php?r=pedido/status&id=<?= (int)$p['id'] ?>" data-status="<?= e($p['status']) ?>">
          <ol class="etapas">
            <?php foreach (ETAPAS as $k => $et): ?>
              <li data-etapa="<?= $k ?>" class="<?= $k < $__et ? 'feito' : ($k === $__et ? 'atual' : '') ?>"><?= e(STATUS_PEDIDO[$et]) ?></li>
            <?php endforeach; ?>
          </ol>
          <p class="nota" id="acomp-aviso"><?= in_array($p['status'], ['entregue'], true) ? '' : 'Esta tela confere o andamento a cada 30 segundos.' ?></p>
        </div>
        <script src="js/acompanhar.js"></script>
      <?php endif; ?>
      <?php if ($p['entregador_nome'] && in_array($p['status'], ['pronto', 'a_caminho', 'entregue'], true)): ?>
        <p>Entregador: <strong><?= e($p['entregador_nome']) ?></strong></p>
      <?php endif; ?>
      <p><?= $p['tipo_entrega'] === 'agendada' ? 'Entrega agendada para <strong>' . e(data_br($p['data_agendada'])) . '</strong>' : 'Entrega imediata' ?></p>
    </div>

    <?php if ($p['status'] === 'entregue' && $u['perfil'] === 'cliente'): ?>
    <div class="bloco">
      <h2>Avaliação da entrega</h2>
      <?php if ($p['nota_cliente']): ?>
        <p>Sua nota: <strong><?= (int)$p['nota_cliente'] ?>/5</strong><?= $p['comentario_cliente'] ? ' &mdash; ' . e($p['comentario_cliente']) : '' ?></p>
      <?php else: ?>
        <form method="post" action="index.php?r=pedido/avaliarentrega">
          <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <label for="nota">Nota</label>
          <select name="nota" id="nota" required>
            <option value="">Escolha...</option>
            <?php for ($n = 5; $n >= 1; $n--): ?><option value="<?= $n ?>"><?= $n ?> <?= $n === 1 ? 'estrela' : 'estrelas' ?></option><?php endfor; ?>
          </select>
          <label for="comentario">Comentário (opcional)</label>
          <input type="text" name="comentario" id="comentario" maxlength="255">
          <button type="submit">Enviar avaliação</button>
        </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="bloco">
      <h2>Itens</h2>
      <?php foreach ($itens as $i): ?>
        <p><strong><?= (int)$i['quantidade'] ?>x <?= e($i['nome']) ?></strong> &mdash; <?= moeda($i['preco_unitario'] * $i['quantidade']) ?><br>
          <?php if ($d = desc_personalizacao(['categoria' => $i['categoria'] ?? ''] + $i)): ?><small class="pers"><?= e($d) ?></small><?php endif; ?></p>
      <?php endforeach; ?>
    </div>

    <div class="bloco">
      <h2>Entrega em</h2>
      <p><?= e($p['logradouro']) ?>, <?= e($p['numero']) ?><?= $p['complemento'] ? ' - ' . e($p['complemento']) : '' ?><br>
         <?= e($p['bairro']) ?>, <?= e($p['cidade']) ?>/<?= e($p['uf']) ?> &middot; CEP <?= e($p['cep']) ?></p>
      <?php if ($p['instrucoes']): ?><p><small>Instruções: <?= e($p['instrucoes']) ?></small></p><?php endif; ?>
    </div>
  </section>

  <aside class="resumo card-form">
    <h2>Pagamento</h2>
    <dl>
      <dt>Subtotal</dt><dd><?= moeda($p['subtotal']) ?></dd>
      <dt>Taxa de entrega</dt><dd><?= $p['taxa_entrega'] > 0 ? moeda($p['taxa_entrega']) : 'Grátis' ?></dd>
      <dt class="total">Total</dt><dd class="total"><?= moeda($p['total']) ?></dd>
    </dl>
    <p>Forma: <strong><?= $p['metodo'] === 'pix' ? 'Pix' : 'Cartão final ' . e($p['cartao_ultimos4']) ?></strong><br>
       Situação: <strong class="pg-<?= e($p['pagamento_status']) ?>"><?= e(STATUS_PAGAMENTO[$p['pagamento_status']] ?? '-') ?></strong></p>

    <?php if ($p['metodo'] === 'pix' && $p['pagamento_status'] === 'pendente' && $u['perfil'] === 'cliente'): ?>
      <div class="pix">
        <div id="qr" aria-label="QR code Pix de demonstração"></div>
        <label for="pixcode">Pix copia e cola (demonstração)</label>
        <textarea id="pixcode" rows="3" readonly><?= e($p['codigo_pix']) ?></textarea>
        <form method="post" action="index.php?r=pedido/pagar">
          <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button type="submit">Simular pagamento do Pix</button>
        </form>
        <p class="nota">Código fictício: não funciona em aplicativos de banco. O botão acima simula a confirmação.</p>
      </div>
      <script src="js/qrcode.js"></script>
      <script>
        (function () {
          try {
            var qr = qrcode(0, 'M'); qr.addData(document.getElementById('pixcode').value); qr.make();
            document.getElementById('qr').innerHTML = qr.createSvgTag({ cellSize: 4, margin: 2, scalable: true });
          } catch (e) { document.getElementById('qr').textContent = 'Não foi possível gerar o QR code.'; }
        })();
      </script>
    <?php endif; ?>
  </aside>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
