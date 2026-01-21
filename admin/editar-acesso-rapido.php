<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getInstance()->getConnection();

$card_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar card
$stmt = $pdo->prepare("SELECT * FROM acesso_rapido_cards WHERE id = ?");
$stmt->execute([$card_id]);
$card = $stmt->fetch();

if (!$card) {
    header('Location: acesso-rapido.php');
    exit;
}

$pageTitle = 'Editar ' . $card['titulo'];

$meses = [
    '' => 'Selecione o mês (opcional)...',
    'Janeiro' => 'Janeiro',
    'Fevereiro' => 'Fevereiro',
    'Março' => 'Março',
    'Abril' => 'Abril',
    'Maio' => 'Maio',
    'Junho' => 'Junho',
    'Julho' => 'Julho',
    'Agosto' => 'Agosto',
    'Setembro' => 'Setembro',
    'Outubro' => 'Outubro',
    'Novembro' => 'Novembro',
    'Dezembro' => 'Dezembro',
    'personalizado' => '✏️ Personalizado...'
];

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // === CARD ===
    if ($action === 'update_card') {
        $stmt = $pdo->prepare("UPDATE acesso_rapido_cards SET titulo = ?, descricao = ? WHERE id = ?");
        $stmt->execute([$_POST['titulo'], $_POST['descricao'], $card_id]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    // === SEÇÃO ===
    if ($action === 'add_secao') {
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem), 0) + 1 as nova_ordem FROM acesso_rapido_secoes WHERE card_id = ?");
        $stmt->execute([$card_id]);
        $nova_ordem = $stmt->fetch()['nova_ordem'];
        
        $stmt = $pdo->prepare("INSERT INTO acesso_rapido_secoes (card_id, titulo, ordem) VALUES (?, ?, ?)");
        $stmt->execute([$card_id, $_POST['titulo'], $nova_ordem]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'update_secao') {
        $stmt = $pdo->prepare("UPDATE acesso_rapido_secoes SET titulo = ? WHERE id = ?");
        $stmt->execute([$_POST['titulo'], $_POST['secao_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'delete_secao') {
        $stmt = $pdo->prepare("DELETE FROM acesso_rapido_secoes WHERE id = ?");
        $stmt->execute([$_POST['secao_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'move_secao') {
        $secao_id = $_POST['secao_id'];
        $direcao = $_POST['direcao'];
        
        $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_secoes WHERE id = ?");
        $stmt->execute([$secao_id]);
        $secao = $stmt->fetch();
        
        if ($secao) {
            if ($direcao === 'up') {
                $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_secoes WHERE card_id = ? AND ordem < ? ORDER BY ordem DESC LIMIT 1");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_secoes WHERE card_id = ? AND ordem > ? ORDER BY ordem ASC LIMIT 1");
            }
            $stmt->execute([$card_id, $secao['ordem']]);
            $outra = $stmt->fetch();
            
            if ($outra) {
                $stmt = $pdo->prepare("UPDATE acesso_rapido_secoes SET ordem = ? WHERE id = ?");
                $stmt->execute([$outra['ordem'], $secao_id]);
                $stmt->execute([$secao['ordem'], $outra['id']]);
            }
        }
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    // === SUBSEÇÃO ===
    if ($action === 'add_subsecao') {
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem_geral), 0) as max_ordem FROM acesso_rapido_subsecoes WHERE secao_id = ?");
        $stmt->execute([$_POST['secao_id']]);
        $max_sub = $stmt->fetch()['max_ordem'];
        
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem_geral), 0) as max_ordem FROM acesso_rapido_anos WHERE secao_id = ? AND (subsecao_id IS NULL OR subsecao_id = 0)");
        $stmt->execute([$_POST['secao_id']]);
        $max_ano = $stmt->fetch()['max_ordem'];
        
        $nova_ordem = max($max_sub, $max_ano) + 10;
        
        $stmt = $pdo->prepare("INSERT INTO acesso_rapido_subsecoes (secao_id, titulo, ordem, ordem_geral) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['secao_id'], $_POST['titulo'], $nova_ordem, $nova_ordem]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'update_subsecao') {
        $stmt = $pdo->prepare("UPDATE acesso_rapido_subsecoes SET titulo = ? WHERE id = ?");
        $stmt->execute([$_POST['titulo'], $_POST['subsecao_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'delete_subsecao') {
        $stmt = $pdo->prepare("DELETE FROM acesso_rapido_subsecoes WHERE id = ?");
        $stmt->execute([$_POST['subsecao_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    // === MOVER ITEM ===
    if ($action === 'move_item') {
        $tipo = $_POST['tipo'];
        $item_id = $_POST['item_id'];
        $direcao = $_POST['direcao'];
        $secao_id = $_POST['secao_id'];
        
        if ($tipo === 'subsecao') {
            $stmt = $pdo->prepare("SELECT id, ordem_geral, 'subsecao' as tipo FROM acesso_rapido_subsecoes WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT id, ordem_geral, 'ano' as tipo FROM acesso_rapido_anos WHERE id = ?");
        }
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if ($item) {
            $stmt = $pdo->prepare("
                SELECT id, ordem_geral, 'subsecao' as tipo FROM acesso_rapido_subsecoes WHERE secao_id = ?
                UNION ALL
                SELECT id, ordem_geral, 'ano' as tipo FROM acesso_rapido_anos WHERE secao_id = ? AND (subsecao_id IS NULL OR subsecao_id = 0)
                ORDER BY ordem_geral " . ($direcao === 'up' ? 'DESC' : 'ASC')
            );
            $stmt->execute([$secao_id, $secao_id]);
            $todos = $stmt->fetchAll();
            
            $encontrou_atual = false;
            $vizinho = null;
            foreach ($todos as $t) {
                if ($encontrou_atual) {
                    $vizinho = $t;
                    break;
                }
                if ($t['id'] == $item_id && $t['tipo'] == $tipo) {
                    $encontrou_atual = true;
                }
            }
            
            if ($vizinho) {
                if ($tipo === 'subsecao') {
                    $stmt = $pdo->prepare("UPDATE acesso_rapido_subsecoes SET ordem_geral = ? WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE acesso_rapido_anos SET ordem_geral = ? WHERE id = ?");
                }
                $stmt->execute([$vizinho['ordem_geral'], $item_id]);
                
                if ($vizinho['tipo'] === 'subsecao') {
                    $stmt = $pdo->prepare("UPDATE acesso_rapido_subsecoes SET ordem_geral = ? WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE acesso_rapido_anos SET ordem_geral = ? WHERE id = ?");
                }
                $stmt->execute([$item['ordem_geral'], $vizinho['id']]);
            }
        }
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    // === ANO ===
    if ($action === 'add_ano') {
        $secao_id = isset($_POST['secao_id']) && $_POST['secao_id'] ? (int)$_POST['secao_id'] : null;
        $subsecao_id = isset($_POST['subsecao_id']) && $_POST['subsecao_id'] ? (int)$_POST['subsecao_id'] : null;
        
        $ordem_geral = 0;
        if (!$subsecao_id && $secao_id) {
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem_geral), 0) as max_ordem FROM acesso_rapido_subsecoes WHERE secao_id = ?");
            $stmt->execute([$secao_id]);
            $max_sub = $stmt->fetch()['max_ordem'];
            
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem_geral), 0) as max_ordem FROM acesso_rapido_anos WHERE secao_id = ? AND (subsecao_id IS NULL OR subsecao_id = 0)");
            $stmt->execute([$secao_id]);
            $max_ano = $stmt->fetch()['max_ordem'];
            
            $ordem_geral = max($max_sub, $max_ano) + 10;
        }
        
        $stmt = $pdo->prepare("INSERT INTO acesso_rapido_anos (secao_id, subsecao_id, ano, ordem_geral) VALUES (?, ?, ?, ?)");
        $stmt->execute([$secao_id, $subsecao_id, $_POST['ano'], $ordem_geral]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'update_ano') {
        $stmt = $pdo->prepare("UPDATE acesso_rapido_anos SET ano = ? WHERE id = ?");
        $stmt->execute([$_POST['ano'], $_POST['ano_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'delete_ano') {
        $stmt = $pdo->prepare("DELETE FROM acesso_rapido_anos WHERE id = ?");
        $stmt->execute([$_POST['ano_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'move_ano') {
        $ano_id = $_POST['ano_id'];
        $direcao = $_POST['direcao'];
        
        $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_anos WHERE id = ?");
        $stmt->execute([$ano_id]);
        $ano = $stmt->fetch();
        
        if ($ano && $ano['subsecao_id']) {
            if ($direcao === 'up') {
                $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_anos WHERE subsecao_id = ? AND ano > ? ORDER BY ano ASC LIMIT 1");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_anos WHERE subsecao_id = ? AND ano < ? ORDER BY ano DESC LIMIT 1");
            }
            $stmt->execute([$ano['subsecao_id'], $ano['ano']]);
            $outra = $stmt->fetch();
            
            if ($outra) {
                $stmt = $pdo->prepare("UPDATE acesso_rapido_anos SET ano = ? WHERE id = ?");
                $stmt->execute([$outra['ano'], $ano_id]);
                $stmt->execute([$ano['ano'], $outra['id']]);
            }
        }
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    // === ARQUIVO/DOCUMENTO ===
    if ($action === 'add_arquivo') {
        $upload_dir = '../uploads/acesso-rapido/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file = $_FILES['arquivo'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $file_path = $upload_dir . $file_name;
        
        $titulo_mes = trim($_POST['titulo_mes'] ?? '');
        $titulo_personalizado = trim($_POST['titulo_personalizado'] ?? '');
        $titulo = !empty($titulo_personalizado) ? $titulo_personalizado : $titulo_mes;
        
        if (empty($titulo)) {
            $titulo = 'Documento';
        }
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $stmt = $pdo->prepare("INSERT INTO acesso_rapido_arquivos (ano_id, titulo, arquivo_path) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['ano_id'], $titulo, '/uploads/acesso-rapido/' . $file_name]);
        }
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'update_arquivo') {
        $stmt = $pdo->prepare("UPDATE acesso_rapido_arquivos SET titulo = ? WHERE id = ?");
        $stmt->execute([$_POST['titulo'], $_POST['arquivo_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
    
    if ($action === 'delete_arquivo') {
        $stmt = $pdo->prepare("SELECT arquivo_path FROM acesso_rapido_arquivos WHERE id = ?");
        $stmt->execute([$_POST['arquivo_id']]);
        $arquivo = $stmt->fetch();
        
        if ($arquivo && file_exists('..' . $arquivo['arquivo_path'])) {
            unlink('..' . $arquivo['arquivo_path']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM acesso_rapido_arquivos WHERE id = ?");
        $stmt->execute([$_POST['arquivo_id']]);
        header("Location: editar-acesso-rapido.php?id=$card_id&success=1");
        exit;
    }
}

// Buscar seções
$stmt = $pdo->prepare("SELECT * FROM acesso_rapido_secoes WHERE card_id = ? ORDER BY ordem ASC");
$stmt->execute([$card_id]);
$secoes = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="mb-8">
        <a href="acesso-rapido.php" class="text-blue-600 hover:underline mb-4 inline-block">← Voltar para Acesso Rápido</a>
        <h1 class="text-3xl font-bold text-gray-800">Editar <?= htmlspecialchars($card['titulo']) ?></h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">Operação realizada com sucesso!</div>
    <?php endif; ?>

    <!-- Dados do Card -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">📋 Dados do Card</h2>
            <button onclick="toggleEditCard()" id="btnEditCard" class="text-gray-600 hover:text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
        </div>
        <form method="POST" id="formCard" class="space-y-4">
            <input type="hidden" name="action" value="update_card">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($card['titulo']) ?>" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50" id="inputTitulo">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição <span class="text-gray-400 font-normal">(opcional)</span></label>
                <textarea name="descricao" rows="3" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 resize-none" id="inputDescricao"><?= htmlspecialchars($card['descricao'] ?? '') ?></textarea>
            </div>
            <div id="btnSaveCard" style="display: none;">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Salvar</button>
                <button type="button" onclick="toggleEditCard()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 ml-2">Cancelar</button>
            </div>
        </form>
    </div>

    <!-- Adicionar Seção -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">➕ Adicionar Nova Seção</h2>
        <form method="POST" class="flex gap-4">
            <input type="hidden" name="action" value="add_secao">
            <input type="text" name="titulo" placeholder="Título da Seção" required class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Adicionar Seção</button>
        </form>
    </div>

    <!-- Seções -->
    <?php foreach ($secoes as $secao_idx => $secao): ?>
    <?php
        $stmt = $pdo->prepare("SELECT *, 'subsecao' as tipo FROM acesso_rapido_subsecoes WHERE secao_id = ?");
        $stmt->execute([$secao['id']]);
        $subsecoes = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT *, 'ano' as tipo FROM acesso_rapido_anos WHERE secao_id = ? AND (subsecao_id IS NULL OR subsecao_id = 0)");
        $stmt->execute([$secao['id']]);
        $anos_secao = $stmt->fetchAll();
        
        $itens_secao = array_merge($subsecoes, $anos_secao);
        usort($itens_secao, function($a, $b) {
            return ($a['ordem_geral'] ?? 0) - ($b['ordem_geral'] ?? 0);
        });
        
        $totalSecao = 0;
        foreach ($anos_secao as $a) {
            $st = $pdo->prepare("SELECT COUNT(*) FROM acesso_rapido_arquivos WHERE ano_id = ?");
            $st->execute([$a['id']]);
            $totalSecao += $st->fetchColumn();
        }
        foreach ($subsecoes as $sub) {
            $st = $pdo->prepare("SELECT fa.id FROM acesso_rapido_arquivos fa JOIN acesso_rapido_anos an ON fa.ano_id = an.id WHERE an.subsecao_id = ?");
            $st->execute([$sub['id']]);
            $totalSecao += $st->rowCount();
        }
    ?>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4 pb-4 border-b-2 border-blue-500">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span>
                <h2 class="text-xl font-bold text-gray-800" id="secao_<?= $secao['id'] ?>_titulo"><?= htmlspecialchars($secao['titulo']) ?> (<?= $totalSecao ?>)</h2>
                <button onclick="editarInline('secao', <?= $secao['id'] ?>, '<?= addslashes($secao['titulo']) ?>')" class="text-gray-400 hover:text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($secao_idx > 0): ?>
                <form method="POST" class="inline"><input type="hidden" name="action" value="move_secao"><input type="hidden" name="secao_id" value="<?= $secao['id'] ?>"><input type="hidden" name="direcao" value="up">
                    <button class="p-1 text-gray-500 hover:text-blue-600" title="Mover para cima">▲</button>
                </form>
                <?php endif; ?>
                <?php if ($secao_idx < count($secoes) - 1): ?>
                <form method="POST" class="inline"><input type="hidden" name="action" value="move_secao"><input type="hidden" name="secao_id" value="<?= $secao['id'] ?>"><input type="hidden" name="direcao" value="down">
                    <button class="p-1 text-gray-500 hover:text-blue-600" title="Mover para baixo">▼</button>
                </form>
                <?php endif; ?>
                <span class="text-gray-300">|</span>
                <form method="POST" class="inline" onsubmit="return confirm('Excluir seção e todo conteúdo?')"><input type="hidden" name="action" value="delete_secao"><input type="hidden" name="secao_id" value="<?= $secao['id'] ?>">
                    <button class="text-red-600 hover:text-red-800 text-sm">🗑️ Excluir</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <h3 class="text-sm font-semibold text-purple-700 mb-3">📁 Nova Subseção</h3>
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="action" value="add_subsecao">
                    <input type="hidden" name="secao_id" value="<?= $secao['id'] ?>">
                    <input type="text" name="titulo" placeholder="Título da subseção" required class="flex-1 border border-purple-300 rounded px-3 py-2 text-sm">
                    <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded text-sm hover:bg-purple-600">+</button>
                </form>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h3 class="text-sm font-semibold text-blue-700 mb-3">📅 Novo Ano (direto na seção)</h3>
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="action" value="add_ano">
                    <input type="hidden" name="secao_id" value="<?= $secao['id'] ?>">
                    <input type="number" name="ano" placeholder="Ex: 2025" required min="2000" max="2100" class="w-28 border border-blue-300 rounded px-3 py-2 text-sm">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600">+ Ano</button>
                </form>
            </div>
        </div>

        <?php foreach ($itens_secao as $item_idx => $item): ?>
            <?php if ($item['tipo'] === 'subsecao'): ?>
                <?php $subsecao = $item; ?>
                <?php
                    $stmt = $pdo->prepare("SELECT * FROM acesso_rapido_anos WHERE subsecao_id = ? ORDER BY ano DESC");
                    $stmt->execute([$subsecao['id']]);
                    $anos_sub = $stmt->fetchAll();
                ?>
                <div class="bg-purple-50 rounded-lg p-4 mb-4 border-l-4 border-purple-400">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-purple-500 font-bold">📁</span>
                            <span class="font-semibold text-purple-800" id="subsecao_<?= $subsecao['id'] ?>_titulo"><?= htmlspecialchars($subsecao['titulo']) ?></span>
                            <button onclick="editarInline('subsecao', <?= $subsecao['id'] ?>, '<?= addslashes($subsecao['titulo']) ?>')" class="text-gray-400 hover:text-purple-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-1">
                            <?php if ($item_idx > 0): ?>
                            <form method="POST" class="inline"><input type="hidden" name="action" value="move_item"><input type="hidden" name="tipo" value="subsecao"><input type="hidden" name="item_id" value="<?= $subsecao['id'] ?>"><input type="hidden" name="secao_id" value="<?= $secao['id'] ?>"><input type="hidden" name="direcao" value="up">
                                <button class="p-1 text-gray-500 hover:text-purple-600 text-sm" title="Mover para cima">▲</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($item_idx < count($itens_secao) - 1): ?>
                            <form method="POST" class="inline"><input type="hidden" name="action" value="move_item"><input type="hidden" name="tipo" value="subsecao"><input type="hidden" name="item_id" value="<?= $subsecao['id'] ?>"><input type="hidden" name="secao_id" value="<?= $secao['id'] ?>"><input type="hidden" name="direcao" value="down">
                                <button class="p-1 text-gray-500 hover:text-purple-600 text-sm" title="Mover para baixo">▼</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Excluir subseção?')"><input type="hidden" name="action" value="delete_subsecao"><input type="hidden" name="subsecao_id" value="<?= $subsecao['id'] ?>">
                                <button class="p-1 text-red-500 hover:text-red-700 text-sm">🗑️</button>
                            </form>
                        </div>
                    </div>
                    
                    <form method="POST" class="flex gap-2 mb-3">
                        <input type="hidden" name="action" value="add_ano">
                        <input type="hidden" name="secao_id" value="<?= $secao['id'] ?>">
                        <input type="hidden" name="subsecao_id" value="<?= $subsecao['id'] ?>">
                        <input type="number" name="ano" placeholder="Ano" required min="2000" max="2100" class="w-24 border border-purple-300 rounded px-2 py-1 text-sm">
                        <button type="submit" class="bg-purple-500 text-white px-3 py-1 rounded text-sm hover:bg-purple-600">+ Ano</button>
                    </form>
                    
                    <?php foreach ($anos_sub as $ano_idx => $ano): ?>
                    <?php include __DIR__ . '/includes/_ano_bloco_acesso.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php 
                    $ano = $item;
                    $ano_idx = $item_idx;
                ?>
                <div class="mb-4 border-l-4 border-blue-400 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-1 bg-blue-50 px-2 py-1">
                        <?php if ($item_idx > 0): ?>
                        <form method="POST" class="inline"><input type="hidden" name="action" value="move_item"><input type="hidden" name="tipo" value="ano"><input type="hidden" name="item_id" value="<?= $ano['id'] ?>"><input type="hidden" name="secao_id" value="<?= $secao['id'] ?>"><input type="hidden" name="direcao" value="up">
                            <button class="p-1 text-gray-500 hover:text-blue-600 text-xs" title="Mover para cima">▲</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($item_idx < count($itens_secao) - 1): ?>
                        <form method="POST" class="inline"><input type="hidden" name="action" value="move_item"><input type="hidden" name="tipo" value="ano"><input type="hidden" name="item_id" value="<?= $ano['id'] ?>"><input type="hidden" name="secao_id" value="<?= $secao['id'] ?>"><input type="hidden" name="direcao" value="down">
                            <button class="p-1 text-gray-500 hover:text-blue-600 text-xs" title="Mover para baixo">▼</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php include __DIR__ . '/includes/_ano_bloco_acesso.php'; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php if (empty($itens_secao)): ?>
        <p class="text-gray-400 text-center py-4 text-sm">Nenhum conteúdo nesta seção ainda.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if (empty($secoes)): ?>
    <div class="bg-white rounded-xl shadow-md p-8 text-center text-gray-500">
        Nenhuma seção criada. Adicione uma seção acima para começar.
    </div>
    <?php endif; ?>
</div>

<!-- Modal para adicionar documento -->
<div id="modalDocumento" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-gray-800 mb-4">📄 Adicionar Documento</h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add_arquivo">
            <input type="hidden" name="ano_id" id="modal_ano_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Título do documento</label>
                <div class="flex gap-2 mb-2">
                    <button type="button" id="btnTipoMes" onclick="usarTipoMes()" class="flex-1 px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-600 text-white">📅 Mês (Jan-Dez)</button>
                    <button type="button" id="btnTipoPersonalizado" onclick="usarTipoPersonalizado()" class="flex-1 px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">✏️ Personalizado</button>
                </div>
                <select name="titulo_mes" id="selectTituloMes" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="">Selecione o mês...</option>
                    <option value="Janeiro">Janeiro</option>
                    <option value="Fevereiro">Fevereiro</option>
                    <option value="Março">Março</option>
                    <option value="Abril">Abril</option>
                    <option value="Maio">Maio</option>
                    <option value="Junho">Junho</option>
                    <option value="Julho">Julho</option>
                    <option value="Agosto">Agosto</option>
                    <option value="Setembro">Setembro</option>
                    <option value="Outubro">Outubro</option>
                    <option value="Novembro">Novembro</option>
                    <option value="Dezembro">Dezembro</option>
                </select>
                <input type="text" name="titulo_personalizado" id="inputTituloPersonalizado" placeholder="Ex: 1º Semestre, Relatório Anual, etc" class="w-full border border-gray-300 rounded-lg px-4 py-2 hidden">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo (PDF)</label>
                <input type="file" name="arquivo" required accept=".pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Enviar</button>
                <button type="button" onclick="fecharModalDocumento()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para editar documento -->
<div id="modalEditDocumento" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-gray-800 mb-4">✏️ Editar Documento</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="update_arquivo">
            <input type="hidden" name="arquivo_id" id="modal_edit_arquivo_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input type="text" name="titulo" id="modal_edit_titulo" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Salvar</button>
                <button type="button" onclick="fecharModalEditDocumento()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEditCard() {
    const titulo = document.getElementById('inputTitulo');
    const descricao = document.getElementById('inputDescricao');
    const btnSave = document.getElementById('btnSaveCard');
    const btnEdit = document.getElementById('btnEditCard');
    
    if (titulo.hasAttribute('readonly')) {
        titulo.removeAttribute('readonly');
        descricao.removeAttribute('readonly');
        titulo.classList.remove('bg-gray-50');
        descricao.classList.remove('bg-gray-50');
        btnSave.style.display = 'block';
        btnEdit.style.display = 'none';
    } else {
        titulo.setAttribute('readonly', 'readonly');
        descricao.setAttribute('readonly', 'readonly');
        titulo.classList.add('bg-gray-50');
        descricao.classList.add('bg-gray-50');
        btnSave.style.display = 'none';
        btnEdit.style.display = 'block';
    }
}

function editarInline(tipo, id, valorAtual) {
    const el = document.getElementById(tipo + '_' + id + '_titulo');
    const form = document.createElement('form');
    form.method = 'POST';
    form.className = 'flex items-center gap-2';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_${tipo}">
        <input type="hidden" name="${tipo}_id" value="${id}">
        <input type="text" name="titulo" value="${valorAtual}" class="border rounded px-2 py-1 text-sm" required>
        <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">OK</button>
        <button type="button" onclick="location.reload()" class="bg-gray-300 px-2 py-1 rounded text-xs">X</button>
    `;
    el.replaceWith(form);
    form.querySelector('input[name="titulo"]').focus();
}

function abrirModalDocumento(anoId) {
    document.getElementById('modal_ano_id').value = anoId;
    document.getElementById('modalDocumento').classList.remove('hidden');
    usarTipoMes();
    document.getElementById('selectTituloMes').value = '';
    document.getElementById('inputTituloPersonalizado').value = '';
}

function fecharModalDocumento() {
    document.getElementById('modalDocumento').classList.add('hidden');
}

function fecharModalEditDocumento() {
    document.getElementById('modalEditDocumento').classList.add('hidden');
}

function usarTipoMes() {
    document.getElementById('btnTipoMes').classList.remove('bg-gray-200', 'text-gray-700');
    document.getElementById('btnTipoMes').classList.add('bg-blue-600', 'text-white');
    document.getElementById('btnTipoPersonalizado').classList.remove('bg-blue-600', 'text-white');
    document.getElementById('btnTipoPersonalizado').classList.add('bg-gray-200', 'text-gray-700');
    document.getElementById('selectTituloMes').classList.remove('hidden');
    document.getElementById('selectTituloMes').required = true;
    document.getElementById('inputTituloPersonalizado').classList.add('hidden');
    document.getElementById('inputTituloPersonalizado').required = false;
    document.getElementById('inputTituloPersonalizado').value = '';
}

function usarTipoPersonalizado() {
    document.getElementById('btnTipoPersonalizado').classList.remove('bg-gray-200', 'text-gray-700');
    document.getElementById('btnTipoPersonalizado').classList.add('bg-blue-600', 'text-white');
    document.getElementById('btnTipoMes').classList.remove('bg-blue-600', 'text-white');
    document.getElementById('btnTipoMes').classList.add('bg-gray-200', 'text-gray-700');
    document.getElementById('selectTituloMes').classList.add('hidden');
    document.getElementById('selectTituloMes').required = false;
    document.getElementById('selectTituloMes').value = '';
    document.getElementById('inputTituloPersonalizado').classList.remove('hidden');
    document.getElementById('inputTituloPersonalizado').required = true;
    document.getElementById('inputTituloPersonalizado').focus();
}

document.getElementById('modalDocumento').addEventListener('click', function(e) {
    if (e.target === this) fecharModalDocumento();
});
document.getElementById('modalEditDocumento').addEventListener('click', function(e) {
    if (e.target === this) fecharModalEditDocumento();
});
</script>

<?php include 'includes/footer.php'; ?>
