<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Carrinho.php';
require_once __DIR__ . '/../models/Endereco.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Notificacao.php';

/** Finalizacao de pedido, pagamento (simulado) e "Meus pedidos". */
class PedidoController
{
    private function flash(string $m): void { $_SESSION['flash'] = $m; }

    /** GET: tela de checkout. POST: valida e cria o pedido. */
    public function checkout(): void
    {
        $u = Auth::require(['cliente']);
        $r = Carrinho::resumo();
        if (!$r['itens']) {
            $this->flash('Seu carrinho está vazio.');
            header('Location: index.php?r=carrinho/index');
            exit;
        }
        $enderecos = Endereco::doUsuario((int)$u['id']);
        $erro = '';
        $d = ['endereco_id' => $enderecos ? (string)$enderecos[0]['id'] : 'novo', 'cep' => '', 'logradouro' => '',
              'numero' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => '', 'instrucoes' => '',
              'tipo_entrega' => 'imediata', 'data_agendada' => '', 'metodo' => 'pix', 'nome_cartao' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            foreach ($d as $k => $_) {
                if (isset($_POST[$k]) && is_string($_POST[$k])) $d[$k] = trim($_POST[$k]);
            }
            $erro = $this->processar($u, $r, $d, (string)($_POST['total_exibido'] ?? ''));
        }
        $min = date('Y-m-d\TH:i', strtotime('+30 minutes'));
        $max = date('Y-m-d\TH:i', strtotime('+7 days'));
        require __DIR__ . '/../views/pedido/checkout.php';
    }

    /** Retorna mensagem de erro; em caso de sucesso redireciona e encerra. */
    private function processar(array $u, array $r, array $d, string $totalExibido): string
    {
        // CA11: o valor exibido deve ser igual ao cobrado
        if ($totalExibido !== number_format($r['total'], 2, '.', '')) {
            return 'Os valores foram atualizados. Confira o novo total antes de confirmar.';
        }
        // --- entrega
        $tipo = $d['tipo_entrega'] === 'agendada' ? 'agendada' : 'imediata';
        $dataAg = null;
        if ($tipo === 'agendada') {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $d['data_agendada']);
            if (!$dt || $dt->format('Y-m-d\TH:i') !== $d['data_agendada']) return 'Informe uma data e hora válidas para a entrega agendada.';
            if ($dt->getTimestamp() < strtotime('+29 minutes') || $dt->getTimestamp() > strtotime('+7 days 1 minute')) {
                return 'A entrega agendada deve ser entre 30 minutos e 7 dias a partir de agora.';
            }
            $dataAg = $dt->format('Y-m-d H:i:s');
        }
        // --- pagamento
        $metodo = $d['metodo'] === 'cartao' ? 'cartao' : 'pix';
        $ultimos4 = null;
        if ($metodo === 'cartao') {
            $num = preg_replace('/\D/', '', (string)($_POST['numero_cartao'] ?? ''));
            $val = trim((string)($_POST['validade'] ?? ''));
            $cvv = preg_replace('/\D/', '', (string)($_POST['cvv'] ?? ''));
            if (!luhn_ok($num)) return 'Número de cartão inválido.';
            if (!preg_match('#^(0[1-9]|1[0-2])/(\d{2})$#', $val, $m) ||
                (int)('20' . $m[2]) * 100 + (int)$m[1] < (int)date('Y') * 100 + (int)date('n')) return 'Validade do cartão inválida ou vencida (use MM/AA).';
            if (!preg_match('/^\d{3,4}$/', $cvv)) return 'CVV inválido.';
            if (mb_strlen($d['nome_cartao']) < 3) return 'Informe o nome impresso no cartão.';
            $ultimos4 = substr($num, -4);   // NUNCA armazenar numero completo, validade ou CVV
        }
        // --- endereco
        $uid = (int)$u['id'];
        if ($d['endereco_id'] !== 'novo' && ctype_digit($d['endereco_id'])) {
            $end = Endereco::porId((int)$d['endereco_id'], $uid);
            if (!$end) return 'Endereço inválido.';
            $enderecoId = (int)$end['id'];
            if ($d['instrucoes'] !== '' && $d['instrucoes'] !== (string)$end['instrucoes']) {
                Endereco::atualizarInstrucoes($enderecoId, mb_substr($d['instrucoes'], 0, 255));
            }
        } else {
            $erroEnd = $this->validarEndereco($d);
            if ($erroEnd) return $erroEnd;
            $enderecoId = Endereco::criar($uid, [
                'cep' => preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', preg_replace('/\D/', '', $d['cep'])),
                'logradouro' => $d['logradouro'], 'numero' => $d['numero'], 'complemento' => $d['complemento'],
                'bairro' => $d['bairro'], 'cidade' => $d['cidade'], 'uf' => strtoupper($d['uf']),
                'instrucoes' => mb_substr($d['instrucoes'], 0, 255)]);
        }
        // --- grava
        try {
            $id = Pedido::criar($uid, $enderecoId, $tipo, $dataAg, $r, $metodo, $ultimos4);
        } catch (RuntimeException $e) {
            return $e->getMessage();
        } catch (Throwable $e) {
            error_log('Falha ao criar pedido: ' . $e->getMessage());
            return 'Não foi possível concluir o pedido agora. Tente novamente.';
        }
        Carrinho::limpar();
        $this->flash($metodo === 'pix' ? 'Pedido criado! Pague com o Pix abaixo para liberar o preparo.' : 'Pedido confirmado! Pagamento aprovado.');
        header('Location: index.php?r=pedido/ver&id=' . $id);
        exit;
    }

    private function validarEndereco(array $d): string
    {
        if (!preg_match('/^\d{5}-?\d{3}$/', $d['cep'])) return 'CEP inválido (use 00000-000).';
        foreach (['logradouro' => 'rua', 'numero' => 'número', 'bairro' => 'bairro', 'cidade' => 'cidade'] as $k => $n) {
            if ($d[$k] === '' || mb_strlen($d[$k]) > 150) return "Informe o $n do endereço.";
        }
        if (!preg_match('/^[A-Za-z]{2}$/', $d['uf'])) return 'Informe a UF com 2 letras.';
        return '';
    }

    public function meus(): void
    {
        $u = Auth::require(['cliente']);
        $pedidos = Pedido::doCliente((int)$u['id']);
        $notificacoes = Notificacao::recentes((int)$u['id']);
        Notificacao::marcarLidas((int)$u['id']);
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        require __DIR__ . '/../views/pedido/meus.php';
    }

    public function ver(): void
    {
        $u = Auth::require(['cliente', 'gerente', 'administrador']);
        $p = Pedido::porId((int)($_GET['id'] ?? 0));
        // cliente so enxerga o proprio pedido (404 para nao revelar existencia)
        if (!$p || ($u['perfil'] === 'cliente' && (int)$p['cliente_id'] !== (int)$u['id'])) {
            http_response_code(404);
            exit('Pedido não encontrado.');
        }
        $itens = Pedido::itens((int)$p['id']);
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        require __DIR__ . '/../views/pedido/ver.php';
    }

    /** Simula a confirmacao do Pix (no sistema real viria do banco/gateway). */
    public function pagar(): void
    {
        $u = Auth::require(['cliente']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $id = (int)($_POST['id'] ?? 0);
        $p = Pedido::porId($id);
        if (!$p || (int)$p['cliente_id'] !== (int)$u['id']) { http_response_code(404); exit('Pedido não encontrado.'); }
        if (Pedido::aprovarPix($id)) {
            Notificacao::criar((int)$u['id'], "Pagamento Pix do pedido #$id confirmado.");
            $this->flash('Pagamento Pix confirmado (simulação).');
        }
        header('Location: index.php?r=pedido/ver&id=' . $id);
        exit;
    }

    /** JSON para o acompanhamento em tempo quase real (consultado a cada 30 s). */
    public function status(): void
    {
        $u = Auth::require(['cliente', 'gerente', 'administrador']);
        $p = Pedido::porId((int)($_GET['id'] ?? 0));
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        if (!$p || ($u['perfil'] === 'cliente' && (int)$p['cliente_id'] !== (int)$u['id'])) {
            http_response_code(404);
            echo json_encode(['erro' => 'não encontrado']);
            return;
        }
        echo json_encode([
            'status'    => $p['status'],
            'rotulo'    => STATUS_PEDIDO[$p['status']] ?? $p['status'],
            'etapa'     => array_search($p['status'], ETAPAS, true),
            'entregador'=> $p['entregador_nome'],
            'pagamento' => $p['pagamento_status'],
        ]);
    }

    /** Cliente avalia a entrega (1 a 5) uma unica vez. */
    public function avaliarentrega(): void
    {
        $u = Auth::require(['cliente']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $id = (int)($_POST['id'] ?? 0);
        $p = Pedido::porId($id);
        if (!$p || (int)$p['cliente_id'] !== (int)$u['id']) { http_response_code(404); exit('Pedido não encontrado.'); }
        $nota = (int)($_POST['nota'] ?? 0);
        $com = mb_substr(trim((string)($_POST['comentario'] ?? '')), 0, 255);
        if ($nota < 1 || $nota > 5) {
            $this->flash('Escolha uma nota de 1 a 5.');
        } elseif (Pedido::avaliarEntrega($id, $nota, $com)) {
            $this->flash('Obrigado pela avaliação da entrega!');
        } else {
            $this->flash('Não foi possível registrar a avaliação (pedido não entregue ou já avaliado).');
        }
        header('Location: index.php?r=pedido/ver&id=' . $id);
        exit;
    }
}
