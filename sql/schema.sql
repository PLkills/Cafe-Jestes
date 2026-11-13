-- =====================================================
-- Cafeteria Gourmet Virtual Delivery - Projeto Fisico
-- SGBD: MySQL 5.7+ / MariaDB 10.3+
-- Obs.: em hospedagem gratuita, o banco ja vem criado:
--       remova as linhas CREATE DATABASE / USE e importe pelo phpMyAdmin.
-- =====================================================
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS cafeteria_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cafeteria_db;

DROP TABLE IF EXISTS notificacoes, pedidos_favoritos, avaliacoes, entregas, pagamentos,
  itens_pedido, pedidos, produtos, categorias, insumos, enderecos, usuarios;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  telefone VARCHAR(20),
  perfil ENUM('cliente','gerente','barista','entregador','administrador') NOT NULL DEFAULT 'cliente',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_perfil (perfil)
) ENGINE=InnoDB;

CREATE TABLE enderecos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  cep VARCHAR(9) NOT NULL,
  logradouro VARCHAR(150) NOT NULL,
  numero VARCHAR(10) NOT NULL,
  complemento VARCHAR(80),
  bairro VARCHAR(80) NOT NULL,
  cidade VARCHAR(80) NOT NULL,
  uf CHAR(2) NOT NULL,
  instrucoes VARCHAR(255),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nome VARCHAR(120) NOT NULL,
  descricao TEXT,
  origem_grao VARCHAR(80),
  intensidade TINYINT NOT NULL DEFAULT 3 COMMENT '1 a 5',
  preco DECIMAL(10,2) NOT NULL,
  preco_promocional DECIMAL(10,2) NULL,
  imagem VARCHAR(255),
  estoque INT NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  INDEX idx_ativo (ativo)
) ENGINE=InnoDB;

CREATE TABLE insumos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  unidade VARCHAR(10) NOT NULL DEFAULT 'kg',
  quantidade DECIMAL(10,2) NOT NULL DEFAULT 0,
  minimo DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  endereco_id INT NOT NULL,
  tipo_entrega ENUM('imediata','agendada') NOT NULL DEFAULT 'imediata',
  data_agendada DATETIME NULL,
  status ENUM('aguardando_preparo','em_preparo','pronto','a_caminho','entregue','cancelado') NOT NULL DEFAULT 'aguardando_preparo',
  subtotal DECIMAL(10,2) NOT NULL,
  taxa_entrega DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
  FOREIGN KEY (endereco_id) REFERENCES enderecos(id),
  INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE itens_pedido (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  preco_unitario DECIMAL(10,2) NOT NULL,
  tipo_leite ENUM('integral','desnatado','vegetal','sem_leite') NOT NULL DEFAULT 'integral',
  acucar ENUM('sem_acucar','pouco','normal','muito') NOT NULL DEFAULT 'normal',
  intensidade ENUM('suave','media','forte') NOT NULL DEFAULT 'media',
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

CREATE TABLE pagamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL UNIQUE,
  metodo ENUM('cartao','pix') NOT NULL,
  status ENUM('pendente','aprovado','recusado') NOT NULL DEFAULT 'pendente',
  valor DECIMAL(10,2) NOT NULL,
  codigo_pix VARCHAR(255) NULL,
  cartao_ultimos4 CHAR(4) NULL COMMENT 'Nunca armazenar o numero completo',
  pago_em DATETIME NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE entregas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL UNIQUE,
  entregador_id INT NULL,
  status ENUM('aguardando','a_caminho','entregue') NOT NULL DEFAULT 'aguardando',
  atribuido_em DATETIME NULL,
  entregue_em DATETIME NULL,
  nota_cliente TINYINT NULL COMMENT '1 a 5',
  comentario_cliente VARCHAR(255) NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (entregador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE avaliacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  produto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  nota TINYINT NOT NULL,
  comentario VARCHAR(500),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT chk_nota CHECK (nota BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE pedidos_favoritos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  nome VARCHAR(80) NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  tipo_leite VARCHAR(20) NOT NULL DEFAULT 'integral',
  acucar VARCHAR(20) NOT NULL DEFAULT 'normal',
  intensidade VARCHAR(20) NOT NULL DEFAULT 'media',
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notificacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  mensagem VARCHAR(255) NOT NULL,
  lida TINYINT(1) NOT NULL DEFAULT 0,
  criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= DADOS INICIAIS =================
-- Senha de TODOS os usuarios de exemplo: senha123  (trocar antes de divulgar!)
INSERT INTO usuarios (nome,email,senha_hash,telefone,perfil) VALUES
('Administrador','admin@cafeteria.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000001','administrador'),
('Gerente Maria','gerente@cafeteria.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000002','gerente'),
('Barista Joao','barista@cafeteria.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000003','barista'),
('Entregador Carlos','entregador@cafeteria.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000004','entregador'),
('Entregadora Ana','entregador2@cafeteria.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000005','entregador'),
('Cliente Teste','cliente@cafeteria.com','$2y$10$MdSkZicneKF6WjsXzw9fyeE2Qu21t3lZo0VAw031B./HlOvuT9IOK','11900000006','cliente');

INSERT INTO categorias (nome) VALUES ('Espresso'),('Cappuccino'),('Graos'),('Gelados');

INSERT INTO produtos (categoria_id,nome,descricao,origem_grao,intensidade,preco,preco_promocional,imagem,estoque) VALUES
(1,'Espresso Classico','Cafe curto, encorpado, com crema dourada.','Cerrado Mineiro',5,8.50,NULL,'espresso.jpg',100),
(1,'Espresso Duplo','Dose dupla para quem precisa de energia.','Cerrado Mineiro',5,12.00,10.00,'espresso-duplo.jpg',100),
(2,'Cappuccino Cremoso','Espresso, leite vaporizado e espuma aveludada.','Sul de Minas',3,14.00,NULL,'cappuccino.jpg',80),
(2,'Cappuccino de Canela','Cappuccino com toque de canela do Ceilao.','Sul de Minas',3,15.50,NULL,'cappuccino-canela.jpg',80),
(3,'Graos Bourbon Amarelo 250g','Notas de caramelo e chocolate, torra media.','Mogiana',3,38.00,34.00,'bourbon.jpg',40),
(3,'Graos Gesha 250g','Edicao limitada, floral e citrico.','Panama',2,89.90,NULL,'gesha.jpg',15),
(4,'Cold Brew','Extracao a frio por 18 horas.','Etiopia',3,16.00,NULL,'coldbrew.jpg',60);

INSERT INTO insumos (nome,unidade,quantidade,minimo) VALUES
('Graos Bourbon','kg',25.00,5.00),('Graos Gesha','kg',4.00,2.00),('Leite integral','l',40.00,10.00),('Leite vegetal','l',15.00,5.00);

INSERT INTO enderecos (usuario_id,cep,logradouro,numero,bairro,cidade,uf,instrucoes) VALUES
(6,'01310-100','Avenida Paulista','1000','Bela Vista','Sao Paulo','SP','Deixar na portaria');
