<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController
{
    public function login(): void
    {
        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $u = Usuario::porEmail(trim($_POST['email'] ?? ''));
            if ($u && password_verify($_POST['senha'] ?? '', $u['senha_hash'])) {
                Auth::login($u);
                header('Location: index.php?r=' . ($u['perfil'] === 'cliente' ? 'produto/index' : 'home/painel'));
                exit;
            }
            $erro = 'E-mail ou senha invalidos.';
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function cadastro(): void
    {
        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
                $erro = 'Preencha nome, e-mail valido e senha com 6+ caracteres.';
            } elseif (Usuario::porEmail($email)) {
                $erro = 'E-mail ja cadastrado.';
            } else {
                Usuario::criarCliente($nome, $email, $senha, trim($_POST['telefone'] ?? ''));
                header('Location: index.php?r=auth/login');
                exit;
            }
        }
        require __DIR__ . '/../views/auth/cadastro.php';
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: index.php?r=auth/login');
    }
}
