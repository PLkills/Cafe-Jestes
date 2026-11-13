<?php
require_once __DIR__ . '/../core/Database.php';

class Categoria
{
    public static function todas(): array
    {
        return Database::conn()->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();
    }
}
