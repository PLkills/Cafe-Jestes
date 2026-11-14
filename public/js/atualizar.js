/* Telas de operacao: a cada 30 s busca a pagina de novo e troca so o conteudo,
   sem recarregar. Nao atualiza enquanto a pessoa mexe em algum campo. */
(function () {
  var main = document.querySelector('main.container');
  if (!main) return;

  function ocupado() {
    var a = document.activeElement;
    if (a && /^(INPUT|SELECT|TEXTAREA)$/.test(a.tagName)) return true;
    var sels = main.querySelectorAll('select');
    for (var i = 0; i < sels.length; i++) {
      for (var j = 0; j < sels[i].options.length; j++) {
        if (sels[i].options[j].selected !== sels[i].options[j].defaultSelected) return true;
      }
    }
    var cps = main.querySelectorAll('input:not([type=hidden]),textarea');
    for (var k = 0; k < cps.length; k++) if (cps[k].value !== cps[k].defaultValue) return true;
    return false;
  }

  setInterval(function () {
    if (document.hidden || ocupado()) return;
    fetch(location.href, { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) {
        if (!r.ok || (r.redirected && r.url.indexOf('auth/login') > -1)) { location.reload(); return null; }
        return r.text();
      })
      .then(function (html) {
        if (!html) return;
        var novo = new DOMParser().parseFromString(html, 'text/html').querySelector('main.container');
        if (!novo) { location.reload(); return; }
        if (ocupado() || novo.innerHTML === main.innerHTML) return;
        var y = window.scrollY;
        main.innerHTML = novo.innerHTML;
        window.scrollTo(0, y);
      })
      .catch(function () { /* sem rede: tenta de novo no proximo ciclo */ });
  }, 30000);
})();
