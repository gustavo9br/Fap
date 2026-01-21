<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Apenas admin pode acessar
if (!Session::isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Configurações do Site';
$mensagem = '';
$tipo_mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        foreach ($_POST as $chave => $valor) {
            if (strpos($chave, 'config_') === 0) {
                $chave_config = str_replace('config_', '', $chave);
                
                $stmt = $db->prepare("
                    UPDATE configuracoes 
                    SET valor = ? 
                    WHERE chave = ?
                ");
                $stmt->execute([$valor, $chave_config]);
            }
        }
        
        $mensagem = 'Configurações salvas com sucesso!';
        $tipo_mensagem = 'sucesso';
        
    } catch (PDOException $e) {
        error_log("Erro ao salvar configurações: " . $e->getMessage());
        $mensagem = 'Erro ao salvar configurações.';
        $tipo_mensagem = 'erro';
    }
}

// Buscar configurações
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM configuracoes ORDER BY chave");
    $configuracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar configurações: " . $e->getMessage());
    $configuracoes = [];
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Configurações do Site</h1>
        <p class="text-gray-600 mt-2">Gerencie as configurações gerais do sistema</p>
    </div>

    <?php if ($mensagem): ?>
        <div class="mb-6 p-4 rounded-lg <?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-xl shadow-lg p-8">
        
        <!-- Configurações de IA -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">
                🤖 Inteligência Artificial
            </h2>
            <p class="text-sm text-gray-600 mb-4">Configure a API de IA para geração automática de notícias</p>
            
            <?php foreach ($configuracoes as $config): ?>
                <?php if (strpos($config['chave'], 'ia_') === 0): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <?= htmlspecialchars($config['descricao']) ?>
                        </label>
                        
                        <?php if ($config['tipo'] === 'senha'): ?>
                            <input 
                                type="password" 
                                name="config_<?= $config['chave'] ?>"
                                value="<?= htmlspecialchars($config['valor']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                                placeholder="sk-..."
                            >
                            <p class="text-xs text-gray-500 mt-1">
                                <?php if ($config['chave'] === 'ia_api_key'): ?>
                                    Para OpenAI: obtenha em <a href="https://platform.openai.com/api-keys" target="_blank" class="text-green-primary hover:underline">platform.openai.com/api-keys</a>
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <input 
                                type="text" 
                                name="config_<?= $config['chave'] ?>"
                                value="<?= htmlspecialchars($config['valor']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                            >
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Configurações do Site -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">
                🌐 Configurações Gerais
            </h2>
            
            <?php foreach ($configuracoes as $config): ?>
                <?php if (strpos($config['chave'], 'site_') === 0): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <?= htmlspecialchars($config['descricao']) ?>
                        </label>
                        
                        <?php if ($config['tipo'] === 'textarea'): ?>
                            <textarea 
                                name="config_<?= $config['chave'] ?>"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                            ><?= htmlspecialchars($config['valor']) ?></textarea>
                        <?php else: ?>
                            <input 
                                type="text" 
                                name="config_<?= $config['chave'] ?>"
                                value="<?= htmlspecialchars($config['valor']) ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                            >
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Configurações de Upload -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">
                📁 Upload de Arquivos
            </h2>
            
            <?php foreach ($configuracoes as $config): ?>
                <?php if (strpos($config['chave'], 'uploads_') === 0): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <?= htmlspecialchars($config['descricao']) ?>
                        </label>
                        <input 
                            type="number" 
                            name="config_<?= $config['chave'] ?>"
                            value="<?= htmlspecialchars($config['valor']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Atual: <?= number_format($config['valor'] / 1048576, 2) ?> MB
                        </p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="flex gap-4">
            <button 
                type="submit" 
                class="bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-dark transition-all font-medium shadow-lg"
            >
                Salvar Configurações
            </button>
            <a 
                href="index.php" 
                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-all font-medium"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
