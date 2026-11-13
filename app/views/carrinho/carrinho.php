<?php $titulo = 'Meu carrinho'; require __DIR__ . '/../layout/header.php'; ?>
<h1>Meu carrinho</h1>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>
<?php if ($r['removidos']): ?>
  <p class="erro" role="alert">Removemos por indisponibilidade: <?= e(implode(', ', $r['removidos'])) ?>.</p>
<?php endif; ?>

<?php if (!$r['itens']): ?>
  <p class="vazio">Seu carrinho está vazio. <a href="index.php?r=produto/index">Escolha um café</a>.</p>
<?php else: ?>
<div class="carrinho-layout">
  <section class="itens">
    <?php foreach ($r['itens'] as $i): $p = $i['produto']; ?>
    <article class="linha-item">
      <img class="mini-g" src="<?= e(imagem_url($p['imagem'])) ?>" alt="<?= e($p['nome']) ?>">
      <div class="info">
        <h2><a href="index.php?r=produto/ver&amp;id=<?= (int)$p['id'] ?>"><?= e($p['nome']) ?></a></h2>
        <?php if ($i['personalizado']): ?>
          <p class="pers"><?= e(OPCOES_LEITE[$i['tipo_leite']]) ?> &middot; <?= e(OPCOES_ACUCAR[$i['acucar']]) ?> &middot; Intensidade <?= e(strtolower(OPCOES_INTENSIDADE[$i['intensidade']])) ?></p>
        <?php endif; ?>
        <p class="nota"><?= moeda($i['unitario']) ?> cada</p>
        <div class="acoes-item">
          <form method="post" action="index.php?r=carrinho/atualizar" class="qtd">
            <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="chave" value="<?= e($i['chave']) ?>">
            <label class="sr" for="q<?= e($i['chave']) ?>">Quantidade</label>
            <input id="q<?= e($i['chave']) ?>" type="number" name="quantidade" min="0" max="<?= (int)config_loja()['max_por_item'] ?>" value="<?= (int)$i['quantidade'] ?>">
            <button type="submit" class="sec">Atualizar</button>
          </form>
          <form method="post" action="index.php?r=carrinho/remover">
            <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="chave" value="<?= e($i['chave']) ?>">
            <button type="submit" class="link">Remover</button>
          </form>
        </div>
      </div>
      <strong class="sub"><?= moeda($i['total']) ?></strong>
    </article>
    <?php endforeach; ?>
    <form method="post" action="index.php?r=carrinho/limpar" onsubmit="return confirm('Esvaziar o carrinho?');">
      <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
      <button type="submit" class="link">Esvaziar carrinho</button>
    </form>
  </section>

  <aside class="resumo card-form">
    <h2>Resumo do pedido</h2>
    <dl>
      <dt>Subtotal</dt><dd><?= moeda($r['subtotal']) ?></dd>
      <dt>Taxa de entrega</dt><dd><?= $r['taxa'] > 0 ? moeda($r['taxa']) : '<span class="gratis">Grátis</span>' ?></dd>
      <dt class="total">Total</dt><dd class="total"><?= moeda($r['total']) ?></dd>
    </dl>
    <?php if ($r['falta_frete_gratis'] > 0): ?>
      <p class="nota">Faltam <?= moeda($r['falta_frete_gratis']) ?> para entrega grátis.</p>
    <?php endif; ?>
    <a class="btn btn-bloco" href="index.php?r=pedido/checkout">Finalizar pedido</a>
    <p><a href="index.php?r=produto/index">&larr; Continuar comprando</a></p>
  </aside>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
