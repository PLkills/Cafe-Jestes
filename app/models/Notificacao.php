<?php
require_once __DIR__ . '/../core/Database.php';

class Notificacao
{
    public static function criar(int $uid, string $msg): void
    {
        Database::conn()->prepare('INSERT INTO notificacoes (usuario_id, mensagem) VALUES (?,?)')
            ->execute([$uid, mb_substr($msg, 0, 255)]);
    }

    public static function recentes(int $uid, int $limite = 5): array
    {
        $st = Database::conn()->prepare('SELECT * FROM notificacoes WHERE usuario_id = ? ORDER BY id DESC LIMIT ' . (int)$limite);
        $st->execute([$uid]);
        return $st->fetchAll();
    }

    public static function marcarLidas(int $uid): void
    {
        Database::conn()->prepare('UPDATE notificacoes SET lida = 1 WHERE usuario_id = ? AND lida = 0')->execute([$uid]);
    }

    public static function naoLidas(int $uid): int
    {
        $st = Database::conn()->prepare('SELECT COUNT(*) FROM notificacoes WHERE usuario_id = ? AND lida = 0');
        $st->execute([$uid]);
        return (int)$st->fetchColumn();
    }
}
