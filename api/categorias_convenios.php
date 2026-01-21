<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$pdo = Database::getInstance()->getConnection();

try {
    $sql = "SELECT * FROM convenios_categorias WHERE ativo = 1 ORDER BY ordem, nome";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'sucesso' => true,
        'categorias' => $categorias
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
}
?>
