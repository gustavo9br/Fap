# TEMPLATE PADRÃO PARA NOVAS PÁGINAS - FAP PADUA

Este documento define o padrão visual e estrutural que TODAS as páginas do site devem seguir.

## 🎨 ESTRUTURA COMPLETA DO TEMPLATE

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[NOME DA PÁGINA] - FAP PADUA</title>
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
                        <li class="font-semibold">[NOME DA PÁGINA EM MAIÚSCULAS]</li>
                    </ol>
                </nav>

                <!-- Título -->
                <h1 class="text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4">[TÍTULO DA PÁGINA]</h1>
            </div>
        </div>
    </div>
</section>

<!-- Conteúdo -->
<section class="py-6 bg-gray-100">
    <div class="container mx-auto px-6 space-y-8">
        
        <!-- Aqui vai o conteúdo específico da página -->
        <div class="bg-white rounded-2xl shadow-md p-8">
            <h2 class="text-xl md:text-2xl font-bold mb-6" style="color: #B8621B;">
                Título da Seção
            </h2>
            <p class="text-gray-700 leading-relaxed">
                Conteúdo aqui...
            </p>
        </div>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
```

## 📋 ESPECIFICAÇÕES OBRIGATÓRIAS

### 1. BANNER AZUL (TOPO)
- **Container externo**: `<section class="py-6">`
- **Container interno**: `<div class="max-w-7xl mx-auto px-4">`
- **Banner**: `<div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-3xl p-10 shadow-xl relative overflow-hidden">`
- **Círculos decorativos**: Sempre 2 círculos brancos com opacity-10
  - Um no canto superior direito (w-96 h-96)
  - Um no canto inferior esquerdo (w-96 h-96)

### 2. BREADCRUMB
- Estrutura: `<nav class="mb-6">` dentro de `<ol class="flex items-center gap-2 text-white text-sm">`
- Primeiro item: Link para "/" (home) com ícone de casa
- Classe do link: `hover:underline flex items-center gap-1`
- Separador: `<li class="text-white/70">›</li>`
- Último item: Nome da página em MAIÚSCULAS com `class="font-semibold"`

### 3. TÍTULO
- Tag: `<h1>`
- Classes: `text-white text-2xl md:text-3xl font-bold border-l-4 border-white pl-4`
- Sempre em MAIÚSCULAS

### 4. SEÇÃO DE CONTEÚDO
- Container: `<section class="py-6 bg-gray-100">`
- Container interno: `<div class="container mx-auto px-6 space-y-8">`

### 5. CARDS DE CONTEÚDO
- Classes padrão: `bg-white rounded-2xl shadow-md p-8`
- Títulos dentro dos cards: `style="color: #B8621B;"` (cor laranja/marrom)
- Espaçamento entre cards: usar `space-y-8` no container pai

### 6. CORES PADRÃO
- **Verde primário**: `#00A859` (green-primary)
- **Azul primário**: `#1e3a8a` (blue-primary)
- **Azul do banner**: `from-blue-600 to-blue-800`
- **Títulos dentro de cards**: `#B8621B` (laranja/marrom)
- **Fundo da página**: `bg-gray-100`
- **Cards**: `bg-white`

### 7. RESPONSIVIDADE
- Usar classes `md:` para breakpoints médios
- Usar classes `lg:` para breakpoints grandes
- Padding: `px-6` em mobile, `px-4` no container max-w-7xl
- Grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` (exemplo)

### 8. TIPOGRAFIA
- Fonte: Roboto (já configurada no header global)
- Títulos H1: `text-2xl md:text-3xl font-bold`
- Títulos H2: `text-xl md:text-2xl font-bold`
- Texto normal: `text-gray-700 leading-relaxed`

## 📦 COMPONENTES COMUNS

### Card com Ícone
```html
<div class="bg-white rounded-2xl shadow-md p-8 hover:shadow-lg transition-shadow">
    <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-2xl mb-6">
        <!-- Ícone SVG aqui -->
    </div>
    <h2 class="text-xl font-bold mb-3 text-gray-800">Título</h2>
    <p class="text-gray-600 mb-6 leading-relaxed">Descrição</p>
    <a href="#" class="inline-block bg-green-primary text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition-colors">
        Botão
    </a>
</div>
```

### Grid de Cards 3 Colunas
```html
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Cards aqui -->
</div>
```

### Botão Verde Padrão
```html
<a href="#" class="inline-block bg-green-primary text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition-colors">
    Texto do Botão
</a>
```

## ✅ CHECKLIST DE VALIDAÇÃO

Ao criar uma nova página, verifique:

- [ ] Banner azul com altura padrão (py-6 no section, p-10 no banner)
- [ ] Círculos decorativos de fundo presentes
- [ ] Breadcrumb com link clicável para home e hover:underline
- [ ] Título com border-left branco
- [ ] Fundo cinza (bg-gray-100) na seção de conteúdo
- [ ] Cards brancos com rounded-2xl e shadow-md
- [ ] Títulos dentro de cards com cor #B8621B
- [ ] Footer incluído no final
- [ ] Tailwind CDN configurado no head
- [ ] Cores green-primary e blue-primary configuradas
- [ ] Responsivo (classes md: e lg:)

## 🎯 EXEMPLOS DE REFERÊNCIA

As seguintes páginas seguem este padrão perfeitamente:
- `progestao.php`
- `sobre.php`
- `servicos.php`
- `conselhos-e-comites.php`
- `contato.php`
- `noticias.php`

**IMPORTANTE**: Sempre que criar uma nova página, siga EXATAMENTE este template. A consistência visual é fundamental para a experiência do usuário.
