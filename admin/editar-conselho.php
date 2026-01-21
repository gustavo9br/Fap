<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getInstance()->getConnection();

$conselho_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar conselho
$stmt = $pdo->prepare("SELECT * FROM conselhos WHERE id = ?");
$stmt->execute([$conselho_id]);
$conselho = $stmt->fetch();

if (!$conselho) {
    header('Location: conselhos');
    exit;
}

$pageTitle = 'Editar ' . $conselho['nome'];

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_conselho') {
        $stmt = $pdo->prepare("UPDATE conselhos SET nome = ?, descricao = ? WHERE id = ?");
        $stmt->execute([$_POST['nome'], $_POST['descricao'], $conselho_id]);
        header("Location: editar-conselho.php?id=$conselho_id&success=conselho_updated");
        exit;
    }
    
    if ($_POST['action'] === 'add_secao') {
        // Buscar a maior ordem atual
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem), 0) + 1 as nova_ordem FROM conselho_secoes WHERE conselho_id = ?");
        $stmt->execute([$conselho_id]);
        $nova_ordem = $stmt->fetch()['nova_ordem'];
        
        $stmt = $pdo->prepare("INSERT INTO conselho_secoes (conselho_id, titulo, tipo, ordem) VALUES (?, ?, 'outros', ?)");
        $stmt->execute([$conselho_id, $_POST['titulo'], $nova_ordem]);
        header("Location: editar-conselho.php?id=$conselho_id&success=secao_added");
        exit;
    }
    
    if ($_POST['action'] === 'move_secao') {
        $secao_id = $_POST['secao_id'];
        $direcao = $_POST['direcao'];
        
        // Primeiro, normalizar todas as ordens (garantir sequência 1,2,3...)
        $stmt = $pdo->prepare("SELECT id FROM conselho_secoes WHERE conselho_id = ? ORDER BY ordem ASC, id ASC");
        $stmt->execute([$conselho_id]);
        $todas_secoes = $stmt->fetchAll();
        
        $ordem = 1;
        foreach ($todas_secoes as $sec) {
            $stmt = $pdo->prepare("UPDATE conselho_secoes SET ordem = ? WHERE id = ?");
            $stmt->execute([$ordem, $sec['id']]);
            $ordem++;
        }
        
        // Buscar seção atual APÓS normalização
        $stmt = $pdo->prepare("SELECT * FROM conselho_secoes WHERE id = ?");
        $stmt->execute([$secao_id]);
        $secao_atual = $stmt->fetch();
        
        if ($direcao === 'up' && $secao_atual['ordem'] > 1) {
            // Buscar seção imediatamente anterior
            $stmt = $pdo->prepare("SELECT * FROM conselho_secoes WHERE conselho_id = ? AND ordem = ?");
            $stmt->execute([$conselho_id, $secao_atual['ordem'] - 1]);
            $secao_troca = $stmt->fetch();
            
            if ($secao_troca) {
                // Trocar as ordens
                $stmt = $pdo->prepare("UPDATE conselho_secoes SET ordem = ? WHERE id = ?");
                $stmt->execute([$secao_atual['ordem'], $secao_troca['id']]);
                $stmt->execute([$secao_atual['ordem'] - 1, $secao_id]);
            }
        } elseif ($direcao === 'down') {
            // Buscar seção imediatamente posterior
            $stmt = $pdo->prepare("SELECT * FROM conselho_secoes WHERE conselho_id = ? AND ordem = ?");
            $stmt->execute([$conselho_id, $secao_atual['ordem'] + 1]);
            $secao_troca = $stmt->fetch();
            
            if ($secao_troca) {
                // Trocar as ordens
                $stmt = $pdo->prepare("UPDATE conselho_secoes SET ordem = ? WHERE id = ?");
                $stmt->execute([$secao_atual['ordem'], $secao_troca['id']]);
                $stmt->execute([$secao_atual['ordem'] + 1, $secao_id]);
            }
        }
        
        header("Location: editar-conselho.php?id=$conselho_id&success=secao_moved");
        exit;
    }
    
    if ($_POST['action'] === 'add_ano') {
        $stmt = $pdo->prepare("INSERT INTO conselho_anos (secao_id, ano) VALUES (?, ?)");
        $stmt->execute([$_POST['secao_id'], $_POST['ano']]);
        header("Location: editar-conselho.php?id=$conselho_id&success=ano_added");
        exit;
    }
    
    if ($_POST['action'] === 'add_arquivo') {
        // Upload de arquivo
        $upload_dir = '../uploads/conselhos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['arquivo'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $stmt = $pdo->prepare("INSERT INTO conselho_arquivos (ano_id, titulo, arquivo_path) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['ano_id'], $_POST['titulo'], '/uploads/conselhos/' . $file_name]);
            header("Location: editar-conselho.php?id=$conselho_id&success=arquivo_added");
            exit;
        }
    }
    
    if ($_POST['action'] === 'delete_arquivo') {
        $stmt = $pdo->prepare("SELECT arquivo_path FROM conselho_arquivos WHERE id = ?");
        $stmt->execute([$_POST['arquivo_id']]);
        $arquivo = $stmt->fetch();
        
        if ($arquivo && file_exists('..' . $arquivo['arquivo_path'])) {
            unlink('..' . $arquivo['arquivo_path']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM conselho_arquivos WHERE id = ?");
        $stmt->execute([$_POST['arquivo_id']]);
        header("Location: editar-conselho.php?id=$conselho_id&success=arquivo_deleted");
        exit;
    }
    
    if ($_POST['action'] === 'delete_ano') {
        $stmt = $pdo->prepare("DELETE FROM conselho_anos WHERE id = ?");
        $stmt->execute([$_POST['ano_id']]);
        header("Location: editar-conselho.php?id=$conselho_id&success=ano_deleted");
        exit;
    }
    
    if ($_POST['action'] === 'delete_secao') {
        $stmt = $pdo->prepare("DELETE FROM conselho_secoes WHERE id = ?");
        $stmt->execute([$_POST['secao_id']]);
        header("Location: editar-conselho.php?id=$conselho_id&success=secao_deleted");
        exit;
    }
    
    if ($_POST['action'] === 'update_secao') {
        $stmt = $pdo->prepare("UPDATE conselho_secoes SET titulo = ? WHERE id = ?");
        $stmt->execute([$_POST['titulo'], $_POST['secao_id']]);
        header("Location: editar-conselho.php?id=$conselho_id&success=secao_updated");
        exit;
    }
    
    if ($_POST['action'] === 'update_ano') {
        $stmt = $pdo->prepare("UPDATE conselho_anos SET ano = ? WHERE id = ?");
        $stmt->execute([$_POST['ano'], $_POST['ano_id']]);
        header("Location: editar-conselho.php?id=$conselho_id&success=ano_updated");
        exit;
    }
}

// Buscar seções
$stmt = $pdo->prepare("SELECT * FROM conselho_secoes WHERE conselho_id = ? ORDER BY ordem ASC");
$stmt->execute([$conselho_id]);
$secoes = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="conselhos.php" class="text-blue-600 hover:underline mb-4 inline-block">← Voltar para Conselhos</a>
        <h1 class="text-3xl font-bold text-gray-800">Editar Conselho</h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        Operação realizada com sucesso!
    </div>
    <?php endif; ?>

    <!-- Editar Dados do Conselho -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Dados do Conselho</h2>
            <button onclick="toggleEditConselho()" id="btnEditConselho" class="text-gray-600 hover:text-blue-600 transition-colors" title="Editar dados do conselho">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
        </div>
        <form method="POST" id="formConselho" class="space-y-4">
            <input type="hidden" name="action" value="update_conselho">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Conselho</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($conselho['nome']); ?>" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 bg-gray-50 cursor-not-allowed" id="inputNome">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <textarea name="descricao" rows="3" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 bg-gray-50 cursor-not-allowed" id="inputDescricao"><?php echo htmlspecialchars($conselho['descricao']); ?></textarea>
            </div>
            <div id="btnSaveConselho" style="display: none;">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Salvar Alterações
                </button>
                <button type="button" onclick="toggleEditConselho()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors ml-2">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    <script>
    function toggleEditConselho() {
        const nome = document.getElementById('inputNome');
        const descricao = document.getElementById('inputDescricao');
        const btnSave = document.getElementById('btnSaveConselho');
        const btnEdit = document.getElementById('btnEditConselho');
        
        if (nome.hasAttribute('readonly')) {
            // Habilitar edição
            nome.removeAttribute('readonly');
            descricao.removeAttribute('readonly');
            nome.classList.remove('bg-gray-50', 'cursor-not-allowed');
            descricao.classList.remove('bg-gray-50', 'cursor-not-allowed');
            nome.classList.add('bg-white');
            descricao.classList.add('bg-white');
            btnSave.style.display = 'block';
            btnEdit.style.display = 'none';
        } else {
            // Desabilitar edição
            nome.setAttribute('readonly', 'readonly');
            descricao.setAttribute('readonly', 'readonly');
            nome.classList.add('bg-gray-50', 'cursor-not-allowed');
            descricao.classList.add('bg-gray-50', 'cursor-not-allowed');
            nome.classList.remove('bg-white');
            descricao.classList.remove('bg-white');
            btnSave.style.display = 'none';
            btnEdit.style.display = 'block';
            // Resetar valores originais
            nome.value = '<?php echo htmlspecialchars($conselho['nome']); ?>';
            descricao.value = '<?php echo htmlspecialchars($conselho['descricao']); ?>';
        }
    }
    </script>

    <!-- Adicionar Nova Seção -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Adicionar Nova Seção</h2>
        <form method="POST" class="flex gap-4">
            <input type="hidden" name="action" value="add_secao">
            <input type="text" name="titulo" placeholder="Título da Seção" required class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 flex-grow">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-600">
                Adicionar Seção
            </button>
        </form>
    </div>

    <!-- Seções Existentes -->
    <?php foreach ($secoes as $secao): 
        // Buscar anos da seção
        $stmt = $pdo->prepare("SELECT * FROM conselho_anos WHERE secao_id = ? ORDER BY ano DESC");
        $stmt->execute([$secao['id']]);
        $anos = $stmt->fetchAll();
    ?>
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3 flex-1">
                <h2 class="text-2xl font-bold text-gray-800" id="secao_titulo_<?php echo $secao['id']; ?>">
                    <?php echo htmlspecialchars($secao['titulo']); ?>
                </h2>
                <button onclick="editarSecao(<?php echo $secao['id']; ?>, '<?php echo htmlspecialchars($secao['titulo']); ?>')" 
                        class="text-gray-400 hover:text-blue-600 transition-colors" 
                        title="Editar título da seção"
                        id="btn_edit_secao_<?php echo $secao['id']; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
                <form method="POST" style="display: none;" id="form_edit_secao_<?php echo $secao['id']; ?>" class="flex items-center gap-2 flex-1">
                    <input type="hidden" name="action" value="update_secao">
                    <input type="hidden" name="secao_id" value="<?php echo $secao['id']; ?>">
                    <input type="text" 
                           name="titulo" 
                           value="<?php echo htmlspecialchars($secao['titulo']); ?>" 
                           required 
                           class="border border-blue-500 rounded-lg px-3 py-1 text-xl font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 flex-1"
                           id="input_secao_<?php echo $secao['id']; ?>">
                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm">
                        Salvar
                    </button>
                    <button type="button" onclick="cancelarEditarSecao(<?php echo $secao['id']; ?>)" class="bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 text-sm">
                        Cancelar
                    </button>
                </form>
            </div>
            <div class="flex items-center gap-2">
                <!-- Botões de Ordenação -->
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="move_secao">
                    <input type="hidden" name="secao_id" value="<?php echo $secao['id']; ?>">
                    <input type="hidden" name="direcao" value="up">
                    <button type="submit" class="text-gray-600 hover:text-blue-600 transition-colors" title="Mover para cima">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                </form>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="move_secao">
                    <input type="hidden" name="secao_id" value="<?php echo $secao['id']; ?>">
                    <input type="hidden" name="direcao" value="down">
                    <button type="submit" class="text-gray-600 hover:text-blue-600 transition-colors" title="Mover para baixo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </form>
                <span class="text-gray-300 mx-2">|</span>
                <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta seção?')" class="inline">
                    <input type="hidden" name="action" value="delete_secao">
                    <input type="hidden" name="secao_id" value="<?php echo $secao['id']; ?>">
                    <button type="submit" class="text-red-600 hover:text-red-800">Excluir Seção</button>
                </form>
            </div>
        </div>

        <!-- Adicionar Ano -->
        <form method="POST" class="flex gap-4 mb-6">
            <input type="hidden" name="action" value="add_ano">
            <input type="hidden" name="secao_id" value="<?php echo $secao['id']; ?>">
            <input type="number" name="ano" placeholder="Ano (ex: 2025)" required min="2000" max="2100" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 flex-grow">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-600">
                Adicionar Ano
            </button>
        </form>

        <!-- Anos -->
        <?php foreach ($anos as $ano): 
            // Buscar arquivos do ano
            $stmt = $pdo->prepare("SELECT * FROM conselho_arquivos WHERE ano_id = ? ORDER BY ordem ASC");
            $stmt->execute([$ano['id']]);
            $arquivos = $stmt->fetchAll();
        ?>
        <div class="border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-bold text-gray-700" id="ano_titulo_<?php echo $ano['id']; ?>">
                        Ano: <?php echo $ano['ano']; ?>
                    </h3>
                    <button onclick="editarAno(<?php echo $ano['id']; ?>, <?php echo $ano['ano']; ?>)" 
                            class="text-gray-400 hover:text-blue-600 transition-colors" 
                            title="Editar ano"
                            id="btn_edit_ano_<?php echo $ano['id']; ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <form method="POST" style="display: none;" id="form_edit_ano_<?php echo $ano['id']; ?>" class="flex items-center gap-2">
                        <input type="hidden" name="action" value="update_ano">
                        <input type="hidden" name="ano_id" value="<?php echo $ano['id']; ?>">
                        <span class="text-lg font-bold text-gray-700">Ano:</span>
                        <input type="number" 
                               name="ano" 
                               value="<?php echo $ano['ano']; ?>" 
                               required 
                               min="2000" 
                               max="2100"
                               class="border border-blue-500 rounded px-2 py-1 w-24 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               id="input_ano_<?php echo $ano['id']; ?>">
                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm">
                            Salvar
                        </button>
                        <button type="button" onclick="cancelarEditarAno(<?php echo $ano['id']; ?>)" class="bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 text-sm">
                            Cancelar
                        </button>
                    </form>
                </div>
                <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este ano e todos seus arquivos?')">
                    <input type="hidden" name="action" value="delete_ano">
                    <input type="hidden" name="ano_id" value="<?php echo $ano['id']; ?>">
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Excluir Ano</button>
                </form>
            </div>

            <!-- Upload de Arquivo -->
            <form method="POST" enctype="multipart/form-data" class="bg-gray-50 rounded-lg p-4 mb-4">
                <input type="hidden" name="action" value="add_arquivo">
                <input type="hidden" name="ano_id" value="<?php echo $ano['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="titulo" placeholder="Título do Arquivo" required class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                    <input type="file" name="arquivo" required accept=".pdf,.doc,.docx,.xls,.xlsx" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-600">
                        Upload
                    </button>
                </div>
            </form>

            <!-- Lista de Arquivos -->
            <div class="space-y-2">
                <?php foreach ($arquivos as $arquivo): ?>
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700"><?php echo htmlspecialchars($arquivo['titulo']); ?></span>
                        <a href="<?php echo htmlspecialchars($arquivo['arquivo_path']); ?>" target="_blank" class="text-blue-600 hover:underline text-sm">Ver arquivo</a>
                    </div>
                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este arquivo?')">
                        <input type="hidden" name="action" value="delete_arquivo">
                        <input type="hidden" name="arquivo_id" value="<?php echo $arquivo['id']; ?>">
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Excluir</button>
                    </form>
                </div>
                <?php endforeach; ?>
                
                <?php if (count($arquivos) === 0): ?>
                <p class="text-gray-500 text-center py-4">Nenhum arquivo adicionado ainda</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (count($anos) === 0): ?>
        <p class="text-gray-500 text-center py-8">Nenhum ano adicionado ainda. Adicione um ano acima para começar.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    
    <?php if (count($secoes) === 0): ?>
    <div class="bg-white rounded-xl shadow-md p-8 text-center text-gray-500">
        Nenhuma seção criada ainda. Adicione uma seção acima para começar.
    </div>
    <?php endif; ?>
</div>

<script>
// Funções para editar seção
function editarSecao(secaoId, tituloAtual) {
    // Ocultar o título e botão de editar
    document.getElementById('secao_titulo_' + secaoId).style.display = 'none';
    document.getElementById('btn_edit_secao_' + secaoId).style.display = 'none';
    
    // Mostrar o formulário de edição
    const form = document.getElementById('form_edit_secao_' + secaoId);
    form.style.display = 'flex';
    
    // Focar no input
    const input = document.getElementById('input_secao_' + secaoId);
    input.focus();
    input.select();
}

function cancelarEditarSecao(secaoId) {
    // Mostrar o título e botão de editar
    document.getElementById('secao_titulo_' + secaoId).style.display = 'block';
    document.getElementById('btn_edit_secao_' + secaoId).style.display = 'block';
    
    // Ocultar o formulário de edição
    document.getElementById('form_edit_secao_' + secaoId).style.display = 'none';
}

// Funções para editar ano
function editarAno(anoId, anoAtual) {
    // Ocultar o título e botão de editar
    document.getElementById('ano_titulo_' + anoId).style.display = 'none';
    document.getElementById('btn_edit_ano_' + anoId).style.display = 'none';
    
    // Mostrar o formulário de edição
    const form = document.getElementById('form_edit_ano_' + anoId);
    form.style.display = 'flex';
    
    // Focar no input
    const input = document.getElementById('input_ano_' + anoId);
    input.focus();
    input.select();
}

function cancelarEditarAno(anoId) {
    // Mostrar o título e botão de editar
    document.getElementById('ano_titulo_' + anoId).style.display = 'block';
    document.getElementById('btn_edit_ano_' + anoId).style.display = 'block';
    
    // Ocultar o formulário de edição
    document.getElementById('form_edit_ano_' + anoId).style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
