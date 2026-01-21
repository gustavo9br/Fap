<?php
// Detectar a página atual
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
if ($currentPage === 'index') {
    $currentPage = 'inicio';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Instituto de Previdência</title>
    <meta name="description" content="Instituto de Previdência - Cuidando do futuro dos servidores públicos">
    <link rel="icon" type="image/png" href="/imagens/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'green-primary': '#4A90E2',
                        'green-dark': '#2E5C8A',
                        'teal-primary': '#6AB4E8',
                        'teal-dark': '#1C3D5A',
                    },
                    fontFamily: {
                        'sans': ['Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans" style="background-color: #f5f5f5">
    <!-- Header Único -->
    <header class="bg-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <!-- Badges/Selos (Esquerda) -->
                <div class="hidden lg:flex items-center gap-3">
                    <img src="/imagens/SELOPROGESTAO-NIVELII-RPPS-05f-1.png" alt="Selo Pró-Gestão" class="h-16 w-auto">
                    <a href="/progestao">
                        <img src="/imagens/progestao2.png" alt="Pró-Gestão Nível II" class="h-14 w-auto">
                    </a>
                </div>
                
                <!-- Logo (Centro) -->
                <a href="/" class="flex items-center">
                    <img id="site-logo" src="/imagens/fap logo preto.png" alt="FAP Pádua" class="h-12 md:h-16 w-auto">
                </a>
                
                <!-- Redes Sociais + Acessibilidade + Busca (Direita) -->
                <div class="flex items-center gap-2 md:gap-3">
                    <!-- Redes Sociais -->
                    <div class="hidden md:flex items-center gap-2">
                        <a href="https://www.instagram.com/fappadua/" target="_blank" class="w-9 h-9 rounded-full border-2 border-gray-300 flex items-center justify-center text-gray-600 hover:border-green-primary hover:text-green-primary transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" target="_blank" class="w-9 h-9 rounded-full border-2 border-gray-300 flex items-center justify-center text-gray-600 hover:border-green-primary hover:text-green-primary transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://wa.me/5522000000000" target="_blank" class="w-9 h-9 rounded-full border-2 border-gray-300 flex items-center justify-center text-gray-600 hover:border-green-primary hover:text-green-primary transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    </div>
                    
                    <!-- Botão Acessibilidade com Dropdown -->
                    <div class="hidden md:block relative accessibility-menu">
                        <button class="flex items-center gap-2 text-sm text-gray-700 hover:text-green-primary transition-colors px-3 py-1.5 rounded hover:bg-white">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                            <span class="hidden lg:inline font-medium">Acessibilidade</span>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="accessibility-dropdown absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible transition-all duration-200 z-[100]">
                            <div class="p-4">
                                <h3 class="text-lg font-bold text-gray-800 mb-4">Acessibilidade</h3>
                                
                                <!-- Fonte Grande -->
                                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                                    <span class="text-gray-700 font-medium">Fonte grande</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="toggle-font-size" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-primary"></div>
                                    </label>
                                </div>
                                
                                <!-- Alto Contraste (Modo Dark) -->
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-medium">Alto contraste</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="toggle-dark-mode" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-primary"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button id="search-btn" class="bg-green-primary text-white w-9 h-9 rounded-md flex items-center justify-center hover:bg-green-dark transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    
                    <!-- Menu Hamburguer Mobile -->
                    <button id="mobile-menu-btn" class="md:hidden text-gray-700 ml-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Menu Desktop -->
    <nav class="hidden md:block bg-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <ul class="flex flex-nowrap items-center justify-center gap-6 lg:gap-10 py-3.5">
                <li class="whitespace-nowrap"><a href="/" class="<?php echo ($currentPage === 'inicio') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium text-[15px] hover:text-green-primary transition-colors">Início</a></li>
                <li class="whitespace-nowrap"><a href="/servicos" class="<?php echo ($currentPage === 'servicos') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium text-[15px] hover:text-green-primary transition-colors">Serviços</a></li>
                <li class="whitespace-nowrap"><a href="/progestao" class="<?php echo ($currentPage === 'progestao') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium text-[15px] hover:text-green-primary transition-colors">Pró-Gestão</a></li>
                <li class="whitespace-nowrap"><a href="/noticias" class="<?php echo ($currentPage === 'noticias' || $currentPage === 'noticia') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium text-[15px] hover:text-green-primary transition-colors">Notícias</a></li>
                <li class="whitespace-nowrap"><a href="/conselhos-e-comites" class="<?php echo ($currentPage === 'conselhos-e-comites') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium text-[15px] hover:text-green-primary transition-colors">Conselhos e Comitês</a></li>
                <li class="whitespace-nowrap"><a href="/sobre" class="<?php echo ($currentPage === 'sobre') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium text-[15px] hover:text-green-primary transition-colors">Sobre</a></li>
                <li class="whitespace-nowrap"><a href="/contato" class="<?php echo ($currentPage === 'contato') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium text-[15px] hover:text-green-primary transition-colors">Fale Conosco</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- Menu Mobile -->
    <div id="mobile-menu" class="hidden md:hidden bg-white shadow-lg">
        <div class="px-4 py-2">
            <a href="/" class="block py-3 <?php echo ($currentPage === 'inicio') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium border-b border-gray-200">Início</a>
            <a href="/servicos" class="block py-3 <?php echo ($currentPage === 'servicos') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium border-b border-gray-200">Serviços</a>
            <a href="/progestao" class="block py-3 <?php echo ($currentPage === 'progestao') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium border-b border-gray-200">Pró-Gestão</a>
            <a href="/noticias" class="block py-3 <?php echo ($currentPage === 'noticias' || $currentPage === 'noticia') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium border-b border-gray-200">Notícias</a>
            <a href="/conselhos-e-comites" class="block py-3 <?php echo ($currentPage === 'conselhos-e-comites') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium border-b border-gray-200">Conselhos e Comitês</a>
            <a href="/sobre" class="block py-3 <?php echo ($currentPage === 'sobre') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium border-b border-gray-200">Sobre</a>
            <a href="/contato" class="block py-3 <?php echo ($currentPage === 'contato') ? 'text-green-primary' : 'text-gray-700'; ?> font-medium">Fale Conosco</a>
        </div>
    </div>
    
    <!-- Modal de Busca -->
    <div id="search-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl mx-4">
            <!-- Header do Modal -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Buscar no portal</h2>
                <button id="close-search" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Conteúdo do Modal -->
            <div class="p-6">
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input 
                            type="text" 
                            id="search-input"
                            placeholder="Digite o que procura" 
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary focus:border-transparent text-gray-700"
                        >
                    </div>
                    <button 
                        id="search-submit"
                        class="bg-green-primary text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-dark transition-colors shadow-sm"
                    >
                        Pesquisar
                    </button>
                </div>
                
                <!-- Resultados aparecerão aqui -->
                <div id="search-results" class="mt-6 hidden">
                    <p class="text-gray-600 text-sm">Resultados da busca aparecerão aqui...</p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .accessibility-menu:hover .accessibility-dropdown {
            opacity: 1;
            visibility: visible;
        }
        
        body.large-font {
            font-size: 18px;
        }
        
        body.large-font h1 { font-size: 2.5rem; }
        body.large-font h2 { font-size: 2rem; }
        body.large-font h3 { font-size: 1.75rem; }
        body.large-font p, body.large-font span, body.large-font a { font-size: 1.125rem; }
        
        body.dark-mode {
            background-color: #1a1a1a !important;
            color: #e5e5e5 !important;
        }
        
        body.dark-mode header,
        body.dark-mode nav,
        body.dark-mode footer {
            background-color: #2d2d2d !important;
        }
        
        body.dark-mode .bg-white {
            background-color: #3d3d3d !important;
            color: #e5e5e5 !important;
        }
        
        body.dark-mode .text-gray-700,
        body.dark-mode .text-gray-800,
        body.dark-mode .text-gray-900 {
            color: #e5e5e5 !important;
        }
        
        body.dark-mode .bg-gray-100 {
            background-color: #2d2d2d !important;
        }
        
        body.dark-mode .border-gray-200,
        body.dark-mode .border-gray-300 {
            border-color: #4d4d4d !important;
        }
    </style>
    
    <script>
        // Menu Mobile
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Modal de Busca
        const searchModal = document.getElementById('search-modal');
        const searchBtn = document.getElementById('search-btn');
        const closeSearch = document.getElementById('close-search');
        const searchInput = document.getElementById('search-input');
        
        searchBtn.addEventListener('click', () => {
            searchModal.classList.remove('hidden');
            searchModal.classList.add('flex');
            setTimeout(() => searchInput.focus(), 100);
        });
        
        closeSearch.addEventListener('click', () => {
            searchModal.classList.add('hidden');
            searchModal.classList.remove('flex');
        });
        
        // Fechar ao clicar fora do modal
        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) {
                searchModal.classList.add('hidden');
                searchModal.classList.remove('flex');
            }
        });
        
        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !searchModal.classList.contains('hidden')) {
                searchModal.classList.add('hidden');
                searchModal.classList.remove('flex');
            }
        });
        
        // Carregar preferências salvas
        document.addEventListener('DOMContentLoaded', function() {
            const largeFontEnabled = localStorage.getItem('largeFont') === 'true';
            const darkModeEnabled = localStorage.getItem('darkMode') === 'true';
            
            if (largeFontEnabled) {
                document.body.classList.add('large-font');
                document.getElementById('toggle-font-size').checked = true;
            }
            
            if (darkModeEnabled) {
                document.body.classList.add('dark-mode');
                document.getElementById('site-logo').src = 'imagens/fap logo preto.png';
                document.getElementById('toggle-dark-mode').checked = true;
            }
        });
        
        // Toggle Fonte Grande
        document.getElementById('toggle-font-size').addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('large-font');
                localStorage.setItem('largeFont', 'true');
            } else {
                document.body.classList.remove('large-font');
                localStorage.setItem('largeFont', 'false');
            }
        });
        
        // Toggle Modo Dark
        document.getElementById('toggle-dark-mode').addEventListener('change', function() {
            const logo = document.getElementById('site-logo');
            if (this.checked) {
                document.body.classList.add('dark-mode');
                logo.src = 'imagens/fap logo preto.png';
                localStorage.setItem('darkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                logo.src = 'imagens/11.png';
                localStorage.setItem('darkMode', 'false');
            }
        });
    </script>
    
    <main>
