<?php
require_once 'config/database.php';

$pdo = Database::getInstance()->getConnection();

// Pegar o slug da URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Buscar conselho
$stmt = $pdo->prepare("SELECT * FROM conselhos WHERE slug = ? AND ativo = 1");
$stmt->execute([$slug]);
$conselho = $stmt->fetch();

if (!$conselho) {
    header('Location: /conselhos-e-comites.php');
    exit;
}

// Buscar seções do conselho
$stmt = $pdo->prepare("SELECT * FROM conselho_secoes WHERE conselho_id = ? AND ativo = 1 ORDER BY ordem ASC");
$stmt->execute([$conselho['id']]);
$secoes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($conselho['nome']); ?> - FAP Pádua</title>
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

<!-- Banner Topo -->
<section class="py-6">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-gradient-to-br <?php echo $conselho['cor_banner']; ?> rounded-3xl p-10 shadow-xl relative overflow-hidden">
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
                        <li class="font-semibold"><?php echo strtoupper($conselho['nome']); ?></li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4"><?php echo strtoupper($conselho['nome']); ?></h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Descrição -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-8">
            <p class="text-gray-700 leading-relaxed">
                <?php echo htmlspecialchars($conselho['descricao']); ?>
            </p>
        </div>

        <?php foreach ($secoes as $secao): 
            // Buscar anos da seção
            $stmt = $pdo->prepare("SELECT * FROM conselho_anos WHERE secao_id = ? ORDER BY ano DESC");
            $stmt->execute([$secao['id']]);
            $anos = $stmt->fetchAll();
            
            if (count($anos) > 0):
        ?>
        
        <!-- Seção -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 class="text-xl md:text-2xl font-bold mb-6 border-l-4 pl-4" style="color: #B8621B; border-color: #B8621B;">
                <?php echo htmlspecialchars($secao['titulo']); ?> (<?php echo count($anos); ?>/<?php 
                    $stmt_total = $pdo->prepare("SELECT COUNT(DISTINCT ca.id) as total FROM conselho_anos ca JOIN conselho_arquivos caq ON ca.id = caq.ano_id WHERE ca.secao_id = ?");
                    $stmt_total->execute([$secao['id']]);
                    $total_count = $stmt_total->fetch();
                    echo $total_count['total'];
                ?>)
            </h2>
            
            <div class="space-y-4">
                <?php foreach ($anos as $ano): 
                    // Buscar arquivos do ano
                    $stmt = $pdo->prepare("SELECT * FROM conselho_arquivos WHERE ano_id = ? ORDER BY ordem ASC, data_upload DESC");
                    $stmt->execute([$ano['id']]);
                    $arquivos = $stmt->fetchAll();
                    
                    if (count($arquivos) > 0):
                ?>
                
                <!-- Accordion do Ano -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button onclick="toggleAccordion('ano-<?php echo $ano['id']; ?>')" class="w-full flex items-center justify-between bg-gray-50 hover:bg-gray-100 px-6 py-4 transition-colors">
                        <span class="font-semibold text-gray-800 text-lg">+ <?php echo $ano['ano']; ?></span>
                        <svg id="icon-ano-<?php echo $ano['id']; ?>" class="w-5 h-5 text-gray-600 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div id="ano-<?php echo $ano['id']; ?>" class="hidden bg-white">
                        <div class="px-6 py-4 space-y-3">
                            <?php foreach ($arquivos as $arquivo): ?>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5 text-green-primary flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="<?php echo htmlspecialchars($arquivo['arquivo_path']); ?>" target="_blank" class="flex-grow text-gray-700 hover:text-green-primary">
                                    <?php echo htmlspecialchars($arquivo['titulo']); ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <?php endif; endforeach; ?>
            </div>
        </div>
        
        <?php endif; endforeach; ?>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function toggleAccordion(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}
</script>

</body>
</html>
