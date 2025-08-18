CREATE DATABASE futebol_db;
USE futebol_db;

CREATE TABLE times (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL
);

CREATE TABLE jogadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    posicao VARCHAR(30) NOT NULL,
    numero_camisa INT NOT NULL,
    time_id INT,
    FOREIGN KEY (time_id) REFERENCES times(id)
);

CREATE TABLE partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time_casa_id INT NOT NULL,
    time_fora_id INT NOT NULL,
    data_jogo DATE NOT NULL,
    gols_casa INT DEFAULT 0,
    gols_fora INT DEFAULT 0,
    FOREIGN KEY (time_casa_id) REFERENCES times(id),
    FOREIGN KEY (time_fora_id) REFERENCES times(id)
);

-- dados exemplos
INSERT INTO times (nome, cidade) VALUES
('Atlético Central', 'Belo Horizonte'),
('Grêmio', 'Porto Alegre'),
('Corinthians', 'São Paulo');

INSERT INTO jogadores (nome, posicao, numero_camisa, time_id) VALUES
('Carlos Silva', 'GOL', 1, 1),
('Rafael Souza', 'ATA', 9, 1),
('João Lima', 'MEI', 8, 2),
('Pedro Rocha', 'ZAG', 4, 3);

INSERT INTO partidas (time_casa_id, time_fora_id, data_jogo, gols_casa, gols_fora) VALUES
(1, 2, '2025-08-20', 2, 1),
(3, 1, '2025-08-27', 0, 0);