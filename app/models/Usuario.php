<?php
require_once __DIR__ . '/../core/Database.php';

class Usuario
{
    public static function porEmail(string $email): ?array
    {
        $st = Database::conn()->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1');
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    public static function criarCliente(string $nome, string $email, string $senha, string $tel): int
    {
        $st = Database::conn()->prepare(
            "INSERT INTO usuarios (nome,email,senha_hash,telefone,perfil) VALUES (?,?,?,?, 'cliente')"
        );
        $st->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $tel]);
        return (int) Database::conn()->lastInsertId();
    }

    /** Entregadores ativos com a quantidade de entregas em andamento (para o gerente escolher). */
    public static function entregadores(): array
    {
        return Database::conn()->query(
            "SELECT u.id, u.nome, COUNT(e.id) AS em_andamento
             FROM usuarios u LEFT JOIN entregas e ON e.entregador_id = u.id AND e.status <> 'entregue'
             WHERE u.perfil = 'entregador' AND u.ativo = 1
             GROUP BY u.id, u.nome ORDER BY u.nome")->fetchAll();
    }

    // ---------- Administracao de contas (Dia 6) ----------
    public const PERFIS = ['cliente', 'gerente', 'barista', 'entregador', 'administrador'];

    public static function listar(string $perfil = '', string $busca = ''): array
    {
        $where = []; $par = [];
        if (in_array($perfil, self::PERFIS, true)) { $where[] = 'u.perfil = ?'; $par[] = $perfil; }
        if ($busca !== '') {
            $where[] = '(u.nome LIKE ? OR u.email LIKE ?)';
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            array_push($par, $like, $like);
        }
        $st = Database::conn()->prepare(
            'SELECT u.id, u.nome, u.email, u.telefone, u.perfil, u.ativo, u.criado_em,
                    (SELECT COUNT(*) FROM pedidos p WHERE p.cliente_id = u.id) AS qtd_pedidos,
                    (SELECT COUNT(*) FROM entregas e WHERE e.entregador_id = u.id) AS qtd_entregas
             FROM usuarios u' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY u.perfil, u.nome');
        $st->execute($par);
        return $st->fetchAll();
    }

    public static function porId(int $id): ?array
    {
        $st = Database::conn()->prepare('SELECT * FROM usuarios WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function existeEmail(string $email): bool
    {
        $st = Database::conn()->prepare('SELECT 1 FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        return (bool)$st->fetchColumn();
    }

    public static function criarComPerfil(string $nome, string $email, string $senha, string $tel, string $perfil): int
    {
        $st = Database::conn()->prepare('INSERT INTO usuarios (nome,email,senha_hash,telefone,perfil) VALUES (?,?,?,?,?)');
        $st->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $tel, $perfil]);
        return (int)Database::conn()->lastInsertId();
    }

    public static function definirAtivo(int $id, bool $ativo): void
    {
        Database::conn()->prepare('UPDATE usuarios SET ativo = ? WHERE id = ?')->execute([$ativo ? 1 : 0, $id]);
    }

    public static function redefinirSenha(int $id, string $senha): void
    {
        Database::conn()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
            ->execute([password_hash($senha, PASSWORD_DEFAULT), $id]);
    }

    public static function estaAtivo(int $id): bool
    {
        $st = Database::conn()->prepare('SELECT ativo FROM usuarios WHERE id = ?');
        $st->execute([$id]);
        return (bool)$st->fetchColumn();
    }
}
