<?php
/**
 * Funções auxiliares do sistema
 */

/**
 * Normaliza o caminho de um arquivo para garantir que seja absoluto (comece com /)
 * Isso resolve o problema de caminhos relativos em URLs com rewrite rules
 * 
 * @param string $caminho Caminho do arquivo (pode ser relativo ou absoluto)
 * @return string Caminho normalizado sempre começando com /
 */
function normalizar_caminho_arquivo($caminho) {
    if (empty($caminho)) {
        return '';
    }
    
    // Remove espaços
    $caminho = trim($caminho);
    
    // Se já começa com /, retorna como está
    if (strpos($caminho, '/') === 0) {
        return $caminho;
    }
    
    // Se não começa com /, adiciona
    return '/' . $caminho;
}
