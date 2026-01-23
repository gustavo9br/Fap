<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pdo = Database::getInstance()->getConnection();

// Buscar todas as categorias ativas
$stmtCat = $pdo->query("SELECT * FROM categorias_balancetes WHERE ativo = 1 ORDER BY ordem, nome");
$categorias = $stmtCat->fetchAll();

// Buscar balancetes por categoria
$balancetesPorCategoria = [];
foreach ($categorias as $categoria) {
    $stmt = $pdo->prepare("
        SELECT * FROM balancetes 
        WHERE categoria_id = ? AND ativo = 1 
        ORDER BY ano DESC, data_documento DESC, ordem
    ");
    $stmt->execute([$categoria['id']]);
    $balancetesPorCategoria[$categoria['id']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balancetes - FAP Pádua</title>
    <link rel="icon" type="image/png" href="/imagens/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'green-primary': '#00A859',
                        'blue-primary': '#1e3a8a',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white">

<?php include 'includes/header.php'; ?>

<!-- Banner Topo -->
<section class="py-6">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-3xl p-10 shadow-xl relative overflow-hidden">
            <!-- Efeito de fundo com círculos -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -mr-48 -mt-48"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full -ml-48 -mb-48"></div>
            </div>
            
            <div class="relative z-10">
                <!-- Breadcrumb -->
                <nav class="mb-6">
                    <ol class="flex items-center gap-2 text-white text-sm">
                        <li>
                            <a href="/" class="hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                FAP PADUA
                            </a>
                        </li>
                        <li class="text-white/70">›</li>
                        <li class="font-semibold">BALANCETES</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">BALANCETES</h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-2 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        <?php foreach ($categorias as $categoria): ?>
            <?php 
            $balancetes = $balancetesPorCategoria[$categoria['id']] ?? [];
            if (empty($balancetes)) continue;
            
            // Agrupar por ano
            $porAno = [];
            foreach ($balancetes as $bal) {
                $porAno[$bal['ano']][] = $bal;
            }
            krsort($porAno); // Ordenar anos decrescente
            ?>
            
            <div id="<?php echo htmlspecialchars($categoria['slug']); ?>" class="bg-white rounded-2xl shadow-md p-8 mb-8">
                <h2 class="text-xl md:text-2xl font-bold mb-6 uppercase" style="color: #B8621B;">
                    <?php echo htmlspecialchars($categoria['nome']); ?>
                </h2>

                <?php foreach ($porAno as $ano => $bals): ?>
                    <div class="mb-6 last:mb-0">
                        <h3 class="text-lg font-bold mb-3" style="color: #B8621B;"><?php echo $ano; ?></h3>
                        
                        <div class="space-y-1">
                            <?php foreach ($bals as $bal): ?>
                                <a href="<?php echo htmlspecialchars(normalizar_caminho_arquivo($bal['arquivo'])); ?>" 
                                   target="_blank"
                                   class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded transition-colors group">
                                    <!-- Ícone PDF -->
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    
                                    <!-- Data -->
                                    <span class="text-gray-600 text-sm font-medium flex-shrink-0">
                                        <?php echo date('d/m/Y', strtotime($bal['data_documento'])); ?>
                                    </span>
                                    
                                    <span class="text-gray-400">-</span>
                                    
                                    <!-- Título -->
                                    <span class="text-gray-800 group-hover:text-green-600 transition-colors flex-1">
                                        <?php echo htmlspecialchars($bal['titulo']); ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <?php if (empty($categorias) || array_sum(array_map('count', $balancetesPorCategoria)) === 0): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500 text-lg">Nenhum balancete disponível no momento.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
