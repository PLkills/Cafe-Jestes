<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Avaliacao.php';

class ProdutoController
{
    /** Vitrine publica (nao exige login). */
    public function index(): void
    {
        $cat = isset($_GET['cat']) && ctype_digit($_GET['cat']) ? (int)$_GET['cat'] : null;
        $busca = trim($_GET['q'] ?? '');
        $categorias = Categoria::todas();
        $ordem = in_array($_GET['ordem'] ?? '', ['preco_asc', 'preco_desc', 'avaliacao'], true) ? $_GET['ordem'] : '';
        $produtos = Produto::vitrine($cat, $busca, $ordem);
        require __DIR__ . '/../views/produtos/vitrine.php';
    }

    public function ver(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $p = $id ? Produto::porId($id) : null;
        if (!$p || !$p['ativo']) {
            http_response_code(404);
            exit('Produto nao encontrado.');
        }
        $avaliacoes = Avaliacao::recentes($id);
        $u = Auth::user();
        $minha = ($u && $u['perfil'] === 'cliente') ? Avaliacao::doUsuario($id, (int)$u['id']) : null;
        $descricao = mb_strimwidth($p['nome'] . ' — ' . preg_replace('/\s+/', ' ', (string)$p['descricao']), 0, 158, '...');
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        require __DIR__ . '/../views/produtos/detalhe.php';
    }

    /** Somente clientes logados avaliam (POST + CSRF). */
    public function avaliar(): void
    {
        $u = Auth::require(['cliente']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $id = (int)($_POST['produto_id'] ?? 0);
        $nota = (int)($_POST['nota'] ?? 0);
        $coment = mb_substr(trim($_POST['comentario'] ?? ''), 0, 500);
        $p = $id ? Produto::porId($id) : null;
        if (!$p || $nota < 1 || $nota > 5) {
            $_SESSION['flash'] = 'Escolha uma nota de 1 a 5.';
        } else {
            Avaliacao::salvar($id, (int)$u['id'], $nota, $coment);
            $_SESSION['flash'] = 'Avaliacao registrada. Obrigado!';
        }
        header('Location: index.php?r=produto/ver&id=' . $id);
        exit;
    }
}
