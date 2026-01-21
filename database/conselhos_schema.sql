-- Tabela para armazenar os conselhos e comitês
CREATE TABLE IF NOT EXISTS conselhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    descricao TEXT,
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela para seções (Calendário de Reuniões, Atas, etc)
CREATE TABLE IF NOT EXISTS conselho_secoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conselho_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    ativo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conselho_id) REFERENCES conselhos(id) ON DELETE CASCADE
);

-- Tabela para anos dentro de cada seção
CREATE TABLE IF NOT EXISTS conselho_anos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secao_id INT NOT NULL,
    ano INT NOT NULL,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (secao_id) REFERENCES conselho_secoes(id) ON DELETE CASCADE
);

-- Tabela para arquivos dentro de cada ano
CREATE TABLE IF NOT EXISTS conselho_arquivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ano_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ano_id) REFERENCES conselho_anos(id) ON DELETE CASCADE
);

-- Inserir conselhos padrão
INSERT INTO conselhos (nome, slug, descricao, ordem) VALUES
('Conselho Municipal Previdenciário – CMP', 'conselho-municipal-previdenciario', 'O órgão colegiado de deliberação superior da previdência municipal.', 1),
('Conselho Fiscal – CF', 'conselho-fiscal', 'O Conselho Fiscal é o órgão de fiscalização dos atos de gestão do GOIANIAPREV.', 2),
('Comitê de Investimentos', 'comite-investimentos', 'Tem por finalidade propor, acompanhar, assessorar e auxiliar na elaboração e execução da Política de Investimento do RPPS.', 3);

-- Inserir seções exemplo para Conselho Fiscal
INSERT INTO conselho_secoes (conselho_id, titulo, ordem) VALUES
(2, 'Calendário de Reuniões', 1),
(2, 'Atas', 2);

-- Inserir anos exemplo
INSERT INTO conselho_anos (secao_id, ano, ordem) VALUES
(1, 2025, 1),
(1, 2024, 2),
(2, 2025, 1),
(2, 2024, 2),
(2, 2023, 3),
(2, 2022, 4),
(2, 2021, 5);
