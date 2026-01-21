<?php
require_once 'config/database.php';

$pdo = Database::getInstance()->getConnection();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre - FAP Pádua</title>
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
                        <li class="font-semibold">SOBRE</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">SOBRE O INSTITUTO</h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Quem Somos -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 class="text-xl md:text-2xl font-bold mb-6" style="color: #B8621B;">
                Quem Somos
            </h2>
            
            <div class="prose max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">
                    O Instituto de Previdência é uma autarquia responsável pela gestão do regime próprio de previdência social dos servidores públicos municipais. Nossa missão é garantir a segurança previdenciária e proporcionar qualidade de vida aos segurados e seus dependentes.
                </p>
            </div>
        </div>

        <!-- Missão, Visão e Valores -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-md p-8">
                <div class="text-4xl mb-4">🎯</div>
                <h3 class="text-lg font-bold mb-3" style="color: #B8621B;">Missão</h3>
                <p class="text-gray-700 leading-relaxed">
                    Garantir a sustentabilidade do sistema previdenciário, oferecendo benefícios com qualidade e transparência aos servidores públicos e seus dependentes.
                </p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-md p-8">
                <div class="text-4xl mb-4">👁️</div>
                <h3 class="text-lg font-bold mb-3" style="color: #B8621B;">Visão</h3>
                <p class="text-gray-700 leading-relaxed">
                    Ser referência em gestão previdenciária, reconhecida pela excelência no atendimento e pela sustentabilidade do sistema.
                </p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-md p-8">
                <div class="text-4xl mb-4">⭐</div>
                <h3 class="text-lg font-bold mb-3" style="color: #B8621B;">Valores</h3>
                <p class="text-gray-700 leading-relaxed">
                    Transparência, ética, compromisso com o servidor, sustentabilidade, inovação e excelência no atendimento.
                </p>
            </div>
        </div>

        <!-- Nossa História -->
        <div class="bg-white rounded-2xl shadow-md p-8">
            <h2 class="text-xl md:text-2xl font-bold mb-6" style="color: #B8621B;">
                Nossa História
            </h2>
            
            <div class="space-y-6">
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-600 text-white font-bold px-4 py-2 rounded-lg">2000</div>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Fundação</h4>
                        <p class="text-gray-700 leading-relaxed">
                            Criação do Instituto através da Lei Municipal, marcando o início da previdência própria.
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-600 text-white font-bold px-4 py-2 rounded-lg">2010</div>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Modernização</h4>
                        <p class="text-gray-700 leading-relaxed">Implementação de sistemas digitais para melhor atendimento aos segurados.</p>
                    </div>
                </div>
                
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-600 text-white font-bold px-4 py-2 rounded-lg">2020</div>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Certificação Pró-Gestão</h4>
                        <p class="text-gray-700 leading-relaxed">Conquista da certificação do Programa de Modernização da Gestão Fiscal.</p>
                    </div>
                </div>
                
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-600 text-white font-bold px-4 py-2 rounded-lg">2025</div>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Transformação Digital</h4>
                        <p class="text-gray-700 leading-relaxed">Lançamento do novo portal com serviços 100% online.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Números do Instituto -->
        <div class="bg-white rounded-2xl shadow-md p-8">
            <h2 class="text-xl md:text-2xl font-bold mb-8 text-center" style="color: #B8621B;">
                Números do Instituto
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-8 rounded-2xl text-center shadow-md">
                    <div class="text-4xl md:text-5xl font-bold mb-2">5.000+</div>
                    <div class="text-sm md:text-base opacity-90">Segurados Ativos</div>
                </div>
                
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-8 rounded-2xl text-center shadow-md">
                    <div class="text-4xl md:text-5xl font-bold mb-2">2.000+</div>
                    <div class="text-sm md:text-base opacity-90">Aposentados</div>
                </div>
                
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-8 rounded-2xl text-center shadow-md">
                    <div class="text-4xl md:text-5xl font-bold mb-2">500+</div>
                    <div class="text-sm md:text-base opacity-90">Pensionistas</div>
                </div>
                
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-8 rounded-2xl text-center shadow-md">
                    <div class="text-4xl md:text-5xl font-bold mb-2">25</div>
                    <div class="text-sm md:text-base opacity-90">Anos de História</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
