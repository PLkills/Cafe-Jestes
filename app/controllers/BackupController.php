<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';

/** Backup dos dados (INSERTs) em .sql, feito em PHP porque a hospedagem compartilhada nem sempre tem mysqldump. */
class BackupController
{
    /** Ordem respeita as chaves estrangeiras ao restaurar. */
    private const TABELAS = ['usuarios', 'enderecos', 'categorias', 'produtos', 'insumos', 'pedidos',
                             'itens_pedido', 'pagamentos', 'entregas', 'avaliacoes', 'pedidos_favoritos', 'notificacoes'];

    public function index(): void
    {
        Auth::require(['administrador']);
        $titulo = 'Backup';
        require __DIR__ . '/../views/backup/index.php';
    }

    public function baixar(): void
    {
        Auth::require(['administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $pdo = Database::conn();
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="cafe-jester-backup-' . date('Y-m-d_His') . '.sql"');
        header('Cache-Control: no-store');
        echo "-- Café Jester - backup de dados (" . date('d/m/Y H:i') . ")\n";
        echo "-- Restaurar: importe sql/schema.sql (estrutura) e depois este arquivo no phpMyAdmin.\n";
        echo "-- ATENÇÃO: contém dados pessoais e hashes de senha. Guarde em local seguro.\n";
        echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach (self::TABELAS as $t) {
            try {
                $linhas = $pdo->query("SELECT * FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                echo "-- tabela $t indisponível\n\n";
                continue;
            }
            echo "-- $t (" . count($linhas) . " registros)\n";
            foreach ($linhas as $l) {
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), $l);
                echo "INSERT INTO `$t` (`" . implode('`,`', array_keys($l)) . '`) VALUES (' . implode(',', $vals) . ");\n";
            }
            echo "\n";
        }
        echo "SET FOREIGN_KEY_CHECKS=1;\n";
    }
}
