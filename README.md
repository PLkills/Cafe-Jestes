# Café Jester - Cafeteria Gourmet Virtual Delivery (PIT II)
PHP 8 + MySQL, padrao MVC.

## Instalacao local
1. Importe `sql/schema.sql` (phpMyAdmin ou `mysql -u root < sql/schema.sql`).
2. Copie `config/database.example.php` para `config/database.php` e ajuste.
3. Aponte o servidor para `public/` (ou `php -S localhost:8000 -t public`).
4. Usuarios de teste (senha XXX): admin@, gerente@, barista@, entregador@, cliente@cafeteria.com

## Dia 2
Importe tambem `sql/dia2_avaliacoes.sql` (nao apaga dados). Rotas novas:
`?r=produto/index` (vitrine), `?r=produto/ver&id=1`, `?r=adminproduto/index` (gerente/administrador).
Em hospedagem, deixe `public/uploads/` com permissao de escrita (755 ou 775).

## Dia 3 - Carrinho
Sem alteracao no banco. Regras em `config/loja.php` (taxa R$ 6,00; frete gratis a partir de R$ 80,00; maximo 10 por item).
Publicacao: veja `deploy/LEIA-ME-publicacao.md`.
Rotas: `?r=carrinho/index` (somente cliente logado). Precos e estoque sao sempre lidos do banco.

## Dia 4 - Pedido e pagamento (simulado)
Sem alteracao no banco (usa enderecos, pedidos, itens_pedido, pagamentos, entregas, notificacoes).
Rotas: `?r=pedido/checkout`, `?r=pedido/ver&id=N`, `?r=pedido/meus`. Pix e cartao sao SIMULADOS: nenhum valor real e cobrado;
do cartao guarda-se apenas os 4 ultimos digitos. A biblioteca `public/js/qrcode.js` (qrcode-generator, MIT) gera o QR localmente.

## Dia 5 - Operacao e rastreamento
- **Barista** (`?r=barista/index`): fila de pedidos *com pagamento aprovado*; "Iniciar preparo" -> "Marcar como pronto".
- **Gerente** (`?r=gestao/index`): pedidos em aberto, atribuir/trocar entregador, entregas concluidas com nota.
- **Entregador** (`?r=entregador/index`): so as proprias entregas; rota no Google Maps; "saiu para entrega" -> "confirmar entrega".
- **Cliente** (`?r=pedido/ver&id=N`): linha do tempo do pedido, atualizada a cada 30 s (`pedido/status` em JSON); avaliacao da entrega (1-5) apos entregue.
- Transicoes sao atomicas (UPDATE condicionado ao status anterior) e notificam o cliente.
- Nao ha alteracao de banco neste dia (usa colunas ja existentes de `entregas`).
- Contas de teste (senha XXX, trocar antes de divulgar): barista@, entregador@, entregador2@, gerente@cafeteria.com

## Dia 6 - Administracao, relatorios e backup
- **Usuarios** (`?r=adminusuario/index`, gerente/administrador): lista com busca e filtro por perfil, criar conta (entregador, barista etc.), desativar/reativar e redefinir senha. Gerente nao altera gerente/administrador; ninguem desativa a propria conta. Conta desativada perde a sessao na hora.
- **Relatorios** (`?r=relatorio/index`): faturamento, pedidos, ticket medio, vendas por dia, mais vendidos, formas de pagamento e entregadores (nota media). CSV (Excel pt-BR) e impressao/PDF pelo navegador.
- **Backup** (`?r=backup/index`, so administrador): baixa .sql com os dados (INSERTs). Restaurar = `sql/schema.sql` + backup no phpMyAdmin.
- Conexao MySQL agora fixa `time_zone = -03:00` para casar com o PHP.
- Sem alteracao de banco: basta enviar os arquivos.

## Dia 7 — Ajustes após o teste dos colegas

Correções feitas a partir da lista de problemas e melhorias levantada nos testes:

- **Acentuação** dos dados de exemplo (categorias, produtos, insumos, cidade, comentários). Para o banco que já está no ar: executar `sql/dia7_acentos.sql` no phpMyAdmin (só ajusta textos; não apaga pedidos nem contas). `schema.sql` e `dia2_avaliacoes.sql` já nascem corretos.
- **Vitrine:** ordenação (menor/maior preço, melhor avaliação), contagem de produtos encontrados.
- **Detalhe do produto:** indicador visual de força do grão; "Grãos" não mostram leite/açúcar; leite padrão "Sem leite" para espresso e gelados; avaliação por estrelas clicáveis (também na nota da entrega).
- **Carrinho:** botões − e + de quantidade, confirmação ao remover.
- **Celular:** menu com botão (hambúrguer) e atalho do carrinho, alvos de toque ≥ 44 px, "Meus pedidos" em cartões, mapa não prende a rolagem em telas de toque.
- **Telas de operação** (barista, gestão, entregador): atualização automática a cada 30 s sem recarregar e sem interromper quem está preenchendo algo; confirmação antes de marcar "a caminho".
- **Rodapé:** contato, Política de Privacidade (`?r=home/privacidade`) e link do GitHub. Preencher e-mail/WhatsApp reais em `config/loja.php` (`contato`).
- Correção de CSS: a barra dos relatórios (`.barra-rel`) não conflita mais com a barra de intensidade.

Verificação: 133 testes automatizados de ponta a ponta continuam passando, mais checagens das telas novas.
