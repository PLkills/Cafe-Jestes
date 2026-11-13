<?php
require_once __DIR__ . '/Produto.php';

/**
 * Carrinho guardado na sessao. Guarda SOMENTE ids, quantidades e opcoes;
 * precos e estoque sao sempre lidos do banco (nunca confiar no navegador).
 */
class Carrinho
{
    private static function &linhas(): array
    {
        if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
        return $_SESSION['carrinho'];
    }

    private static function chave(int $id, string $leite, string $acucar, string $int): string
    {
        return md5("$id|$leite|$acucar|$int");
    }

    /** Retorna '' se ok ou a mensagem de erro. */
    public static function adicionar(int $id, int $qtd, string $leite, string $acucar, string $int): string
    {
        $p = Produto::porId($id);
        if (!$p || !$p['ativo']) return 'Produto indisponível.';
        if (!personalizavel($p)) { $leite = 'sem_leite'; $acucar = 'sem_acucar'; $int = 'media'; }
        if (!isset(OPCOES_LEITE[$leite]) || !isset(OPCOES_ACUCAR[$acucar]) || !isset(OPCOES_INTENSIDADE[$int])) {
            return 'Opção de personalização inválida.';
        }
        $max = min((int)config_loja()['max_por_item'], (int)$p['estoque']);
        if ($max < 1) return 'Produto esgotado.';
        $qtd = max(1, $qtd);

        $l = &self::linhas();
        $k = self::chave($id, $leite, $acucar, $int);
        $atual = $l[$k]['quantidade'] ?? 0;
        $nova = $atual + $qtd;
        $aviso = '';
        if ($nova > $max) { $nova = $max; $aviso = "Quantidade limitada a $max unidades (estoque/limite)."; }
        $l[$k] = ['produto_id' => $id, 'quantidade' => $nova, 'tipo_leite' => $leite,
                  'acucar' => $acucar, 'intensidade' => $int];
        return $aviso;
    }

    public static function atualizar(string $k, int $qtd): string
    {
        $l = &self::linhas();
        if (!isset($l[$k])) return '';
        if ($qtd <= 0) { unset($l[$k]); return 'Item removido.'; }
        $p = Produto::porId((int)$l[$k]['produto_id']);
        $max = $p ? min((int)config_loja()['max_por_item'], (int)$p['estoque']) : 0;
        if ($max < 1) { unset($l[$k]); return 'Item indisponível foi removido.'; }
        $l[$k]['quantidade'] = min($qtd, $max);
        return $qtd > $max ? "Quantidade limitada a $max unidades." : '';
    }

    public static function remover(string $k): void { unset(self::linhas()[$k]); }

    public static function limpar(): void { $_SESSION['carrinho'] = []; }

    public static function quantidadeTotal(): int
    {
        return array_sum(array_column($_SESSION['carrinho'] ?? [], 'quantidade'));
    }

    /**
     * Resumo calculado com dados do banco: itens, subtotal, taxa e total (CA11).
     * Itens que ficaram inativos/sem estoque sao descartados e listados em 'removidos'.
     */
    public static function resumo(): array
    {
        $cfg = config_loja();
        $itens = []; $removidos = []; $subtotal = 0.0;
        $l = &self::linhas();
        foreach ($l as $k => $linha) {
            $p = Produto::porId((int)$linha['produto_id']);
            if (!$p || !$p['ativo'] || $p['estoque'] < 1) {
                $removidos[] = $p['nome'] ?? 'Produto';
                unset($l[$k]);
                continue;
            }
            $qtd = min((int)$linha['quantidade'], (int)$p['estoque'], (int)$cfg['max_por_item']);
            $l[$k]['quantidade'] = $qtd;
            $unit = preco_final($p);
            $total = round($unit * $qtd, 2);
            $subtotal += $total;
            $itens[] = ['chave' => $k, 'produto' => $p, 'quantidade' => $qtd, 'unitario' => $unit,
                        'total' => $total, 'tipo_leite' => $linha['tipo_leite'],
                        'acucar' => $linha['acucar'], 'intensidade' => $linha['intensidade'],
                        'personalizado' => personalizavel($p)];
        }
        $subtotal = round($subtotal, 2);
        $taxa = 0.0;
        if ($itens) {
            $gratis = (float)$cfg['frete_gratis_acima'];
            $taxa = ($gratis > 0 && $subtotal >= $gratis) ? 0.0 : (float)$cfg['taxa_entrega'];
        }
        return ['itens' => $itens, 'removidos' => $removidos, 'subtotal' => $subtotal,
                'taxa' => $taxa, 'total' => round($subtotal + $taxa, 2),
                'falta_frete_gratis' => ($cfg['frete_gratis_acima'] > 0 && $itens && $taxa > 0)
                    ? round($cfg['frete_gratis_acima'] - $subtotal, 2) : 0];
    }
}
