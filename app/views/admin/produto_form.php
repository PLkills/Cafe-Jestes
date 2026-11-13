<?php $titulo = $editando ? 'Editar café' : 'Novo café'; require __DIR__ . '/../layout/header.php'; ?>
<section class="card card-larga">
  <h1><?= $editando ? 'Editar café' : 'Novo café' ?></h1>
  <?php if ($erro): ?><p class="erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">

    <label for="nome">Nome</label>
    <input id="nome" name="nome" maxlength="120" required value="<?= e($dados['nome']) ?>">

    <label for="categoria_id">Categoria</label>
    <select id="categoria_id" name="categoria_id" required>
      <option value="">Selecione...</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= (int)$dados['categoria_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao" rows="3"><?= e($dados['descricao']) ?></textarea>

    <label for="origem_grao">Origem do grão</label>
    <input id="origem_grao" name="origem_grao" maxlength="80" value="<?= e($dados['origem_grao']) ?>">

    <div class="linha">
      <div><label for="intensidade">Intensidade (1-5)</label>
        <input id="intensidade" type="number" name="intensidade" min="1" max="5" value="<?= (int)$dados['intensidade'] ?>"></div>
      <div><label for="estoque">Estoque</label>
        <input id="estoque" type="number" name="estoque" min="0" value="<?= (int)$dados['estoque'] ?>"></div>
    </div>
    <div class="linha">
      <div><label for="preco">Preço (R$)</label>
        <input id="preco" name="preco" inputmode="decimal" required value="<?= e($dados['preco']) ?>"></div>
      <div><label for="preco_promocional">Preço promocional (opcional)</label>
        <input id="preco_promocional" name="preco_promocional" inputmode="decimal" value="<?= e($dados['preco_promocional']) ?>"></div>
    </div>

    <label for="imagem">Imagem (JPG, PNG ou WEBP, até 3 MB)</label>
    <?php if ($editando && $dados['imagem']): ?>
      <img class="mini" src="<?= e(imagem_url($dados['imagem'])) ?>" alt="Imagem atual">
    <?php endif; ?>
    <input id="imagem" type="file" name="imagem" accept="image/jpeg,image/png,image/webp">

    <button type="submit">Salvar</button>
  </form>
  <p><a href="index.php?r=adminproduto/index">Cancelar</a></p>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
