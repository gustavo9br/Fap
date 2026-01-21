<?php
/**
 * Sistema de Download Seguro
 * Arquivos só podem ser acessados via token único
 */

require_once '../config/database.php';

// Pegar o token da URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    die('Token inválido');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar arquivo pelo token
    $stmt = $db->prepare("
        SELECT id, titulo, nome_arquivo, caminho, tipo_arquivo, tamanho
        FROM arquivos 
        WHERE token = ?
    ");
    $stmt->execute([$token]);
    $arquivo = $stmt->fetch();
    
    if (!$arquivo) {
        http_response_code(404);
        die('Arquivo não encontrado');
    }
    
    $caminho_completo = __DIR__ . '/../' . $arquivo['caminho'];
    
    if (!file_exists($caminho_completo)) {
        http_response_code(404);
        die('Arquivo não encontrado no servidor');
    }
    
    // Incrementar contador de downloads
    $stmt = $db->prepare("UPDATE arquivos SET downloads = downloads + 1 WHERE id = ?");
    $stmt->execute([$arquivo['id']]);
    
    // Registrar log de download
    $stmt = $db->prepare("
        INSERT INTO logs_atividades (acao, descricao, ip_address) 
        VALUES ('download', ?, ?)
    ");
    $stmt->execute([
        "Download do arquivo: {$arquivo['titulo']} (Token: $token)",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Headers para download
    header('Content-Type: ' . ($arquivo['tipo_arquivo'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . $arquivo['nome_arquivo'] . '"');
    header('Content-Length: ' . filesize($caminho_completo));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Limpar buffer de saída
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Enviar arquivo
    readfile($caminho_completo);
    exit;
    
} catch (PDOException $e) {
    error_log("Erro no download: " . $e->getMessage());
    http_response_code(500);
    die('Erro ao processar download');
}
