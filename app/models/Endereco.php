<?php
require_once __DIR__ . '/../core/Database.php';

class Endereco
{
    public static function doUsuario(int $uid): array
    {
        $st = Database::conn()->prepare('SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY id DESC');
        $st->execute([$uid]);
        return $st->fetchAll();
    }

    /** Garante que o endereco pertence ao usuario. */
    public static function porId(int $id, int $uid): ?array
    {
        $st = Database::conn()->prepare('SELECT * FROM enderecos WHERE id = ? AND usuario_id = ?');
        $st->execute([$id, $uid]);
        return $st->fetch() ?: null;
    }

    public static function criar(int $uid, array $d): int
    {
        $pdo = Database::conn();
        $pdo->prepare('INSERT INTO enderecos (usuario_id,cep,logradouro,numero,complemento,bairro,cidade,uf,instrucoes)
                       VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$uid, $d['cep'], $d['logradouro'], $d['numero'], $d['complemento'] ?: null,
                       $d['bairro'], $d['cidade'], $d['uf'], $d['instrucoes'] ?: null]);
        return (int)$pdo->lastInsertId();
    }

    public static function atualizarInstrucoes(int $id, string $instrucoes): void
    {
        Database::conn()->prepare('UPDATE enderecos SET instrucoes = ? WHERE id = ?')
            ->execute([$instrucoes ?: null, $id]);
    }
}
