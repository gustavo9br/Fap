<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = Database::getInstance()->getConnection();
$pageTitle = 'Gerenciamento de Arquivos';

// Função para escanear diretórios recursivamente
function scanDirectory($dir, $baseDir = '') {
    $files = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.htaccess') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        $relativePath = ($baseDir ? $baseDir . '/' : '') . $item;
        
        if (is_dir($path)) {
            $files = array_merge($files, scanDirectory($path, $relativePath));
        } else {
            $files[] = [
                'path' => $path,
                'relative' => 'uploads/' . $relativePath,
                'name' => $item,
                'folder' => $baseDir,
                'size' => filesize($path),
                'modified' => filemtime($path),
                'extension' => strtolower(pathinfo($item, PATHINFO_EXTENSION))
            ];
        }
    }
    
    return $files;
}

// Escanear pasta uploads
$uploadsDir = '../uploads';
$allFiles = scanDirectory($uploadsDir);

// Função para buscar origem do arquivo no banco
function buscarOrigemArquivo($pdo, $caminho) {
    $origens = [];
    
    // Normalizar caminho (pode ter ou não / no início)
    $caminhoNormalizado = '/' . ltrim($caminho, '/');
    $caminhoSemBarra = ltrim($caminho, '/');
    
    // Balancetes
    $stmt = $pdo->prepare("SELECT b.id, b.titulo, b.ano, c.id as categoria_id, c.nome as categoria_nome, c.slug as categoria_slug 
                          FROM balancetes b 
                          JOIN categorias_balancetes c ON b.categoria_id = c.id 
                          WHERE b.arquivo = ? OR b.arquivo = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $balancetes = $stmt->fetchAll();
    foreach ($balancetes as $b) {
        $origens[] = [
            'tipo' => 'Balancete',
            'titulo' => $b['titulo'],
            'ano' => $b['ano'],
            'categoria' => $b['categoria_nome'],
            'link' => "editar-balancete.php?categoria=" . $b['categoria_id'],
            'publico' => "/balancete/" . $b['categoria_slug']
        ];
    }
    
    // Demonstrativos
    $stmt = $pdo->prepare("SELECT d.id, d.titulo, d.ano, c.id as categoria_id, c.nome as categoria_nome, c.slug as categoria_slug 
                          FROM demonstrativos d 
                          JOIN categorias_demonstrativos c ON d.categoria_id = c.id 
                          WHERE d.arquivo = ? OR d.arquivo = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $demonstrativos = $stmt->fetchAll();
    foreach ($demonstrativos as $d) {
        $origens[] = [
            'tipo' => 'Demonstrativo',
            'titulo' => $d['titulo'],
            'ano' => $d['ano'],
            'categoria' => $d['categoria_nome'],
            'link' => "editar-demonstrativo.php?categoria=" . $d['categoria_id'],
            'publico' => "/demonstrativo/" . $d['categoria_slug']
        ];
    }
    
    // Financeiro Cards (PDF direto)
    $stmt = $pdo->prepare("SELECT id, titulo, slug FROM financeiro_cards WHERE arquivo_pdf = ? OR arquivo_pdf = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $financeiroCards = $stmt->fetchAll();
    foreach ($financeiroCards as $fc) {
        $origens[] = [
            'tipo' => 'Financeiro (Card)',
            'titulo' => $fc['titulo'],
            'link' => "editar-financeiro.php?id=" . $fc['id'],
            'publico' => "/financeiro/" . $fc['slug']
        ];
    }
    
    // Financeiro Arquivos
    $stmt = $pdo->prepare("SELECT fa.id, fa.titulo, fa.arquivo_path, fs.titulo as secao_titulo, fc.titulo as card_titulo, fc.slug
                           FROM financeiro_arquivos fa
                           JOIN financeiro_anos fa_anos ON fa.ano_id = fa_anos.id
                           JOIN financeiro_secoes fs ON fa_anos.secao_id = fs.id
                           JOIN financeiro_cards fc ON fs.card_id = fc.id
                           WHERE fa.arquivo_path = ? OR fa.arquivo_path = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $financeiroArquivos = $stmt->fetchAll();
    foreach ($financeiroArquivos as $fa) {
        $origens[] = [
            'tipo' => 'Financeiro (Arquivo)',
            'titulo' => $fa['titulo'],
            'secao' => $fa['secao_titulo'],
            'card' => $fa['card_titulo'],
            'link' => "editar-financeiro.php?id=" . $fa['id'],
            'publico' => "/financeiro/" . $fa['slug']
        ];
    }
    
    // Acesso Rápido Cards (PDF direto)
    $stmt = $pdo->prepare("SELECT id, titulo, slug FROM acesso_rapido_cards WHERE arquivo_pdf = ? OR arquivo_pdf = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $acessoRapidoCards = $stmt->fetchAll();
    foreach ($acessoRapidoCards as $arc) {
        $origens[] = [
            'tipo' => 'Acesso Rápido (Card)',
            'titulo' => $arc['titulo'],
            'link' => "editar-acesso-rapido.php?id=" . $arc['id'],
            'publico' => "/" . $arc['slug']
        ];
    }
    
    // Acesso Rápido Arquivos
    $stmt = $pdo->prepare("SELECT aa.id, aa.titulo, aa.arquivo_path, asec.titulo as secao_titulo, ac.titulo as card_titulo, ac.slug
                           FROM acesso_rapido_arquivos aa
                           JOIN acesso_rapido_anos aa_anos ON aa.ano_id = aa_anos.id
                           JOIN acesso_rapido_secoes asec ON aa_anos.secao_id = asec.id
                           JOIN acesso_rapido_cards ac ON asec.card_id = ac.id
                           WHERE aa.arquivo_path = ? OR aa.arquivo_path = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $acessoRapidoArquivos = $stmt->fetchAll();
    foreach ($acessoRapidoArquivos as $ara) {
        $origens[] = [
            'tipo' => 'Acesso Rápido (Arquivo)',
            'titulo' => $ara['titulo'],
            'secao' => $ara['secao_titulo'],
            'card' => $ara['card_titulo'],
            'link' => "editar-acesso-rapido.php?id=" . $ara['id'],
            'publico' => "/" . $ara['slug']
        ];
    }
    
    // Conselho Arquivos
    $stmt = $pdo->prepare("SELECT ca.id, ca.titulo, ca.arquivo_path, cs.titulo as secao_titulo, c.nome as conselho_nome, c.slug as conselho_slug
                           FROM conselho_arquivos ca
                           JOIN conselho_anos ca_anos ON ca.ano_id = ca_anos.id
                           JOIN conselho_secoes cs ON ca_anos.secao_id = cs.id
                           JOIN conselhos c ON cs.conselho_id = c.id
                           WHERE ca.arquivo_path = ? OR ca.arquivo_path = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $conselhoArquivos = $stmt->fetchAll();
    foreach ($conselhoArquivos as $ca) {
        $origens[] = [
            'tipo' => 'Conselho',
            'titulo' => $ca['titulo'],
            'secao' => $ca['secao_titulo'],
            'conselho' => $ca['conselho_nome'],
            'link' => "editar-conselho.php?id=" . $ca['id'],
            'publico' => "/conselho/" . $ca['conselho_slug']
        ];
    }
    
    // Progestão Cards
    $stmt = $pdo->prepare("SELECT id, titulo FROM progestao_cards WHERE arquivo = ? OR arquivo = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $progestaoCards = $stmt->fetchAll();
    foreach ($progestaoCards as $pc) {
        $origens[] = [
            'tipo' => 'Progestão',
            'titulo' => $pc['titulo'],
            'link' => "progestao_card_form.php?id=" . $pc['id']
        ];
    }
    
    // Notícias (imagens)
    $stmt = $pdo->prepare("SELECT id, titulo, slug FROM noticias WHERE imagem_destaque = ? OR imagem_destaque = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $noticias = $stmt->fetchAll();
    foreach ($noticias as $n) {
        $origens[] = [
            'tipo' => 'Notícia (Imagem)',
            'titulo' => $n['titulo'],
            'link' => "noticia_form.php?id=" . $n['id'],
            'publico' => "/noticia/" . $n['slug']
        ];
    }
    
    // Arquivos (tabela de imagens de notícias)
    $stmt = $pdo->prepare("SELECT id, titulo, categoria FROM arquivos WHERE caminho = ? OR caminho = ?");
    $stmt->execute([$caminhoNormalizado, $caminhoSemBarra]);
    $arquivos = $stmt->fetchAll();
    foreach ($arquivos as $a) {
        $origens[] = [
            'tipo' => 'Arquivo (' . $a['categoria'] . ')',
            'titulo' => $a['titulo'],
            'link' => "#"
        ];
    }
    
    return $origens;
}

// Processar arquivos e buscar origens
$arquivosComInfo = [];
foreach ($allFiles as $file) {
    $origens = buscarOrigemArquivo($pdo, $file['relative']);
    $arquivosComInfo[] = [
        'file' => $file,
        'origens' => $origens
    ];
}

// Ordenar por pasta e nome
usort($arquivosComInfo, function($a, $b) {
    $folderCompare = strcmp($a['file']['folder'], $b['file']['folder']);
    if ($folderCompare !== 0) {
        return $folderCompare;
    }
    return strcmp($a['file']['name'], $b['file']['name']);
});

// Agrupar por pasta
$arquivosPorPasta = [];
foreach ($arquivosComInfo as $item) {
    $pasta = $item['file']['folder'] ?: 'uploads';
    if (!isset($arquivosPorPasta[$pasta])) {
        $arquivosPorPasta[$pasta] = [];
    }
    $arquivosPorPasta[$pasta][] = $item;
}

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📁 Gerenciamento de Arquivos</h1>
            <p class="text-gray-500 mt-1">Visualize todos os arquivos enviados e suas origens no sistema</p>
        </div>
        <div class="text-sm text-gray-500">
            Total: <?= count($allFiles) ?> arquivo(s)
        </div>
    </div>

    <?php if (empty($arquivosPorPasta)): ?>
    <div class="bg-white rounded-xl shadow-md p-8 text-center">
        <p class="text-gray-500">Nenhum arquivo encontrado na pasta uploads.</p>
    </div>
    <?php else: ?>
    
    <?php foreach ($arquivosPorPasta as $pasta => $arquivos): ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <?= htmlspecialchars($pasta) ?>
                <span class="text-sm font-normal opacity-90">(<?= count($arquivos) ?> arquivo(s))</span>
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arquivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamanho</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modificado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origem</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($arquivos as $item): 
                        $file = $item['file'];
                        $origens = $item['origens'];
                        $tamanhoFormatado = $file['size'] < 1024 ? $file['size'] . ' B' : 
                                          ($file['size'] < 1048576 ? round($file['size'] / 1024, 2) . ' KB' : 
                                          round($file['size'] / 1048576, 2) . ' MB');
                        $dataFormatada = date('d/m/Y H:i', $file['modified']);
                        $urlArquivo = '/' . $file['relative'];
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <?php
                                $icone = '';
                                switch ($file['extension']) {
                                    case 'pdf':
                                        $icone = '<svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                    case 'webp':
                                        $icone = '<svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>';
                                        break;
                                    default:
                                        $icone = '<svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>';
                                }
                                echo $icone;
                                ?>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($file['name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($file['relative']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $tamanhoFormatado ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $dataFormatada ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php if (empty($origens)): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    ⚠️ Não referenciado
                                </span>
                            <?php else: ?>
                                <div class="space-y-1">
                                    <?php foreach ($origens as $origem): ?>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($origem['tipo']) ?>
                                        </span>
                                        <?php if (isset($origem['link']) && $origem['link'] !== '#'): ?>
                                        <a href="<?= htmlspecialchars($origem['link']) ?>" class="text-blue-600 hover:text-blue-800 text-xs" target="_blank">
                                            <?= htmlspecialchars($origem['titulo'] ?? 'Ver') ?>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (isset($origem['publico'])): ?>
                                        <a href="<?= htmlspecialchars($origem['publico']) ?>" class="text-green-600 hover:text-green-800 text-xs" target="_blank">
                                            (Ver público)
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (isset($origem['categoria'])): ?>
                                    <div class="text-xs text-gray-400 ml-4">Categoria: <?= htmlspecialchars($origem['categoria']) ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($origem['ano'])): ?>
                                    <div class="text-xs text-gray-400 ml-4">Ano: <?= htmlspecialchars($origem['ano']) ?></div>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= htmlspecialchars($urlArquivo) ?>" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50" 
                                   title="Visualizar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="<?= htmlspecialchars($urlArquivo) ?>" 
                                   download 
                                   class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50" 
                                   title="Download">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
