<?php
require_once '../config/session.php';
require_once '../config/database.php';

if (Session::isLoggedIn()) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Registrar logout
        $stmt = $db->prepare("
            INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) 
            VALUES (?, 'logout', 'Logout realizado', ?)
        ");
        $stmt->execute([Session::getUserId(), $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        error_log("Erro ao registrar logout: " . $e->getMessage());
    }
}

Session::destroy();
header('Location: login.php');
exit;
