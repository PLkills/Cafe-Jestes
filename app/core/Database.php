<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $c = require __DIR__ . '/../../config/database.php';
            $dsn = "mysql:host={$c['host']};dbname={$c['dbname']};charset={$c['charset']}";
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // horarios gravados pelo MySQL (criado_em etc.) no mesmo fuso do PHP; o Brasil nao tem horario de verao
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '-03:00'",
            ]);
        }
        return self::$pdo;
    }
}
