-- Dia 7: corrige os textos sem acento que ja estao no banco do site.
-- Execute no phpMyAdmin (aba SQL). Nao apaga pedidos nem contas; so ajusta textos.
-- Cada comando procura o texto antigo, entao pode ser executado mais de uma vez.
USE cafejester;
SET NAMES utf8mb4;

UPDATE categorias SET nome = 'Grãos' WHERE nome = 'Graos';

UPDATE produtos SET nome = 'Espresso Clássico', descricao = 'Café curto, encorpado, com crema dourada.' WHERE nome = 'Espresso Classico';
UPDATE produtos SET descricao = 'Cappuccino com toque de canela do Ceilão.' WHERE nome = 'Cappuccino de Canela';
UPDATE produtos SET nome = 'Grãos Bourbon Amarelo 250g', descricao = 'Notas de caramelo e chocolate, torra média.' WHERE nome = 'Graos Bourbon Amarelo 250g';
UPDATE produtos SET nome = 'Grãos Gesha 250g', descricao = 'Edição limitada, floral e cítrico.', origem_grao = 'Panamá' WHERE nome = 'Graos Gesha 250g';
UPDATE produtos SET descricao = 'Extração a frio por 18 horas.', origem_grao = 'Etiópia' WHERE nome = 'Cold Brew';

UPDATE insumos SET nome = 'Grãos Bourbon' WHERE nome = 'Graos Bourbon';
UPDATE insumos SET nome = 'Grãos Gesha' WHERE nome = 'Graos Gesha';

UPDATE usuarios SET nome = 'Barista João' WHERE nome = 'Barista Joao';
UPDATE enderecos SET cidade = 'São Paulo' WHERE cidade = 'Sao Paulo';

UPDATE avaliacoes SET comentario = 'Muito bom, só achei um pouco forte.' WHERE comentario = 'Muito bom, so achei um pouco forte.';
UPDATE avaliacoes SET comentario = 'Notas de chocolate incríveis.' WHERE comentario = 'Notas de chocolate incriveis.';
