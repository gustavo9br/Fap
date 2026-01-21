<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$pagina = $_GET['pagina'] ?? 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;
$categoria = $_GET['categoria'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "
        SELECT n.*, u.nome as autor_nome
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        WHERE n.status = 'publicado'
    ";
    
    $params = [];
    
    if ($categoria) {
        $sql .= " AND n.categoria = ?";
        $params[] = $categoria;
    }
    
    $sql .= " ORDER BY n.publicado_em DESC LIMIT ? OFFSET ?";
    $params[] = $por_pagina;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar se tem mais notícias
    $sql_count = "SELECT COUNT(*) FROM noticias WHERE status = 'publicado'";
    if ($categoria) {
        $sql_count .= " AND categoria = ?";
        $stmt_count = $db->prepare($sql_count);
        $stmt_count->execute([$categoria]);
    } else {
        $stmt_count = $db->query($sql_count);
    }
    $total = $stmt_count->fetchColumn();
    $tem_mais = ($pagina * $por_pagina) < $total;
    
    echo json_encode([
        'sucesso' => true,
        'noticias' => $noticias,
        'tem_mais' => $tem_mais,
        'pagina_atual' => (int)$pagina,
        'total' => (int)$total
    ]);
    
} catch (Exception $e) {
    error_log("Erro API notícias: " . $e->getMessage());
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao carregar notícias'
    ]);
}
