<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$pdo = Database::getInstance()->getConnection();
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todos';

try {
    if ($categoria === 'todos') {
        $sql = "SELECT c.*, cat.nome as categoria_nome, cat.cor as categoria_cor, cat.slug as categoria_slug 
                FROM convenios c 
                LEFT JOIN convenios_categorias cat ON c.categoria_id = cat.id 
                WHERE c.ativo = 1 
                ORDER BY c.ordem, c.nome";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT c.*, cat.nome as categoria_nome, cat.cor as categoria_cor, cat.slug as categoria_slug 
                FROM convenios c 
                LEFT JOIN convenios_categorias cat ON c.categoria_id = cat.id 
                WHERE c.ativo = 1 AND cat.slug = ? 
                ORDER BY c.ordem, c.nome";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categoria]);
    }
    
    $convenios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'sucesso' => true,
        'convenios' => $convenios
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
}
?>
