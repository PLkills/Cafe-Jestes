<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Usuario.php';

/** Administracao de contas: clientes, entregadores, baristas e gerentes. */
class AdminusuarioController
{
    private const PERFIS = ['gerente', 'administrador'];

    /** Perfis que o usuario logado pode criar/alterar. Gerente nao mexe em gerente/administrador. */
    private function gerenciaveis(array $u): array
    {
        return $u['perfil'] === 'administrador'
            ? ['cliente', 'gerente', 'barista', 'entregador', 'administrador']
            : ['cliente', 'barista', 'entregador'];
    }

    public function index(): void
    {
        $u = Auth::require(self::PERFIS);
        $perfil = (string)($_GET['perfil'] ?? '');
        $q = trim((string)($_GET['q'] ?? ''));
        $usuarios = Usuario::listar($perfil, mb_substr($q, 0, 100));
        $gerenciaveis = $this->gerenciaveis($u);
        $msg = $_SESSION['flash'] ?? '';
        unset($_SESSION['flash']);
        $titulo = 'Usuários';
        require __DIR__ . '/../views/adminusuario/lista.php';
    }

    public function novo(): void
    {
        $u = Auth::require(self::PERFIS);
        $perm = $this->gerenciaveis($u);
        $erro = '';
        $d = ['nome' => '', 'email' => '', 'telefone' => '', 'perfil' => 'entregador'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            foreach ($d as $k => $_) $d[$k] = trim((string)($_POST[$k] ?? ''));
            $senha = (string)($_POST['senha'] ?? '');
            if (mb_strlen($d['nome']) < 3 || mb_strlen($d['nome']) > 120) $erro = 'Informe o nome (3 a 120 caracteres).';
            elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 150) $erro = 'E-mail inválido.';
            elseif (!in_array($d['perfil'], $perm, true)) $erro = 'Você não pode criar este tipo de conta.';
            elseif (mb_strlen($senha) < 8) $erro = 'A senha deve ter pelo menos 8 caracteres.';
            elseif (mb_strlen($d['telefone']) > 20) $erro = 'Telefone muito longo.';
            elseif (Usuario::existeEmail($d['email'])) $erro = 'Já existe uma conta com este e-mail.';
            else {
                Usuario::criarComPerfil($d['nome'], $d['email'], $senha, $d['telefone'], $d['perfil']);
                $_SESSION['flash'] = 'Conta criada: ' . $d['nome'] . ' (' . $d['perfil'] . ').';
                header('Location: index.php?r=adminusuario/index');
                exit;
            }
        }
        $titulo = 'Nova conta';
        require __DIR__ . '/../views/adminusuario/form.php';
    }

    /** Carrega o alvo e confere se o usuario logado pode altera-lo; senao encerra. */
    private function alvo(array $u): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Auth::csrfCheck();
        $alvo = Usuario::porId((int)($_POST['id'] ?? 0));
        if (!$alvo) { http_response_code(404); exit('Conta não encontrada.'); }
        if (!in_array($alvo['perfil'], $this->gerenciaveis($u), true)) { http_response_code(403); exit('Acesso negado.'); }
        return $alvo;
    }

    public function alternar(): void
    {
        $u = Auth::require(self::PERFIS);
        $alvo = $this->alvo($u);
        if ((int)$alvo['id'] === (int)$u['id']) {
            $_SESSION['flash'] = 'Você não pode desativar a própria conta.';
        } else {
            Usuario::definirAtivo((int)$alvo['id'], !$alvo['ativo']);
            $_SESSION['flash'] = $alvo['nome'] . ($alvo['ativo'] ? ' foi desativado(a).' : ' foi reativado(a).');
        }
        header('Location: index.php?r=adminusuario/index');
        exit;
    }

    public function senha(): void
    {
        $u = Auth::require(self::PERFIS);
        $alvo = $this->alvo($u);
        $nova = (string)($_POST['senha'] ?? '');
        if (mb_strlen($nova) < 8) {
            $_SESSION['flash'] = 'A nova senha deve ter pelo menos 8 caracteres.';
        } else {
            Usuario::redefinirSenha((int)$alvo['id'], $nova);
            $_SESSION['flash'] = 'Senha de ' . $alvo['nome'] . ' redefinida.';
        }
        header('Location: index.php?r=adminusuario/index');
        exit;
    }
}
