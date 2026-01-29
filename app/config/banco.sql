CREATE DATABASE hotel;

USE hotel;

CREATE TABLE endereco (
    endereco_id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_endereco VARCHAR(50) DEFAULT 'principal',
    current_country VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    neighborhood VARCHAR(50) NOT NULL,
    street VARCHAR(50) NOT NULL,
    address_number VARCHAR(50) NOT NULL, 
    cep VARCHAR(14),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cadastro (
    cadastro_id INT AUTO_INCREMENT PRIMARY KEY,
    endereco INT,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    full_name VARCHAR(100),
    cpf_cnpj VARCHAR(14) UNIQUE,                     
    rg VARCHAR(20) UNIQUE,
    birth_date DATE,
    gender ENUM('masculino', 'feminino', 'outro'),
    ethnicity ENUM('branco', 'negro', 'pardo', 'indigena'),                
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (endereco) REFERENCES endereco(endereco_id)
);

CREATE TABLE empresa (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    endereco INT,
    cadastro VARCHAR(14),
    data_fundacao DATE,
    telefone_comercial VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cadastro) REFERENCES cadastro(cpf_cnpj)
);

CREATE TABLE login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cadastro VARCHAR(14),
    empresa INT,
    senha VARCHAR(255) NOT NULL,             
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cadastro) REFERENCES cadastro(cpf_cnpj)
);

CREATE TABLE quarto (
    quarto_id INT AUTO_INCREMENT PRIMARY KEY,
    hospedagem_id INT,
    numero INT NOT NULL UNIQUE,
    room_type ENUM('ar-condicionado', 'ventilador'),
    room_status ENUM('livre', 'ocupado') DEFAULT 'livre',
    clean_status ENUM('limpo', 'sujo') DEFAULT 'limpo',
    bed_quantity ENUM('single', 'duplo', 'triplo', 'quaduplo'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reserva (
    reserva_id INT AUTO_INCREMENT PRIMARY KEY,
    cadastro VARCHAR(14) NOT NULL,
    quarto INT,
    data_checkin DATE NOT NULL,
    data_checkout DATE NOT NULL,
    situacao ENUM('pendente', 'cancelado'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cadastro) REFERENCES cadastro(cpf_cnpj),
    FOREIGN KEY (quarto) REFERENCES quarto(numero)
);


CREATE TABLE hospedagem (
    hospedagem_id INT AUTO_INCREMENT PRIMARY KEY,
    reserva INT,
    hospedes INT,
    quarto INT,
    data_checkin DATE NOT NULL,
    data_checkout DATE NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    observacoes LONGTEXT,
    situacao ENUM('ativa', 'encerrada'),
    usuario_responsavel VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospedes) REFERENCES cadastro(cadastro_id),
    FOREIGN KEY (reserva) REFERENCES reserva(reserva_id),
    FOREIGN KEY (quarto) REFERENCES quarto(quarto_id)
);

CREATE TABLE hospedagem_acompanhantes (
    acompanhantes_id INT AUTO_INCREMENT PRIMARY KEY,
    hospedagem_id INT NOT NULL, 
    cadastro_id INT NOT NULL,   
    FOREIGN KEY (hospedagem_id) REFERENCES hospedagem(hospedagem_id),
    FOREIGN KEY (cadastro_id) REFERENCES cadastro(cadastro_id)
);

CREATE TABLE produto (
    produto_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco_venda DECIMAL(10, 2) NOT NULL,
    estoque_atual INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE hospedagem_consumo (
    consumo_id INT AUTO_INCREMENT PRIMARY KEY,
    hospedagem_id INT NOT NULL,     
    hospede_id INT NOT NULL,        
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario_pago DECIMAL(10, 2) NOT NULL,
    data_consumo DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospedagem_id) REFERENCES hospedagem(hospedagem_id),
    FOREIGN KEY (hospede_id) REFERENCES cadastro(cadastro_id),
    FOREIGN KEY (produto_id) REFERENCES produto(produto_id)
);
