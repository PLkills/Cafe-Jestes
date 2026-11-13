/* Acompanhamento do pedido: consulta o status a cada 30 s e atualiza a tela. */
(function () {
  var box = document.getElementById('acompanhar');
  if (!box) return;
  var url = box.getAttribute('data-url');
  var atual = box.getAttribute('data-status');
  var etapas = box.querySelectorAll('li[data-etapa]');
  var rot = document.getElementById('status-rotulo');
  var aviso = document.getElementById('acomp-aviso');

  function pintar(n) {
    for (var i = 0; i < etapas.length; i++) {
      etapas[i].className = i < n ? 'feito' : (i === n ? 'atual' : '');
    }
  }
  function consultar() {
    fetch(url, { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (d) {
        if (d.status !== atual) { window.location.reload(); return; }  // muda: recarrega p/ mostrar tudo
        if (rot) rot.textContent = d.rotulo;
        pintar(d.etapa);
        if (aviso) aviso.textContent = 'Atualizado às ' + new Date().toLocaleTimeString('pt-BR');
      })
      .catch(function () { if (aviso) aviso.textContent = 'Sem conexão. Tentando novamente em 30 s...'; });
  }
  if (atual !== 'entregue' && atual !== 'cancelado') setInterval(consultar, 30000);
})();
