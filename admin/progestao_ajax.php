<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Verificar se está logado
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$db = Database::getInstance()->getConnection();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'mover_secao':
            $id = (int)($_POST['id'] ?? 0);
            $direcao = $_POST['direcao'] ?? '';
            
            if ($id <= 0 || !in_array($direcao, ['cima', 'baixo'])) {
                throw new Exception('Parâmetros inválidos');
            }
            
            // Buscar seção atual
            $stmt = $db->prepare("SELECT ordem FROM progestao_secoes WHERE id = ?");
            $stmt->execute([$id]);
            $secao = $stmt->fetch();
            
            if (!$secao) {
                throw new Exception('Seção não encontrada');
            }
            
            $ordem_atual = $secao['ordem'];
            
            // Buscar seção adjacente
            if ($direcao === 'cima') {
                $stmt = $db->prepare("SELECT id, ordem FROM progestao_secoes WHERE ordem < ? ORDER BY ordem DESC LIMIT 1");
            } else {
                $stmt = $db->prepare("SELECT id, ordem FROM progestao_secoes WHERE ordem > ? ORDER BY ordem ASC LIMIT 1");
            }
            $stmt->execute([$ordem_atual]);
            $secao_adjacente = $stmt->fetch();
            
            if (!$secao_adjacente) {
                echo json_encode(['success' => false, 'message' => 'Não é possível mover nesta direção']);
                exit;
            }
            
            // Trocar ordens
            $db->beginTransaction();
            
            $stmt = $db->prepare("UPDATE progestao_secoes SET ordem = ? WHERE id = ?");
            $stmt->execute([$secao_adjacente['ordem'], $id]);
            
            $stmt = $db->prepare("UPDATE progestao_secoes SET ordem = ? WHERE id = ?");
            $stmt->execute([$ordem_atual, $secao_adjacente['id']]);
            
            $db->commit();
            
            echo json_encode(['success' => true]);
            break;
            
        case 'excluir_secao':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            $stmt = $db->prepare("DELETE FROM progestao_secoes WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'excluir_card':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            // Buscar arquivo para deletar
            $stmt = $db->prepare("SELECT arquivo FROM progestao_cards WHERE id = ?");
            $stmt->execute([$id]);
            $card = $stmt->fetch();
            
            if ($card && $card['arquivo']) {
                $arquivo_path = '../uploads/progestao/' . $card['arquivo'];
                if (file_exists($arquivo_path)) {
                    unlink($arquivo_path);
                }
            }
            
            $stmt = $db->prepare("DELETE FROM progestao_cards WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Ação inválida');
    }
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Erro em progestao_ajax: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
