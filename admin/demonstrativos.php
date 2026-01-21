<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

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

// Buscar demonstrativos
$busca = $_GET['busca'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$ano = $_GET['ano'] ?? '';

$sql = "SELECT d.*, c.nome as categoria_nome 
        FROM demonstrativos d 
        LEFT JOIN categorias_demonstrativos c ON d.categoria_id = c.id 
        WHERE 1=1";

$params = [];

if (!empty($busca)) {
    $sql .= " AND d.titulo LIKE ?";
    $params[] = "%$busca%";
}

if (!empty($categoria) && is_numeric($categoria)) {
    $sql .= " AND d.categoria_id = ?";
    $params[] = $categoria;
}

if (!empty($ano)) {
    $sql .= " AND d.ano = ?";
    $params[] = $ano;
}

$sql .= " ORDER BY d.ano DESC, d.data_documento DESC, d.ordem";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demonstrativos = $stmt->fetchAll();

// Buscar categorias
$stmtCat = $pdo->query("SELECT * FROM categorias_demonstrativos WHERE ativo = 1 ORDER BY ordem, nome");
$categorias = $stmtCat->fetchAll();

// Buscar anos distintos
$stmtAnos = $pdo->query("SELECT DISTINCT ano FROM demonstrativos ORDER BY ano DESC");
$anos = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Demonstrativos - Admin FAP</title>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Gerenciar Demonstrativos</h1>
            <a href="demonstrativo_form" class="bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors font-medium text-center">
                + Novo Demonstrativo
            </a>
        </div>

        <?php if ($mensagem): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipoMensagem === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" 
                       name="busca" 
                       value="<?php echo htmlspecialchars($busca); ?>" 
                       placeholder="Buscar demonstrativo..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                
                <select name="categoria" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="ano" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <option value="">Todos os anos</option>
                    <?php foreach ($anos as $anoItem): ?>
                        <option value="<?php echo $anoItem; ?>" <?php echo $ano == $anoItem ? 'selected' : ''; ?>>
                            <?php echo $anoItem; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-primary text-white px-6 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                        Filtrar
                    </button>
                    
                    <?php if ($busca || $categoria || $ano): ?>
                        <a href="demonstrativos" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                            Limpar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabela -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table id="demonstrativosTable" class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Categoria</th>
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
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    Nenhum demonstrativo encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($demonstrativos as $demo): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-700">
                                            <?php echo htmlspecialchars($demo['categoria_nome']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($demo['titulo']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        <?php echo date('d/m/Y', strtotime($demo['data_documento'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            <?php echo $demo['ano']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="../<?php echo htmlspecialchars($demo['arquivo']); ?>" 
                                           target="_blank"
                                           class="text-red-600 hover:text-red-800 inline-flex items-center gap-1">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
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
                                            <a href="demonstrativo_form?id=<?php echo $demo['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                                               title="Editar">
                                                ✏️
                                            </a>
                                            <a href="?toggle=<?php echo $demo['id']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-800 font-medium text-sm"
                                               title="<?php echo $demo['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                               onclick="return confirm('Deseja alterar o status?')">
                                                🔄
                                            </a>
                                            <a href="?deletar=<?php echo $demo['id']; ?>" 
                                               class="text-red-600 hover:text-red-800 font-medium text-sm"
                                               title="Deletar"
                                               onclick="return confirm('Tem certeza? O arquivo também será deletado.')">
                                                🗑️
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#demonstrativosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                pageLength: 25,
                order: [[3, 'desc'], [2, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [4, 6] }
                ]
            });
        });
    </script>
</body>
</html>
