<?php $titulo = 'Gerenciar produtos'; require __DIR__ . '/../layout/header.php'; ?>
<div class="barra-topo">
  <h1>Produtos</h1>
  <a class="btn" href="index.php?r=adminproduto/novo">+ Novo café</a>
</div>
<?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>

<div class="tabela-wrap">
<table class="tabela">
  <thead><tr><th>Imagem</th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Promo</th><th>Estoque</th><th>Status</th><th>Ações</th></tr></thead>
  <tbody>
  <?php foreach ($produtos as $p): ?>
    <tr class="<?= $p['ativo'] ? '' : 'inativo' ?>">
      <td><img class="mini" src="<?= e(imagem_url($p['imagem'])) ?>" alt=""></td>
      <td><?= e($p['nome']) ?></td>
      <td><?= e($p['categoria']) ?></td>
      <td><?= moeda($p['preco']) ?></td>
      <td><?= em_promocao($p) ? moeda($p['preco_promocional']) : '-' ?></td>
      <td><?= (int)$p['estoque'] ?><?= $p['estoque'] == 0 ? ' <span class="alerta">esgotado</span>' : '' ?></td>
      <td><?= $p['ativo'] ? 'Na vitrine' : 'Removido' ?></td>
      <td class="acoes">
        <a href="index.php?r=adminproduto/editar&amp;id=<?= (int)$p['id'] ?>">Editar</a>
        <form method="post" action="index.php?r=adminproduto/alternar" onsubmit="return confirm('Confirmar?');">
          <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button type="submit" class="link"><?= $p['ativo'] ? 'Remover' : 'Reativar' ?></button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>
