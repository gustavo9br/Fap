<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

$mensagem = '';
$tipoMensagem = '';

// Deletar convênio
if (isset($_GET['deletar']) && is_numeric($_GET['deletar'])) {
    $id = $_GET['deletar'];
    $stmt = $pdo->prepare("DELETE FROM convenios WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensagem = 'Convênio deletado com sucesso!';
        $tipoMensagem = 'success';
    } else {
        $mensagem = 'Erro ao deletar convênio.';
        $tipoMensagem = 'error';
    }
}

// Alternar status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE convenios SET ativo = NOT ativo WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensagem = 'Status alterado com sucesso!';
        $tipoMensagem = 'success';
    } else {
        $mensagem = 'Erro ao alterar status.';
        $tipoMensagem = 'error';
    }
}

// Buscar convênios
$busca = $_GET['busca'] ?? '';
$categoria = $_GET['categoria'] ?? '';

$sql = "SELECT c.*, cat.nome as categoria_nome, cat.cor as categoria_cor 
        FROM convenios c 
        LEFT JOIN convenios_categorias cat ON c.categoria_id = cat.id 
        WHERE 1=1";

$params = [];

if (!empty($busca)) {
    $sql .= " AND (c.nome LIKE ? OR c.descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if (!empty($categoria) && is_numeric($categoria)) {
    $sql .= " AND c.categoria_id = ?";
    $params[] = $categoria;
}

$sql .= " ORDER BY c.ordem, c.nome";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$convenios = $stmt->fetchAll();

// Buscar categorias para o filtro
$stmtCat = $pdo->query("SELECT * FROM convenios_categorias WHERE ativo = 1 ORDER BY ordem, nome");
$categorias = $stmtCat->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Convênios - Admin FAP</title>
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
            <h1 class="text-3xl font-bold text-gray-800">Gerenciar Convênios</h1>
            <div class="flex gap-3">
                <a href="categorias_convenios.php" class="bg-blue-primary text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition-colors font-medium">
                    📁 Categorias
                </a>
                <a href="convenio_form.php" class="bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors font-medium">
                    + Novo Convênio
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
                       placeholder="Buscar convênio..." 
                       class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                
                <select name="categoria" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="bg-blue-primary text-white px-6 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                    Filtrar
                </button>
                
                <?php if ($busca || $categoria): ?>
                    <a href="convenios.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela de Convênios -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table id="conveniosTable" class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Logo</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nome</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Categoria</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Desconto</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Contato</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($convenios)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Nenhum convênio encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($convenios as $convenio): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <?php if ($convenio['logo']): ?>
                                        <img src="../<?php echo htmlspecialchars($convenio['logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($convenio['nome']); ?>" 
                                             class="w-16 h-16 object-contain">
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-500 text-xs">
                                            LOGO
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($convenio['nome']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($convenio['categoria_nome']): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" 
                                              style="background-color: <?php echo htmlspecialchars($convenio['categoria_cor']); ?>20; color: <?php echo htmlspecialchars($convenio['categoria_cor']); ?>; border: 1px solid <?php echo htmlspecialchars($convenio['categoria_cor']); ?>;">
                                            <?php echo htmlspecialchars($convenio['categoria_nome']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars(mb_strimwidth($convenio['desconto'], 0, 50, '...')); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php if ($convenio['telefone']): ?>
                                        <div><?php echo htmlspecialchars($convenio['telefone']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($convenio['email']): ?>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($convenio['email']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($convenio['ativo']): ?>
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
                                        <a href="convenio_form.php?id=<?php echo $convenio['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                                           title="Editar">
                                            ✏️
                                        </a>
                                        <a href="?toggle=<?php echo $convenio['id']; ?>" 
                                           class="text-yellow-600 hover:text-yellow-800 font-medium text-sm"
                                           title="<?php echo $convenio['ativo'] ? 'Desativar' : 'Ativar'; ?>"
                                           onclick="return confirm('Deseja alterar o status deste convênio?')">
                                            🔄
                                        </a>
                                        <a href="?deletar=<?php echo $convenio['id']; ?>" 
                                           class="text-red-600 hover:text-red-800 font-medium text-sm"
                                           title="Deletar"
                                           onclick="return confirm('Tem certeza que deseja deletar este convênio? Esta ação não pode ser desfeita.')">
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
            Total de <?php echo count($convenios); ?> convênio(s) encontrado(s).
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#conveniosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                pageLength: 25,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [0, 6] }
                ]
            });
        });
    </script>
</body>
</html>
