<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!Session::isEditor()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    Session::setFlash('mensagem', 'ID da notícia não fornecido.');
    Session::setFlash('tipo_mensagem', 'erro');
    header('Location: noticias.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar notícia
    $stmt = $db->prepare("SELECT * FROM noticias WHERE id = ?");
    $stmt->execute([$id]);
    $noticia = $stmt->fetch();
    
    if (!$noticia) {
        throw new Exception('Notícia não encontrada.');
    }
    
    // Deletar imagem se existir
    if (!empty($noticia['imagem_destaque'])) {
        $caminho_imagem = '../' . $noticia['imagem_destaque'];
        if (file_exists($caminho_imagem)) {
            unlink($caminho_imagem);
        }
    }
    
    // Deletar notícia
    $stmt = $db->prepare("DELETE FROM noticias WHERE id = ?");
    $stmt->execute([$id]);
    
    // Registrar log
    $stmt = $db->prepare("
        INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) 
        VALUES (?, 'noticia_deletada', ?, ?)
    ");
    $stmt->execute([
        Session::getUserId(),
        "Notícia deletada: {$noticia['titulo']}",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    Session::setFlash('mensagem', 'Notícia deletada com sucesso!');
    Session::setFlash('tipo_mensagem', 'sucesso');
    
} catch (Exception $e) {
    Session::setFlash('mensagem', $e->getMessage());
    Session::setFlash('tipo_mensagem', 'erro');
} catch (PDOException $e) {
    error_log("Erro ao deletar notícia: " . $e->getMessage());
    Session::setFlash('mensagem', 'Erro ao deletar notícia.');
    Session::setFlash('tipo_mensagem', 'erro');
}

header('Location: noticias.php');
exit;
