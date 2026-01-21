<?php
/**
 * API de Integração com IA
 * Suporta OpenAI, Anthropic Claude e Google Gemini
 */

require_once '../config/database.php';
require_once '../config/session.php';

// Verificar autenticação
if (!Session::isEditor()) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

// Pegar dados do POST
$dados = json_decode(file_get_contents('php://input'), true);
$prompt = $dados['prompt'] ?? '';
$tipo = $dados['tipo'] ?? 'conteudo'; // conteudo, titulo, resumo

if (empty($prompt)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Prompt vazio']);
    exit;
}

try {
    // Buscar configurações de IA
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT valor FROM configuracoes WHERE chave = 'ia_api_key'");
    $stmt->execute();
    $api_key = $stmt->fetchColumn();
    
    if (empty($api_key)) {
        throw new Exception('API Key da IA não configurada. Configure em Configurações do Site.');
    }
    
    $stmt = $db->prepare("SELECT valor FROM configuracoes WHERE chave = 'ia_api_provider'");
    $stmt->execute();
    $provider = $stmt->fetchColumn() ?: 'openai';
    
    $stmt = $db->prepare("SELECT valor FROM configuracoes WHERE chave = 'ia_modelo'");
    $stmt->execute();
    $modelo = $stmt->fetchColumn() ?: 'gpt-4o-mini';
    
    // Montar prompt baseado no tipo
    $system_prompt = '';
    $user_prompt = $prompt;
    
    switch ($tipo) {
        case 'titulo':
            $system_prompt = 'Você é um especialista em criar títulos jornalísticos atrativos. Crie um título chamativo, conciso e informativo (máximo 80 caracteres) baseado no tema fornecido. Retorne APENAS o título, sem aspas ou explicações.';
            $user_prompt = "Tema: $prompt";
            break;
            
        case 'resumo':
            $system_prompt = 'Você é um especialista em resumir textos. Crie um resumo conciso e atrativo (2-3 frases, máximo 200 caracteres) do texto fornecido. Retorne APENAS o resumo, sem título ou explicações adicionais.';
            break;
            
        case 'conteudo':
        default:
            $system_prompt = 'Você é um jornalista especializado em notícias institucionais e previdência. Escreva uma notícia completa, bem estruturada com parágrafos, em HTML limpo (use apenas <p>, <h2>, <h3>, <strong>, <em>, <ul>, <li>). Seja informativo, claro e use linguagem formal mas acessível. NÃO inclua título (será adicionado separadamente).';
            break;
    }
    
    // Chamar API baseado no provider
    $conteudo_gerado = '';
    
    switch ($provider) {
        case 'openai':
            $conteudo_gerado = chamarOpenAI($api_key, $modelo, $system_prompt, $user_prompt);
            break;
            
        case 'anthropic':
            $conteudo_gerado = chamarAnthropic($api_key, $modelo, $system_prompt, $user_prompt);
            break;
            
        case 'gemini':
            $conteudo_gerado = chamarGemini($api_key, $modelo, $system_prompt, $user_prompt);
            break;
            
        default:
            throw new Exception('Provedor de IA não suportado: ' . $provider);
    }
    
    // Registrar uso no log
    $stmt = $db->prepare("
        INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) 
        VALUES (?, 'ia_gerado', ?, ?)
    ");
    $stmt->execute([
        Session::getUserId(), 
        "Conteúdo gerado via IA ($tipo): " . substr($prompt, 0, 100),
        $_SERVER['REMOTE_ADDR']
    ]);
    
    echo json_encode([
        'sucesso' => true,
        'conteudo' => $conteudo_gerado,
        'provider' => $provider
    ]);
    
} catch (Exception $e) {
    error_log("Erro na API IA: " . $e->getMessage());
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
}

/**
 * Chamar OpenAI API
 */
function chamarOpenAI($api_key, $modelo, $system_prompt, $user_prompt) {
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => $modelo,
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 2000
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        $error = json_decode($response, true);
        throw new Exception('Erro OpenAI: ' . ($error['error']['message'] ?? 'Erro desconhecido'));
    }
    
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? '';
}

/**
 * Chamar Anthropic Claude API
 */
function chamarAnthropic($api_key, $modelo, $system_prompt, $user_prompt) {
    $url = 'https://api.anthropic.com/v1/messages';
    
    $data = [
        'model' => $modelo ?: 'claude-3-5-sonnet-20241022',
        'max_tokens' => 2000,
        'system' => $system_prompt,
        'messages' => [
            ['role' => 'user', 'content' => $user_prompt]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $api_key,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        $error = json_decode($response, true);
        throw new Exception('Erro Anthropic: ' . ($error['error']['message'] ?? 'Erro desconhecido'));
    }
    
    $result = json_decode($response, true);
    return $result['content'][0]['text'] ?? '';
}

/**
 * Chamar Google Gemini API
 */
function chamarGemini($api_key, $modelo, $system_prompt, $user_prompt) {
    $modelo_final = $modelo ?: 'gemini-pro';
    $url = "https://generativelanguage.googleapis.com/v1/models/{$modelo_final}:generateContent?key={$api_key}";
    
    $prompt_completo = $system_prompt . "\n\n" . $user_prompt;
    
    $data = [
        'contents' => [
            ['parts' => [['text' => $prompt_completo]]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2000
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        $error = json_decode($response, true);
        throw new Exception('Erro Gemini: ' . ($error['error']['message'] ?? 'Erro desconhecido'));
    }
    
    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
}
