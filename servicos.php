<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - FAP PADUA</title>
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
                        <li class="font-semibold">SERVIÇOS</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">SERVIÇOS DISPONÍVEIS</h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="container mx-auto px-6 space-y-8">
        
        <!-- Introdução -->
        <div class="bg-white rounded-2xl shadow-md p-8">
            <p class="text-gray-700 text-lg leading-relaxed">
                Conheça os serviços disponíveis para os servidores públicos municipais. Oferecemos diversas facilidades para garantir seus direitos previdenciários.
            </p>
        </div>
        
        <!-- Grid de Serviços -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Cadastro de Segurados -->
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <div class="text-6xl mb-4">👤</div>
                <h3 class="text-xl font-bold mb-3" style="color: #B8621B;">Cadastro de Segurados</h3>
                <p class="text-gray-700 mb-6 leading-relaxed">Realize o cadastro inicial no sistema de previdência.</p>
                <a href="#" class="inline-block bg-green-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar Serviço →
                </a>
            </div>
            
            <!-- Solicitação de Benefícios -->
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <div class="text-6xl mb-4">📄</div>
                <h3 class="text-xl font-bold mb-3" style="color: #B8621B;">Solicitação de Benefícios</h3>
                <p class="text-gray-700 mb-6 leading-relaxed">Solicite aposentadoria, pensão e outros benefícios.</p>
                <a href="#" class="inline-block bg-green-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar Serviço →
                </a>
            </div>
            
            <!-- Consulta de Contribuições -->
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <div class="text-6xl mb-4">💰</div>
                <h3 class="text-xl font-bold mb-3" style="color: #B8621B;">Consulta de Contribuições</h3>
                <p class="text-gray-700 mb-6 leading-relaxed">Consulte suas contribuições previdenciárias.</p>
                <a href="#" class="inline-block bg-green-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar Serviço →
                </a>
            </div>
            
            <!-- Certidões -->
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <div class="text-6xl mb-4">📋</div>
                <h3 class="text-xl font-bold mb-3" style="color: #B8621B;">Certidões</h3>
                <p class="text-gray-700 mb-6 leading-relaxed">Emita certidões de tempo de contribuição e outras.</p>
                <a href="#" class="inline-block bg-green-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar Serviço →
                </a>
            </div>
            
            <!-- Recadastramento -->
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <div class="text-6xl mb-4">🔄</div>
                <h3 class="text-xl font-bold mb-3" style="color: #B8621B;">Recadastramento</h3>
                <p class="text-gray-700 mb-6 leading-relaxed">Atualize seus dados cadastrais.</p>
                <a href="#" class="inline-block bg-green-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar Serviço →
                </a>
            </div>
            
            <!-- Simulação de Aposentadoria -->
            <div class="bg-white rounded-2xl shadow-md p-8 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-2">
                <div class="text-6xl mb-4">📊</div>
                <h3 class="text-xl font-bold mb-3" style="color: #B8621B;">Simulação de Aposentadoria</h3>
                <p class="text-gray-700 mb-6 leading-relaxed">Simule sua aposentadoria e planeje seu futuro.</p>
                <a href="#" class="inline-block bg-green-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar Serviço →
                </a>
            </div>
        </div>
        
        <!-- Como Utilizar os Serviços -->
        <div class="bg-white rounded-2xl shadow-md p-8">
            <h2 class="text-xl md:text-2xl font-bold mb-6" style="color: #B8621B;">
                Como Utilizar os Serviços
            </h2>
            
            <ol class="space-y-4 text-gray-700 leading-relaxed">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">1</span>
                    <span class="pt-1">Escolha o serviço desejado acima</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">2</span>
                    <span class="pt-1">Tenha em mãos os documentos necessários</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">3</span>
                    <span class="pt-1">Preencha o formulário online ou agende atendimento presencial</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">4</span>
                    <span class="pt-1">Acompanhe o andamento através do número de protocolo</span>
                </li>
            </ol>
        </div>
        
        <!-- Documentos Necessários -->
        <div class="bg-white rounded-2xl shadow-md p-8">
            <h2 class="text-xl md:text-2xl font-bold mb-6" style="color: #B8621B;">
                Documentos Geralmente Necessários
            </h2>
            
            <ul class="space-y-3 text-gray-700 leading-relaxed">
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>RG e CPF</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Comprovante de residência</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Carteira de trabalho (se houver)</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-primary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Documentos específicos conforme o serviço solicitado</span>
                </li>
            </ul>
        </div>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
