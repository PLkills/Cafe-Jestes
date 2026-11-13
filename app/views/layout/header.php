<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($titulo ?? 'Início') . ' | Café Jester') ?></title>
<link rel="stylesheet" href="css/style.css">
<?php if (!empty($atualizar)): ?><meta http-equiv="refresh" content="30"><?php endif; ?>
</head>
<body>
<header class="topo">
  <a class="logo" href="index.php">&#9749; Café Jester</a>
  <nav>
    <a href="index.php?r=produto/index">Vitrine</a>
    <?php $__u = Auth::user(); ?>
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
        <a href="index.php?r=carrinho/index">&#128722; Carrinho<?= $__n ? ' <span class="badge">' . (int)$__n . '</span>' : '' ?></a>
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
