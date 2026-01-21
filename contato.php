<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fale Conosco - FAP PADUA</title>
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
                        <li class="font-semibold">FALE CONOSCO</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">FALE CONOSCO</h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="container mx-auto px-6 space-y-8">
        
        <!-- Grid de 3 Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Card 1: Ouvidoria e Atendimento -->
            <div class="bg-white rounded-2xl shadow-md p-8 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-2xl mb-6">
                    <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                
                <h2 class="text-xl font-bold mb-3 text-gray-800">Ouvidoria e Atendimento</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    A ponte entre você e FAP PADUA. Aqui você pode apresentar suas solicitações, sugestões, elogios ou reclamações.
                </p>
                
                <a href="#formulario" class="inline-block bg-green-primary text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar
                </a>
            </div>
            
            <!-- Card 2: Perguntas Frequentes -->
            <div class="bg-white rounded-2xl shadow-md p-8 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-2xl mb-6">
                    <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                
                <h2 class="text-xl font-bold mb-3 text-gray-800">Perguntas Frequentes</h2>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Encontre aqui a lista das perguntas e dúvidas mais frequentes enviadas pelos cidadãos em relação à previdência.
                </p>
                
                <a href="#faq" class="inline-block bg-green-primary text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Acessar
                </a>
            </div>
            
            <!-- Card 3: Redes Sociais -->
            <div class="bg-white rounded-2xl shadow-md p-8 hover:shadow-lg transition-shadow">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Siga FAP PADUA nas redes sociais</h2>
                
                <div class="space-y-3">
                    <a href="https://www.facebook.com/" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-green-50 hover:text-green-primary transition-all group">
                        <div class="w-10 h-10 bg-green-primary group-hover:bg-green-600 rounded-lg flex items-center justify-center text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </div>
                        <span class="font-medium text-gray-700 group-hover:text-green-primary">Facebook</span>
                    </a>
                    
                    <a href="https://www.instagram.com/fappadua/" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-green-50 hover:text-green-primary transition-all group">
                        <div class="w-10 h-10 bg-green-primary group-hover:bg-green-600 rounded-lg flex items-center justify-center text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </div>
                        <span class="font-medium text-gray-700 group-hover:text-green-primary">Instagram</span>
                    </a>
                    
                    <a href="https://twitter.com/" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-green-50 hover:text-green-primary transition-all group">
                        <div class="w-10 h-10 bg-green-primary group-hover:bg-green-600 rounded-lg flex items-center justify-center text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </div>
                        <span class="font-medium text-gray-700 group-hover:text-green-primary">Twitter</span>
                    </a>
                </div>
            </div>
            
        </div>
        
        <!-- Mapa -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="h-96 md:h-[500px] w-full">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3698.8166778989635!2d-42.18394442498974!3d-21.956299580285774!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x978b8e8e8e8e8e8f%3A0x1234567890abcdef!2sR.%20Pref.%20Eug%C3%AAnio%20Leite%20Lima%2C%2082%20-%20Centro%2C%20Santo%20Ant%C3%B4nio%20de%20P%C3%A1dua%20-%20RJ%2C%2028470-000!5e0!3m2!1spt-BR!2sbr!4v1234567890123!5m2!1spt-BR!2sbr" 
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    class="w-full h-full"
                ></iframe>
            </div>
        </div>
        
        <!-- Informações de Contato -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="text-4xl mb-3">📞</div>
                <h3 class="font-bold text-gray-800 mb-2">Telefone</h3>
                <p class="text-gray-600">(22) 3851-0077</p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="text-4xl mb-3">✉️</div>
                <h3 class="font-bold text-gray-800 mb-2">E-mail</h3>
                <p class="text-gray-600 text-sm">fap@santoantoniodepadua.rj.gov.br</p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="text-4xl mb-3">📍</div>
                <h3 class="font-bold text-gray-800 mb-2">Endereço</h3>
                <p class="text-gray-600 text-sm">Rua Prefeito Eugênio Leite Lima, Nº 82<br>Centro - Santo Antônio de Pádua / RJ</p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="text-4xl mb-3">🕐</div>
                <h3 class="font-bold text-gray-800 mb-2">Horário</h3>
                <p class="text-gray-600">Segunda a Sexta<br>08:00 às 17:00</p>
            </div>
        </div>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
