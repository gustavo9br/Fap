<?php
require_once 'config/database.php';

$pdo = Database::getInstance()->getConnection();

// Buscar dados do conselho
$stmt = $pdo->prepare("SELECT * FROM conselhos WHERE slug = 'conselho-fiscal' AND ativo = 1");
$stmt->execute();
$conselho = $stmt->fetch();

if (!$conselho) {
    header('Location: /conselhos-e-comites');
    exit;
}

// Buscar seções com anos e arquivos
$stmt = $pdo->prepare("
    SELECT 
        s.id as secao_id,
        s.titulo as secao_titulo,
        a.id as ano_id,
        a.ano,
        COUNT(arq.id) as total_arquivos
    FROM conselho_secoes s
    LEFT JOIN conselho_anos a ON s.id = a.secao_id
    LEFT JOIN conselho_arquivos arq ON a.id = arq.ano_id
    WHERE s.conselho_id = ? AND s.ativo = 1
    GROUP BY s.id, s.titulo, a.id, a.ano
    ORDER BY s.ordem ASC, a.ano DESC
");
$stmt->execute([$conselho['id']]);
$dados = $stmt->fetchAll(PDO::FETCH_GROUP);
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
        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-3xl p-10 shadow-xl relative overflow-hidden">
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
                        <li class="font-semibold">CONSELHO FISCAL – CF</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">CONSELHO FISCAL - CF</h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Descrição -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <p class="text-gray-700 leading-relaxed">
                <?php echo nl2br(htmlspecialchars($conselho['descricao'])); ?>
            </p>
        </div>

        <?php
        // Buscar seções com anos
        $stmt = $pdo->prepare("
            SELECT s.*, a.id as ano_id, a.ano
            FROM conselho_secoes s
            LEFT JOIN conselho_anos a ON s.id = a.secao_id
            WHERE s.conselho_id = ? AND s.ativo = 1
            ORDER BY s.ordem ASC, a.ano DESC
        ");
        $stmt->execute([$conselho['id']]);
        $secoes_raw = $stmt->fetchAll();
        
        // Organizar por seção
        $secoes = [];
        foreach ($secoes_raw as $row) {
            $secao_id = $row['id'];
            if (!isset($secoes[$secao_id])) {
                $secoes[$secao_id] = [
                    'titulo' => $row['titulo'],
                    'anos' => []
                ];
            }
            if ($row['ano_id']) {
                // Buscar total de arquivos
                $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM conselho_arquivos WHERE ano_id = ?");
                $stmt_count->execute([$row['ano_id']]);
                $total = $stmt_count->fetchColumn();
                
                $secoes[$secao_id]['anos'][] = [
                    'id' => $row['ano_id'],
                    'ano' => $row['ano'],
                    'total_arquivos' => $total
                ];
            }
        }

        foreach ($secoes as $secao):
            if (empty($secao['anos'])) continue;
        ?>
        
        <!-- Seção -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-8 bg-green-primary"></div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <?php echo htmlspecialchars($secao['titulo']); ?> 
                    (<?php echo count($secao['anos']); ?>/<?php echo array_sum(array_column($secao['anos'], 'total_arquivos')); ?>)
                </h2>
            </div>
            
            <div class="space-y-3">
                <?php foreach ($secao['anos'] as $ano_data): ?>
                <div class="bg-gray-100 rounded-lg">
                    <button 
                        onclick="toggleAno(<?php echo $ano_data['id']; ?>)"
                        class="w-full flex items-center justify-between p-5 hover:bg-gray-200 transition-colors rounded-lg"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-bold text-gray-700">+</span>
                            <span class="text-xl font-semibold text-gray-800"><?php echo $ano_data['ano']; ?></span>
                        </div>
                    </button>
                    
                    <div id="ano-<?php echo $ano_data['id']; ?>" class="hidden px-5 pb-5">
                        <?php
                        // Buscar arquivos do ano
                        $stmt_arq = $pdo->prepare("SELECT * FROM conselho_arquivos WHERE ano_id = ? ORDER BY ordem ASC, created_at DESC");
                        $stmt_arq->execute([$ano_data['id']]);
                        $arquivos = $stmt_arq->fetchAll();
                        
                        if (count($arquivos) > 0):
                            foreach ($arquivos as $arquivo):
                        ?>
                        <a href="/uploads/conselhos/<?php echo htmlspecialchars($arquivo['arquivo']); ?>" 
                           target="_blank"
                           class="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-shadow mb-2">
                            <svg class="w-5 h-5 text-green-primary flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700"><?php echo htmlspecialchars($arquivo['titulo']); ?></span>
                        </a>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <p class="text-gray-500 text-center py-4">Nenhum arquivo disponível</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
        
    </div>
</section>

<script>
function toggleAno(anoId) {
    const element = document.getElementById('ano-' + anoId);
    const button = element.previousElementSibling.querySelector('span:first-child');
    
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
        button.textContent = '-';
    } else {
        element.classList.add('hidden');
        button.textContent = '+';
    }
}
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>
