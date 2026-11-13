<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Relatorio.php';

class RelatorioController
{
    private const PERFIS = ['gerente', 'administrador'];

    /** Le o periodo da URL (YYYY-MM-DD); padrao: ultimos 30 dias. */
    private function periodo(): array
    {
        $ok = fn($v) => is_string($v) && ($d = DateTime::createFromFormat('Y-m-d', $v)) && $d->format('Y-m-d') === $v;
        $ate = $ok($_GET['ate'] ?? null) ? $_GET['ate'] : date('Y-m-d');
        $de  = $ok($_GET['de'] ?? null) ? $_GET['de'] : date('Y-m-d', strtotime('-29 days'));
        if ($de > $ate) [$de, $ate] = [$ate, $de];
        if (strtotime($ate) - strtotime($de) > 366 * 86400) $de = date('Y-m-d', strtotime($ate . ' -366 days'));
        return [$de, $ate];
    }

    public function index(): void
    {
        $u = Auth::require(self::PERFIS);
        [$de, $ate] = $this->periodo();
        $tot = Relatorio::totais($de, $ate);
        $dias = Relatorio::porDia($de, $ate);
        $top = Relatorio::maisVendidos($de, $ate);
        $pags = Relatorio::porPagamento($de, $ate);
        $ents = Relatorio::entregadores($de, $ate);
        $max = 0; foreach ($dias as $d) $max = max($max, (float)$d['total']);
        $titulo = 'Relatórios';
        require __DIR__ . '/../views/relatorio/index.php';
    }

    /** CSV para Excel pt-BR: separador ';', virgula decimal, BOM UTF-8. */
    public function csv(): void
    {
        Auth::require(self::PERFIS);
        [$de, $ate] = $this->periodo();
        $tipo = (string)($_GET['tipo'] ?? '');
        $num = fn($v) => number_format((float)$v, 2, ',', '');
        switch ($tipo) {
            case 'vendas':
                $cab = ['Dia', 'Pedidos', 'Total (R$)'];
                $lin = array_map(fn($r) => [date('d/m/Y', strtotime($r['dia'])), $r['pedidos'], $num($r['total'])], Relatorio::porDia($de, $ate));
                break;
            case 'produtos':
                $cab = ['Produto', 'Quantidade', 'Total (R$)'];
                $lin = array_map(fn($r) => [$r['nome'], $r['qtd'], $num($r['total'])], Relatorio::maisVendidos($de, $ate, 1000));
                break;
            case 'pagamentos':
                $cab = ['Forma de pagamento', 'Pedidos', 'Total (R$)'];
                $lin = array_map(fn($r) => [$r['metodo'] === 'pix' ? 'Pix' : 'Cartão', $r['pedidos'], $num($r['total'])], Relatorio::porPagamento($de, $ate));
                break;
            default:
                http_response_code(400); exit('Tipo de relatório inválido.');
        }
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"cafe-jester-$tipo-$de-a-$ate.csv\"");
        header('Cache-Control: no-store');
        $o = fopen('php://output', 'w');
        fwrite($o, "\xEF\xBB\xBF");
        $san = fn($c) => is_string($c) && $c !== '' && strpos('=+-@', $c[0]) !== false ? "'" . $c : $c;  // anti formula-injection
        fputcsv($o, $cab, ';');
        foreach ($lin as $l) fputcsv($o, array_map($san, $l), ';');
        fclose($o);
    }
}
