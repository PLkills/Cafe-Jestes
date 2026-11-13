<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Notificacao.php';

/** Painel do gerente: acompanha pedidos em aberto e atribui entregadores. */
class GestaoController
{
    private const PERFIS = ['gerente', 'administrador'];

    public function index(): void
    {
        $u = Auth::require(self::PERFIS);
        $pedidos = Pedido::emAberto();
        $entregadores = Usuario::entregadores();
        $recentes = Pedido::entreguesRecentes();
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        $titulo = 'Gestão de entregas';
        $atualizar = true;
        require __DIR__ . '/../views/gestao/painel.php';
    }

    public function atribuir(): void
    {
        Auth::require(self::PERFIS);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $pid = (int)($_POST['id'] ?? 0);
        $eid = (int)($_POST['entregador_id'] ?? 0);
        $erro = Pedido::atribuir($pid, $eid);
        $_SESSION['flash'] = $erro !== '' ? $erro : "Pedido #$pid atribuído.";
        header('Location: index.php?r=gestao/index');
        exit;
    }
}
