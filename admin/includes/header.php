<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - Painel FAP Pádua</title>
    <link rel="icon" type="image/png" href="/imagens/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'green-primary': '#2ecc71',
                        'green-dark': '#27ae60',
                        'teal-primary': '#16a085',
                        'teal-dark': '#0e6655',
                    },
                    fontFamily: {
                        'sans': ['Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
        }
        .sidebar-collapsed {
            width: 80px;
        }
        .sidebar-expanded {
            width: 280px;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar-open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="font-sans bg-gray-100">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar sidebar-expanded fixed top-0 left-0 h-screen bg-white shadow-xl z-40 flex flex-col">
        <!-- Logo -->
        <div class="p-4 border-b flex items-center justify-between">
            <div class="flex items-center gap-3 overflow-hidden">
                <img src="../imagens/fap logo preto.png" alt="FAP" class="h-10 flex-shrink-0">
                <div class="sidebar-text">
                    <h1 class="text-sm font-bold text-gray-800 whitespace-nowrap">Painel Admin</h1>
                    <p class="text-xs text-gray-500 whitespace-nowrap">FAP Pádua</p>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <nav class="flex-1 overflow-y-auto py-4">
            <a href="index" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="sidebar-text font-medium">Dashboard</span>
            </a>

            <a href="noticias" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <span class="sidebar-text font-medium">Notícias</span>
            </a>

            <a href="convenios" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="sidebar-text font-medium">Convênios</span>
            </a>

            <a href="demonstrativos_index.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="sidebar-text font-medium">Demonstrativos</span>
            </a>

            <a href="balancetes_index.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="sidebar-text font-medium">Balancetes</span>
            </a>

            <a href="conselhos" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="sidebar-text font-medium">Conselhos</span>
            </a>

            <a href="financeiro" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="sidebar-text font-medium">Financeiro</span>
            </a>

            <a href="progestao" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="sidebar-text font-medium">Pró-Gestão</span>
            </a>

            <a href="acesso-rapido" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors group">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="sidebar-text font-medium">Acesso Rápido</span>
            </a>

            <?php if (Session::isAdmin()): ?>
                <div class="px-4 py-2 mt-4">
                    <p class="sidebar-text text-xs font-semibold text-gray-400 uppercase tracking-wider">Administração</p>
                </div>

                <a href="arquivos" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="sidebar-text font-medium">Arquivos</span>
                </a>

                <a href="usuarios" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="sidebar-text font-medium">Usuários</span>
                </a>

                <a href="configuracoes" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-primary transition-colors group">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="sidebar-text font-medium">Configurações</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- User Info & Logout -->
        <div class="border-t p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-green-primary text-white flex items-center justify-center font-bold flex-shrink-0">
                    <?= strtoupper(substr(Session::getUserName(), 0, 1)) ?>
                </div>
                <div class="sidebar-text overflow-hidden">
                    <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars(Session::getUserName()) ?></p>
                    <p class="text-xs text-gray-500"><?= ucfirst(Session::getUserType()) ?></p>
                </div>
            </div>
            <a href="logout" class="flex items-center justify-center gap-2 w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="sidebar-text">Sair</span>
            </a>
        </div>

        <!-- Toggle Button (Desktop) -->
        <button onclick="toggleSidebar()" class="hidden md:flex absolute -right-3 top-20 bg-white border-2 border-gray-200 rounded-full w-6 h-6 items-center justify-center hover:bg-gray-50 transition-colors">
            <svg id="toggleIcon" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </aside>

    <!-- Mobile Menu Button -->
    <button onclick="toggleMobileSidebar()" class="md:hidden fixed top-4 left-4 z-50 bg-white p-2 rounded-lg shadow-lg">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Overlay for mobile -->
    <div id="overlay" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden" onclick="toggleMobileSidebar()"></div>

    <!-- Main Content -->
    <main id="mainContent" class="transition-all duration-300 min-h-screen pb-12 pt-4 md:pt-8 px-4 md:px-8" style="margin-left: 280px;">

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleIcon = document.getElementById('toggleIcon');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            
            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                mainContent.style.marginLeft = '80px';
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>';
                sidebarTexts.forEach(text => text.style.display = 'none');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                mainContent.style.marginLeft = '280px';
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>';
                sidebarTexts.forEach(text => text.style.display = 'block');
            }
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('hidden');
        }

        // Close mobile menu when clicking a link
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    toggleMobileSidebar();
                }
            });
        });

        // Adjust main content margin on load for mobile
        window.addEventListener('load', () => {
            const mainContent = document.getElementById('mainContent');
            if (window.innerWidth < 768) {
                mainContent.style.marginLeft = '0';
            }
        });

        window.addEventListener('resize', () => {
            const mainContent = document.getElementById('mainContent');
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 768) {
                mainContent.style.marginLeft = '0';
            } else {
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    mainContent.style.marginLeft = '80px';
                } else {
                    mainContent.style.marginLeft = '280px';
                }
            }
        });
    </script>

