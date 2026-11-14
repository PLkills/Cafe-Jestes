<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= e($descricao ?? 'Café Jester: cafeteria gourmet com delivery online. Escolha, personalize e acompanhe seu pedido.') ?>">
<title><?= e(($titulo ?? 'Início') . ' | Café Jester') ?></title>
<link rel="stylesheet" href="css/style.css">
<script>document.documentElement.className += ' js';</script>
<script src="js/menu.js" defer></script>
<?php if (!empty($atualizar)): ?><script src="js/atualizar.js" defer></script><?php endif; ?>
</head>
<body>
<header class="topo">
  <a class="logo" href="index.php">&#9749; Café Jester</a>
  <?php $__u = Auth::user(); ?>
  <?php if ($__u && $__u['perfil'] === 'cliente'): require_once __DIR__ . '/../../models/Carrinho.php'; $__n0 = Carrinho::quantidadeTotal(); ?>
    <a class="cart-m" href="index.php?r=carrinho/index">&#128722; Carrinho<?= $__n0 ? ' <span class="badge">' . (int)$__n0 . '</span>' : '' ?></a>
  <?php endif; ?>
  <button type="button" id="menu-btn" class="menu-btn" aria-expanded="false" aria-controls="menu-nav">&#9776; Menu</button>
  <nav id="menu-nav">
    <a href="index.php?r=produto/index">Vitrine</a>
    <?php if ($__u): ?>
      <?php if (in_array($__u['perfil'], ['gerente', 'administrador'], true)): ?>
        <a href="index.php?r=adminproduto/index">Produtos</a>
        <a href="index.php?r=adminusuario/index">Usuários</a>
        <a href="index.php?r=relatorio/index">Relatórios</a>
      <?php endif; ?>
      <?php if (in_array($__u['perfil'], ['barista', 'gerente', 'administrador'], true)): ?><a href="index.php?r=barista/index">Fila de preparo</a><?php endif; ?>
      <?php if (in_array($__u['perfil'], ['gerente', 'administrador'], true)): ?><a href="index.php?r=gestao/index">Entregas</a><?php endif; ?>
      <?php if ($__u['perfil'] === 'entregador'): ?><a href="index.php?r=entregador/index">Minhas entregas</a><?php endif; ?>
      <?php if ($__u['perfil'] === 'cliente'): require_once __DIR__ . '/../../models/Carrinho.php'; $__n = Carrinho::quantidadeTotal(); ?>
        <a class="cart-d" href="index.php?r=carrinho/index">&#128722; Carrinho<?= $__n ? ' <span class="badge">' . (int)$__n . '</span>' : '' ?></a>
      <?php endif; ?>
      <?php if ($__u['perfil'] === 'cliente'): ?><a href="index.php?r=pedido/meus">Meus pedidos</a><?php endif; ?>
      <a href="index.php?r=home/painel"><?= e($__u['nome']) ?> (<?= e($__u['perfil']) ?>)</a>
      <a href="index.php?r=auth/logout">Sair</a>
    <?php else: ?>
      <a href="index.php?r=auth/login">Entrar</a>
      <a href="index.php?r=auth/cadastro">Criar conta</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
