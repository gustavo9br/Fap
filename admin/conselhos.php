<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Conselhos e Comitês';

$pdo = Database::getInstance()->getConnection();

// Buscar todos os conselhos
$stmt = $pdo->query("SELECT * FROM conselhos ORDER BY ordem ASC");
$conselhos = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Conselhos e Comitês</h1>
    </div>

    <!-- Grid de Conselhos -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($conselhos as $conselho): ?>
        <a href="editar-conselho?id=<?php echo $conselho['id']; ?>" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="text-center">
                <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-xl mb-4 mx-auto">
                    <?php if ($conselho['slug'] == 'conselho-administrativo'): ?>
                    <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                    </svg>
                    <?php elseif ($conselho['slug'] == 'conselho-fiscal'): ?>
                    <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <?php elseif ($conselho['slug'] == 'comite-investimentos'): ?>
                    <svg class="w-10 h-10 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($conselho['nome']); ?></h3>
                <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars(substr($conselho['descricao'], 0, 100)) . '...'; ?></p>
                <span class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600">
                    Gerenciar →
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
