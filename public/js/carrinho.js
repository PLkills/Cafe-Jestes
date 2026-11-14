/* Carrinho: botoes - e + mudam a quantidade e atualizam o total na hora. */
(function () {
  document.querySelectorAll('form.qtd').forEach(function (f) {
    var campo = f.querySelector('input[name=quantidade]');
    f.querySelectorAll('.qtd-btn').forEach(function (b) {
      b.addEventListener('click', function () {
        var min = parseInt(campo.min || '0', 10), max = parseInt(campo.max || '99', 10);
        var novo = Math.max(min, Math.min(max, (parseInt(campo.value, 10) || 0) + parseInt(b.getAttribute('data-delta'), 10)));
        if (novo === parseInt(campo.value, 10)) return;
        campo.value = novo;
        f.submit();
      });
    });
    campo.addEventListener('change', function () { f.submit(); });
  });
})();
