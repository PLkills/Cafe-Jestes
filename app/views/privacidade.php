<?php $titulo = 'Privacidade'; $descricao = 'Como o Café Jester trata os dados dos clientes.'; require __DIR__ . '/layout/header.php'; ?>
<section class="card card-larga">
  <h1>Privacidade</h1>
  <p>O Café Jester é um projeto acadêmico (Projeto Integrador II). Veja o que o sistema guarda e por quê:</p>
  <ul>
    <li><strong>Conta:</strong> nome, e-mail e telefone, para identificar o cliente e permitir o contato na entrega. A senha é guardada de forma criptografada (hash).</li>
    <li><strong>Endereço:</strong> usado só para entregar o pedido. O entregador vê o endereço e o telefone apenas das entregas que estão com ele.</li>
    <li><strong>Pagamento:</strong> é uma simulação, sem cobrança real. Do cartão guardamos só os 4 últimos dígitos; número completo, validade e CVV nunca são gravados.</li>
    <li><strong>Pedidos e avaliações:</strong> ficam registrados para o acompanhamento, os relatórios da loja e o histórico do cliente.</li>
  </ul>
  <p class="nota">Os dados não são vendidos nem compartilhados com terceiros.</p>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
