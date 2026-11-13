<?php
require_once __DIR__ . '/../core/Database.php';

class Avaliacao
{
    public static function recentes(int $produtoId, int $limite = 10): array
    {
        $st = Database::conn()->prepare(
            'SELECT a.nota, a.comentario, a.criado_em, u.nome
             FROM avaliacoes a JOIN usuarios u ON u.id = a.usuario_id
             WHERE a.produto_id = ? ORDER BY a.criado_em DESC LIMIT ' . (int)$limite);
        $st->execute([$produtoId]);
        return $st->fetchAll();
    }

    public static function doUsuario(int $produtoId, int $usuarioId): ?array
    {
        $st = Database::conn()->prepare('SELECT * FROM avaliacoes WHERE produto_id = ? AND usuario_id = ?');
        $st->execute([$produtoId, $usuarioId]);
        return $st->fetch() ?: null;
    }

    /** Uma avaliacao por cliente/produto: reenviar atualiza a anterior. */
    public static function salvar(int $produtoId, int $usuarioId, int $nota, string $comentario): void
    {
        $existe = self::doUsuario($produtoId, $usuarioId);
        $pdo = Database::conn();
        if ($existe) {
            $pdo->prepare('UPDATE avaliacoes SET nota = ?, comentario = ?, criado_em = NOW() WHERE id = ?')
                ->execute([$nota, $comentario, $existe['id']]);
        } else {
            $pdo->prepare('INSERT INTO avaliacoes (produto_id, usuario_id, nota, comentario) VALUES (?,?,?,?)')
                ->execute([$produtoId, $usuarioId, $nota, $comentario]);
        }
    }
}
