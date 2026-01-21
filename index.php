<?php
require_once 'config/database.php';
include 'includes/header.php';
$pdo = Database::getInstance()->getConnection();

// Buscar cards de acesso rápido
$stmtCards = $pdo->query("SELECT * FROM acesso_rapido_cards WHERE ativo = 1 ORDER BY ordem ASC");
$cardsAcessoRapido = $stmtCards->fetchAll();

// Mapeamento de ícones
$icones = [
    'estrutura' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>',
    'cadprev' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>',
    'processo' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>',
    'transparencia' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/></svg>',
    'busca' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M9 9a2 2 0 114 0 2 2 0 01-4 0z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a4 4 0 00-3.446 6.032l-2.261 2.26a1 1 0 101.414 1.415l2.261-2.261A4 4 0 1011 5z" clip-rule="evenodd"/></svg>',
    'balanca' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>',
    'livro' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>',
    'documento' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/></svg>',
    'escudo' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
    'cartao' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>',
    'calendario' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>',
    'casa' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>',
    'grafico' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>',
    'dinheiro' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/></svg>',
    'certificado' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
    'link' => '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>',
];

// Ícone padrão
$iconeDefault = '<svg class="w-7 h-7 md:w-9 md:h-9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/></svg>';
?>

<div style="background-color: #f5f5f5" class="min-h-screen">

<!-- Acesso Rápido Section -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-gradient-to-br from-green-primary to-teal-primary rounded-3xl p-10 shadow-xl relative overflow-hidden">
            <!-- Efeito de fundo com círculos -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -mr-48 -mt-48"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-white rounded-full -ml-48 -mb-48"></div>
            </div>
            
            <div class="relative z-10">
                <h2 class="text-white text-2xl md:text-3xl font-bold mb-6 md:mb-8 border-l-4 border-white pl-4">Acesso Rápido</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
            <?php foreach ($cardsAcessoRapido as $card): ?>
            <?php
                // Determinar o link
                $link = '/' . $card['slug'];
                $target = '';
                if ($card['tipo'] === 'link' && !empty($card['link_externo'])) {
                    $link = $card['link_externo'];
                    $target = 'target="_blank"';
                } elseif ($card['tipo'] === 'pdf' && !empty($card['arquivo_pdf'])) {
                    $link = $card['arquivo_pdf'];
                    $target = 'target="_blank"';
                }
                
                // Obter ícone
                $icone = isset($icones[$card['icone']]) ? $icones[$card['icone']] : $iconeDefault;
            ?>
            <a href="<?= htmlspecialchars($link) ?>" <?= $target ?> class="bg-white rounded-xl p-4 md:p-5 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 group">
                <div class="flex items-center gap-3">
                    <div class="text-green-primary text-2xl md:text-3xl group-hover:scale-110 transition-transform">
                        <?= $icone ?>
                    </div>
                    <span class="text-gray-800 font-medium text-xs md:text-sm leading-tight"><?= htmlspecialchars($card['titulo']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
            
            <?php if (empty($cardsAcessoRapido)): ?>
            <!-- Fallback caso não haja cards cadastrados -->
            <div class="col-span-full text-center text-white/70 py-8">
                <p>Nenhum card de acesso rápido cadastrado.</p>
            </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</section>

<!-- Notícias Section -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6 md:mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 border-l-4 border-green-primary pl-4">Notícias</h2>
            <a href="noticias" class="flex items-center gap-2 text-green-primary hover:text-green-dark font-medium border-2 border-green-primary px-5 py-2.5 rounded-lg hover:bg-green-primary hover:text-white transition-all">
                Todas as notícias
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        
        <div id="noticias-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <!-- Notícias serão carregadas aqui via JavaScript -->
        </div>
        
        <div id="loading-noticias" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-primary"></div>
        </div>
    </div>
</section>

<script>
// Carregar notícias do banco de dados
async function carregarNoticias() {
    try {
        const response = await fetch('api/noticias.php?pagina=1');
        const data = await response.json();
        
        const container = document.getElementById('noticias-container');
        const loading = document.getElementById('loading-noticias');
        
        if (data.sucesso && data.noticias.length > 0) {
            // Pegar apenas as 3 primeiras notícias
            const noticias = data.noticias.slice(0, 3);
            
            noticias.forEach((noticia, index) => {
                const card = criarCardNoticia(noticia, index === 0);
                container.innerHTML += card;
            });
            
            loading.style.display = 'none';
        } else {
            container.innerHTML = '<div class="col-span-2 text-center py-12 text-gray-500">Nenhuma notícia publicada ainda.</div>';
            loading.style.display = 'none';
        }
    } catch (error) {
        console.error('Erro ao carregar notícias:', error);
        document.getElementById('loading-noticias').innerHTML = '<p class="text-red-500">Erro ao carregar notícias</p>';
    }
}

function criarCardNoticia(noticia, isDestaque = false) {
    const data = new Date(noticia.publicado_em);
    const dataFormatada = data.toLocaleDateString('pt-BR');
    
    // Gerar cor aleatória para imagens sem destaque
    const cores = [
        'from-green-400 to-teal-500',
        'from-blue-400 to-blue-600',
        'from-purple-400 to-purple-600',
        'from-pink-400 to-pink-600',
        'from-orange-400 to-orange-600',
        'from-red-400 to-red-600'
    ];
    const corAleatoria = cores[Math.floor(Math.random() * cores.length)];
    
    if (isDestaque) {
        // Card grande (primeira notícia)
        return `
            <a href="noticia/${noticia.slug}" class="md:row-span-2 bg-white rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 group cursor-pointer">
                <div class="h-80 relative overflow-hidden">
                    ${noticia.imagem_destaque ? 
                        `<img src="${noticia.imagem_destaque}" alt="${noticia.titulo}" class="w-full h-full object-cover">` :
                        `<div class="w-full h-full bg-gradient-to-br ${corAleatoria}"></div>`
                    }
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-all"></div>
                    ${noticia.categoria ? `
                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-1 inline-block mb-2">
                            <span class="text-xs font-medium px-2">${noticia.categoria}</span>
                        </div>
                    </div>` : ''}
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                        ${dataFormatada}
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 group-hover:text-green-primary transition-colors leading-tight">
                        ${noticia.titulo}
                    </h3>
                    ${noticia.resumo ? `<p class="text-gray-600 text-sm mb-4 leading-relaxed">${noticia.resumo}</p>` : ''}
                    <span class="text-green-primary font-semibold hover:underline inline-flex items-center gap-1">
                        Leia mais 
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
        `;
    } else {
        // Cards médios (demais notícias)
        return `
            <a href="noticia/${noticia.slug}" class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 group cursor-pointer">
                <div class="h-48 relative overflow-hidden">
                    ${noticia.imagem_destaque ? 
                        `<img src="${noticia.imagem_destaque}" alt="${noticia.titulo}" class="w-full h-full object-cover">` :
                        `<div class="w-full h-full bg-gradient-to-br ${corAleatoria}"></div>`
                    }
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-all"></div>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                        ${dataFormatada}
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-green-primary transition-colors leading-tight">
                        ${noticia.titulo}
                    </h3>
                    ${noticia.resumo ? `<p class="text-gray-600 text-sm mb-3 line-clamp-2">${noticia.resumo}</p>` : ''}
                    <span class="text-green-primary font-semibold hover:underline text-sm inline-flex items-center gap-1">
                        Leia mais 
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
        `;
    }
}

// Carregar notícias quando a página carregar
document.addEventListener('DOMContentLoaded', carregarNoticias);
</script>

<!-- Demonstrativos Contábeis Section -->
<section class="py-16" style="background-color: #ebeced">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-10 border-l-4 border-green-primary pl-4">Demonstrativos contábeis</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5">
            <?php
            // Buscar todos os cards do financeiro
            try {
                $stmt = $pdo->query("SELECT * FROM financeiro_cards WHERE ativo = 1 ORDER BY ordem ASC");
                $cards = $stmt->fetchAll();
            } catch (PDOException $e) {
                $cards = [];
            }
            
            foreach ($cards as $card):
                // Determinar URL do card
                if ($card['tipo'] === 'pdf' && $card['arquivo_pdf']) {
                    $url = $card['arquivo_pdf'];
                    $target = 'target="_blank"';
                } elseif ($card['tipo'] === 'link' && $card['link_externo']) {
                    $url = $card['link_externo'];
                    $target = '';
                } elseif ($card['tipo'] === 'pagina') {
                    $url = "/financeiro/" . htmlspecialchars($card['slug']);
                    $target = '';
                } else {
                    $url = "#";
                    $target = '';
                }
            ?>
            <a href="<?= $url ?>" <?= $target ?> class="bg-white rounded-xl p-4 md:p-6 hover:bg-green-primary hover:text-white transition-all duration-300 group shadow-sm hover:shadow-lg">
                <div class="text-green-primary group-hover:text-white text-3xl mb-3">
                    <?= $card['icone'] ?>
                </div>
                <span class="font-medium text-sm leading-tight block"><?= htmlspecialchars($card['titulo']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Clube FAP Vantagens Section -->
<section class="py-16" style="background-color: #f5f5f5">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6 border-l-4 border-green-primary pl-4">Clube FAP Vantagens</h2>
            <div class="bg-white rounded-xl p-6 md:p-8 shadow-sm">
                <h3 class="text-xl md:text-2xl font-bold text-green-primary mb-4">Clube de vantagens do Aposentado Municipal</h3>
                <p class="text-gray-700 mb-3 leading-relaxed">
                    A FAP trabalha por oportunidades de vida melhor, também para a terceira idade.
                    Por isso lançou, através do Instituto de Previdência do Município de Santo Antonio de Padua - RJ, o Clube de vantagens do Aposentado Municipal.
                </p>
                <p class="text-gray-700 mb-3 leading-relaxed">
                    Com ele, ex-servidores têm benefícios, descontos e promoções, numa ampla rede de conveniados.
                </p>
                <p class="text-gray-700 leading-relaxed font-semibold">
                    Um reconhecimento a quem tanto contribuiu para o desenvolvimento da cidade.
                </p>
            </div>
        </div>

        <!-- Filtro de Categorias -->
        <div class="mb-8">
            <select id="categoria-filter" class="w-full md:w-auto px-6 py-3 bg-white border-2 border-gray-300 rounded-lg text-gray-700 font-medium focus:outline-none focus:border-green-primary">
                <option value="todos">Todas as categorias</option>
            </select>
        </div>

        <!-- Grid de Conveniados -->
        <div id="convenios-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Convênios serão carregados aqui via JavaScript -->
        </div>

        <!-- Loading -->
        <div id="loading-convenios" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-primary"></div>
            <p class="text-gray-600 mt-4">Carregando convênios...</p>
        </div>

        <!-- Mensagem quando não há resultados -->
        <div id="no-results" class="hidden text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-500 text-lg">Nenhum convênio encontrado nesta categoria.</p>
        </div>
    </div>
</section>

<script>
// Carregar categorias
async function carregarCategorias() {
    try {
        const response = await fetch('api/categorias_convenios.php');
        const data = await response.json();
        
        if (data.sucesso && data.categorias.length > 0) {
            const select = document.getElementById('categoria-filter');
            data.categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.slug;
                option.textContent = cat.nome;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}

// Carregar convênios
async function carregarConvenios(categoria = 'todos') {
    try {
        const response = await fetch(`api/convenios.php?categoria=${categoria}`);
        const data = await response.json();
        
        const container = document.getElementById('convenios-container');
        const loading = document.getElementById('loading-convenios');
        const noResults = document.getElementById('no-results');
        
        container.innerHTML = '';
        
        if (data.sucesso && data.convenios.length > 0) {
            data.convenios.forEach(convenio => {
                const card = criarCardConvenio(convenio);
                container.innerHTML += card;
            });
            
            loading.style.display = 'none';
            noResults.classList.add('hidden');
        } else {
            loading.style.display = 'none';
            noResults.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Erro ao carregar convênios:', error);
        document.getElementById('loading-convenios').innerHTML = '<p class="text-red-500">Erro ao carregar convênios</p>';
    }
}

function criarCardConvenio(convenio) {
    const corCategoria = convenio.categoria_cor || '#4A90E2';
    const logoHtml = convenio.logo ? 
        `<img src="${convenio.logo}" alt="${convenio.nome}" class="h-full w-full object-contain">` :
        `<div class="text-center">
            <div class="text-4xl font-bold text-gray-400 mb-2">LOGO</div>
            <span class="text-xs text-gray-500">${convenio.nome}</span>
        </div>`;
    
    return `
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
            <div class="h-32 bg-gray-200 flex items-center justify-center p-4">
                ${logoHtml}
            </div>
            <div class="p-5">
                <h3 class="text-lg font-bold text-gray-800 mb-3">${convenio.nome}</h3>
                ${convenio.categoria_nome ? `
                <div class="flex items-start gap-2 mb-3">
                    <span class="inline-block px-2 py-1 text-white text-xs font-semibold rounded" style="background-color: ${corCategoria}">
                        ${convenio.categoria_nome}
                    </span>
                </div>` : ''}
                <p class="text-gray-600 text-sm leading-relaxed mb-3">
                    ${convenio.descricao || convenio.desconto || 'Desconto especial para aposentados'}
                </p>
                ${convenio.endereco ? `
                <p class="text-gray-500 text-xs mt-2">
                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    ${convenio.endereco}
                </p>` : ''}
                ${convenio.telefone ? `
                <p class="text-gray-500 text-xs mt-1">
                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                    </svg>
                    ${convenio.telefone}
                </p>` : ''}
            </div>
        </div>
    `;
}

// Filtro de convênios
document.getElementById('categoria-filter').addEventListener('change', function() {
    carregarConvenios(this.value);
});

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    carregarCategorias();
    carregarConvenios();
});
</script>

<!-- Redes Sociais Section -->
<section class="py-12 md:py-16" style="background-color: #ebeced">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Instagram Preview -->
            <div class="flex justify-center lg:justify-end">
                <img src="imagens/Fap insta .png" alt="Instagram FAP Pádua" class="w-64 md:w-80 lg:w-96 rounded-lg shadow-xl">
            </div>
            
            <!-- Conteúdo: Texto + Botões -->
            <div class="flex flex-col items-center lg:items-start">
                <!-- Texto -->
                <div class="text-center lg:text-left mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Siga nossas</h2>
                    <h3 class="text-2xl md:text-3xl font-bold text-blue-600">Redes Sociais</h3>
                </div>
                
                <!-- Botões de Redes Sociais -->
                <div class="flex flex-col gap-4 w-full max-w-sm">
                    <!-- WhatsApp -->
                    <a href="https://wa.me/5522000000000" target="_blank" class="flex items-center justify-center gap-3 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span class="font-bold">WHATSAPP</span>
                    </a>
                    
                    <!-- Instagram -->
                    <a href="https://www.instagram.com/fappadua/" target="_blank" class="flex items-center justify-center gap-3 bg-gradient-to-r from-purple-500 via-pink-500 to-red-500 hover:from-purple-600 hover:via-pink-600 hover:to-red-600 text-white px-6 py-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        <span class="font-bold">INSTAGRAM</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

</div>

<?php include 'includes/footer.php'; ?>
