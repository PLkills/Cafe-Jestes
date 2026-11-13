<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Carrinho.php';

/** Carrinho: apenas clientes logados (CA05, CA06, CA07). Toda escrita e POST + CSRF. */
class CarrinhoController
{
    private function exigirPost(): void
    {
        Auth::require(['cliente']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
    }

    public function index(): void
    {
        $u = Auth::require(['cliente']);
        $r = Carrinho::resumo();
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        require __DIR__ . '/../views/carrinho/carrinho.php';
    }

    public function adicionar(): void
    {
        $this->exigirPost();
        $id = (int)($_POST['produto_id'] ?? 0);
        $erro = Carrinho::adicionar($id, (int)($_POST['quantidade'] ?? 1),
            (string)($_POST['tipo_leite'] ?? 'integral'), (string)($_POST['acucar'] ?? 'normal'),
            (string)($_POST['intensidade'] ?? 'media'));
        if ($erro !== '' && !str_starts_with($erro, 'Quantidade limitada')) {
            $_SESSION['flash'] = $erro;
            header('Location: index.php?r=produto/ver&id=' . $id);
            exit;
        }
        $_SESSION['flash'] = $erro !== '' ? $erro : 'Item adicionado ao carrinho.';
        header('Location: index.php?r=carrinho/index');
        exit;
    }

    public function atualizar(): void
    {
        $this->exigirPost();
        $aviso = Carrinho::atualizar((string)($_POST['chave'] ?? ''), (int)($_POST['quantidade'] ?? 0));
        if ($aviso !== '') $_SESSION['flash'] = $aviso;
        header('Location: index.php?r=carrinho/index');
        exit;
    }

    public function remover(): void
    {
        $this->exigirPost();
        Carrinho::remover((string)($_POST['chave'] ?? ''));
        $_SESSION['flash'] = 'Item removido.';
        header('Location: index.php?r=carrinho/index');
        exit;
    }

    public function limpar(): void
    {
        $this->exigirPost();
        Carrinho::limpar();
        $_SESSION['flash'] = 'Carrinho esvaziado.';
        header('Location: index.php?r=carrinho/index');
        exit;
    }
}
