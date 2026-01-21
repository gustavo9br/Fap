<?php
require_once 'config/database.php';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: noticias.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar notícia
    $stmt = $db->prepare("
        SELECT n.*, u.nome as autor_nome, c.nome as categoria_nome, c.cor as categoria_cor
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        LEFT JOIN categorias c ON n.categoria = c.slug
        WHERE n.slug = ? AND n.status = 'publicado'
    ");
    $stmt->execute([$slug]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$noticia) {
        header('Location: noticias.php');
        exit;
    }
    
    // Incrementar visualizações
    $stmt = $db->prepare("UPDATE noticias SET visualizacoes = visualizacoes + 1 WHERE id = ?");
    $stmt->execute([$noticia['id']]);
    
    // Buscar notícias relacionadas (mesma categoria)
    $stmt = $db->prepare("
        SELECT n.*, u.nome as autor_nome
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        WHERE n.categoria = ? AND n.id != ? AND n.status = 'publicado'
        ORDER BY n.publicado_em DESC
        LIMIT 4
    ");
    $stmt->execute([$noticia['categoria'], $noticia['id']]);
    $relacionadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Erro ao carregar notícia: " . $e->getMessage());
    header('Location: noticias.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($noticia['titulo']) ?> - FAP Pádua</title>
    <link rel="icon" type="image/png" href="/imagens/favicon.png">
    <meta name="description" content="<?= htmlspecialchars($noticia['resumo']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($noticia['titulo']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($noticia['resumo']) ?>">
    <?php if ($noticia['imagem_destaque']): ?>
    <meta property="og:image" content="<?= htmlspecialchars($noticia['imagem_destaque']) ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .article-content {
            font-size: 17px;
            line-height: 1.7;
            color: #374151;
        }
        
        .article-content p {
            margin-bottom: 1.25rem;
        }
        
        .article-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .article-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        
        .article-content ul, .article-content ol {
            margin-left: 1.5rem;
            margin-bottom: 1.25rem;
        }
        
        .article-content li {
            margin-bottom: 0.5rem;
        }
        
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        
        .article-content strong {
            font-weight: 600;
            color: #111827;
        }
        
        .related-card:hover {
            transform: translateY(-2px);
            transition: all 0.2s;
        }
        
        .share-btn {
            transition: all 0.2s;
        }
        
        .share-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-4 md:py-6 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <a href="index.html" class="text-xl md:text-2xl font-bold">FAP Pádua</a>
                <nav class="hidden md:flex space-x-6">
                    <a href="index.html" class="hover:text-blue-200 transition">Início</a>
                    <a href="noticias.php" class="hover:text-blue-200 transition">Notícias</a>
                    <a href="#" class="hover:text-blue-200 transition">Sobre</a>
                    <a href="#" class="hover:text-blue-200 transition">Contato</a>
                </nav>
                <button class="md:hidden">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="bg-white py-2 md:py-3 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex items-center text-xs md:text-sm text-gray-600">
                <a href="index.html" class="hover:text-blue-600">Início</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="noticias.php" class="hover:text-blue-600">Notícias</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-900"><?= htmlspecialchars($noticia['titulo']) ?></span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="background-color: #f4f4f5" class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-4 md:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-8">
                <!-- Main Article -->
                <div class="lg:col-span-8">
                    <!-- Back Button -->
                    <div class="mb-4 md:mb-6">
                        <a href="noticias.php" class="inline-flex items-center text-gray-700 hover:text-blue-600 transition text-sm md:text-base">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <span class="font-medium">Notícias</span>
                        </a>
                    </div>

                    <!-- Article Card -->
                    <article class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <!-- Header -->
                        <div class="p-4 md:p-8 pb-4 md:pb-6">
                            <!-- Title -->
                            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-3 md:mb-4 leading-tight">
                                <?= htmlspecialchars($noticia['titulo']) ?>
                            </h1>
                            
                            <!-- Resume -->
                            <?php if ($noticia['resumo']): ?>
                            <p class="text-base md:text-lg text-gray-600 mb-4 md:mb-6 font-normal">
                                <?= htmlspecialchars($noticia['resumo']) ?>
                            </p>
                            <?php endif; ?>
                            
                            <!-- Meta Info -->
                            <div class="flex flex-wrap items-center text-gray-500 text-xs md:text-sm border-b border-gray-200 pb-3 md:pb-4">
                                <span class="mr-2"><?php
                                    $meses = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 
                                             'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
                                    $dias_semana = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 
                                                   'quinta-feira', 'sexta-feira', 'sábado'];
                                    $data = new DateTime($noticia['publicado_em']);
                                    echo $dias_semana[$data->format('w')] . ', ' . 
                                         $data->format('d') . ' ' . 
                                         $meses[(int)$data->format('m')] . ' ' . 
                                         $data->format('Y, H:i');
                                ?></span>
                                <span class="mx-2">|</span>
                                <?php if ($noticia['categoria_nome']): ?>
                                <a href="noticias.php?categoria=<?= urlencode($noticia['categoria']) ?>" 
                                   class="font-medium uppercase text-xs tracking-wider"
                                   style="color: <?= htmlspecialchars($noticia['categoria_cor']) ?>">
                                    <?= strtoupper(htmlspecialchars($noticia['categoria_nome'])) ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Featured Image -->
                        <?php if ($noticia['imagem_destaque']): ?>
                        <div class="px-4 md:px-8">
                            <img src="<?= htmlspecialchars($noticia['imagem_destaque']) ?>" 
                                 alt="<?= htmlspecialchars($noticia['titulo']) ?>"
                                 class="w-full rounded-lg">
                        </div>
                        <?php endif; ?>
                        
                        <div class="p-4 md:p-8">
                            <!-- Share Icons -->
                            <div class="mb-4 md:mb-6 pb-4 md:pb-6 border-b border-gray-200">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs md:text-sm font-medium text-gray-700 mr-2">Compartilhar</span>
                                    <div class="flex space-x-2">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                           target="_blank"
                                           class="share-btn w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700">
                                            <i class="fab fa-facebook-f text-sm"></i>
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($noticia['titulo']) ?>&url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                           target="_blank"
                                           class="share-btn w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-sky-500 text-white hover:bg-sky-600">
                                            <i class="fab fa-twitter text-sm"></i>
                                        </a>
                                        <a href="https://wa.me/?text=<?= urlencode($noticia['titulo'] . ' - https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                           target="_blank"
                                           class="share-btn w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-green-600 text-white hover:bg-green-700">
                                            <i class="fab fa-whatsapp text-sm"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="article-content">
                                <?= $noticia['conteudo'] ?>
                            </div>

                            <!-- Author/Source -->
                            <div class="mt-6 md:mt-8 pt-4 md:pt-6 border-t border-gray-200">
                                <p class="text-xs md:text-sm text-gray-500 italic">
                                    <?= htmlspecialchars($noticia['autor_nome']) ?> - Instituto de Previdência FAP Pádua
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            
                <!-- Sidebar -->
                <aside class="lg:col-span-4">
                    <!-- Related News -->
                    <?php if (!empty($relacionadas)): ?>
                    <div class="bg-white rounded-lg shadow-sm mb-4 md:mb-6">
                        <div class="p-4 md:p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-base md:text-lg font-bold text-gray-900">Relacionadas</h2>
                                <i class="fas fa-th text-gray-400"></i>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($relacionadas as $rel): ?>
                            <a href="noticia.php?slug=<?= urlencode($rel['slug']) ?>" 
                               class="related-card block p-4 hover:bg-gray-50 transition group">
                                <div class="flex gap-4">
                                    <?php if ($rel['imagem_destaque']): ?>
                                    <img src="<?= htmlspecialchars($rel['imagem_destaque']) ?>" 
                                         alt="<?= htmlspecialchars($rel['titulo']) ?>"
                                         class="w-24 h-24 object-cover rounded flex-shrink-0">
                                    <?php endif; ?>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-medium text-gray-900 group-hover:text-blue-600 transition line-clamp-3 text-sm leading-snug">
                                            <?= htmlspecialchars($rel['titulo']) ?>
                                        </h3>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-blue-900 to-gray-900 text-white py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8 mb-6 md:mb-8">
                <!-- Logo -->
                <div class="flex items-start justify-center sm:justify-start">
                    <img src="imagens/fap logo.png" alt="FAP Logo" class="h-16 md:h-24 w-auto">
                </div>
                
                <!-- Informações Institucionais -->
                <div class="text-center sm:text-left">
                    <h3 class="text-base md:text-lg font-bold mb-3 md:mb-4">FAP – Fundo de Aposentadoria e Pensões</h3>
                    <p class="text-xs md:text-sm text-blue-100 mb-2">Santo Antônio de Pádua</p>
                    <p class="text-xs md:text-sm text-blue-100">CNPJ: 39.421.813/0001-90</p>
                </div>
                
                <!-- Contato -->
                <div class="text-center sm:text-left">
                    <h3 class="text-base md:text-lg font-bold mb-3 md:mb-4">Contato</h3>
                    <div class="space-y-2 text-xs md:text-sm text-blue-100">
                        <p><i class="fas fa-phone mr-2"></i> (22) 3851-0077</p>
                        <p><i class="fas fa-envelope mr-2"></i> fap@santoantoniodepadua.rj.gov.br</p>
                    </div>
                </div>
                
                <!-- Endereço -->
                <div class="text-center sm:text-left">
                    <h3 class="text-base md:text-lg font-bold mb-3 md:mb-4">Endereço</h3>
                    <p class="text-xs md:text-sm text-blue-100">
                        Rua Prefeito Eugênio Leite Lima, Nº 82<br>
                        Centro, Santo Antônio de Pádua / RJ<br>
                        CEP: 28.470-000
                    </p>
                </div>
            </div>
            
            <!-- Redes Sociais e Copyright -->
            <div class="border-t border-blue-800 pt-4 md:pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex space-x-4 mb-4 md:mb-0">
                    <a href="#" class="text-white hover:text-blue-300 transition">
                        <i class="fab fa-facebook text-xl"></i>
                    </a>
                    <a href="#" class="text-white hover:text-blue-300 transition">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="#" class="text-white hover:text-blue-300 transition">
                        <i class="fab fa-youtube text-xl"></i>
                    </a>
                    <a href="#" class="text-white hover:text-blue-300 transition">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                </div>
                <p class="text-sm text-blue-100">© <?= date('Y') ?> FAP. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
