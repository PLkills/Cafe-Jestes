<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/Notificacao.php';

class Pedido
{
    /**
     * Cria pedido + itens + pagamento + entrega + notificacao em UMA transacao.
     * Baixa o estoque com UPDATE condicional (evita vender mais do que existe).
     * Lanca RuntimeException (mensagem segura p/ o usuario) se faltar estoque.
     * @param array $resumo retorno de Carrinho::resumo()
     */
    public static function criar(int $clienteId, int $enderecoId, string $tipo, ?string $dataAgendada,
                                 array $resumo, string $metodo, ?string $ultimos4): int
    {
        $pdo = Database::conn();
        $pdo->beginTransaction();
        try {
            foreach ($resumo['itens'] as $i) {
                $q = (int)$i['quantidade'];
                $st = $pdo->prepare('UPDATE produtos SET estoque = estoque - :q WHERE id = :id AND ativo = 1 AND estoque >= :q2');
                $st->bindValue(':q', $q, PDO::PARAM_INT);
                $st->bindValue(':id', (int)$i['produto']['id'], PDO::PARAM_INT);
                $st->bindValue(':q2', $q, PDO::PARAM_INT);
                $st->execute();
                if ($st->rowCount() === 0) {
                    throw new RuntimeException('Estoque insuficiente para "' . $i['produto']['nome'] . '". Ajuste o carrinho.');
                }
            }
            $pdo->prepare("INSERT INTO pedidos (cliente_id,endereco_id,tipo_entrega,data_agendada,status,subtotal,taxa_entrega,total)
                           VALUES (?,?,?,?, 'aguardando_preparo', ?,?,?)")
                ->execute([$clienteId, $enderecoId, $tipo, $dataAgendada, $resumo['subtotal'], $resumo['taxa'], $resumo['total']]);
            $pedidoId = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO itens_pedido (pedido_id,produto_id,quantidade,preco_unitario,tipo_leite,acucar,intensidade)
                                  VALUES (?,?,?,?,?,?,?)');
            foreach ($resumo['itens'] as $i) {
                $ins->execute([$pedidoId, $i['produto']['id'], $i['quantidade'], $i['unitario'],
                               $i['tipo_leite'], $i['acucar'], $i['intensidade']]);
            }

            // Pagamento SIMULADO: cartao aprovado na hora; Pix fica pendente ate "pagar"
            $codigoPix = $metodo === 'pix'
                ? 'PIX-SIMULADO|CAFEJESTER|PEDIDO:' . $pedidoId . '|VALOR:' . number_format($resumo['total'], 2, '.', '') . '|' . bin2hex(random_bytes(6))
                : null;
            $status = $metodo === 'cartao' ? 'aprovado' : 'pendente';
            $pdo->prepare('INSERT INTO pagamentos (pedido_id,metodo,status,valor,codigo_pix,cartao_ultimos4,pago_em) VALUES (?,?,?,?,?,?,?)')
                ->execute([$pedidoId, $metodo, $status, $resumo['total'], $codigoPix, $ultimos4,
                           $status === 'aprovado' ? date('Y-m-d H:i:s') : null]);

            $pdo->prepare("INSERT INTO entregas (pedido_id, status) VALUES (?, 'aguardando')")->execute([$pedidoId]);

            Notificacao::criar($clienteId, "Pedido #$pedidoId recebido. Status: Aguardando preparo.");
            if ($status === 'aprovado') Notificacao::criar($clienteId, "Pagamento do pedido #$pedidoId aprovado.");

            $pdo->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function doCliente(int $uid): array
    {
        $st = Database::conn()->prepare(
            'SELECT p.*, pg.metodo, pg.status AS pagamento_status
             FROM pedidos p LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
             WHERE p.cliente_id = ? ORDER BY p.id DESC');
        $st->execute([$uid]);
        return $st->fetchAll();
    }

    public static function porId(int $id): ?array
    {
        $st = Database::conn()->prepare(
            'SELECT p.*, e.cep, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf, e.instrucoes,
                    pg.metodo, pg.status AS pagamento_status, pg.codigo_pix, pg.cartao_ultimos4, pg.pago_em,
                    en.entregador_id, en.nota_cliente, en.comentario_cliente, d.nome AS entregador_nome
             FROM pedidos p
             JOIN enderecos e ON e.id = p.endereco_id
             LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
             LEFT JOIN entregas en ON en.pedido_id = p.id
             LEFT JOIN usuarios d ON d.id = en.entregador_id
             WHERE p.id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function itens(int $pedidoId): array
    {
        $st = Database::conn()->prepare(
            'SELECT i.*, pr.nome, pr.categoria_id, c.nome AS categoria FROM itens_pedido i
             JOIN produtos pr ON pr.id = i.produto_id JOIN categorias c ON c.id = pr.categoria_id
             WHERE i.pedido_id = ? ORDER BY i.id');
        $st->execute([$pedidoId]);
        return $st->fetchAll();
    }

    /** Simula a confirmacao do Pix. Retorna true se mudou de pendente para aprovado. */
    public static function aprovarPix(int $pedidoId): bool
    {
        $pdo = Database::conn();
        $st = $pdo->prepare("UPDATE pagamentos SET status = 'aprovado', pago_em = ? WHERE pedido_id = ? AND metodo = 'pix' AND status = 'pendente'");
        $st->execute([date('Y-m-d H:i:s'), $pedidoId]);
        return $st->rowCount() > 0;
    }

    // ================= Operacao interna (Dia 5) =================

    private const MSG_STATUS = [
        'em_preparo' => 'Pedido #%d: o barista começou o preparo.',
        'pronto'     => 'Pedido #%d está pronto e aguarda o entregador.',
        'a_caminho'  => 'Pedido #%d saiu para entrega.',
        'entregue'   => 'Pedido #%d foi entregue. Avalie a entrega!',
    ];

    /** Itens de varios pedidos de uma vez, agrupados por pedido (evita N consultas). */
    public static function itensDosPedidos(array $ids): array
    {
        if (!$ids) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st = Database::conn()->prepare(
            "SELECT i.*, pr.nome, c.nome AS categoria
             FROM itens_pedido i JOIN produtos pr ON pr.id = i.produto_id JOIN categorias c ON c.id = pr.categoria_id
             WHERE i.pedido_id IN ($in) ORDER BY i.id");
        $st->execute(array_map('intval', array_values($ids)));
        $out = [];
        foreach ($st->fetchAll() as $r) $out[$r['pedido_id']][] = $r;
        return $out;
    }

    /** Fila do barista: so pedidos com pagamento aprovado, do mais antigo/proximo ao mais recente. */
    public static function fila(): array
    {
        return Database::conn()->query(
            "SELECT p.id, p.status, p.tipo_entrega, p.data_agendada, p.criado_em, u.nome AS cliente
             FROM pedidos p JOIN usuarios u ON u.id = p.cliente_id
             JOIN pagamentos pg ON pg.pedido_id = p.id AND pg.status = 'aprovado'
             WHERE p.status IN ('aguardando_preparo','em_preparo')
             ORDER BY COALESCE(p.data_agendada, p.criado_em), p.id")->fetchAll();
    }

    /**
     * Muda o status de forma atomica: so avanca se o pedido ainda estiver em $de
     * (dois cliques ou duas pessoas nao duplicam a transicao), esta pago e,
     * quando informado, pertence ao entregador. Notifica o cliente.
     */
    public static function mudarStatus(int $id, string $de, string $para, ?int $entregadorId = null): bool
    {
        $pdo = Database::conn();
        $agora = date('Y-m-d H:i:s');
        $pdo->beginTransaction();
        try {
            $sql = "UPDATE pedidos SET status = ?, atualizado_em = ? WHERE id = ? AND status = ?
                    AND EXISTS (SELECT 1 FROM pagamentos pg WHERE pg.pedido_id = pedidos.id AND pg.status = 'aprovado')";
            $par = [$para, $agora, $id, $de];
            if ($entregadorId !== null) {
                $sql .= ' AND EXISTS (SELECT 1 FROM entregas en WHERE en.pedido_id = pedidos.id AND en.entregador_id = ?)';
                $par[] = $entregadorId;
            }
            $st = $pdo->prepare($sql);
            $st->execute($par);
            if ($st->rowCount() !== 1) { $pdo->rollBack(); return false; }

            if ($para === 'a_caminho') {
                $pdo->prepare("UPDATE entregas SET status = 'a_caminho' WHERE pedido_id = ?")->execute([$id]);
            } elseif ($para === 'entregue') {
                $pdo->prepare("UPDATE entregas SET status = 'entregue', entregue_em = ? WHERE pedido_id = ?")->execute([$agora, $id]);
            }
            $c = $pdo->prepare('SELECT cliente_id FROM pedidos WHERE id = ?');
            $c->execute([$id]);
            if (isset(self::MSG_STATUS[$para]) && ($cid = $c->fetchColumn())) {
                Notificacao::criar((int)$cid, sprintf(self::MSG_STATUS[$para], $id));
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    /** Painel do gerente: todos os pedidos em aberto + situacao de pagamento e entregador. */
    public static function emAberto(): array
    {
        return Database::conn()->query(
            "SELECT p.id, p.status, p.tipo_entrega, p.data_agendada, p.total, p.criado_em,
                    c.nome AS cliente, en.entregador_id, d.nome AS entregador,
                    pg.status AS pagamento_status, pg.metodo
             FROM pedidos p
             JOIN usuarios c ON c.id = p.cliente_id
             LEFT JOIN entregas en ON en.pedido_id = p.id
             LEFT JOIN usuarios d ON d.id = en.entregador_id
             LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
             WHERE p.status NOT IN ('entregue','cancelado')
             ORDER BY COALESCE(p.data_agendada, p.criado_em), p.id")->fetchAll();
    }

    public static function entreguesRecentes(int $limite = 10): array
    {
        return Database::conn()->query(
            "SELECT p.id, p.total, en.entregue_em, en.nota_cliente, en.comentario_cliente,
                    c.nome AS cliente, d.nome AS entregador
             FROM pedidos p
             JOIN entregas en ON en.pedido_id = p.id
             JOIN usuarios c ON c.id = p.cliente_id
             LEFT JOIN usuarios d ON d.id = en.entregador_id
             WHERE p.status = 'entregue' ORDER BY en.entregue_em DESC, p.id DESC LIMIT " . (int)$limite)->fetchAll();
    }

    /** Atribui (ou reatribui) a entrega. Retorna '' se ok, senao a mensagem de erro. */
    public static function atribuir(int $pedidoId, int $entregadorId): string
    {
        $pdo = Database::conn();
        $u = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND perfil = 'entregador' AND ativo = 1");
        $u->execute([$entregadorId]);
        if (!$u->fetch()) return 'Entregador inválido ou inativo.';

        $p = $pdo->prepare("SELECT p.status, pg.status AS pag FROM pedidos p
                            LEFT JOIN pagamentos pg ON pg.pedido_id = p.id WHERE p.id = ?");
        $p->execute([$pedidoId]);
        $ped = $p->fetch();
        if (!$ped) return 'Pedido não encontrado.';
        if ($ped['pag'] !== 'aprovado') return 'O pagamento deste pedido ainda não foi aprovado.';
        if (!in_array($ped['status'], ['aguardando_preparo', 'em_preparo', 'pronto'], true)) {
            return 'Este pedido já saiu para entrega ou foi encerrado.';
        }
        $pdo->prepare('UPDATE entregas SET entregador_id = ?, atribuido_em = ? WHERE pedido_id = ?')
            ->execute([$entregadorId, date('Y-m-d H:i:s'), $pedidoId]);
        Notificacao::criar($entregadorId, "Nova entrega: pedido #$pedidoId atribuído a você.");
        return '';
    }

    /** Entregas do entregador: em aberto (cozinha/pronto/a caminho). */
    public static function doEntregador(int $uid): array
    {
        $st = Database::conn()->prepare(
            "SELECT p.id, p.status, p.tipo_entrega, p.data_agendada, p.total,
                    c.nome AS cliente, c.telefone,
                    en.cep, en.logradouro, en.numero, en.complemento, en.bairro, en.cidade, en.uf, en.instrucoes,
                    pg.metodo, pg.status AS pagamento_status
             FROM entregas e
             JOIN pedidos p ON p.id = e.pedido_id
             JOIN usuarios c ON c.id = p.cliente_id
             JOIN enderecos en ON en.id = p.endereco_id
             LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
             WHERE e.entregador_id = ? AND p.status IN ('aguardando_preparo','em_preparo','pronto','a_caminho')
             ORDER BY (p.status = 'pronto') DESC, (p.status = 'a_caminho') DESC, COALESCE(p.data_agendada, p.criado_em), p.id");
        $st->execute([$uid]);
        return $st->fetchAll();
    }

    public static function entreguesDo(int $uid, int $limite = 5): array
    {
        $st = Database::conn()->prepare(
            "SELECT p.id, p.total, e.entregue_em, e.nota_cliente, c.nome AS cliente
             FROM entregas e JOIN pedidos p ON p.id = e.pedido_id JOIN usuarios c ON c.id = p.cliente_id
             WHERE e.entregador_id = ? AND p.status = 'entregue' ORDER BY e.entregue_em DESC LIMIT " . (int)$limite);
        $st->execute([$uid]);
        return $st->fetchAll();
    }

    /** O cliente avalia a entrega uma unica vez, depois de entregue. */
    public static function avaliarEntrega(int $pedidoId, int $nota, string $comentario): bool
    {
        $st = Database::conn()->prepare(
            "UPDATE entregas SET nota_cliente = ?, comentario_cliente = ?
             WHERE pedido_id = ? AND status = 'entregue' AND nota_cliente IS NULL");
        $st->execute([$nota, $comentario !== '' ? $comentario : null, $pedidoId]);
        return $st->rowCount() === 1;
    }
}
