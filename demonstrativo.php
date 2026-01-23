<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pdo = Database::getInstance()->getConnection();

// Pegar o slug da URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Buscar categoria do demonstrativo pelo slug
$stmt = $pdo->prepare("SELECT * FROM categorias_demonstrativos WHERE slug = ? AND ativo = 1");
$stmt->execute([$slug]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header('Location: /');
    exit;
}

// Config de cores por categoria
$config = [
    1 => ['cor' => 'from-blue-500 to-blue-700', 'icone' => '📋'],   // DIPR
    2 => ['cor' => 'from-green-500 to-green-700', 'icone' => '💹'],  // DAIR
    3 => ['cor' => 'from-purple-500 to-purple-700', 'icone' => '📊'], // DPIN
    4 => ['cor' => 'from-orange-500 to-orange-700', 'icone' => '📈'], // DRAA
    5 => ['cor' => 'from-red-500 to-red-700', 'icone' => '📉'],       // Comparativo Despesa
    6 => ['cor' => 'from-teal-500 to-teal-700', 'icone' => '📈'],     // Comparativo Receita
    7 => ['cor' => 'from-indigo-500 to-indigo-700', 'icone' => '📊'], // Avaliação Atuarial
    8 => ['cor' => 'from-emerald-500 to-emerald-700', 'icone' => '✅'], // Certidões Negativas
    9 => ['cor' => 'from-cyan-500 to-cyan-700', 'icone' => '🏅'],     // FGTS
    10 => ['cor' => 'from-amber-500 to-amber-700', 'icone' => '⚖️'],  // Acórdão TCM
];
$cfg = $config[$categoria['id']] ?? ['cor' => 'from-blue-600 to-blue-800', 'icone' => '📄'];

// Buscar demonstrativos desta categoria agrupados por ano
$stmt = $pdo->prepare("
    SELECT * FROM demonstrativos 
    WHERE categoria_id = ? AND ativo = 1 
    ORDER BY ano DESC, data_documento DESC, ordem
");
$stmt->execute([$categoria['id']]);
$demonstrativos = $stmt->fetchAll();

// Agrupar por ano
$porAno = [];
foreach ($demonstrativos as $demo) {
    $porAno[$demo['ano']][] = $demo;
}
krsort($porAno);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoria['nome']); ?> - FAP Pádua</title>
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
        <div class="bg-gradient-to-br <?php echo $cfg['cor']; ?> rounded-3xl p-10 shadow-xl relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -mr-48 -mt-48"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full -ml-48 -mb-48"></div>
            </div>
            
            <div class="relative z-10">
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
                        <li class="font-semibold"><?php echo strtoupper($categoria['nome']); ?></li>
                    </ol>
                </nav>
                <h1 class="text-white text-xl md:text-2xl font-bold border-l-4 border-white pl-4"><?php echo strtoupper($categoria['nome']); ?></h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-200">
    <div class="max-w-7xl mx-auto px-4">
        <?php if (empty($demonstrativos)): ?>
            <div class="bg-white rounded-2xl shadow-md p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500 text-lg">Nenhum documento disponível no momento.</p>
            </div>
        <?php else: ?>
            
            <!-- Anos com Accordion -->
            <div class="space-y-4">
                <?php $first = true; foreach ($porAno as $ano => $demonstrativosAno): ?>
                
                <!-- Accordion do Ano -->
                <div class="bg-gray-100 rounded-xl overflow-hidden">
                    <button onclick="toggleAccordion('ano-<?php echo $ano; ?>')" 
                            class="w-full flex items-center gap-3 px-6 py-4 hover:bg-gray-200 transition-colors text-left">
                        <span id="icon-toggle-<?php echo $ano; ?>" class="text-gray-600 font-medium text-lg w-5 text-center"><?php echo $first ? '−' : '+'; ?></span>
                        <span class="font-bold text-gray-800 text-lg"><?php echo $ano; ?></span>
                    </button>
                    
                    <div id="ano-<?php echo $ano; ?>" class="<?php echo $first ? '' : 'hidden'; ?> px-4 pb-4 space-y-2">
                        <?php foreach ($demonstrativosAno as $demo): ?>
                        <a href="<?php echo htmlspecialchars(normalizar_caminho_arquivo($demo['arquivo'])); ?>"
                           target="_blank"
                           class="flex items-center gap-3 p-4 bg-white rounded-xl hover:shadow-md transition-all group">
                            <svg class="w-5 h-5 text-green-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="font-medium text-gray-700 group-hover:text-green-primary transition-colors">
                                <?php echo htmlspecialchars($demo['titulo']); ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php $first = false; endforeach; ?>
            </div>
            
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function toggleAccordion(id) {
    const content = document.getElementById(id);
    const iconToggle = document.getElementById('icon-toggle-' + id.replace('ano-', ''));
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        iconToggle.textContent = '−';
    } else {
        content.classList.add('hidden');
        iconToggle.textContent = '+';
    }
}
</script>
</body>
</html>
