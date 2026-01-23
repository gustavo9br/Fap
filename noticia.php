<?php
require_once 'config/database.php';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: noticias');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar notícia
    $stmt = $db->prepare("
        SELECT n.*, u.nome as autor_nome
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        WHERE n.slug = ? AND n.status = 'publicado'
    ");
    $stmt->execute([$slug]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$noticia) {
        header('Location: noticias');
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
        LIMIT 3
    ");
    $stmt->execute([$noticia['categoria'], $noticia['id']]);
    $relacionadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Erro ao carregar notícia: " . $e->getMessage());
    header('Location: noticias');
    exit;
}

$pageTitle = htmlspecialchars($noticia['titulo']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - FAP Pádua</title>
    <link rel="icon" type="image/png" href="/imagens/favicon.png">
    <meta name="description" content="<?= htmlspecialchars($noticia['resumo']) ?>">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= htmlspecialchars($noticia['resumo']) ?>">
    <?php if (!empty($noticia['imagem_destaque'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($noticia['imagem_destaque']) ?>">
    <?php endif; ?>
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
        .article-content {
            font-size: 1.125rem;
            line-height: 1.8;
            color: #374151;
        }
        
        .article-content p {
            margin-bottom: 1.5rem;
        }
        
        .article-content h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #B8621B;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
        }
        
        .article-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #B8621B;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .article-content ul, .article-content ol {
            margin-left: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .article-content li {
            margin-bottom: 0.75rem;
        }
        
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 2rem 0;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        
        .article-content strong {
            font-weight: 600;
            color: #111827;
        }

        .article-content a {
            color: #00A859;
            text-decoration: underline;
        }

        .article-content a:hover {
            color: #00854a;
        }
    </style>
</head>
<body class="bg-gray-50">

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
                    <ol class="flex items-center gap-2 text-white text-sm flex-wrap">
                        <li>
                            <a href="/" class="hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                FAP PADUA
                            </a>
                        </li>
                        <li class="text-white/70">›</li>
                        <li>
                            <a href="noticias" class="hover:underline">NOTÍCIAS</a>
                        </li>
                        <li class="text-white/70">›</li>
                        <li class="font-semibold"><?= strtoupper(mb_substr($pageTitle, 0, 50)) ?><?= mb_strlen($pageTitle) > 50 ? '...' : '' ?></li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">
                    <?= strtoupper($pageTitle) ?>
                </h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Conteúdo Principal -->
            <div class="lg:col-span-2">
                <!-- Artigo -->
                <article class="bg-white rounded-2xl shadow-md overflow-hidden">
                    <!-- Imagem Destaque -->
                    <?php if (!empty($noticia['imagem_destaque'])): 
                        // Garantir que o caminho está correto
                        $imagem_path = $noticia['imagem_destaque'];
                        // Se não começar com /, adicionar
                        if (substr($imagem_path, 0, 1) !== '/') {
                            $imagem_path = '/' . $imagem_path;
                        }
                    ?>
                    <div class="w-full">
                        <img src="<?= htmlspecialchars($imagem_path) ?>" 
                             alt="<?= $pageTitle ?>"
                             class="w-full h-auto object-cover"
                             onerror="console.error('Erro ao carregar:', this.src); this.parentElement.style.display='none';">
                    </div>
                    <?php endif; ?>
                    
                    <div class="p-8">
                        <!-- Meta Info -->
                        <div class="flex items-center gap-3 mb-6 text-sm text-gray-600 flex-wrap pb-6 border-b border-gray-200">
                            <?php if (!empty($noticia['categoria'])): ?>
                            <a href="noticias?categoria=<?= htmlspecialchars($noticia['categoria']) ?>" 
                               class="px-3 py-1.5 bg-green-100 text-green-800 rounded-full font-medium hover:bg-green-200 transition-colors">
                                <?= htmlspecialchars($noticia['categoria']) ?>
                            </a>
                            <span class="text-gray-300">|</span>
                            <?php endif; ?>
                            <time class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                <?= date('d/m/Y \à\s H:i', strtotime($noticia['publicado_em'])) ?>
                            </time>
                            <span class="text-gray-300">|</span>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <?= number_format($noticia['visualizacoes'] ?? 0) ?> visualizações
                            </span>
                            <span class="text-gray-300">|</span>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <?= htmlspecialchars($noticia['autor_nome']) ?>
                            </span>
                        </div>

                        <!-- Resumo -->
                        <?php if (!empty($noticia['resumo'])): ?>
                        <div class="mb-6 p-6 bg-gray-50 rounded-xl border-l-4 border-green-primary">
                            <p class="text-lg text-gray-700 leading-relaxed italic">
                                <?= htmlspecialchars($noticia['resumo']) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Compartilhar -->
                        <div class="mb-8 pb-6 border-b border-gray-200">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/>
                                    </svg>
                                    Compartilhar:
                                </span>
                                <div class="flex gap-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                       target="_blank"
                                       class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        Facebook
                                    </a>
                                    <a href="https://wa.me/?text=<?= urlencode($noticia['titulo'] . ' - https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                       target="_blank"
                                       class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conteúdo do Artigo -->
                        <div class="article-content">
                            <?= $noticia['conteudo'] ?>
                        </div>

                        <!-- Autor/Footer -->
                        <div class="mt-10 pt-6 border-t border-gray-200">
                            <div class="flex items-start gap-4 p-6 bg-gray-50 rounded-xl">
                                <div class="w-16 h-16 bg-green-primary rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 mb-1"><?= htmlspecialchars($noticia['autor_nome']) ?></p>
                                    <p class="text-sm text-gray-600">Assessoria de Comunicação - FAP Pádua</p>
                                    <p class="text-xs text-gray-500 mt-2">Publicado em <?= date('d/m/Y \à\s H:i', strtotime($noticia['publicado_em'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Botão Voltar -->
                <div class="mt-6">
                    <a href="noticias" class="inline-flex items-center gap-2 text-green-primary hover:text-green-700 font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Voltar para Notícias
                    </a>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Notícias Relacionadas -->
                <?php if (!empty($relacionadas)): ?>
                <div class="bg-white rounded-2xl shadow-md p-6 mb-6 sticky top-4">
                    <h3 class="text-xl font-bold mb-6 flex items-center gap-2" style="color: #B8621B;">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                        </svg>
                        Relacionadas
                    </h3>
                    
                    <div class="space-y-4">
                        <?php foreach ($relacionadas as $rel): ?>
                        <a href="noticia/<?= htmlspecialchars($rel['slug']) ?>" 
                           class="block group">
                            <article class="flex gap-3 hover:bg-gray-50 p-3 rounded-xl transition-colors">
                                <?php if (!empty($rel['imagem_destaque'])): ?>
                                <img src="<?= htmlspecialchars($rel['imagem_destaque']) ?>" 
                                     alt="<?= htmlspecialchars($rel['titulo']) ?>"
                                     class="w-20 h-20 object-cover rounded-lg flex-shrink-0"
                                     onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 group-hover:text-green-primary transition-colors line-clamp-3 text-sm leading-tight mb-2">
                                        <?= htmlspecialchars($rel['titulo']) ?>
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        <?= date('d/m/Y', strtotime($rel['publicado_em'])) ?>
                                    </p>
                                </div>
                            </article>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Newsletter -->
                <div class="bg-gradient-to-br from-green-primary to-green-600 rounded-2xl shadow-md p-6 text-white">
                    <svg class="w-12 h-12 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <h3 class="text-xl font-bold mb-3">Fique por dentro</h3>
                    <p class="mb-6 text-sm opacity-90">Receba as últimas notícias da FAP Pádua diretamente no seu email</p>
                    <a href="contato" class="block w-full bg-white text-green-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors text-center">
                        Assinar Newsletter
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
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
