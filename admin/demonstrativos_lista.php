<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

// Verificar se categoria foi passada
$categoria_id = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;

if (!$categoria_id) {
    header('Location: demonstrativos_index.php');
    exit;
}

// Buscar categoria
$stmtCat = $pdo->prepare("SELECT * FROM categorias_demonstrativos WHERE id = ?");
$stmtCat->execute([$categoria_id]);
$categoria = $stmtCat->fetch();

if (!$categoria) {
    header('Location: demonstrativos_index.php');
    exit;
}

// Config de ícones e cores
$config = [
    1 => ['icone' => '📋', 'cor' => 'blue', 'slug' => 'dipr'],
    2 => ['icone' => '💹', 'cor' => 'green', 'slug' => 'dair'],
    3 => ['icone' => '📊', 'cor' => 'purple', 'slug' => 'dpin'],
    4 => ['icone' => '📈', 'cor' => 'orange', 'slug' => 'draa']
];
$cfg = $config[$categoria_id] ?? ['icone' => '📄', 'cor' => 'gray', 'slug' => ''];

$mensagem = '';
$tipoMensagem = '';

// Deletar demonstrativo
if (isset($_GET['deletar']) && is_numeric($_GET['deletar'])) {
    $id = $_GET['deletar'];
    
    // Buscar arquivo para deletar
    $stmt = $pdo->prepare("SELECT arquivo FROM demonstrativos WHERE id = ?");
    $stmt->execute([$id]);
    $demo = $stmt->fetch();
    
    if ($demo && file_exists('../' . $demo['arquivo'])) {
        unlink('../' . $demo['arquivo']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM demonstrativos WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensagem = 'Demonstrativo deletado com sucesso!';
        $tipoMensagem = 'success';
    } else {
        $mensagem = 'Erro ao deletar demonstrativo.';
        $tipoMensagem = 'error';
    }
}

// Alternar status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE demonstrativos SET ativo = NOT ativo WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensagem = 'Status alterado com sucesso!';
        $tipoMensagem = 'success';
    } else {
        $mensagem = 'Erro ao alterar status.';
        $tipoMensagem = 'error';
    }
}

// Buscar demonstrativos desta categoria
$busca = $_GET['busca'] ?? '';
$ano = $_GET['ano'] ?? '';

$sql = "SELECT * FROM demonstrativos WHERE categoria_id = ?";
$params = [$categoria_id];

if (!empty($busca)) {
    $sql .= " AND titulo LIKE ?";
    $params[] = "%$busca%";
}

if (!empty($ano)) {
    $sql .= " AND ano = ?";
    $params[] = $ano;
}

$sql .= " ORDER BY ano DESC, data_documento DESC, ordem";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demonstrativos = $stmt->fetchAll();

// Buscar anos distintos desta categoria
$stmtAnos = $pdo->prepare("SELECT DISTINCT ano FROM demonstrativos WHERE categoria_id = ? ORDER BY ano DESC");
$stmtAnos->execute([$categoria_id]);
$anos = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoria['nome']); ?> - Admin FAP</title>
    <link rel="icon" type="image/png" href="/imagens/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'green-primary': '#00A859',
                        'blue-primary': '#1e3a8a'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center gap-2 text-sm text-gray-600">
                <li>
                    <a href="demonstrativos_index.php" class="hover:text-blue-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Demonstrativos
                    </a>
                </li>
                <li class="text-gray-400">›</li>
                <li class="font-medium text-gray-800"><?php echo htmlspecialchars($categoria['nome']); ?></li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="text-4xl"><?php echo $cfg['icone']; ?></span>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($categoria['nome']); ?></h1>
                    <p class="text-gray-500 text-sm"><?php echo count($demonstrativos); ?> documento(s)</p>
                </div>
            </div>
            <a href="demonstrativo_form.php?categoria=<?php echo $categoria_id; ?>" 
               class="bg-<?php echo $cfg['cor']; ?>-500 text-white px-6 py-3 rounded-lg hover:bg-<?php echo $cfg['cor']; ?>-600 transition-colors font-medium text-center flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Demonstrativo
            </a>
        </div>

        <?php if ($mensagem): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipoMensagem === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
                
                <input type="text" 
                       name="busca" 
                       value="<?php echo htmlspecialchars($busca); ?>" 
                       placeholder="Buscar demonstrativo..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-<?php echo $cfg['cor']; ?>-400">

                <select name="ano" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-<?php echo $cfg['cor']; ?>-400">
                    <option value="">Todos os anos</option>
                    <?php foreach ($anos as $anoItem): ?>
                        <option value="<?php echo $anoItem; ?>" <?php echo $ano == $anoItem ? 'selected' : ''; ?>>
                            <?php echo $anoItem; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-<?php echo $cfg['cor']; ?>-500 text-white px-6 py-2 rounded-lg hover:bg-<?php echo $cfg['cor']; ?>-600 transition-colors">
                        Filtrar
                    </button>
                    
                    <?php if ($busca || $ano): ?>
                        <a href="?categoria=<?php echo $categoria_id; ?>" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                            Limpar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabela -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-<?php echo $cfg['cor']; ?>-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Título</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Data</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Ano</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Arquivo</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($demonstrativos)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Nenhum demonstrativo encontrado nesta categoria.
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($demonstrativos as $demo): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($demo['titulo']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        <?php echo $demo['data_documento'] ? date('d/m/Y', strtotime($demo['data_documento'])) : '-'; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?php echo $cfg['cor']; ?>-100 text-<?php echo $cfg['cor']; ?>-800">
                                            <?php echo $demo['ano']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="../<?php echo htmlspecialchars($demo['arquivo']); ?>" 
                                           target="_blank"
                                           class="text-red-600 hover:text-red-800 inline-flex items-center gap-1"
                                           title="Ver PDF">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($demo['ativo']): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Ativo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inativo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="demonstrativo_form.php?id=<?php echo $demo['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50"
                                               title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <a href="?categoria=<?php echo $categoria_id; ?>&toggle=<?php echo $demo['id']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-800 p-1 rounded hover:bg-yellow-50"
                                               title="<?php echo $demo['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                               onclick="return confirm('Deseja alterar o status?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </a>
                                            <a href="?categoria=<?php echo $categoria_id; ?>&deletar=<?php echo $demo['id']; ?>" 
                                               class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50"
                                               title="Deletar"
                                               onclick="return confirm('Tem certeza? O arquivo também será deletado.')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 text-gray-600 text-sm">
            Total de <?php echo count($demonstrativos); ?> demonstrativo(s) encontrado(s).
        </div>
    </div>
</body>
</html>
