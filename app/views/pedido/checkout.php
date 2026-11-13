<?php $titulo = 'Finalizar pedido'; require __DIR__ . '/../layout/header.php'; ?>
<h1>Finalizar pedido</h1>
<?php if ($erro): ?><p class="erro" role="alert"><?= e($erro) ?></p><?php endif; ?>

<form method="post" class="carrinho-layout" id="form-checkout" novalidate>
  <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
  <input type="hidden" name="total_exibido" value="<?= e(number_format($r['total'], 2, '.', '')) ?>">

  <div>
    <fieldset class="bloco">
      <legend>1. Endereço de entrega</legend>
      <?php foreach ($enderecos as $en): ?>
        <label class="opcao">
          <input type="radio" name="endereco_id" value="<?= (int)$en['id'] ?>" <?= $d['endereco_id'] === (string)$en['id'] ? 'checked' : '' ?>>
          <span><?= e($en['logradouro']) ?>, <?= e($en['numero']) ?><?= $en['complemento'] ? ' - ' . e($en['complemento']) : '' ?> &middot; <?= e($en['bairro']) ?>, <?= e($en['cidade']) ?>/<?= e($en['uf']) ?> &middot; CEP <?= e($en['cep']) ?>
          <?php if ($en['instrucoes']): ?><br><small>Instruções: <?= e($en['instrucoes']) ?></small><?php endif; ?></span>
        </label>
      <?php endforeach; ?>
      <label class="opcao">
        <input type="radio" name="endereco_id" value="novo" <?= $d['endereco_id'] === 'novo' ? 'checked' : '' ?>>
        <span>Usar um novo endereço (fica salvo para os próximos pedidos)</span>
      </label>
      <div id="novo-endereco" class="sub-form">
        <div class="linha"><div><label for="cep">CEP</label><input id="cep" name="cep" inputmode="numeric" maxlength="9" placeholder="00000-000" value="<?= e($d['cep']) ?>"></div>
          <div><label for="uf">UF</label><input id="uf" name="uf" maxlength="2" value="<?= e($d['uf']) ?>"></div></div>
        <label for="logradouro">Rua / Avenida</label><input id="logradouro" name="logradouro" value="<?= e($d['logradouro']) ?>">
        <div class="linha"><div><label for="numero">Número</label><input id="numero" name="numero" maxlength="10" value="<?= e($d['numero']) ?>"></div>
          <div><label for="complemento">Complemento</label><input id="complemento" name="complemento" maxlength="80" value="<?= e($d['complemento']) ?>"></div></div>
        <div class="linha"><div><label for="bairro">Bairro</label><input id="bairro" name="bairro" value="<?= e($d['bairro']) ?>"></div>
          <div><label for="cidade">Cidade</label><input id="cidade" name="cidade" value="<?= e($d['cidade']) ?>"></div></div>
      </div>
      <label for="instrucoes">Instruções para o entregador (opcional)</label>
      <input id="instrucoes" name="instrucoes" maxlength="255" placeholder="Ex.: deixar na portaria" value="<?= e($d['instrucoes']) ?>">
    </fieldset>

    <fieldset class="bloco">
      <legend>2. Quando entregar?</legend>
      <label class="opcao"><input type="radio" name="tipo_entrega" value="imediata" <?= $d['tipo_entrega'] !== 'agendada' ? 'checked' : '' ?>><span>Entrega imediata</span></label>
      <label class="opcao"><input type="radio" name="tipo_entrega" value="agendada" <?= $d['tipo_entrega'] === 'agendada' ? 'checked' : '' ?>><span>Agendar entrega</span></label>
      <div id="agendamento" class="sub-form">
        <label for="data_agendada">Data e hora (entre 30 min e 7 dias)</label>
        <input id="data_agendada" type="datetime-local" name="data_agendada" min="<?= e($min) ?>" max="<?= e($max) ?>" value="<?= e($d['data_agendada']) ?>">
      </div>
    </fieldset>

    <fieldset class="bloco">
      <legend>3. Pagamento</legend>
      <p class="nota">Ambiente de demonstração: nenhum pagamento real é feito.</p>
      <label class="opcao"><input type="radio" name="metodo" value="pix" <?= $d['metodo'] !== 'cartao' ? 'checked' : '' ?>><span>Pix (QR code)</span></label>
      <label class="opcao"><input type="radio" name="metodo" value="cartao" <?= $d['metodo'] === 'cartao' ? 'checked' : '' ?>><span>Cartão de crédito</span></label>
      <div id="dados-cartao" class="sub-form">
        <label for="numero_cartao">Número do cartão</label>
        <input id="numero_cartao" name="numero_cartao" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="0000 0000 0000 0000">
        <label for="nome_cartao">Nome impresso</label>
        <input id="nome_cartao" name="nome_cartao" autocomplete="cc-name" value="<?= e($d['nome_cartao']) ?>">
        <div class="linha"><div><label for="validade">Validade (MM/AA)</label><input id="validade" name="validade" autocomplete="cc-exp" maxlength="5" placeholder="MM/AA"></div>
          <div><label for="cvv">CVV</label><input id="cvv" name="cvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4" type="password"></div></div>
        <p class="nota">Guardamos apenas os 4 últimos dígitos. Número completo, validade e CVV nunca são armazenados.</p>
      </div>
    </fieldset>
  </div>

  <aside class="resumo card-form">
    <h2>Seu pedido</h2>
    <ul class="mini-lista">
      <?php foreach ($r['itens'] as $i): ?>
        <li><?= (int)$i['quantidade'] ?>x <?= e($i['produto']['nome']) ?>
          <?php if ($i['personalizado']): ?><br><small><?= e(OPCOES_LEITE[$i['tipo_leite']]) ?>, <?= e(OPCOES_ACUCAR[$i['acucar']]) ?>, <?= e(strtolower(OPCOES_INTENSIDADE[$i['intensidade']])) ?></small><?php endif; ?>
          <span><?= moeda($i['total']) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <dl>
      <dt>Subtotal</dt><dd><?= moeda($r['subtotal']) ?></dd>
      <dt>Taxa de entrega</dt><dd><?= $r['taxa'] > 0 ? moeda($r['taxa']) : '<span class="gratis">Grátis</span>' ?></dd>
      <dt class="total">Total</dt><dd class="total"><?= moeda($r['total']) ?></dd>
    </dl>
    <button type="submit">Confirmar pedido</button>
    <p><a href="index.php?r=carrinho/index">&larr; Voltar ao carrinho</a></p>
  </aside>
</form>
<script src="js/checkout.js"></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
