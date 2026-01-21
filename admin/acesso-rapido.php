<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getInstance()->getConnection();
$pageTitle = 'Acesso Rápido';

// Lista de ícones disponíveis
$icones_disponiveis = [
    'user' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>', 'nome' => 'Usuário'],
    'users' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'nome' => 'Usuários'],
    'building' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>', 'nome' => 'Prédio'],
    'document' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'nome' => 'Documento'],
    'clipboard' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'nome' => 'Clipboard'],
    'folder' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>', 'nome' => 'Pasta'],
    'search' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>', 'nome' => 'Busca'],
    'scale' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>', 'nome' => 'Balança'],
    'book' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>', 'nome' => 'Livro'],
    'download' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>', 'nome' => 'Download'],
    'check' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'nome' => 'Check'],
    'shield' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>', 'nome' => 'Escudo'],
    'home' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'nome' => 'Casa'],
    'calendar' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', 'nome' => 'Calendário'],
    'chart' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'nome' => 'Gráfico'],
    'cog' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'nome' => 'Engrenagem'],
    'link' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>', 'nome' => 'Link'],
    'external-link' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>', 'nome' => 'Link Externo'],
    'briefcase' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>', 'nome' => 'Maleta'],
    'eye' => ['svg' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>', 'nome' => 'Olho'],
];

// Processar ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_card') {
        $titulo = $_POST['titulo'];
        $icone = $_POST['icone'];
        $tipo = $_POST['tipo'];
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $titulo)));
        $slug = trim($slug, '-');
        
        // Buscar maior ordem
        $stmt = $pdo->query("SELECT COALESCE(MAX(ordem), 0) + 1 as nova_ordem FROM acesso_rapido_cards");
        $nova_ordem = $stmt->fetch()['nova_ordem'];
        
        $link_externo = null;
        $arquivo_pdf = null;
        
        if ($tipo === 'link_externo') {
            $link_externo = $_POST['link_externo'];
            $tipo = 'link';
        } elseif ($tipo === 'pdf' && isset($_FILES['arquivo_pdf']) && $_FILES['arquivo_pdf']['error'] === 0) {
            $upload_dir = '../uploads/acesso-rapido/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['arquivo_pdf']['name']);
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['arquivo_pdf']['tmp_name'], $file_path)) {
                $arquivo_pdf = '/uploads/acesso-rapido/' . $file_name;
            }
        } else {
            $tipo = 'pagina';
        }
        
        // Salvar a key do ícone, não o SVG
        $icone_key = $icone;
        
        $stmt = $pdo->prepare("INSERT INTO acesso_rapido_cards (titulo, icone, tipo, link_externo, arquivo_pdf, slug, ordem) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $icone_key, $tipo, $link_externo, $arquivo_pdf, $slug, $nova_ordem]);
        
        header('Location: acesso-rapido.php?success=added');
        exit;
    }
    
    if ($_POST['action'] === 'update_titulo') {
        $stmt = $pdo->prepare("UPDATE acesso_rapido_cards SET titulo = ? WHERE id = ?");
        $stmt->execute([$_POST['titulo'], $_POST['card_id']]);
        header('Location: acesso-rapido.php');
        exit;
    }
    
    if ($_POST['action'] === 'update_icone') {
        $icone_key = $_POST['icone'];
        if (isset($icones_disponiveis[$icone_key])) {
            $stmt = $pdo->prepare("UPDATE acesso_rapido_cards SET icone = ? WHERE id = ?");
            $stmt->execute([$icone_key, $_POST['card_id']]);
        }
        header('Location: acesso-rapido.php');
        exit;
    }
    
    if ($_POST['action'] === 'delete_card') {
        $stmt = $pdo->prepare("DELETE FROM acesso_rapido_cards WHERE id = ?");
        $stmt->execute([$_POST['card_id']]);
        header('Location: acesso-rapido.php');
        exit;
    }
    
    if ($_POST['action'] === 'toggle_ativo') {
        $stmt = $pdo->prepare("UPDATE acesso_rapido_cards SET ativo = NOT ativo WHERE id = ?");
        $stmt->execute([$_POST['card_id']]);
        header('Location: acesso-rapido.php');
        exit;
    }
    
    if ($_POST['action'] === 'move_card') {
        $card_id = $_POST['card_id'];
        $direcao = $_POST['direcao'];
        
        $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_cards WHERE id = ?");
        $stmt->execute([$card_id]);
        $card = $stmt->fetch();
        
        if ($card) {
            if ($direcao === 'up') {
                $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_cards WHERE ordem < ? ORDER BY ordem DESC LIMIT 1");
                $stmt->execute([$card['ordem']]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_cards WHERE ordem > ? ORDER BY ordem ASC LIMIT 1");
                $stmt->execute([$card['ordem']]);
            }
            $outro = $stmt->fetch();
            
            if ($outro) {
                $stmt = $pdo->prepare("UPDATE acesso_rapido_cards SET ordem = ? WHERE id = ?");
                $stmt->execute([$outro['ordem'], $card_id]);
                $stmt->execute([$card['ordem'], $outro['id']]);
            }
        }
        header('Location: acesso-rapido.php');
        exit;
    }
}

// Buscar cards
$stmt = $pdo->query("SELECT * FROM acesso_rapido_cards ORDER BY ordem ASC");
$cards = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">⚡ Acesso Rápido</h1>
            <p class="text-gray-500 mt-1">Gerencie os cards da seção Acesso Rápido na página inicial</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Card adicionado com sucesso!</div>
    <?php endif; ?>

    <!-- Lista de Cards -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Cards Atuais</h2>
        
        <?php if (count($cards) > 0): ?>
        <div class="space-y-3">
            <?php foreach ($cards as $index => $card): ?>
            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg border <?= $card['ativo'] ? 'border-gray-200' : 'border-red-300 bg-red-50' ?>">
                <div class="flex items-center gap-4">
                    <span class="text-gray-400 font-mono text-sm w-8">#<?= $card['ordem'] ?></span>
                    
                    <!-- Ícone com dropdown para editar -->
                    <div class="relative group">
                        <div class="text-blue-600 w-10 h-10 flex-shrink-0 flex items-center justify-center cursor-pointer hover:bg-blue-100 rounded-lg" onclick="toggleIconeDropdown(<?= $card['id'] ?>)">
                            <?= $card['icone'] ?>
                        </div>
                        <div id="icone-dropdown-<?= $card['id'] ?>" class="hidden absolute top-full left-0 mt-1 bg-white border rounded-lg shadow-lg p-2 z-50 w-64">
                            <p class="text-xs text-gray-500 mb-2 px-2">Alterar ícone:</p>
                            <div class="grid grid-cols-5 gap-1">
                                <?php foreach ($icones_disponiveis as $key => $icone): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_icone">
                                    <input type="hidden" name="card_id" value="<?= $card['id'] ?>">
                                    <input type="hidden" name="icone" value="<?= $key ?>">
                                    <button type="submit" class="p-2 hover:bg-gray-100 rounded text-blue-600" title="<?= $icone['nome'] ?>">
                                        <?= $icone['svg'] ?>
                                    </button>
                                </form>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <form method="POST" class="flex items-center gap-2">
                            <input type="hidden" name="action" value="update_titulo">
                            <input type="hidden" name="card_id" value="<?= $card['id'] ?>">
                            <input type="text" name="titulo" value="<?= htmlspecialchars($card['titulo']) ?>" 
                                   class="font-semibold text-gray-800 bg-transparent border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:outline-none px-1 py-0.5 w-full max-w-md"
                                   onchange="this.form.submit()">
                        </form>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs px-2 py-0.5 rounded <?= $card['tipo'] === 'link' ? 'bg-blue-100 text-blue-700' : ($card['tipo'] === 'pdf' ? 'bg-red-100 text-red-700' : 'bg-purple-100 text-purple-700') ?>">
                                <?= $card['tipo'] === 'link' ? '🔗 Link Externo' : ($card['tipo'] === 'pdf' ? '📄 PDF' : '📑 Página') ?>
                            </span>
                            <?php if (!$card['ativo']): ?><span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Oculto</span><?php endif; ?>
                            <span class="text-xs text-gray-400">→ <?= htmlspecialchars($card['link_externo'] ?: $card['arquivo_pdf'] ?: '/'.$card['slug']) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-1">
                    <?php if ($index > 0): ?>
                    <form method="POST" class="inline"><input type="hidden" name="action" value="move_card"><input type="hidden" name="card_id" value="<?= $card['id'] ?>"><input type="hidden" name="direcao" value="up">
                        <button type="submit" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded" title="Mover para cima"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button>
                    </form>
                    <?php else: ?><span class="p-2 text-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></span><?php endif; ?>
                    
                    <?php if ($index < count($cards) - 1): ?>
                    <form method="POST" class="inline"><input type="hidden" name="action" value="move_card"><input type="hidden" name="card_id" value="<?= $card['id'] ?>"><input type="hidden" name="direcao" value="down">
                        <button type="submit" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded" title="Mover para baixo"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                    </form>
                    <?php else: ?><span class="p-2 text-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></span><?php endif; ?>
                    
                    <span class="text-gray-300 mx-1">|</span>
                    
                    <form method="POST" class="inline"><input type="hidden" name="action" value="toggle_ativo"><input type="hidden" name="card_id" value="<?= $card['id'] ?>">
                        <button type="submit" class="p-2 <?= $card['ativo'] ? 'text-green-500' : 'text-red-500' ?> hover:bg-gray-100 rounded" title="<?= $card['ativo'] ? 'Ocultar' : 'Mostrar' ?>">
                            <?php if ($card['ativo']): ?><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <?php else: ?><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg><?php endif; ?>
                        </button>
                    </form>
                    
                    <?php if ($card['tipo'] === 'pagina'): ?>
                    <a href="editar-acesso-rapido.php?id=<?= $card['id'] ?>" class="p-2 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded" title="Editar Conteúdo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <?php endif; ?>

                    <form method="POST" class="inline" onsubmit="return confirm('Excluir este card?')"><input type="hidden" name="action" value="delete_card"><input type="hidden" name="card_id" value="<?= $card['id'] ?>">
                        <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded" title="Excluir"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-gray-500 text-center py-8">Nenhum card criado ainda.</p>
        <?php endif; ?>
    </div>

    <!-- Adicionar Card -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">➕ Adicionar Novo Card</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add_card">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Título *</label>
                    <input type="text" name="titulo" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ícone</label>
                    <select name="icone" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                        <?php foreach ($icones_disponiveis as $key => $icone): ?>
                        <option value="<?= $key ?>"><?= $icone['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                        <input type="radio" name="tipo" value="link" checked onchange="toggleTipo()" class="w-4 h-4">
                        <span>📑 Página Interna</span>
                        <span class="text-xs text-gray-400">(/titulo-do-card)</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                        <input type="radio" name="tipo" value="link_externo" onchange="toggleTipo()" class="w-4 h-4">
                        <span>🔗 Link Externo</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                        <input type="radio" name="tipo" value="pdf" onchange="toggleTipo()" class="w-4 h-4">
                        <span>📄 PDF</span>
                    </label>
                </div>
            </div>
            <div id="campo_link_externo" style="display:none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">URL do Link Externo</label>
                <input type="text" name="link_externo" placeholder="https://exemplo.com" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div id="campo_pdf" style="display:none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo PDF</label>
                <input type="file" name="arquivo_pdf" accept=".pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
            </div>
            <div id="info_link" class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700">
                ℹ️ <strong>Página Interna:</strong> Será criada uma nova página em <code>/titulo-do-card</code> (ex: /legislacao) onde você poderá adicionar seções, anos e arquivos.
            </div>
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-600">➕ Adicionar</button>
        </form>
    </div>
</div>

<script>
function toggleTipo() {
    var isLink = document.querySelector('input[name="tipo"][value="link"]').checked;
    var isLinkExterno = document.querySelector('input[name="tipo"][value="link_externo"]').checked;
    var isPdf = document.querySelector('input[name="tipo"][value="pdf"]').checked;
    
    document.getElementById('campo_link_externo').style.display = isLinkExterno ? 'block' : 'none';
    document.getElementById('campo_pdf').style.display = isPdf ? 'block' : 'none';
    document.getElementById('info_link').style.display = isLink ? 'block' : 'none';
}

function toggleIconeDropdown(cardId) {
    // Fechar todos os outros dropdowns
    document.querySelectorAll('[id^="icone-dropdown-"]').forEach(el => {
        if (el.id !== 'icone-dropdown-' + cardId) {
            el.classList.add('hidden');
        }
    });
    // Toggle o dropdown atual
    document.getElementById('icone-dropdown-' + cardId).classList.toggle('hidden');
}

// Fechar dropdowns ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="icone-dropdown-"]') && !e.target.closest('.group')) {
        document.querySelectorAll('[id^="icone-dropdown-"]').forEach(el => el.classList.add('hidden'));
    }
});
</script>

<?php include 'includes/footer.php'; ?>
