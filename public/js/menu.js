/* Menu do topo no celular: botao abre/fecha os links. */
(function () {
  var b = document.getElementById('menu-btn'), n = document.getElementById('menu-nav');
  if (!b || !n) return;
  b.addEventListener('click', function () {
    var aberto = n.classList.toggle('aberto');
    b.setAttribute('aria-expanded', aberto ? 'true' : 'false');
  });
})();
