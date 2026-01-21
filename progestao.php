<?php
require_once 'config/database.php';

$pdo = Database::getInstance()->getConnection();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transparência Pró-Gestão - FAP Pádua</title>
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
                        <li class="font-semibold">TRANSPARÊNCIA PRÓ-GESTÃO</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">TRANSPARÊNCIA PRÓ-GESTÃO</h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6">
    <div class="max-w-7xl mx-auto px-4">
        <?php
        try {
            // Buscar seções ativas com seus cards
            $stmt = $pdo->query("
                SELECT * FROM progestao_secoes 
                WHERE ativo = 1 
                ORDER BY ordem ASC, id ASC
            ");
            $secoes = $stmt->fetchAll();
            
            $cor_index = 0;
            $cores = ['#ebeced', '#f5f5f5']; // Cores alternadas
            
            foreach ($secoes as $secao):
                // Buscar cards da seção
                $stmt_cards = $pdo->prepare("
                    SELECT * FROM progestao_cards 
                    WHERE secao_id = ? AND ativo = 1 
                    ORDER BY ordem ASC, id ASC
                ");
                $stmt_cards->execute([$secao['id']]);
                $cards = $stmt_cards->fetchAll();
                
                if (empty($cards)) continue; // Pular seções sem cards
                
                $cor_fundo = $cores[$cor_index % 2];
                $cor_index++;
        ?>
        
        <!-- Seção -->
        <div class="py-8 px-6 rounded-2xl mb-6" style="background-color: <?= $cor_fundo ?>;">
            <h2 class="text-xl md:text-2xl font-bold mb-6 text-gray-800">
                <?= htmlspecialchars($secao['titulo']) ?>
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($cards as $card): ?>
                    <?php 
                    $link_href = '';
                    $target = '';
                    
                    if ($card['tipo_conteudo'] === 'link') {
                        $link_href = htmlspecialchars($card['link']);
                        $target = '_blank';
                    } else {
                        $link_href = '/uploads/progestao/' . htmlspecialchars($card['arquivo']);
                        $target = '_blank';
                    }
                    ?>
                    
                    <a href="<?= $link_href ?>" 
                       target="<?= $target ?>"
                       class="bg-white rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow border border-gray-200 flex items-start gap-4 group">
                        <div class="text-4xl flex-shrink-0">
                            <?= $card['icone'] ?: '📄' ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($card['titulo']) ?>
                            </h3>
                            <?php if ($card['tipo_conteudo'] === 'arquivo'): ?>
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Baixar PDF
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Link externo
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php 
            endforeach;
            
            // Se não há nenhuma seção
            if (empty($secoes)):
        ?>
        
        <div class="bg-white rounded-2xl shadow-md p-12 text-center">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Conteúdo em construção</h3>
            <p class="text-gray-500">Em breve esta página estará disponível com mais informações.</p>
        </div>
        
        <?php 
            endif;
        } catch (PDOException $e) {
            error_log("Erro ao buscar conteúdo Pró-Gestão: " . $e->getMessage());
        ?>
        
        <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg">
            <p class="font-semibold">Erro ao carregar conteúdo</p>
            <p class="text-sm">Por favor, tente novamente mais tarde.</p>
        </div>
        
        <?php } ?>
    </div>
</section>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
