<?php require __DIR__ . '/../layout/header.php'; ?>
<h1>Backup dos dados</h1>
<section class="card">
  <p>Gera um arquivo <strong>.sql</strong> com todos os registros (clientes, pedidos, produtos etc.).</p>
  <p class="nota">O arquivo contém dados pessoais e hashes de senha: guarde em local seguro e não envie ao GitHub.
     Para restaurar, importe antes <code>sql/schema.sql</code> e depois o backup, pelo phpMyAdmin.</p>
  <form method="post" action="index.php?r=backup/baixar">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
    <button type="submit">Baixar backup agora</button>
  </form>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
