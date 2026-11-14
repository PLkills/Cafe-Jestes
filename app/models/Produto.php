<?php
require_once __DIR__ . '/../core/Database.php';

class Produto
{
    private const SELECT = "SELECT p.*, c.nome AS categoria,
        (SELECT ROUND(AVG(a.nota),1) FROM avaliacoes a WHERE a.produto_id = p.id) AS media,
        (SELECT COUNT(*) FROM avaliacoes a WHERE a.produto_id = p.id) AS total_avaliacoes
        FROM produtos p JOIN categorias c ON c.id = p.categoria_id";

    /** Vitrine: so ativos e com estoque. Filtros opcionais por categoria e busca. */
    public static function vitrine(?int $cat, string $busca = '', string $ordem = ''): array
    {
        $sql = self::SELECT . ' WHERE p.ativo = 1 AND p.estoque > 0';
        $par = [];
        if ($cat) { $sql .= ' AND p.categoria_id = ?'; $par[] = $cat; }
        if ($busca !== '') { $sql .= ' AND p.nome LIKE ?'; $par[] = '%' . $busca . '%'; }
        $media = '(SELECT AVG(a.nota) FROM avaliacoes a WHERE a.produto_id = p.id)';
        $sql .= match ($ordem) {
            'preco_asc'  => ' ORDER BY COALESCE(p.preco_promocional, p.preco) ASC, p.nome',
            'preco_desc' => ' ORDER BY COALESCE(p.preco_promocional, p.preco) DESC, p.nome',
            'avaliacao'  => " ORDER BY COALESCE($media, 0) DESC, p.nome",
            default      => ' ORDER BY (p.preco_promocional IS NOT NULL) DESC, p.nome',
        };
        $st = Database::conn()->prepare($sql);
        $st->execute($par);
        return $st->fetchAll();
    }

    /** Lista completa para o gerente (inclui inativos e sem estoque). */
    public static function todos(): array
    {
        return Database::conn()->query(self::SELECT . ' ORDER BY p.ativo DESC, p.nome')->fetchAll();
    }

    public static function porId(int $id): ?array
    {
        $st = Database::conn()->prepare(self::SELECT . ' WHERE p.id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function salvar(?int $id, array $d): int
    {
        $pdo = Database::conn();
        $campos = [$d['categoria_id'], $d['nome'], $d['descricao'], $d['origem_grao'],
                   $d['intensidade'], $d['preco'], $d['preco_promocional'], $d['estoque']];
        if ($id) {
            $sql = 'UPDATE produtos SET categoria_id=?, nome=?, descricao=?, origem_grao=?, intensidade=?,
                    preco=?, preco_promocional=?, estoque=?' . (!empty($d['imagem']) ? ', imagem=?' : '') . ' WHERE id=?';
            if (!empty($d['imagem'])) $campos[] = $d['imagem'];
            $campos[] = $id;
            $pdo->prepare($sql)->execute($campos);
            return $id;
        }
        $campos[] = $d['imagem'] ?? null;
        $pdo->prepare('INSERT INTO produtos (categoria_id,nome,descricao,origem_grao,intensidade,preco,
                       preco_promocional,estoque,imagem) VALUES (?,?,?,?,?,?,?,?,?)')->execute($campos);
        return (int)$pdo->lastInsertId();
    }

    /** Exclusao logica: preserva o historico de pedidos (FK em itens_pedido). */
    public static function definirAtivo(int $id, bool $ativo): void
    {
        Database::conn()->prepare('UPDATE produtos SET ativo = ? WHERE id = ?')->execute([(int)$ativo, $id]);
    }
}
