<?php
// Front controller: ?r=controlador/acao  (ex.: index.php?r=produto/ver&id=1)
require_once __DIR__ . '/../app/core/Auth.php';
// Producao: nunca mostrar erros tecnicos (caminhos, stack trace) ao visitante; registrar no log do servidor.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
set_exception_handler(function (Throwable $e) {
    error_log('Erro nao tratado: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) http_response_code(500);
    exit('Ocorreu um erro interno. Tente novamente em instantes.');
});
Auth::start();
date_default_timezone_set('America/Sao_Paulo');

$rota = $_GET['r'] ?? 'home/index';
[$ctrl, $acao] = array_pad(explode('/', $rota, 2), 2, 'index');

// Lista branca: impede incluir arquivos arbitrarios
$controladores = [
    'home'         => 'HomeController',
    'auth'         => 'AuthController',
    'produto'      => 'ProdutoController',
    'adminproduto' => 'AdminprodutoController',
    'carrinho'     => 'CarrinhoController',
    'pedido'       => 'PedidoController',
    'barista'      => 'BaristaController',
    'gestao'       => 'GestaoController',
    'entregador'   => 'EntregadorController',
    'adminusuario' => 'AdminusuarioController',
    'relatorio'    => 'RelatorioController',
    'backup'       => 'BackupController',
];
if (!isset($controladores[$ctrl]) || !preg_match('/^[a-z]+$/', $acao)) {
    http_response_code(404);
    exit('Página não encontrada.');
}

$classe = $controladores[$ctrl];
require_once __DIR__ . "/../app/controllers/$classe.php";
$obj = new $classe();
if (!method_exists($obj, $acao) || !(new ReflectionMethod($obj, $acao))->isPublic()) {
    http_response_code(404);
    exit('Página não encontrada.');
}
$obj->$acao();
