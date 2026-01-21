<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notícias - FAP PADUA</title>
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

<?php
require_once 'config/database.php';

// Filtro por categoria
$categoria_filtro = $_GET['categoria'] ?? '';

// Paginação
$pagina = $_GET['pagina'] ?? 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar notícias publicadas
    $sql = "
        SELECT n.*, u.nome as autor_nome
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        WHERE n.status = 'publicado'
    ";
    
    $params = [];
    
    if ($categoria_filtro) {
        $sql .= " AND n.categoria = ?";
        $params[] = $categoria_filtro;
    }
    
    $sql .= " ORDER BY n.publicado_em DESC LIMIT ? OFFSET ?";
    $params[] = $por_pagina;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $noticias = $stmt->fetchAll();
    
    // Total de notícias
    $sql_count = "SELECT COUNT(*) FROM noticias WHERE status = 'publicado'";
    if ($categoria_filtro) {
        $sql_count .= " AND categoria = ?";
        $stmt_count = $db->prepare($sql_count);
        $stmt_count->execute([$categoria_filtro]);
    } else {
        $stmt_count = $db->query($sql_count);
    }
    $total_noticias = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_noticias / $por_pagina);
    
    // Buscar categorias com contagem
    $stmt = $db->query("
        SELECT c.*, COUNT(n.id) as total_noticias
        FROM categorias c
        LEFT JOIN noticias n ON n.categoria = c.slug AND n.status = 'publicado'
        WHERE c.ativa = 1
        GROUP BY c.id
        HAVING total_noticias > 0
        ORDER BY c.ordem, c.nome
    ");
    $categorias = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Erro ao buscar notícias: " . $e->getMessage());
    $noticias = [];
    $categorias = [];
}

include 'includes/header.php';
?>

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
                        <li class="font-semibold">NOTÍCIAS</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">
                    <?= $categoria_filtro ? 'NOTÍCIAS - ' . strtoupper(htmlspecialchars($categoria_filtro)) : 'NOTÍCIAS' ?>
                </h1>
                <?php if ($categoria_filtro): ?>
                    <a href="noticias" class="inline-flex items-center text-white/90 hover:text-white hover:underline mt-4 ml-5 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Ver todas as notícias
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Conteúdo Principal -->
            <div class="lg:col-span-2">
                <!-- Lista de Notícias -->
                <div id="lista-noticias" class="space-y-6">
                    <?php if (empty($noticias)): ?>
                        <div class="bg-white rounded-2xl shadow-md p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                            <p class="text-gray-500 text-lg">Nenhuma notícia encontrada</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($noticias as $noticia): ?>
                            <article class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-all">
                                <?php if ($noticia['imagem_destaque']): ?>
                                    <a href="noticia/<?= htmlspecialchars($noticia['slug']) ?>">
                                        <img src="<?= htmlspecialchars($noticia['imagem_destaque']) ?>" 
                                             alt="<?= htmlspecialchars($noticia['titulo']) ?>"
                                             class="w-full h-64 object-cover hover:opacity-90 transition-opacity">
                                    </a>
                                <?php endif; ?>
                                
                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-3 text-sm text-gray-500 flex-wrap">
                                        <a href="noticias?categoria=<?= htmlspecialchars($noticia['categoria']) ?>" 
                                           class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium hover:bg-green-200">
                                            <?= htmlspecialchars($noticia['categoria']) ?>
                                        </a>
                                        <span>•</span>
                                        <time><?= date('d/m/Y H:i', strtotime($noticia['publicado_em'])) ?></time>
                                        <span>•</span>
                                        <span><?= $noticia['visualizacoes'] ?? 0 ?> visualizações</span>
                                    </div>
                                    
                                    <h2 class="text-2xl font-bold text-gray-800 mb-3 hover:text-green-primary transition-colors">
                                        <a href="noticia/<?= htmlspecialchars($noticia['slug']) ?>">
                                            <?= htmlspecialchars($noticia['titulo']) ?>
                                        </a>
                                    </h2>
                                    
                                    <?php if ($noticia['resumo']): ?>
                                        <p class="text-gray-600 mb-4 line-clamp-3">
                                            <?= htmlspecialchars($noticia['resumo']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center justify-between">
                                        <a href="noticia/<?= htmlspecialchars($noticia['slug']) ?>" 
                                           class="inline-flex items-center text-green-primary font-medium hover:text-green-700">
                                            Leia mais
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                        <span class="text-sm text-gray-400">
                                            Por <?= htmlspecialchars($noticia['autor_nome']) ?>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Loading Indicator -->
                <div id="loading" class="hidden text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-primary"></div>
                    <p class="text-gray-600 mt-4">Carregando mais notícias...</p>
                </div>

                <!-- Fim das notícias -->
                <div id="fim-noticias" class="<?= $pagina >= $total_paginas ? '' : 'hidden' ?> text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>Você viu todas as notícias disponíveis</p>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Categorias -->
                <div class="bg-white rounded-2xl shadow-md p-6 mb-6 sticky top-4">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2" style="color: #B8621B;">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                        </svg>
                        Categorias
                    </h3>
                    
                    <?php if (empty($categorias)): ?>
                        <p class="text-gray-500 text-sm">Nenhuma categoria disponível</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($categorias as $cat): ?>
                                <a href="noticias?categoria=<?= htmlspecialchars($cat['slug']) ?>" 
                                   class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group <?= $categoria_filtro === $cat['slug'] ? 'bg-green-50' : '' ?>">
                                    <span class="font-medium text-gray-700 group-hover:text-green-primary <?= $categoria_filtro === $cat['slug'] ? 'text-green-primary' : '' ?>">
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </span>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">
                                        <?= $cat['total_noticias'] ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Newsletter -->
                <div class="bg-gradient-to-br from-green-primary to-green-600 rounded-2xl shadow-md p-6 text-white mb-6">
                    <h3 class="text-xl font-bold mb-3">📧 Fique por dentro</h3>
                    <p class="mb-4">Receba as últimas notícias da FAP Pádua diretamente no seu email</p>
                    <a href="contato" class="bg-white text-green-primary px-4 py-2 rounded-lg font-medium hover:bg-gray-100 inline-block transition-colors">
                        Assinar newsletter
                    </a>
                </div>

                <!-- Informações -->
                <div class="bg-white rounded-2xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4" style="color: #B8621B;">ℹ️ Informações</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-gray-700">Atendimento:</strong><br>
                                <span class="text-gray-600">Segunda a Sexta, 8h às 17h</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            <div>
                                <strong class="text-gray-700">Telefone:</strong><br>
                                <span class="text-gray-600">(22) 3851-0077</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
let paginaAtual = <?= $pagina ?>;
let carregando = false;
let fimDasNoticias = <?= $pagina >= $total_paginas ? 'true' : 'false' ?>;
const categoriaFiltro = '<?= $categoria_filtro ?>';

// Scroll infinito
window.addEventListener('scroll', function() {
    if (carregando || fimDasNoticias) return;
    
    const scrollPosition = window.innerHeight + window.scrollY;
    const pageHeight = document.documentElement.scrollHeight;
    
    // Quando chegar a 80% da página
    if (scrollPosition >= pageHeight * 0.8) {
        carregarMaisNoticias();
    }
});

async function carregarMaisNoticias() {
    carregando = true;
    document.getElementById('loading').classList.remove('hidden');
    
    try {
        let url = `api/noticias.php?pagina=${paginaAtual + 1}`;
        if (categoriaFiltro) {
            url += `&categoria=${categoriaFiltro}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.sucesso && data.noticias.length > 0) {
            const listaNoticias = document.getElementById('lista-noticias');
            
            data.noticias.forEach(noticia => {
                const article = criarCardNoticia(noticia);
                listaNoticias.insertAdjacentHTML('beforeend', article);
            });
            
            paginaAtual++;
            
            if (!data.tem_mais) {
                fimDasNoticias = true;
                document.getElementById('fim-noticias').classList.remove('hidden');
            }
        } else {
            fimDasNoticias = true;
            document.getElementById('fim-noticias').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Erro ao carregar notícias:', error);
    } finally {
        carregando = false;
        document.getElementById('loading').classList.add('hidden');
    }
}

function criarCardNoticia(noticia) {
    const imagem = noticia.imagem_destaque ? `
        <a href="noticia/${noticia.slug}">
            <img src="${noticia.imagem_destaque}" 
                 alt="${noticia.titulo}"
                 class="w-full h-64 object-cover hover:opacity-90 transition-opacity">
        </a>
    ` : '';
    
    const resumo = noticia.resumo ? `<p class="text-gray-600 mb-4 line-clamp-3">${noticia.resumo}</p>` : '';
    
    const data = new Date(noticia.publicado_em);
    const dataFormatada = data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    
    return `
        <article class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-all">
            ${imagem}
            <div class="p-6">
                <div class="flex items-center gap-3 mb-3 text-sm text-gray-500 flex-wrap">
                    <a href="noticias?categoria=${noticia.categoria}" 
                       class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium hover:bg-green-200">
                        ${noticia.categoria}
                    </a>
                    <span>•</span>
                    <time>${dataFormatada}</time>
                    <span>•</span>
                    <span>${noticia.visualizacoes || 0} visualizações</span>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-800 mb-3 hover:text-green-primary transition-colors">
                    <a href="noticia/${noticia.slug}">
                        ${noticia.titulo}
                    </a>
                </h2>
                
                ${resumo}
                
                <div class="flex items-center justify-between">
                    <a href="noticia/${noticia.slug}" 
                       class="inline-flex items-center text-green-primary font-medium hover:text-green-700">
                        Leia mais
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <span class="text-sm text-gray-400">
                        Por ${noticia.autor_nome}
                    </span>
                </div>
            </div>
        </article>
    `;
}
</script>

<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include 'includes/footer.php'; ?>

</body>
</html>
