<?php $titulo = $p['nome']; require __DIR__ . '/../layout/header.php'; ?>
<p><a href="index.php?r=produto/index">&larr; Voltar à vitrine</a></p>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>
<section class="detalhe">
  <img src="<?= e(imagem_url($p['imagem'])) ?>" alt="<?= e($p['nome']) ?>">
  <div>
    <h1><?= e($p['nome']) ?></h1>
    <p class="cat"><?= e($p['categoria']) ?></p>
    <p><?= nl2br(e($p['descricao'])) ?></p>
    <ul class="ficha">
      <li><strong>Origem do grão:</strong> <?= e($p['origem_grao'] ?: 'Não informada') ?></li>
      <li><strong>Força do grão:</strong> <?= (int)$p['intensidade'] ?>/5 <?= graos_intensidade((int)$p['intensidade']) ?></li>
      <li><strong>Disponível:</strong> <?= $p['estoque'] > 0 ? 'Em estoque' : 'Esgotado' ?></li>
    </ul>
    <p class="preco grande">
      <?php if (em_promocao($p)): ?><span class="selo">Promoção</span> <s><?= moeda($p['preco']) ?></s> <strong><?= moeda(preco_final($p)) ?></strong>
      <?php else: ?><strong><?= moeda($p['preco']) ?></strong><?php endif; ?>
    </p>
    <?php if ($p['estoque'] < 1): ?>
      <p class="erro">Produto esgotado no momento.</p>
    <?php elseif ($u && $u['perfil'] === 'cliente'): ?>
      <form method="post" action="index.php?r=carrinho/adicionar" class="card-form pedido-form">
        <h3>Monte o seu pedido</h3>
        <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
        <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
        <?php if (personalizavel($p)): $lp = leite_padrao($p); ?>
          <label for="tipo_leite">Tipo de leite</label>
          <select id="tipo_leite" name="tipo_leite">
            <?php foreach (OPCOES_LEITE as $v => $t): ?><option value="<?= e($v) ?>" <?= $v === $lp ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
          </select>
          <label for="acucar">Açúcar</label>
          <select id="acucar" name="acucar">
            <?php foreach (OPCOES_ACUCAR as $v => $t): ?><option value="<?= e($v) ?>" <?= $v === 'normal' ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
          </select>
          <label for="intensidade_p">Intensidade do preparo</label>
          <select id="intensidade_p" name="intensidade">
            <?php foreach (OPCOES_INTENSIDADE as $v => $t): ?><option value="<?= e($v) ?>" <?= $v === 'media' ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
          </select>
        <?php endif; ?>
        <label for="quantidade">Quantidade</label>
        <input id="quantidade" type="number" name="quantidade" min="1" max="<?= (int)min($p['estoque'], config_loja()['max_por_item']) ?>" value="1" required>
        <button type="submit">Adicionar ao carrinho</button>
      </form>
    <?php elseif (!$u): ?>
      <p><a class="btn" href="index.php?r=auth/login">Entre para pedir</a></p>
    <?php else: ?>
      <p class="nota">Apenas contas de cliente fazem pedidos.</p>
    <?php endif; ?>
  </div>
</section>

<section class="avaliacoes">
  <h2>Avaliações
    <?php if ($p['total_avaliacoes'] > 0): ?>
      <small><span class="estrelas"><?= estrelas((float)$p['media']) ?></span> <?= e($p['media']) ?> (<?= (int)$p['total_avaliacoes'] ?>)</small>
    <?php endif; ?>
  </h2>

  <?php if (!$avaliacoes): ?><p class="vazio">Ainda não há avaliações. Seja o primeiro!</p><?php endif; ?>
  <?php foreach ($avaliacoes as $a): ?>
    <div class="comentario">
      <strong><?= e($a['nome']) ?></strong> <span class="estrelas"><?= estrelas((float)$a['nota']) ?></span>
      <small><?= e(date('d/m/Y', strtotime($a['criado_em']))) ?></small>
      <?php if ($a['comentario']): ?><p><?= e($a['comentario']) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>

  <?php if ($u && $u['perfil'] === 'cliente'): ?>
    <form method="post" action="index.php?r=produto/avaliar" class="card-form">
      <h3><?= $minha ? 'Atualizar minha avaliação' : 'Avaliar este café' ?></h3>
      <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
      <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
      <fieldset class="nota-estrelas">
        <legend>Nota</legend>
<div class="estr">
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <input type="radio" id="nota<?= $i ?>" name="nota" value="<?= $i ?>" <?= (int)($minha['nota'] ?? 5) === $i ? 'checked' : '' ?> required>
          <label for="nota<?= $i ?>" title="<?= $i ?> <?= $i == 1 ? 'estrela' : 'estrelas' ?>"><span class="sr"><?= $i ?> <?= $i == 1 ? 'estrela' : 'estrelas' ?></span>&#9733;</label>
        <?php endfor; ?>
</div>
      </fieldset>
      <label for="comentario">Comentário (opcional)</label>
      <textarea id="comentario" name="comentario" rows="3" maxlength="500"><?= e($minha['comentario'] ?? '') ?></textarea>
      <button type="submit">Enviar avaliação</button>
    </form>
  <?php elseif (!$u): ?>
    <p><a href="index.php?r=auth/login">Entre</a> como cliente para avaliar.</p>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
