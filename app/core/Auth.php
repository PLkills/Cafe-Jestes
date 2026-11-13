<?php
require_once __DIR__ . '/helpers.php';

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function login(array $u): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'nome' => $u['nome'], 'perfil' => $u['perfil']];
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /** Exige login e (opcionalmente) um dos perfis informados. */
    public static function require(array $perfis = []): array
    {
        $u = self::user();
        if (!$u) {
            header('Location: index.php?r=auth/login');
            exit;
        }
        require_once __DIR__ . '/../models/Usuario.php';
        if (!Usuario::estaAtivo((int)$u['id'])) {   // conta desativada: encerra a sessao na hora
            self::logout();
            header('Location: index.php?r=auth/login');
            exit;
        }
        if ($perfis && !in_array($u['perfil'], $perfis, true)) {
            http_response_code(403);
            exit('Acesso negado.');
        }
        return $u;
    }

    public static function csrfToken(): string
    {
        self::start();
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function csrfCheck(): void
    {
        self::start();
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            http_response_code(419);
            exit('Sessao expirada. Recarregue a pagina.');
        }
    }
}

