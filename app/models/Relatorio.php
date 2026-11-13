<?php
require_once __DIR__ . '/../core/Database.php';

/** Consultas dos relatorios. Considera so pedidos com pagamento aprovado e nao cancelados. */
class Relatorio
{
    private static function base(string $de, string $ate): array
    {
        // [de 00:00, ate+1 dia 00:00) usa o indice e inclui o ultimo dia inteiro
        $ini = $de . ' 00:00:00';
        $fim = date('Y-m-d', strtotime($ate . ' +1 day')) . ' 00:00:00';
        $from = " FROM pedidos p JOIN pagamentos pg ON pg.pedido_id = p.id AND pg.status = 'aprovado'
                  WHERE p.status <> 'cancelado' AND p.criado_em >= ? AND p.criado_em < ?";
        return [$from, [$ini, $fim]];
    }

    private static function all(string $sql, array $par): array
    {
        $st = Database::conn()->prepare($sql);
        $st->execute($par);
        return $st->fetchAll();
    }

    public static function totais(string $de, string $ate): array
    {
        [$f, $p] = self::base($de, $ate);
        $r = self::all('SELECT COUNT(*) AS pedidos, COALESCE(SUM(p.total),0) AS faturamento,
                        COALESCE(SUM(p.taxa_entrega),0) AS taxas' . $f, $p)[0];
        $r['ticket'] = $r['pedidos'] > 0 ? $r['faturamento'] / $r['pedidos'] : 0;
        return $r;
    }

    public static function porDia(string $de, string $ate): array
    {
        [$f, $p] = self::base($de, $ate);
        return self::all('SELECT DATE(p.criado_em) AS dia, COUNT(*) AS pedidos, SUM(p.total) AS total' . $f .
                         ' GROUP BY DATE(p.criado_em) ORDER BY dia', $p);
    }

    public static function maisVendidos(string $de, string $ate, int $limite = 10): array
    {
        [$f, $p] = self::base($de, $ate);
        return self::all('SELECT pr.nome, SUM(i.quantidade) AS qtd, SUM(i.quantidade * i.preco_unitario) AS total
                          FROM itens_pedido i JOIN produtos pr ON pr.id = i.produto_id
                          WHERE i.pedido_id IN (SELECT p.id' . $f . ')
                          GROUP BY pr.id, pr.nome ORDER BY qtd DESC, total DESC LIMIT ' . (int)$limite, $p);
    }

    public static function porPagamento(string $de, string $ate): array
    {
        [$f, $p] = self::base($de, $ate);
        return self::all('SELECT pg.metodo, COUNT(*) AS pedidos, SUM(p.total) AS total' . $f .
                         ' GROUP BY pg.metodo ORDER BY total DESC', $p);
    }

    public static function entregadores(string $de, string $ate): array
    {
        [$f, $p] = self::base($de, $ate);
        return self::all("SELECT d.nome, COUNT(*) AS entregas, AVG(en.nota_cliente) AS nota
                          FROM entregas en JOIN usuarios d ON d.id = en.entregador_id
                          WHERE en.status = 'entregue' AND en.pedido_id IN (SELECT p.id" . $f . ')
                          GROUP BY d.id, d.nome ORDER BY entregas DESC', $p);
    }
}
