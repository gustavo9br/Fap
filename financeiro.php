<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pdo = Database::getInstance()->getConnection();

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Buscar card pelo slug
$stmt = $pdo->prepare("SELECT * FROM financeiro_cards WHERE slug = ? AND ativo = 1");
$stmt->execute([$slug]);
$card = $stmt->fetch();

if (!$card) {
    header('Location: /');
    exit;
}

// Buscar seções do card
$stmt = $pdo->prepare("SELECT * FROM financeiro_secoes WHERE card_id = ? ORDER BY ordem ASC");
$stmt->execute([$card['id']]);
$secoes = $stmt->fetchAll();

// Para cada seção, buscar subseções e anos ordenados por ordem_geral
$conteudo = [];
foreach ($secoes as $secao) {
    // Buscar subseções com tipo
    $stmt = $pdo->prepare("SELECT *, 'subsecao' as tipo FROM financeiro_subsecoes WHERE secao_id = ?");
    $stmt->execute([$secao['id']]);
    $subsecoes_raw = $stmt->fetchAll();
    
    // Anos diretos da seção (sem subseção) com tipo
    $stmt = $pdo->prepare("SELECT *, 'ano' as tipo FROM financeiro_anos WHERE secao_id = ? AND (subsecao_id IS NULL OR subsecao_id = 0)");
    $stmt->execute([$secao['id']]);
    $anos_raw = $stmt->fetchAll();
    
    // Combinar e ordenar por ordem_geral
    $itens = array_merge($subsecoes_raw, $anos_raw);
    usort($itens, function($a, $b) {
        return ($a['ordem_geral'] ?? 0) - ($b['ordem_geral'] ?? 0);
    });
    
    // Processar cada item
    $secao['itens'] = [];
    foreach ($itens as $item) {
        if ($item['tipo'] === 'subsecao') {
            // Buscar anos da subseção
            $stmt = $pdo->prepare("SELECT * FROM financeiro_anos WHERE subsecao_id = ? ORDER BY ano DESC");
            $stmt->execute([$item['id']]);
            $anos = $stmt->fetchAll();
            
            $item['anos'] = [];
            foreach ($anos as $ano) {
                $stmt2 = $pdo->prepare("SELECT * FROM financeiro_arquivos WHERE ano_id = ? ORDER BY id ASC");
                $stmt2->execute([$ano['id']]);
                $ano['arquivos'] = $stmt2->fetchAll();
                $item['anos'][] = $ano;
            }
        } else {
            // É um ano direto - buscar arquivos
            $stmt2 = $pdo->prepare("SELECT * FROM financeiro_arquivos WHERE ano_id = ? ORDER BY id ASC");
            $stmt2->execute([$item['id']]);
            $item['arquivos'] = $stmt2->fetchAll();
        }
        $secao['itens'][] = $item;
    }
    
    // Manter compatibilidade com código antigo
    $secao['subsecoes'] = $subsecoes_raw;
    $secao['anos'] = $anos_raw;
    
    $conteudo[] = $secao;
}

// Contar total de documentos
$totalDocs = 0;
foreach ($conteudo as $secao) {
    foreach ($secao['itens'] as $item) {
        if ($item['tipo'] === 'subsecao') {
            foreach ($item['anos'] as $ano) {
                $totalDocs += count($ano['arquivos'] ?? []);
            }
        } else {
            $totalDocs += count($item['arquivos'] ?? []);
        }
    }
}

$pageTitle = htmlspecialchars($card['titulo']);
$pageTitleUpper = mb_strtoupper($card['titulo']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - FAP Pádua</title>
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
    <style>
        .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .accordion-content.open { max-height: 5000px; }
        .accordion-icon { transition: transform 0.3s ease; }
        .accordion-icon.rotated { transform: rotate(180deg); }
        .accordion-plus { display: inline-block; width: 20px; text-align: center; }
    </style>
</head>
<body class="bg-gray-50">

<?php include 'includes/header.php'; ?>

<!-- Banner Topo -->
<section class="py-6">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-3xl p-10 shadow-xl relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -mr-48 -mt-48"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full -ml-48 -mb-48"></div>
            </div>
            <div class="relative z-10">
                <nav class="mb-6">
                    <ol class="flex items-center gap-2 text-white text-sm">
                        <li><a href="/" class="hover:underline flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>FAP PADUA</a></li>
                        <li class="text-white/70">›</li>
                        <li class="font-semibold"><?php echo $pageTitleUpper; ?></li>
                    </ol>
                </nav>
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4"><?php echo $pageTitleUpper; ?></h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 space-y-8">
        
        <?php if (!empty($card['descricao'])): ?>
        <div class="bg-white rounded-2xl shadow-md p-8">
            <p class="text-gray-600 italic leading-relaxed"><?php echo nl2br(htmlspecialchars($card['descricao'])); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (empty($conteudo)): ?>
        <div class="bg-white rounded-2xl shadow-md p-8 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Nenhum documento disponível</h2>
            <p class="text-gray-500 mb-6">Os documentos serão adicionados em breve.</p>
            <a href="/" class="inline-block bg-green-primary text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition-colors">Voltar ao Início</a>
        </div>
        <?php else: ?>
        
        <?php foreach ($conteudo as $secao): ?>
        <div class="bg-white rounded-2xl shadow-md p-8">
            <!-- Título da Seção com borda verde -->
            <h2 class="text-xl md:text-2xl font-bold mb-6 flex items-center gap-3" style="color: #B8621B;">
                <span class="w-1.5 h-8 bg-green-primary rounded-full"></span>
                <?php echo htmlspecialchars($secao['titulo']); ?>
            </h2>
            
            <?php if (empty($secao['itens'])): ?>
            <p class="text-gray-500 text-center py-4">Nenhum documento nesta seção ainda.</p>
            <?php else: ?>
            
            <div class="space-y-4">
                <?php $globalIdx = 0; ?>
                <?php foreach ($secao['itens'] as $item): ?>
                    <?php if ($item['tipo'] === 'subsecao'): ?>
                    <!-- Subseção -->
                    <div class="bg-gray-50 rounded-xl p-5">
                        <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                            <span class="text-gray-400">—</span>
                            <?php echo htmlspecialchars($item['titulo']); ?>
                        </h3>
                        
                        <!-- Anos da subseção -->
                        <div class="space-y-2 ml-4">
                            <?php foreach ($item['anos'] as $anoIdx => $ano): ?>
                            <?php $isFirst = ($anoIdx === 0 && $globalIdx === 0); $globalIdx++; ?>
                            <div class="bg-gray-100 rounded-lg overflow-hidden">
                                <a href="<?php 
                                    if (count($ano['arquivos']) === 1) {
                                        echo normalizar_caminho_arquivo($ano['arquivos'][0]['arquivo_path']);
                                    } else {
                                        echo '#';
                                    }
                                ?>" 
                                   <?php if (count($ano['arquivos']) === 1): ?>target="_blank"<?php else: ?>onclick="toggleAccordion(this); return false;"<?php endif; ?>
                                   class="flex items-center justify-between p-3 hover:bg-gray-200 transition-colors cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <?php if (count($ano['arquivos']) > 1): ?>
                                        <span class="text-green-600 font-bold text-lg accordion-plus"><?php echo $isFirst ? '-' : '+'; ?></span>
                                        <?php else: ?>
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <?php endif; ?>
                                        <span class="font-medium text-gray-700"><?php echo $ano['ano']; ?></span>
                                    </div>
                                    <?php if (count($ano['arquivos']) > 1): ?>
                                    <svg class="accordion-icon w-4 h-4 text-gray-400 <?php echo $isFirst ? 'rotated' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    <?php endif; ?>
                                </a>
                                
                                <?php if (count($ano['arquivos']) > 1): ?>
                                <div class="accordion-content <?php echo $isFirst ? 'open' : ''; ?>">
                                    <div class="p-3 border-t border-gray-200 space-y-2">
                                        <?php foreach ($ano['arquivos'] as $arquivo): ?>
                                        <a href="<?php echo htmlspecialchars(normalizar_caminho_arquivo($arquivo['arquivo_path'])); ?>" target="_blank" class="flex items-center gap-2 p-2 hover:bg-white rounded text-sm text-gray-600 hover:text-green-600">
                                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                            <?php echo htmlspecialchars($arquivo['titulo']); ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Ano direto -->
                    <?php $ano = $item; $isFirst = ($globalIdx === 0); $globalIdx++; ?>
                    <div class="bg-gray-100 rounded-lg overflow-hidden">
                        <a href="<?php 
                            if (count($ano['arquivos']) === 1) {
                                echo normalizar_caminho_arquivo($ano['arquivos'][0]['arquivo_path']);
                            } else {
                                echo '#';
                            }
                        ?>" 
                           <?php if (count($ano['arquivos']) === 1): ?>target="_blank"<?php else: ?>onclick="toggleAccordion(this); return false;"<?php endif; ?>
                           class="flex items-center justify-between p-4 hover:bg-gray-200 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <?php if (count($ano['arquivos']) > 1): ?>
                                <span class="text-green-600 font-bold text-xl accordion-plus"><?php echo $isFirst ? '-' : '+'; ?></span>
                                <?php else: ?>
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <?php endif; ?>
                                <span class="font-semibold text-gray-700"><?php echo $ano['ano']; ?></span>
                            </div>
                            <?php if (count($ano['arquivos']) > 1): ?>
                            <svg class="accordion-icon w-5 h-5 text-gray-400 <?php echo $isFirst ? 'rotated' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <?php endif; ?>
                        </a>
                        
                        <?php if (count($ano['arquivos']) > 1): ?>
                        <div class="accordion-content <?php echo $isFirst ? 'open' : ''; ?>">
                            <div class="p-4 border-t border-gray-200 space-y-2">
                                <?php foreach ($ano['arquivos'] as $arquivo): ?>
                                <a href="<?php echo htmlspecialchars(normalizar_caminho_arquivo($arquivo['arquivo_path'])); ?>" target="_blank" class="flex items-center gap-2 p-2 hover:bg-white rounded text-sm text-gray-600 hover:text-green-600">
                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                    <?php echo htmlspecialchars($arquivo['titulo']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function toggleAccordion(button) {
    const content = button.nextElementSibling;
    if (!content) return;
    
    const icon = button.querySelector('.accordion-icon');
    const plus = button.querySelector('.accordion-plus');
    content.classList.toggle('open');
    if (icon) icon.classList.toggle('rotated');
    if (plus) {
        plus.textContent = content.classList.contains('open') ? '-' : '+';
    }
}
</script>

</body>
</html>
