<?php
/**
 * Script para adicionar coluna 'mes' nas tabelas balancetes e demonstrativos
 * Execute este arquivo uma vez pelo navegador para adicionar a coluna
 */

require_once '../config/database.php';

$pdo = Database::getInstance()->getConnection();

try {
    // Adicionar coluna 'mes' na tabela balancetes se não existir
    $stmt = $pdo->query("SHOW COLUMNS FROM balancetes LIKE 'mes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE balancetes ADD COLUMN mes VARCHAR(100) NULL AFTER ano");
        echo "✓ Coluna 'mes' adicionada na tabela 'balancetes'<br>";
    } else {
        echo "✓ Coluna 'mes' já existe na tabela 'balancetes'<br>";
    }
    
    // Adicionar coluna 'mes' na tabela demonstrativos se não existir
    $stmt = $pdo->query("SHOW COLUMNS FROM demonstrativos LIKE 'mes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE demonstrativos ADD COLUMN mes VARCHAR(100) NULL AFTER ano");
        echo "✓ Coluna 'mes' adicionada na tabela 'demonstrativos'<br>";
    } else {
        echo "✓ Coluna 'mes' já existe na tabela 'demonstrativos'<br>";
    }
    
    echo "<br><strong>Atualização concluída com sucesso!</strong><br>";
    echo "<br><a href='balancetes.php'>Voltar para Balancetes</a>";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
