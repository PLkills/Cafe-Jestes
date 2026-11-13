<?php
require_once __DIR__ . '/../core/Auth.php';

class HomeController
{
    public function index(): void
    {
        header('Location: index.php?r=produto/index');
    }

    public function painel(): void
    {
        $u = Auth::require();
        require __DIR__ . '/../views/painel.php';
    }
}
