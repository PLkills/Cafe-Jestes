<?php $titulo = 'Vitrine'; require __DIR__ . '/../layout/header.php'; ?>
<h1>Nossos cafés</h1>

<form class="filtros" method="get" action="index.php">
  <input type="hidden" name="r" value="produto/index">
  <label for="q" class="sr">Buscar</label>
  <input id="q" name="q" placeholder="Buscar café..." value="<?= e($busca) ?>">
  <label for="cat" class="sr">Categoria</label>
  <select id="cat" name="cat">
    <option value="">Todas as categorias</option>
    <?php foreach ($categorias as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Filtrar</button>
  <?php if ($cat || $busca !== ''): ?><a class="limpar" href="index.php?r=produto/index">Limpar filtro</a><?php endif; ?>
</form>

<?php if (!$produtos): ?>
  <p class="vazio">Nenhum café encontrado para este filtro.</p>
<?php else: ?>
<div class="grade">
  <?php foreach ($produtos as $p): ?>
  <article class="produto">
    <a href="index.php?r=produto/ver&amp;id=<?= (int)$p['id'] ?>">
      <?php if (em_promocao($p)): ?><span class="selo">Promoção</span><?php endif; ?>
      <img src="<?= e(imagem_url($p['imagem'])) ?>" alt="<?= e($p['nome']) ?>" loading="lazy">
      <h2><?= e($p['nome']) ?></h2>
    </a>
    <p class="cat"><?= e($p['categoria']) ?></p>
    <p class="desc"><?= e(mb_strimwidth((string)$p['descricao'], 0, 80, '...')) ?></p>
    <p class="aval" title="Média de avaliações">
      <?php if ($p['total_avaliacoes'] > 0): ?>
        <span class="estrelas"><?= estrelas((float)$p['media']) ?></span> <?= e($p['media']) ?> (<?= (int)$p['total_avaliacoes'] ?>)
      <?php else: ?>Sem avaliações<?php endif; ?>
    </p>
    <p class="preco">
      <?php if (em_promocao($p)): ?><s><?= moeda($p['preco']) ?></s> <strong><?= moeda(preco_final($p)) ?></strong>
      <?php else: ?><strong><?= moeda($p['preco']) ?></strong><?php endif; ?>
    </p>
  </article>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
