<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

$mensagem = '';
$tipoMensagem = '';

// Deletar categoria
if (isset($_GET['deletar']) && is_numeric($_GET['deletar'])) {
    $id = $_GET['deletar'];
    
    // Verificar se há convênios usando esta categoria
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM convenios WHERE categoria_id = ?");
    $stmtCheck->execute([$id]);
    $count = $stmtCheck->fetchColumn();
    
    if ($count > 0) {
        $mensagem = 'Não é possível deletar esta categoria pois existem ' . $count . ' convênio(s) vinculado(s) a ela.';
        $tipoMensagem = 'error';
    } else {
        $stmt = $pdo->prepare("DELETE FROM convenios_categorias WHERE id = ?");
        if ($stmt->execute([$id])) {
            $mensagem = 'Categoria deletada com sucesso!';
            $tipoMensagem = 'success';
        } else {
            $mensagem = 'Erro ao deletar categoria.';
            $tipoMensagem = 'error';
        }
    }
}

// Alternar status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE convenios_categorias SET ativo = NOT ativo WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensagem = 'Status alterado com sucesso!';
        $tipoMensagem = 'success';
    } else {
        $mensagem = 'Erro ao alterar status.';
        $tipoMensagem = 'error';
    }
}

// Buscar categorias
$busca = $_GET['busca'] ?? '';

$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM convenios WHERE categoria_id = c.id) as total_convenios 
        FROM convenios_categorias c 
        WHERE 1=1";

$params = [];

if (!empty($busca)) {
    $sql .= " AND c.nome LIKE ?";
    $params[] = "%$busca%";
}

$sql .= " ORDER BY c.ordem, c.nome";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - Admin FAP</title>
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
    <style>
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            padding: 0.5rem;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Gerenciar Categorias</h1>
            <div class="flex gap-3">
                <a href="convenios.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors font-medium">
                    ← Voltar para Convênios
                </a>
                <a href="categoria_convenio_form.php" class="bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors font-medium">
                    + Nova Categoria
                </a>
            </div>
        </div>

        <?php if ($mensagem): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipoMensagem === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <input type="text" 
                       name="busca" 
                       value="<?php echo htmlspecialchars($busca); ?>" 
                       placeholder="Buscar categoria..." 
                       class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">

                <button type="submit" class="bg-blue-primary text-white px-6 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                    Filtrar
                </button>
                
                <?php if ($busca): ?>
                    <a href="categorias_convenios.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela de Categorias -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table id="categoriasTable" class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nome</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Slug</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Cor</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Ordem</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Convênios</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Nenhuma categoria encontrada.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($categoria['nome']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($categoria['slug']); ?></code>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: <?php echo htmlspecialchars($categoria['cor']); ?>"></div>
                                        <code class="text-xs text-gray-600"><?php echo htmlspecialchars($categoria['cor']); ?></code>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <?php echo $categoria['ordem']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $categoria['total_convenios'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'; ?>">
                                        <?php echo $categoria['total_convenios']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($categoria['ativo']): ?>
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
                                        <a href="categoria_convenio_form.php?id=<?php echo $categoria['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                                           title="Editar">
                                            ✏️
                                        </a>
                                        <a href="?toggle=<?php echo $categoria['id']; ?>" 
                                           class="text-yellow-600 hover:text-yellow-800 font-medium text-sm"
                                           title="<?php echo $categoria['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                           onclick="return confirm('Deseja alterar o status desta categoria?')">
                                            🔄
                                        </a>
                                        <a href="?deletar=<?php echo $categoria['id']; ?>" 
                                           class="text-red-600 hover:text-red-800 font-medium text-sm"
                                           title="Deletar"
                                           onclick="return confirm('Tem certeza que deseja deletar esta categoria? Esta ação não pode ser desfeita.')">
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

        <div class="mt-6 text-gray-600 text-sm">
            Total de <?php echo count($categorias); ?> categoria(s) encontrada(s).
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#categoriasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                pageLength: 25,
                order: [[3, 'asc'], [0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [2, 6] }
                ]
            });
        });
    </script>
</body>
</html>
