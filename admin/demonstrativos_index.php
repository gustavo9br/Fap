<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

// Buscar categorias e contagem de demonstrativos
$stmt = $pdo->query("
    SELECT 
        c.*, 
        COUNT(d.id) as total_demonstrativos,
        SUM(CASE WHEN d.ativo = 1 THEN 1 ELSE 0 END) as ativos
    FROM categorias_demonstrativos c
    LEFT JOIN demonstrativos d ON c.id = d.categoria_id
    GROUP BY c.id
    ORDER BY c.ordem, c.nome
");
$categorias = $stmt->fetchAll();

// Ícones e cores para cada categoria (apenas os 4 demonstrativos)
$config = [
    1 => ['icone' => '📋', 'cor' => 'blue', 'descricao' => 'Informações Previdenciárias e Repasses', 'slug' => 'dipr'],
    2 => ['icone' => '💹', 'cor' => 'green', 'descricao' => 'Aplicações e Investimentos dos Recursos', 'slug' => 'dair'],
    3 => ['icone' => '📊', 'cor' => 'purple', 'descricao' => 'Políticas de Investimento', 'slug' => 'dpin'],
    4 => ['icone' => '📈', 'cor' => 'orange', 'descricao' => 'Resultados da Avaliação Atuarial', 'slug' => 'draa'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demonstrativos - Admin FAP</title>
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Gerenciar Demonstrativos</h1>
            <p class="text-gray-600">Selecione o tipo de demonstrativo que deseja gerenciar</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($categorias as $cat): 
                $cfg = $config[$cat['id']] ?? ['icone' => '📄', 'cor' => 'gray', 'descricao' => '', 'slug' => ''];
            ?>
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-<?php echo $cfg['cor']; ?>-400">
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-4xl"><?php echo $cfg['icone']; ?></span>
                            <span class="bg-<?php echo $cfg['cor']; ?>-100 text-<?php echo $cfg['cor']; ?>-700 text-sm font-medium px-3 py-1 rounded-full">
                                <?php echo $cat['total_demonstrativos']; ?> documentos
                            </span>
                        </div>
                        
                        <h2 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-<?php echo $cfg['cor']; ?>-600 transition-colors">
                            <?php echo htmlspecialchars($cat['nome']); ?>
                        </h2>
                        
                        <p class="text-gray-500 text-sm mb-4">
                            <?php echo $cfg['descricao']; ?>
                        </p>
                        
                        <div class="flex items-center justify-between text-sm mb-4">
                            <span class="text-green-600">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <?php echo $cat['ativos'] ?? 0; ?> ativos
                            </span>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="editar-demonstrativo.php?categoria=<?php echo $cat['id']; ?>" 
                               class="flex-1 text-center bg-<?php echo $cfg['cor']; ?>-500 text-white py-2 px-4 rounded-lg hover:bg-<?php echo $cfg['cor']; ?>-600 transition-colors text-sm font-medium">
                                Gerenciar
                            </a>
                            <a href="demonstrativos_lista.php?categoria=<?php echo $cat['id']; ?>" 
                               class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors text-sm"
                               title="Ver lista">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </a>
                            <a href="../demonstrativo.php?slug=<?php echo $cfg['slug']; ?>" 
                               target="_blank"
                               class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors text-sm"
                               title="Ver no site">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <div class="h-2 bg-<?php echo $cfg['cor']; ?>-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Ação rápida -->
        <div class="mt-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold mb-1">Adicionar Novo Demonstrativo</h3>
                    <p class="text-blue-100">Adicione um novo documento demonstrativo ao sistema</p>
                </div>
                <a href="demonstrativo_form.php" 
                   class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Novo Demonstrativo
                </a>
            </div>
        </div>

        <!-- Estatísticas gerais -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php
            $stmtTotal = $pdo->query("SELECT COUNT(*) FROM demonstrativos");
            $total = $stmtTotal->fetchColumn();
            
            $stmtAtivos = $pdo->query("SELECT COUNT(*) FROM demonstrativos WHERE ativo = 1");
            $totalAtivos = $stmtAtivos->fetchColumn();
            
            $stmtAnos = $pdo->query("SELECT COUNT(DISTINCT ano) FROM demonstrativos");
            $totalAnos = $stmtAnos->fetchColumn();
            
            $stmtRecente = $pdo->query("SELECT MAX(criado_em) FROM demonstrativos");
            $maisRecente = $stmtRecente->fetchColumn();
            ?>
            
            <div class="bg-white rounded-xl p-4 shadow-md">
                <div class="text-2xl font-bold text-gray-800"><?php echo $total; ?></div>
                <div class="text-sm text-gray-500">Total de Demonstrativos</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-md">
                <div class="text-2xl font-bold text-green-600"><?php echo $totalAtivos; ?></div>
                <div class="text-sm text-gray-500">Demonstrativos Ativos</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-md">
                <div class="text-2xl font-bold text-blue-600"><?php echo $totalAnos; ?></div>
                <div class="text-sm text-gray-500">Anos com Registros</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-md">
                <div class="text-2xl font-bold text-purple-600">
                    <?php echo $maisRecente ? date('d/m/Y', strtotime($maisRecente)) : '-'; ?>
                </div>
                <div class="text-sm text-gray-500">Último Cadastro</div>
            </div>
        </div>
    </div>
</body>
</html>
