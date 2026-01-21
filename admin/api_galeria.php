<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Log de início
error_log("=== API Galeria Requisição ===");
error_log("Ação: " . ($_GET['acao'] ?? 'nenhuma'));
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Sessão iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? 'sim' : 'não'));
error_log("É Editor: " . (Session::isEditor() ? 'sim' : 'não'));

// Verificar autenticação
if (!Session::isEditor()) {
    error_log("ERRO: Não autorizado - redirecionando");
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

$acao = $_GET['acao'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    switch ($acao) {
        case 'listar':
            listarImagens($db);
            break;
            
        case 'upload':
            fazerUpload($db);
            break;
            
        case 'gerar_ia':
            gerarImagemIA($db);
            break;
            
        default:
            echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida']);
    }
    
} catch (Exception $e) {
    error_log("Erro na API Galeria: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}

// ============= FUNÇÕES =============

function listarImagens($db) {
    $stmt = $db->query("
        SELECT caminho, tipo_arquivo, tamanho, criado_em,
               CASE WHEN descricao LIKE '%IA:%' THEN 1 ELSE 0 END as ia_gerada
        FROM arquivos 
        WHERE tipo_arquivo LIKE 'image/%' 
        ORDER BY criado_em DESC
        LIMIT 100
    ");
    
    $imagens = $stmt->fetchAll();
    
    echo json_encode([
        'sucesso' => true,
        'imagens' => $imagens
    ]);
}

function fazerUpload($db) {
    error_log("API Upload - Iniciando upload");
    error_log("FILES recebido: " . print_r($_FILES, true));
    
    if (!isset($_FILES['imagens']) || empty($_FILES['imagens']['name'])) {
        error_log("API Upload - Nenhuma imagem enviada");
        throw new Exception('Nenhuma imagem enviada');
    }
    
    $upload_dir = '../uploads/noticias/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $imagens_salvas = [];
    $files = $_FILES['imagens'];
    
    error_log("API Upload - Total de arquivos: " . (is_array($files['name']) ? count($files['name']) : 1));
    
    // Normalizar array de arquivos múltiplos
    $file_count = is_array($files['name']) ? count($files['name']) : 1;
    
    for ($i = 0; $i < $file_count; $i++) {
        $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
        
        if ($error !== UPLOAD_ERR_OK) {
            continue;
        }
        
        $tmp_name = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
        
        // Validar tamanho (5MB)
        if ($size > 5 * 1024 * 1024) {
            continue;
        }
        
        // Validar tipo
        $extensao = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            continue;
        }
        
        // Gerar nome único
        $nome_arquivo = uniqid() . '.' . $extensao;
        $caminho_completo = $upload_dir . $nome_arquivo;
        $caminho_relativo = 'uploads/noticias/' . $nome_arquivo;
        
        // Mover arquivo
        if (move_uploaded_file($tmp_name, $caminho_completo)) {
            // Salvar no banco
            $token = bin2hex(random_bytes(32));
            $mime_type = mime_content_type($caminho_completo);
            
            $stmt = $db->prepare("
                INSERT INTO arquivos (titulo, nome_arquivo, caminho, token, tipo_arquivo, tamanho, categoria, usuario_id)
                VALUES (?, ?, ?, ?, ?, ?, 'imagem_noticia', ?)
            ");
            $stmt->execute([
                $name,
                $nome_arquivo,
                $caminho_relativo,
                $token,
                $mime_type,
                $size,
                Session::getUserId()
            ]);
            
            $imagens_salvas[] = [
                'caminho' => $caminho_relativo,
                'tipo_arquivo' => $mime_type,
                'tamanho' => $size,
                'ia_gerada' => false
            ];
        }
    }
    
    if (empty($imagens_salvas)) {
        throw new Exception('Nenhuma imagem foi salva. Verifique formato e tamanho.');
    }
    
    echo json_encode([
        'sucesso' => true,
        'quantidade' => count($imagens_salvas),
        'imagens' => $imagens_salvas
    ]);
}

function gerarImagemIA($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $descricao = $input['descricao'] ?? '';
    
    if (empty($descricao)) {
        throw new Exception('Descrição é obrigatória');
    }
    
    // Buscar configurações de IA
    $stmt = $db->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('ia_api_provider', 'ia_api_key')");
    $configs = [];
    while ($row = $stmt->fetch()) {
        $configs[$row['chave']] = $row['valor'];
    }
    
    $provider = $configs['ia_api_provider'] ?? 'openai';
    $api_key = $configs['ia_api_key'] ?? '';
    
    if (empty($api_key)) {
        throw new Exception('API Key não configurada. Configure em Configurações.');
    }
    
    // Gerar imagem com IA
    $imagem_url = null;
    
    if ($provider === 'openai') {
        $imagem_url = gerarImagemOpenAI($api_key, $descricao);
    } else {
        throw new Exception('Provider de IA não suporta geração de imagens. Use OpenAI (DALL-E).');
    }
    
    if (!$imagem_url) {
        throw new Exception('Erro ao gerar imagem com IA');
    }
    
    // Baixar imagem gerada
    $upload_dir = '../uploads/noticias/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $nome_arquivo = 'ia_' . uniqid() . '.png';
    $caminho_completo = $upload_dir . $nome_arquivo;
    $caminho_relativo = 'uploads/noticias/' . $nome_arquivo;
    
    $imagem_conteudo = file_get_contents($imagem_url);
    if ($imagem_conteudo === false) {
        throw new Exception('Erro ao baixar imagem gerada');
    }
    
    file_put_contents($caminho_completo, $imagem_conteudo);
    
    // Salvar no banco
    $token = bin2hex(random_bytes(32));
    $tamanho = filesize($caminho_completo);
    
    $stmt = $db->prepare("
        INSERT INTO arquivos (titulo, nome_arquivo, caminho, token, tipo_arquivo, tamanho, categoria, descricao, usuario_id)
        VALUES (?, ?, ?, ?, 'image/png', ?, 'imagem_noticia', ?, ?)
    ");
    $stmt->execute([
        'Imagem gerada por IA',
        $nome_arquivo,
        $caminho_relativo,
        $token,
        $tamanho,
        'IA: ' . $descricao,
        Session::getUserId()
    ]);
    
    // Log de atividade
    $stmt = $db->prepare("
        INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address)
        VALUES (?, 'gerar_imagem_ia', ?, ?)
    ");
    $stmt->execute([
        Session::getUserId(),
        'Gerou imagem com IA: ' . substr($descricao, 0, 100),
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);
    
    echo json_encode([
        'sucesso' => true,
        'imagem' => [
            'caminho' => $caminho_relativo,
            'tipo_arquivo' => 'image/png',
            'tamanho' => $tamanho,
            'ia_gerada' => true
        ]
    ]);
}

function gerarImagemOpenAI($api_key, $descricao) {
    // Criar prompt otimizado para imagem institucional
    $prompt_otimizado = "Professional institutional photography for a government news article: " . $descricao . ". High quality, clean, modern, official government style.";
    
    $data = [
        'model' => 'dall-e-3',
        'prompt' => $prompt_otimizado,
        'n' => 1,
        'size' => '1024x1024',
        'quality' => 'standard'
    ];
    
    $ch = curl_init('https://api.openai.com/v1/images/generations');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        $error = json_decode($response, true);
        throw new Exception('Erro OpenAI: ' . ($error['error']['message'] ?? 'Erro desconhecido'));
    }
    
    $result = json_decode($response, true);
    
    if (!isset($result['data'][0]['url'])) {
        throw new Exception('Resposta inválida da API OpenAI');
    }
    
    return $result['data'][0]['url'];
}
