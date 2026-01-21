-- Tabelas para o Sistema de Pró-Gestão
-- FAP Pádua - Sistema de Gerenciamento

-- Tabela de seções do Pró-Gestão
CREATE TABLE IF NOT EXISTS progestao_secoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ordem (ordem),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cards/itens do Pró-Gestão
CREATE TABLE IF NOT EXISTS progestao_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secao_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    icone VARCHAR(100) DEFAULT NULL,
    tipo_conteudo ENUM('link', 'arquivo') NOT NULL DEFAULT 'link',
    link VARCHAR(500) DEFAULT NULL,
    arquivo VARCHAR(255) DEFAULT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (secao_id) REFERENCES progestao_secoes(id) ON DELETE CASCADE,
    INDEX idx_secao_ordem (secao_id, ordem),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
