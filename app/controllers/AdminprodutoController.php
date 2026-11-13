<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Categoria.php';

/** CRUD de produtos: apenas gerente e administrador. */
class AdminprodutoController
{
    private const PERFIS = ['gerente', 'administrador'];

    public function index(): void
    {
        Auth::require(self::PERFIS);
        $produtos = Produto::todos();
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        require __DIR__ . '/../views/admin/produtos_lista.php';
    }

    public function novo(): void { $this->form(null); }

    public function editar(): void
    {
        Auth::require(self::PERFIS);
        $p = Produto::porId((int)($_GET['id'] ?? 0));
        if (!$p) { http_response_code(404); exit('Produto nao encontrado.'); }
        $this->form($p);
    }

    private function form(?array $p): void
    {
        Auth::require(self::PERFIS);
        $categorias = Categoria::todas();
        $erro = '';
        $dados = $p ?? ['categoria_id' => '', 'nome' => '', 'descricao' => '', 'origem_grao' => '',
                        'intensidade' => 3, 'preco' => '', 'preco_promocional' => '', 'estoque' => 0, 'imagem' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $dados = array_merge($dados, [
                'categoria_id'      => (int)($_POST['categoria_id'] ?? 0),
                'nome'              => trim($_POST['nome'] ?? ''),
                'descricao'         => trim($_POST['descricao'] ?? ''),
                'origem_grao'       => trim($_POST['origem_grao'] ?? ''),
                'intensidade'       => (int)($_POST['intensidade'] ?? 3),
                'preco'             => str_replace(',', '.', trim($_POST['preco'] ?? '')),
                'preco_promocional' => str_replace(',', '.', trim($_POST['preco_promocional'] ?? '')),
                'estoque'           => (int)($_POST['estoque'] ?? 0),
            ]);
            $erro = $this->validar($dados);
            $nomeImg = null;
            if (!$erro) {
                [$nomeImg, $erro] = salvar_imagem($_FILES['imagem'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
            }
            if (!$erro) {
                $dados['preco'] = (float)$dados['preco'];
                $dados['preco_promocional'] = $dados['preco_promocional'] === '' ? null : (float)$dados['preco_promocional'];
                $dados['imagem'] = $nomeImg;
                Produto::salvar($p['id'] ?? null, $dados);
                $_SESSION['flash'] = $p ? 'Produto atualizado.' : 'Produto cadastrado.';
                header('Location: index.php?r=adminproduto/index');
                exit;
            }
        }
        $editando = (bool)$p;
        require __DIR__ . '/../views/admin/produto_form.php';
    }

    private function validar(array $d): string
    {
        if ($d['nome'] === '' || mb_strlen($d['nome']) > 120) return 'Informe o nome (ate 120 caracteres).';
        if ($d['categoria_id'] < 1) return 'Escolha a categoria.';
        if (!is_numeric($d['preco']) || (float)$d['preco'] <= 0) return 'Informe um preco valido.';
        if ($d['preco_promocional'] !== '') {
            if (!is_numeric($d['preco_promocional']) || (float)$d['preco_promocional'] <= 0
                || (float)$d['preco_promocional'] >= (float)$d['preco']) {
                return 'O preco promocional deve ser menor que o preco normal.';
            }
        }
        if ($d['intensidade'] < 1 || $d['intensidade'] > 5) return 'Intensidade deve ser de 1 a 5.';
        if ($d['estoque'] < 0) return 'Estoque nao pode ser negativo.';
        return '';
    }

    /** Remove da vitrine (exclusao logica) ou reativa. */
    public function alternar(): void
    {
        Auth::require(self::PERFIS);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $p = Produto::porId((int)($_POST['id'] ?? 0));
        if ($p) {
            Produto::definirAtivo((int)$p['id'], !$p['ativo']);
            $_SESSION['flash'] = $p['ativo'] ? 'Produto removido da vitrine.' : 'Produto reativado na vitrine.';
        }
        header('Location: index.php?r=adminproduto/index');
        exit;
    }
}
