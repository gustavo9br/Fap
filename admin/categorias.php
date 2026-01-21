<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se é admin
if (!Session::isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Gerenciar Categorias';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        $acao = $_POST['acao'] ?? '';
        
        if ($acao === 'criar') {
            $nome = trim($_POST['nome'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = trim($_POST['cor'] ?? '#2ecc71');
            $icone = trim($_POST['icone'] ?? 'folder');
            
            if (empty($nome)) {
                throw new Exception('Nome da categoria é obrigatório');
            }
            
            // Gerar slug automaticamente se vazio
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', 
                    iconv('UTF-8', 'ASCII//TRANSLIT', $nome)
                ), '-'));
            }
            
            $stmt = $db->prepare("INSERT INTO categorias (nome, slug, descricao, cor, icone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $slug, $descricao, $cor, $icone]);
            
            Session::setFlash('mensagem', 'Categoria criada com sucesso!');
            Session::setFlash('tipo_mensagem', 'sucesso');
            
        } elseif ($acao === 'editar') {
            $id = $_POST['id'] ?? 0;
            $nome = trim($_POST['nome'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = trim($_POST['cor'] ?? '#2ecc71');
            $icone = trim($_POST['icone'] ?? 'folder');
            $ativa = isset($_POST['ativa']) ? 1 : 0;
            
            if (empty($nome)) {
                throw new Exception('Nome da categoria é obrigatório');
            }
            
            $stmt = $db->prepare("UPDATE categorias SET nome = ?, slug = ?, descricao = ?, cor = ?, icone = ?, ativa = ? WHERE id = ?");
            $stmt->execute([$nome, $slug, $descricao, $cor, $icone, $ativa, $id]);
            
            Session::setFlash('mensagem', 'Categoria atualizada com sucesso!');
            Session::setFlash('tipo_mensagem', 'sucesso');
            
        } elseif ($acao === 'deletar') {
            $id = $_POST['id'] ?? 0;
            
            // Verificar se há notícias usando esta categoria
            $stmt = $db->prepare("SELECT COUNT(*) FROM noticias WHERE categoria = (SELECT slug FROM categorias WHERE id = ?)");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception("Não é possível deletar. Existem $count notícia(s) usando esta categoria.");
            }
            
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            
            Session::setFlash('mensagem', 'Categoria deletada com sucesso!');
            Session::setFlash('tipo_mensagem', 'sucesso');
        }
        
        header('Location: categorias.php');
        exit;
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar categorias
$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM noticias WHERE categoria = c.slug) as total_noticias
    FROM categorias c 
    ORDER BY c.ordem, c.nome
");
$categorias = $stmt->fetchAll();

// Mensagens flash
$mensagem = Session::getFlash('mensagem');
$tipo_mensagem = Session::getFlash('tipo_mensagem');

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Categorias de Notícias</h1>
            <p class="text-gray-600 mt-2">Gerencie as categorias para organizar as notícias do site</p>
        </div>
        <button onclick="abrirModal()" class="bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-dark transition-all font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Nova Categoria
        </button>
    </div>

    <?php if ($mensagem): ?>
        <div class="mb-6 p-4 rounded-lg <?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($erro)): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <!-- Lista de Categorias -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notícias</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($categorias as $cat): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: <?= htmlspecialchars($cat['cor']) ?>20;">
                                    <span class="text-2xl"><?= $cat['icone'] === 'building' ? '🏛️' : ($cat['icone'] === 'users' ? '👥' : ($cat['icone'] === 'user-check' ? '👴' : ($cat['icone'] === 'file-text' ? '📄' : ($cat['icone'] === 'calendar' ? '📅' : ($cat['icone'] === 'megaphone' ? '📢' : '📁'))))) ?></span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($cat['nome']) ?></div>
                                    <?php if (!empty($cat['descricao'])): ?>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($cat['descricao']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($cat['slug']) ?></code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?= $cat['total_noticias'] ?> notícia<?= $cat['total_noticias'] != 1 ? 's' : '' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($cat['ativa']): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Ativa
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inativa
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick='editarCategoria(<?= json_encode($cat) ?>)' class="text-blue-600 hover:text-blue-900 mr-3">
                                Editar
                            </button>
                            <?php if ($cat['total_noticias'] == 0): ?>
                                <button onclick="deletarCategoria(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['nome'], ENT_QUOTES) ?>')" class="text-red-600 hover:text-red-900">
                                    Deletar
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Criar/Editar Categoria -->
<div id="modal-categoria" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full">
        <form method="POST" id="form-categoria">
            <input type="hidden" name="acao" id="acao" value="criar">
            <input type="hidden" name="id" id="categoria-id">
            
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800" id="modal-titulo">Nova Categoria</h2>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                    <input type="text" name="nome" id="categoria-nome" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                           placeholder="Ex: Institucional, Servidores...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slug (URL)</label>
                    <input type="text" name="slug" id="categoria-slug"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                           placeholder="institucional, servidores... (deixe vazio para gerar automaticamente)">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                    <textarea name="descricao" id="categoria-descricao" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                              placeholder="Breve descrição da categoria..."></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cor</label>
                        <input type="color" name="cor" id="categoria-cor" value="#2ecc71"
                               class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ícone</label>
                        <select name="icone" id="categoria-icone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary">
                            <option value="building">🏛️ Prédio</option>
                            <option value="users">👥 Pessoas</option>
                            <option value="user-check">👴 Aposentado</option>
                            <option value="file-text">📄 Documento</option>
                            <option value="calendar">📅 Calendário</option>
                            <option value="megaphone">📢 Megafone</option>
                            <option value="folder">📁 Pasta</option>
                        </select>
                    </div>
                </div>
                
                <div id="campo-ativa" class="hidden">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="ativa" id="categoria-ativa" value="1" class="w-5 h-5 text-green-primary">
                        <span class="text-sm font-medium text-gray-700">Categoria ativa</span>
                    </label>
                </div>
            </div>
            
            <div class="p-6 border-t border-gray-200 bg-gray-50 flex gap-3">
                <button type="submit" class="flex-1 bg-green-primary text-white px-6 py-2 rounded-lg hover:bg-green-dark transition-all font-medium">
                    Salvar
                </button>
                <button type="button" onclick="fecharModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto para deletar -->
<form id="form-deletar" method="POST" style="display: none;">
    <input type="hidden" name="acao" value="deletar">
    <input type="hidden" name="id" id="deletar-id">
</form>

<script>
function abrirModal() {
    document.getElementById('modal-categoria').classList.remove('hidden');
    document.getElementById('modal-titulo').textContent = 'Nova Categoria';
    document.getElementById('acao').value = 'criar';
    document.getElementById('form-categoria').reset();
    document.getElementById('campo-ativa').classList.add('hidden');
}

function fecharModal() {
    document.getElementById('modal-categoria').classList.add('hidden');
}

function editarCategoria(cat) {
    document.getElementById('modal-categoria').classList.remove('hidden');
    document.getElementById('modal-titulo').textContent = 'Editar Categoria';
    document.getElementById('acao').value = 'editar';
    document.getElementById('categoria-id').value = cat.id;
    document.getElementById('categoria-nome').value = cat.nome;
    document.getElementById('categoria-slug').value = cat.slug;
    document.getElementById('categoria-descricao').value = cat.descricao || '';
    document.getElementById('categoria-cor').value = cat.cor;
    document.getElementById('categoria-icone').value = cat.icone;
    document.getElementById('categoria-ativa').checked = cat.ativa == 1;
    document.getElementById('campo-ativa').classList.remove('hidden');
}

function deletarCategoria(id, nome) {
    if (confirm(`Tem certeza que deseja deletar a categoria "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        document.getElementById('deletar-id').value = id;
        document.getElementById('form-deletar').submit();
    }
}

// Fechar modal ao clicar fora
document.getElementById('modal-categoria').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
