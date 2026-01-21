<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$secao = null;
$erros = [];

// Processar formulário ANTES de incluir o header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    if (empty($titulo)) {
        $erros[] = "O título é obrigatório";
    }
    
    if (empty($erros)) {
        try {
            if ($id > 0) {
                // Atualizar
                $stmt = $db->prepare("
                    UPDATE progestao_secoes 
                    SET titulo = ?, ativo = ?
                    WHERE id = ?
                ");
                $stmt->execute([$titulo, $ativo, $id]);
                
                $_SESSION['success_message'] = "Seção atualizada com sucesso!";
            } else {
                // Buscar próxima ordem
                $stmt = $db->query("SELECT COALESCE(MAX(ordem), 0) + 1 as proxima_ordem FROM progestao_secoes");
                $proxima_ordem = $stmt->fetch()['proxima_ordem'];
                
                // Inserir
                $stmt = $db->prepare("
                    INSERT INTO progestao_secoes (titulo, ordem, ativo) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$titulo, $proxima_ordem, $ativo]);
                
                $_SESSION['success_message'] = "Seção criada com sucesso!";
            }
            
            header('Location: progestao.php');
            exit;
            
        } catch (PDOException $e) {
            error_log("Erro ao salvar seção: " . $e->getMessage());
            $erros[] = "Erro ao salvar seção. Tente novamente.";
        }
    }
}

// Se está editando, buscar dados da seção
if ($id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM progestao_secoes WHERE id = ?");
        $stmt->execute([$id]);
        $secao = $stmt->fetch();
        
        if (!$secao) {
            header('Location: progestao.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar seção: " . $e->getMessage());
        header('Location: progestao.php');
        exit;
    }
}

$pageTitle = 'Formulário de Seção - Pró-Gestão';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">
    <!-- Cabeçalho -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="progestao.php" class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">
                <?= $id > 0 ? 'Editar Seção' : 'Nova Seção' ?>
            </h1>
        </div>
        <p class="text-gray-600">Preencha os dados da seção abaixo</p>
    </div>

    <?php if (!empty($erros)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulário -->
    <form method="POST" class="bg-white rounded-lg shadow-md p-8">
        <div class="mb-6">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                Título da Seção <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="titulo" 
                   name="titulo" 
                   value="<?= htmlspecialchars($secao['titulo'] ?? '') ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Ex: Regimentos internos, atas e cronograma das reuniões"
                   required>
            <p class="text-sm text-gray-500 mt-1">Este título aparecerá como cabeçalho da seção na página</p>
        </div>

        <div class="mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" 
                       name="ativo" 
                       value="1"
                       <?= ($secao['ativo'] ?? 1) ? 'checked' : '' ?>
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700">Seção ativa</span>
            </label>
            <p class="text-sm text-gray-500 mt-1 ml-6">Se desmarcado, a seção não aparecerá no site</p>
        </div>

        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <a href="progestao.php" class="text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
            <button type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium">
                <?= $id > 0 ? 'Atualizar Seção' : 'Criar Seção' ?>
            </button>
        </div>
    </form>

    <?php if ($id > 0): ?>
        <!-- Info adicional -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 mb-2">Próximos passos</h3>
            <p class="text-sm text-blue-800">
                Após salvar a seção, você pode adicionar cards (documentos/links) clicando no botão 
                "Adicionar Card" na listagem principal.
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
