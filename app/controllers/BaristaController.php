<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Notificacao.php';

/** Fila de preparo: so pedidos com pagamento aprovado. */
class BaristaController
{
    private const PERFIS = ['barista', 'gerente', 'administrador'];
    private const PROXIMO = ['aguardando_preparo' => 'em_preparo', 'em_preparo' => 'pronto'];

    public function index(): void
    {
        $u = Auth::require(self::PERFIS);
        $fila = Pedido::fila();
        $itens = Pedido::itensDosPedidos(array_column($fila, 'id'));
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        $titulo = 'Fila de preparo';
        $atualizar = true;
        require __DIR__ . '/../views/barista/fila.php';
    }

    public function avancar(): void
    {
        Auth::require(self::PERFIS);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $id = (int)($_POST['id'] ?? 0);
        $de = (string)($_POST['de'] ?? '');
        if (!isset(self::PROXIMO[$de])) { http_response_code(400); exit('Transição inválida.'); }
        $ok = Pedido::mudarStatus($id, $de, self::PROXIMO[$de]);
        $_SESSION['flash'] = $ok ? "Pedido #$id atualizado." : "Pedido #$id não pôde ser atualizado (já alterado ou sem pagamento aprovado).";
        header('Location: index.php?r=barista/index');
        exit;
    }
}
