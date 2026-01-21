<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Dashboard';
include 'includes/header.php';

// Buscar estatísticas
try {
    $db = Database::getInstance()->getConnection();
    
    // Total de notícias
    $stmt = $db->query("SELECT COUNT(*) as total FROM noticias WHERE status = 'publicado'");
    $total_noticias = $stmt->fetch()['total'];
    
    // Total de arquivos
    $stmt = $db->query("SELECT COUNT(*) as total FROM arquivos");
    $total_arquivos = $stmt->fetch()['total'];
    
    // Total de downloads
    $stmt = $db->query("SELECT SUM(downloads) as total FROM arquivos");
    $total_downloads = $stmt->fetch()['total'] ?? 0;
    
    // Últimas notícias
    $stmt = $db->query("
        SELECT n.*, u.nome as autor_nome 
        FROM noticias n 
        JOIN usuarios u ON n.autor_id = u.id 
        ORDER BY n.criado_em DESC 
        LIMIT 5
    ");
    $ultimas_noticias = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Cabeçalho -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Bem-vindo, <?= htmlspecialchars(Session::getUserName()) ?>!</h1>
        <p class="text-gray-600 mt-2">Painel de Gerenciamento - FAP Pádua</p>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Notícias -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-primary">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase">Notícias Publicadas</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $total_noticias ?></p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Arquivos -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase">Total de Arquivos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $total_arquivos ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Downloads -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase">Total de Downloads</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= number_format($total_downloads, 0, ',', '.') ?></p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Notícias -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">Últimas Notícias</h2>
            <a href="noticias.php" class="text-green-primary hover:text-green-dark font-medium">
                Ver todas →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Autor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($ultimas_noticias as $noticia): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($noticia['titulo']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($noticia['autor_nome']) ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full <?= $noticia['status'] === 'publicado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= ucfirst($noticia['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?= date('d/m/Y H:i', strtotime($noticia['criado_em'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
