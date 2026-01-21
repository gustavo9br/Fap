-- Tabela principal dos cards financeiros (seção Demonstrativos contábeis)
CREATE TABLE IF NOT EXISTS financeiro_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    icone TEXT DEFAULT NULL,
    tipo ENUM('pdf', 'pagina') NOT NULL DEFAULT 'pagina',
    arquivo_pdf VARCHAR(500) DEFAULT NULL,
    link_externo VARCHAR(500) DEFAULT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    ordem INT DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de seções das páginas financeiras (igual conselho_secoes)
CREATE TABLE IF NOT EXISTS financeiro_secoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    card_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (card_id) REFERENCES financeiro_cards(id) ON DELETE CASCADE
);

-- Tabela de anos das seções financeiras (igual conselho_anos)
CREATE TABLE IF NOT EXISTS financeiro_anos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secao_id INT NOT NULL,
    ano INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (secao_id) REFERENCES financeiro_secoes(id) ON DELETE CASCADE
);

-- Tabela de arquivos dos anos (igual conselho_arquivos)
CREATE TABLE IF NOT EXISTS financeiro_arquivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ano_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    arquivo_path VARCHAR(500) NOT NULL,
    ordem INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ano_id) REFERENCES financeiro_anos(id) ON DELETE CASCADE
);

-- Inserir ícones padrão disponíveis
-- Os ícones são armazenados como SVG inline para flexibilidade
