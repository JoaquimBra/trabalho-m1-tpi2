DROP SCHEMA IF EXISTS livraria;
CREATE DATABASE IF NOT EXISTS livraria;
USE livraria;

-- =========================
-- Tabelas independentes
-- =========================

CREATE TABLE usuario (
  id    INT AUTO_INCREMENT NOT NULL,
  nome  VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_email (email)
);

CREATE TABLE cliente (
  id              INT AUTO_INCREMENT NOT NULL,
  nome            VARCHAR(100) NOT NULL,
  data_nascimento DATE NOT NULL,
  cpf             VARCHAR(11)  NOT NULL,
  email           VARCHAR(100),
  telefone        VARCHAR(9),
  endereco        VARCHAR(255),
  PRIMARY KEY (id),
  UNIQUE KEY uq_cliente_cpf (cpf)
);

CREATE TABLE editora (
  id            INT AUTO_INCREMENT NOT NULL,
  nome          VARCHAR(100) NOT NULL,
  cnpj          VARCHAR(14)  NOT NULL,
  endereco      VARCHAR(255),
  telefone      VARCHAR(11),
  valor_minimo  DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_editora_cnpj (cnpj)
);

CREATE TABLE autor (
  id              INT AUTO_INCREMENT NOT NULL,
  nome            VARCHAR(100) NOT NULL,
  nacionalidade   VARCHAR(100),
  data_nascimento DATE NOT NULL,
  resumo          TEXT,
  genero          VARCHAR(30),
  PRIMARY KEY (id)
);

CREATE TABLE fornecedor (
  id                 INT AUTO_INCREMENT NOT NULL,
  nome               VARCHAR(100) NOT NULL,
  email              VARCHAR(100),
  prazo_entrega      VARCHAR(30) NOT NULL,
  condicao_pagamento TEXT NOT NULL,
  valor_minimo       DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id)
);

-- =========================
-- Tabelas dependentes
-- =========================

CREATE TABLE estoque (
  id              INT AUTO_INCREMENT NOT NULL,
  nome            VARCHAR(100) NOT NULL,
  isbn            VARCHAR(17) NOT NULL,
  qtde            INT NOT NULL DEFAULT 0,
  qtde_minima     INT NOT NULL DEFAULT 0,
  localizacao     VARCHAR(100) NOT NULL,
  data_reposicao  DATE NOT NULL,
  custo_unitario  DECIMAL(10,2) NOT NULL,
  id_editora      INT NOT NULL,
  id_autor        INT NOT NULL,
  id_fornecedor   INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_estoque_isbn (isbn),
  CONSTRAINT fk_editora_id_estoque
    FOREIGN KEY (id_editora) REFERENCES editora (id),
  CONSTRAINT fk_autor_id_estoque
    FOREIGN KEY (id_autor) REFERENCES autor (id),
  CONSTRAINT fk_fornecedor_id_estoque
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedor (id)
);

CREATE TABLE venda (
  id          INT AUTO_INCREMENT NOT NULL,
  data_venda  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  pagamento   ENUM('dinheiro','pix','cartao_credito','cartao_debito','boleto') NOT NULL,
  desconto    DECIMAL(10,2) DEFAULT 0.00,
  cupom_fiscal INT,
  id_cliente  INT NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_cliente_id_venda
    FOREIGN KEY (id_cliente) REFERENCES cliente (id)
);

CREATE TABLE item_venda (
  id          INT AUTO_INCREMENT NOT NULL,
  id_estoque  INT NOT NULL,
  id_venda    INT NOT NULL,
  quantidade  INT NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  CONSTRAINT fk_estoque_id_item_venda
    FOREIGN KEY (id_estoque) REFERENCES estoque (id),
  CONSTRAINT fk_venda_id_item_venda
    FOREIGN KEY (id_venda) REFERENCES venda (id) ON DELETE CASCADE
);

-- =========================
-- USUARIO (senha padrao: 123456)
-- =========================

INSERT INTO usuario (nome, email, senha) VALUES
('Administrador', 'admin@livraria.com', '$2y$12$j2k4b13llgXBhaZMdKLmvuJYI/o7zRuIjX5lWC0gR074bYEG0CcJm');

-- =========================
-- CLIENTE
-- =========================

INSERT INTO cliente (nome, data_nascimento, cpf, email, telefone, endereco) VALUES
('João Silva', '1995-04-12', '12345678901', 'joao@gmail.com', '999990001', 'Rua das Flores, 120'),
('Maria Oliveira', '1990-08-25', '23456789012', 'maria@gmail.com', '999990002', 'Av. Brasil, 450'),
('Pedro Santos', '2001-02-10', '34567890123', 'pedro@gmail.com', '999990003', 'Rua Central, 85'),
('Ana Costa', '1988-11-30', '45678901234', 'ana@gmail.com', '999990004', 'Rua das Acácias, 200'),
('Lucas Pereira', '1999-06-17', '56789012345', 'lucas@gmail.com', '999990005', 'Av. Minas Gerais, 700');


-- =========================
-- EDITORA
-- =========================

INSERT INTO editora (nome, cnpj, endereco, telefone, valor_minimo) VALUES
('Editora Moderna', '12345678000190', 'Rua A, 100', '912415019', 500.00),
('Companhia das Letras', '23456789000191', 'Av. B, 200', '988880002', 750.00),
('Editora Rocco', '34567890000192', 'Rua C, 300', '988880003', 600.00),
('Editora Intrínseca', '45678901000193', 'Av. D, 400', '988880004', 450.00),
('Editora Arqueiro', '56789012000194', 'Rua E, 500', '988880005', 550.00);


-- =========================
-- AUTOR
-- =========================

INSERT INTO autor (nome, nacionalidade, data_nascimento, resumo, genero) VALUES
('Machado de Assis', 'Brasileira', '1839-06-21', 'Escritor brasileiro, autor de Dom Casmurro.', 'Romance'),
('Clarice Lispector', 'Brasileira', '1920-12-10', 'Escritora brasileira conhecida por sua literatura introspectiva.', 'Ficção'),
('George Orwell', 'Britânica', '1903-06-25', 'Escritor e jornalista, autor de 1984 e A Revolução dos Bichos.', 'Distopia'),
('J. R. R. Tolkien', 'Britânica', '1892-01-03', 'Escritor conhecido pela obra O Senhor dos Anéis.', 'Fantasia'),
('Agatha Christie', 'Britânica', '1890-09-15', 'Escritora conhecida por seus romances policiais.', 'Suspense');

-- =========================
-- FORNECEDOR
-- =========================

INSERT INTO fornecedor (nome, email, prazo_entrega, condicao_pagamento, valor_minimo) VALUES
('Distribuidora Pageturner Ltda', 'pedidos@pageturner.com.br', '5 dias úteis', '30/60 dias', 500.00),
('Livros & Cia Atacado', 'vendas@livroscia.com.br', '7 dias úteis', '30 dias', 350.00),
('Papirus Distribuição de Livros', 'compras@papirus.com.br', '3 dias úteis', 'À vista com 5% de desconto', 200.00),
('Central do Livro S.A.', 'pedidos@centraldolivro.com.br', '10 dias úteis', '30/60/90 dias', 1000.00),
('Editorial Horizonte Atacadista', 'comercial@horizonte.com.br', '4 dias úteis', '28 dias', 450.00),
('Mundo das Letras Distribuidora', 'contato@mundodasletras.com.br', '6 dias úteis', '15/30 dias', 300.00),
('Sebo Nacional Fornecimentos', NULL, '12 dias úteis', 'À vista', 150.00),
('Livraria Express Logística', 'pedidos@livrariaexpress.com.br', '2 dias úteis', 'Boleto em 21 dias', 750.00),
('Grupo Saber Distribuição', 'atendimento@gruposaber.com.br', '8 dias úteis', '30/60 dias, entrada de 20%', 800.00),
('Biblos Atacado de Livros', 'biblos@biblosatacado.com.br', '5 dias úteis', 'Cartão de crédito em até 3x', 250.00);


-- =========================
-- ESTOQUE
-- =========================

-- Os IDs de fornecedor abaixo pressupõem que já existam
-- fornecedores cadastrados com IDs de 1 a 5.

INSERT INTO estoque (
    nome,
    isbn,
    qtde,
    qtde_minima,
    localizacao,
    data_reposicao,
    custo_unitario,
    id_editora,
    id_autor,
    id_fornecedor
) VALUES
('Dom Casmurro', '9788535902778', 30, 5, 'Prateleira A1', '2026-10-10', 25.90, 1, 1, 1),
('A Hora da Estrela', '9788532508126', 20, 5, 'Prateleira A2', '2026-10-12', 22.50, 2, 2, 2),
('1984', '9788535914849', 40, 10, 'Prateleira B1', '2026-10-15', 29.90, 3, 3, 3),
('O Senhor dos Anéis', '9788533613379', 15, 5, 'Prateleira B2', '2026-10-18', 65.00, 4, 4, 4),
('Assassinato no Expresso do Oriente', '9788525432345', 25, 5, 'Prateleira C1', '2026-10-20', 32.90, 5, 5, 5);


-- =========================
-- VENDA
-- =========================

INSERT INTO venda (data_venda, pagamento, desconto, cupom_fiscal, id_cliente) VALUES
('2026-10-01 10:30:00', 'pix', 5.00, 1001, 1),
('2026-10-02 14:45:00', 'dinheiro', 0.00, 1002, 2),
('2026-10-03 16:20:00', 'cartao_credito', 10.00, 1003, 3),
('2026-10-04 11:15:00', 'cartao_debito', 2.50, 1004, 4),
('2026-10-05 18:00:00', 'boleto', 0.00, 1005, 5);


-- =========================
-- ITEM_VENDA
-- =========================

INSERT INTO item_venda (id_estoque, id_venda, quantidade) VALUES
(1, 1, 1),
(2, 1, 1),
(3, 2, 1),
(1, 3, 2),
(4, 3, 1),
(5, 4, 1),
(2, 5, 1);
