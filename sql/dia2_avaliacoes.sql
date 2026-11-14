-- Dia 2: executar no phpMyAdmin DEPOIS do schema.sql (nao apaga dados).
SET NAMES utf8mb4;

-- Uma avaliacao por cliente e produto
ALTER TABLE avaliacoes ADD UNIQUE KEY uq_aval_usuario_produto (produto_id, usuario_id);

-- Clientes de demonstracao (senha: senha123)
INSERT IGNORE INTO usuarios (nome,email,senha_hash,telefone,perfil) VALUES
('Luciana Prado','luciana@exemplo.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000011','cliente'),
('Rafael Mendes','rafael@exemplo.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000012','cliente');

-- Avaliacoes de exemplo (para a vitrine mostrar medias)
INSERT IGNORE INTO avaliacoes (produto_id,usuario_id,nota,comentario)
SELECT p.id, u.id, v.nota, v.comentario FROM (
  SELECT 'Espresso Clássico' pn,'cliente@cafeteria.com' em,5 nota,'Encorpado e com crema perfeita.' comentario UNION ALL
  SELECT 'Espresso Clássico','luciana@exemplo.com',4,'Muito bom, só achei um pouco forte.' UNION ALL
  SELECT 'Cappuccino Cremoso','cliente@cafeteria.com',5,'Espuma aveludada, meu favorito.' UNION ALL
  SELECT 'Cappuccino Cremoso','rafael@exemplo.com',4,'Cremoso e equilibrado.' UNION ALL
  SELECT 'Cold Brew','luciana@exemplo.com',5,'Suave e refrescante.' UNION ALL
  SELECT 'Grãos Bourbon Amarelo 250g','rafael@exemplo.com',5,'Notas de chocolate incríveis.'
) v JOIN produtos p ON p.nome = v.pn JOIN usuarios u ON u.email = v.em;
