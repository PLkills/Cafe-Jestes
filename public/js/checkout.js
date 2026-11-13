// Mostra/oculta blocos do checkout (sem JS tudo fica visivel e o servidor valida do mesmo jeito)
(function () {
  var f = document.getElementById('form-checkout');
  if (!f) return;
  function val(n) { var c = f.querySelector('input[name="' + n + '"]:checked'); return c ? c.value : ''; }
  function toggle(id, on) { var el = document.getElementById(id); if (el) el.style.display = on ? '' : 'none'; }
  function atualizar() {
    toggle('novo-endereco', val('endereco_id') === 'novo');
    toggle('agendamento', val('tipo_entrega') === 'agendada');
    toggle('dados-cartao', val('metodo') === 'cartao');
  }
  f.addEventListener('change', atualizar);
  atualizar();

  var n = document.getElementById('numero_cartao');
  if (n) n.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 19).replace(/(\d{4})(?=\d)/g, '$1 ');
  });
  var v = document.getElementById('validade');
  if (v) v.addEventListener('input', function () {
    var s = this.value.replace(/\D/g, '').slice(0, 4);
    this.value = s.length > 2 ? s.slice(0, 2) + '/' + s.slice(2) : s;
  });
  var c = document.getElementById('cep');
  if (c) c.addEventListener('input', function () {
    var s = this.value.replace(/\D/g, '').slice(0, 8);
    this.value = s.length > 5 ? s.slice(0, 5) + '-' + s.slice(5) : s;
  });
})();
