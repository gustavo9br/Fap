<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado e se pode editar notícias
if (!Session::isEditor()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Gerenciar Notícias';
include 'includes/header.php';

// Buscar notícias
try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar categorias para filtro
    $stmt_cats = $db->query("SELECT slug, nome FROM categorias WHERE ativa = 1 ORDER BY nome");
    $categorias = $stmt_cats->fetchAll();
    
    $filtro_status = $_GET['status'] ?? '';
    $filtro_categoria = $_GET['categoria'] ?? '';
    $busca = $_GET['busca'] ?? '';
    
    $sql = "
        SELECT n.*, u.nome as autor_nome 
        FROM noticias n 
        JOIN usuarios u ON n.autor_id = u.id 
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($filtro_status) {
        $sql .= " AND n.status = ?";
        $params[] = $filtro_status;
    }
    
    if ($filtro_categoria) {
        $sql .= " AND n.categoria = ?";
        $params[] = $filtro_categoria;
    }
    
    if ($busca) {
        $sql .= " AND (n.titulo LIKE ? OR n.resumo LIKE ?)";
        $params[] = "%$busca%";
        $params[] = "%$busca%";
    }
    
    $sql .= " ORDER BY n.criado_em DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $noticias = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Erro ao buscar notícias: " . $e->getMessage());
    $noticias = [];
}

// Mensagem flash
$mensagem = Session::getFlash('mensagem');
$tipo_mensagem = Session::getFlash('tipo_mensagem');
?>

<div class="container mx-auto px-4 py-8">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gerenciar Notícias</h1>
            <p class="text-gray-600 mt-2">Crie, edite e publique notícias</p>
        </div>
        <div class="flex gap-3">
            <a href="categorias.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                </svg>
                Categorias
            </a>
            <a href="noticia_form.php" class="bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-dark transition-all font-medium shadow-lg">
                + Nova Notícia
            </a>
        </div>
    </div>

    <!-- Mensagem de Feedback -->
    <?php if ($mensagem): ?>
        <div class="mb-6 p-4 rounded-lg <?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    name="busca" 
                    placeholder="Buscar por título ou resumo..." 
                    value="<?= htmlspecialchars($busca) ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                >
            </div>
            <select name="categoria" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary">
                <option value="">Todas categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $filtro_categoria === $cat['slug'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary">
                <option value="">Todos os status</option>
                <option value="rascunho" <?= $filtro_status === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                <option value="publicado" <?= $filtro_status === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                <option value="arquivado" <?= $filtro_status === 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
            </select>
            <button type="submit" class="bg-green-primary text-white px-6 py-2 rounded-lg hover:bg-green-dark">
                Filtrar
            </button>
            <?php if ($busca || $filtro_status || $filtro_categoria): ?>
                <a href="noticias.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                    Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lista de Notícias -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <?php if (empty($noticias)): ?>
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500">Nenhuma notícia encontrada</p>
                <a href="noticia_form.php" class="inline-block mt-4 text-green-primary hover:text-green-dark">
                    Criar primeira notícia →
                </a>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Notícia</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Autor</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Visualizações</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($noticias as $noticia): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($noticia['imagem_destaque']): ?>
                                        <img src="../<?= htmlspecialchars($noticia['imagem_destaque']) ?>" alt="" class="w-12 h-12 rounded object-cover">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($noticia['titulo']) ?></p>
                                        <?php if ($noticia['categoria']): ?>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <span class="bg-gray-100 px-2 py-0.5 rounded"><?= htmlspecialchars($noticia['categoria']) ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= htmlspecialchars($noticia['autor_nome']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                    <?php
                                    switch($noticia['status']) {
                                        case 'publicado':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'rascunho':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'arquivado':
                                            echo 'bg-gray-100 text-gray-800';
                                            break;
                                    }
                                    ?>">
                                    <?= ucfirst($noticia['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= number_format($noticia['visualizacoes'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= date('d/m/Y', strtotime($noticia['criado_em'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="noticia_form.php?id=<?= $noticia['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Editar">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </a>
                                    <?php if ($noticia['status'] === 'publicado'): ?>
                                        <a href="../noticia/<?= $noticia['slug'] ?>" target="_blank" class="text-green-600 hover:text-green-800" title="Ver">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="confirmarExclusao(<?= $noticia['id'] ?>, '<?= addslashes($noticia['titulo']) ?>')" class="text-red-600 hover:text-red-800" title="Excluir">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmarExclusao(id, titulo) {
    if (confirm(`Tem certeza que deseja excluir a notícia "${titulo}"?`)) {
        window.location.href = `noticia_delete.php?id=${id}`;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
