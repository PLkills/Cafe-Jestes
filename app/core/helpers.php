<?php
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function moeda($v): string { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }

/** Preco efetivo: promocional (se houver e for menor) ou normal. */
function preco_final(array $p): float
{
    if ($p['preco_promocional'] !== null && (float)$p['preco_promocional'] > 0
        && (float)$p['preco_promocional'] < (float)$p['preco']) {
        return (float)$p['preco_promocional'];
    }
    return (float)$p['preco'];
}

function em_promocao(array $p): bool { return preco_final($p) < (float)$p['preco']; }

/**
 * Pasta publica (onde ficam css/, img/, uploads/ e index.php).
 * Funciona nos dois modos de publicacao: projeto inteiro com pasta public/
 * OU conteudo de public/ direto na raiz do site (app/ e config/ fora dela).
 */
function dir_publico(): string
{
    $p = __DIR__ . '/../../public';
    if (is_dir($p)) return rtrim(realpath($p), '/') . '/';
    return rtrim(dirname($_SERVER['SCRIPT_FILENAME'] ?? __DIR__), '/') . '/';
}

/** URL da imagem; se o arquivo nao existir usa o placeholder SVG. */
function imagem_url(?string $arq): string
{
    $arq = basename((string)$arq);
    $pub = dir_publico();
    if ($arq !== '' && is_file($pub . 'uploads/' . $arq)) return 'uploads/' . rawurlencode($arq);
    if ($arq !== '' && is_file($pub . 'img/' . $arq))     return 'img/' . rawurlencode($arq);
    return 'img/placeholder.svg';
}

function estrelas(?float $media): string
{
    $n = (int)round($media ?? 0);
    return str_repeat('&#9733;', $n) . str_repeat('&#9734;', 5 - $n);
}

/**
 * Upload seguro de imagem. Retorna [nomeArquivo|null, erro|null].
 * Valida tamanho, tipo REAL (finfo) e gera nome aleatorio.
 */
function salvar_imagem(array $f): array
{
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'Falha no upload da imagem.'];
    if ($f['size'] > 3 * 1024 * 1024) return [null, 'Imagem maior que 3 MB.'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) return [null, 'Formato invalido. Use JPG, PNG ou WEBP.'];
    $dir = dir_publico() . 'uploads/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $nome = bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . $nome)) return [null, 'Nao foi possivel salvar a imagem.'];
    return [$nome, null];
}

// ---- Personalizacao (CA06) ----
const OPCOES_LEITE = ['integral' => 'Leite integral', 'desnatado' => 'Leite desnatado',
                      'vegetal' => 'Leite vegetal', 'sem_leite' => 'Sem leite'];
const OPCOES_ACUCAR = ['sem_acucar' => 'Sem açúcar', 'pouco' => 'Pouco açúcar',
                       'normal' => 'Açúcar normal', 'muito' => 'Muito açúcar'];
const OPCOES_INTENSIDADE = ['suave' => 'Suave', 'media' => 'Média', 'forte' => 'Forte'];

/** Regras da loja. Se config/loja.php nao existir no servidor, usa estes padroes. */
function config_loja(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $padrao = ['taxa_entrega' => 6.00, 'frete_gratis_acima' => 80.00, 'max_por_item' => 10,
                   'categorias_sem_personalizacao' => ['Graos'],
                   'contato' => ['email' => '', 'whatsapp' => '', 'horario' => 'Todos os dias, das 8h às 20h']];
        $arq = __DIR__ . '/../../config/loja.php';
        $cfg = is_file($arq) ? array_merge($padrao, (array)(require $arq)) : $padrao;
    }
    return $cfg;
}

/** Graos em pacote nao tem leite/acucar: sem personalizacao. */
function personalizavel(array $p): bool
{
    // compara sem acento/maiuscula: "Graos" e "Grãos" sao a mesma categoria
    $sem = array_map('sem_acento', config_loja()['categorias_sem_personalizacao']);
    return !in_array(sem_acento((string)($p['categoria'] ?? '')), $sem, true);
}

function sem_acento(string $s): string
{
    return strtr(mb_strtolower($s), ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','é'=>'e','ê'=>'e','è'=>'e',
        'í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ú'=>'u','ü'=>'u','ç'=>'c']);
}

/** Cafes puros (espresso, cold brew) vem sem leite por padrao; os demais, com leite integral. */
function leite_padrao(array $p): string
{
    return in_array(sem_acento((string)($p['categoria'] ?? '')), ['espresso', 'gelados'], true) ? 'sem_leite' : 'integral';
}

/** Cinco "graos" para mostrar a intensidade de 1 a 5 (decorativo; o texto fica no aria-label). */
function graos_intensidade(int $n): string
{
    $n = max(0, min(5, $n));
    $h = '<span class="graos" role="img" aria-label="Intensidade ' . $n . ' de 5">';
    for ($i = 1; $i <= 5; $i++) $h .= '<i class="' . ($i <= $n ? 'on' : '') . '"></i>';
    return $h . '</span>';
}

// ---- Pedidos / pagamento ----
const STATUS_PEDIDO = [
    'aguardando_preparo' => 'Aguardando preparo', 'em_preparo' => 'Em preparo', 'pronto' => 'Pronto para entrega',
    'a_caminho' => 'A caminho', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado',
];
const STATUS_PAGAMENTO = ['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'recusado' => 'Recusado'];

function data_br(?string $d): string { return $d ? date('d/m/Y H:i', strtotime($d)) : '-'; }

/** Validacao de numero de cartao (algoritmo de Luhn). */
function luhn_ok(string $n): bool
{
    $n = preg_replace('/\D/', '', $n);
    $len = strlen($n);
    if ($len < 13 || $len > 19) return false;
    $soma = 0;
    for ($i = 0; $i < $len; $i++) {
        $d = (int)$n[$len - 1 - $i];
        if ($i % 2 === 1) { $d *= 2; if ($d > 9) $d -= 9; }
        $soma += $d;
    }
    return $soma % 10 === 0;
}


// ---- Operacao interna / rastreamento ----
const ETAPAS = ['aguardando_preparo', 'em_preparo', 'pronto', 'a_caminho', 'entregue'];

/** "Leite vegetal · Pouco açúcar · Intensidade forte" (vazio para graos em pacote). */
function desc_personalizacao(array $i): string
{
    if (!personalizavel(['categoria' => $i['categoria'] ?? ''])) return '';
    return (OPCOES_LEITE[$i['tipo_leite']] ?? '') . ' · ' . (OPCOES_ACUCAR[$i['acucar']] ?? '')
         . ' · Intensidade ' . strtolower(OPCOES_INTENSIDADE[$i['intensidade']] ?? '');
}

function endereco_texto(array $p): string
{
    return "{$p['logradouro']}, {$p['numero']}" . (!empty($p['complemento']) ? " - {$p['complemento']}" : '')
         . ", {$p['bairro']}, {$p['cidade']} - {$p['uf']}, CEP {$p['cep']}";
}

/** Link que abre a rota no Google Maps (sem chave de API). */
function url_mapa_rota(array $p): string
{
    return 'https://www.google.com/maps/dir/?api=1&travelmode=driving&destination=' . rawurlencode(endereco_texto($p) . ', Brasil');
}

function url_mapa_embed(array $p): string
{
    return 'https://maps.google.com/maps?output=embed&q=' . rawurlencode(endereco_texto($p) . ', Brasil');
}
