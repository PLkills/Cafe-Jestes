<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Notificacao.php';

/** Painel do entregador: so enxerga e altera as proprias entregas. */
class EntregadorController
{
    private const PROXIMO = ['pronto' => 'a_caminho', 'a_caminho' => 'entregue'];

    public function index(): void
    {
        $u = Auth::require(['entregador']);
        $entregas = Pedido::doEntregador((int)$u['id']);
        $feitas = Pedido::entreguesDo((int)$u['id']);
        $avisos = Notificacao::recentes((int)$u['id']);
        Notificacao::marcarLidas((int)$u['id']);
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        $titulo = 'Minhas entregas';
        $atualizar = true;
        require __DIR__ . '/../views/entregador/entregas.php';
    }

    public function avancar(): void
    {
        $u = Auth::require(['entregador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $id = (int)($_POST['id'] ?? 0);
        $de = (string)($_POST['de'] ?? '');
        if (!isset(self::PROXIMO[$de])) { http_response_code(400); exit('Transição inválida.'); }
        $ok = Pedido::mudarStatus($id, $de, self::PROXIMO[$de], (int)$u['id']);
        $_SESSION['flash'] = $ok ? "Pedido #$id atualizado." : "Pedido #$id não pôde ser atualizado.";
        header('Location: index.php?r=entregador/index');
        exit;
    }
}
