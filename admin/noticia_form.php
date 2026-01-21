<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado e se pode editar notícias
if (!Session::isEditor()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Nova Notícia';
$noticia_id = $_GET['id'] ?? null;
$noticia = null;

// Buscar categorias ativas
$db = Database::getInstance()->getConnection();
$stmt_cats = $db->query("SELECT id, nome, slug, cor FROM categorias WHERE ativa = 1 ORDER BY ordem, nome");
$categorias = $stmt_cats->fetchAll();

// Se for edição, buscar notícia
if ($noticia_id) {
    try {
        $stmt = $db->prepare("SELECT * FROM noticias WHERE id = ?");
        $stmt->execute([$noticia_id]);
        $noticia = $stmt->fetch();
        
        if (!$noticia) {
            Session::setFlash('mensagem', 'Notícia não encontrada.');
            Session::setFlash('tipo_mensagem', 'erro');
            header('Location: noticias.php');
            exit;
        }
        
        $pageTitle = 'Editar Notícia';
    } catch (PDOException $e) {
        error_log("Erro ao buscar notícia: " . $e->getMessage());
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        
        $titulo = trim($_POST['titulo'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $resumo = trim($_POST['resumo'] ?? '');
        $conteudo = $_POST['conteudo'] ?? '';
        $categoria = trim($_POST['categoria'] ?? '');
        $status = $_POST['status'] ?? 'rascunho';
        $imagem_destaque = trim($_POST['imagem_destaque'] ?? ($noticia['imagem_destaque'] ?? ''));
        
        // Validações
        if (empty($titulo)) {
            throw new Exception('O título é obrigatório.');
        }
        
        // Gerar slug se vazio
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', 
                iconv('UTF-8', 'ASCII//TRANSLIT', $titulo)
            ), '-'));
        }
        
        // Upload de imagem direto no form (opcional)
        if (isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/noticias/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $extensao = strtolower(pathinfo($_FILES['imagem_destaque']['name'], PATHINFO_EXTENSION));
            $nome_arquivo = uniqid() . '.' . $extensao;
            $caminho_completo = $upload_dir . $nome_arquivo;
            
            if (move_uploaded_file($_FILES['imagem_destaque']['tmp_name'], $caminho_completo)) {
                $imagem_destaque = 'uploads/noticias/' . $nome_arquivo;
            }
        }
        
        // Garantir campo vazio como NULL no banco
        if ($imagem_destaque === '') {
            $imagem_destaque = null;
        }
        
        if ($noticia_id) {
            // Atualizar
            $stmt = $db->prepare("
                UPDATE noticias 
                SET titulo = ?, slug = ?, resumo = ?, conteudo = ?, 
                    imagem_destaque = ?, categoria = ?, status = ?,
                    publicado_em = CASE WHEN status = 'publicado' AND publicado_em IS NULL THEN NOW() ELSE publicado_em END
                WHERE id = ?
            ");
            $stmt->execute([$titulo, $slug, $resumo, $conteudo, $imagem_destaque, $categoria, $status, $noticia_id]);
            
            Session::setFlash('mensagem', 'Notícia atualizada com sucesso!');
        } else {
            // Criar nova
            $stmt = $db->prepare("
                INSERT INTO noticias (titulo, slug, resumo, conteudo, imagem_destaque, categoria, status, autor_id, publicado_em)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CASE WHEN ? = 'publicado' THEN NOW() ELSE NULL END)
            ");
            $stmt->execute([$titulo, $slug, $resumo, $conteudo, $imagem_destaque, $categoria, $status, Session::getUserId(), $status]);
            
            Session::setFlash('mensagem', 'Notícia criada com sucesso!');
        }
        
        Session::setFlash('tipo_mensagem', 'sucesso');
        header('Location: noticias.php');
        exit;
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    } catch (PDOException $e) {
        error_log("Erro ao salvar notícia: " . $e->getMessage());
        $erro = 'Erro ao salvar notícia.';
    }
}
?>

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
</head>
<body class="font-sans bg-gray-100">

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800"><?= $noticia_id ? 'Editar' : 'Nova' ?> Notícia</h1>
            <p class="text-gray-600 mt-2">Crie e publique conteúdo para o site</p>
        </div>
        <a href="noticias.php" class="text-gray-600 hover:text-gray-800">
            ← Voltar para lista
        </a>
    </div>

    <?php if (isset($erro)): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Coluna Principal (Editor) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Assistente de IA -->
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-6 border-2 border-purple-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-purple-500 text-white p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 7H7v6h6V7z"/>
                                <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Assistente de IA</h3>
                            <p class="text-sm text-gray-600">Gere conteúdo automaticamente com inteligência artificial</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <textarea 
                            id="ia-prompt" 
                            rows="3" 
                            class="w-full px-4 py-3 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Ex: Escreva uma notícia sobre a aposentadoria dos servidores públicos em Pádua, destacando os benefícios do regime próprio..."
                        ></textarea>
                        
                        <div class="flex gap-2">
                            <button 
                                type="button" 
                                onclick="gerarConteudoIA()" 
                                id="btn-gerar-ia"
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-all font-medium shadow-lg flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                                Gerar com IA
                            </button>
                            <button 
                                type="button" 
                                onclick="gerarTituloIA()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all font-medium"
                            >
                                Gerar Título
                            </button>
                            <button 
                                type="button" 
                                onclick="gerarResumoIA()" 
                                class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition-all font-medium"
                            >
                                Gerar Resumo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Título -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Título da Notícia *
                    </label>
                    <input 
                        type="text" 
                        name="titulo" 
                        id="titulo"
                        value="<?= htmlspecialchars($noticia['titulo'] ?? '') ?>"
                        required
                        class="w-full px-4 py-3 text-xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                        placeholder="Digite o título da notícia..."
                        onkeyup="gerarSlug()"
                    >
                </div>

                <!-- Slug -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        URL Amigável (Slug)
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 text-sm">padua.fap.rj.gov.br/noticia/</span>
                        <input 
                            type="text" 
                            name="slug" 
                            id="slug"
                            value="<?= htmlspecialchars($noticia['slug'] ?? '') ?>"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                            placeholder="titulo-da-noticia"
                        >
                    </div>
                </div>

                <!-- Resumo -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Resumo / Chamada
                    </label>
                    <textarea 
                        name="resumo" 
                        id="resumo"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent"
                        placeholder="Breve resumo que aparecerá na listagem de notícias..."
                    ><?= htmlspecialchars($noticia['resumo'] ?? '') ?></textarea>
                </div>

                <!-- Editor de Conteúdo -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Conteúdo *
                    </label>
                    <div id="editor" style="min-height: 500px;"><?= $noticia['conteudo'] ?? '' ?></div>
                    <textarea name="conteudo" id="conteudo-hidden" style="display:none;"></textarea>
                </div>
            </div>

            <!-- Sidebar (Publicação e Metadados) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Publicar -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Publicação</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select 
                            name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                        >
                            <option value="rascunho" <?= ($noticia['status'] ?? '') === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                            <option value="publicado" <?= ($noticia['status'] ?? '') === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                            <option value="arquivado" <?= ($noticia['status'] ?? '') === 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button 
                            type="submit" 
                            class="flex-1 bg-green-primary text-white px-4 py-2 rounded-lg hover:bg-green-dark transition-all font-medium"
                        >
                            <?= $noticia_id ? 'Atualizar' : 'Publicar' ?>
                        </button>
                        <a 
                            href="noticias.php" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                        >
                            Cancelar
                        </a>
                    </div>
                </div>

                <!-- Categoria -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800">Categoria</h3>
                        <?php if (Session::isAdmin()): ?>
                            <a href="categorias.php" target="_blank" class="text-xs text-blue-600 hover:text-blue-800">
                                + Gerenciar categorias
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($categorias)): ?>
                        <div class="text-sm text-gray-500 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            ⚠️ Nenhuma categoria disponível. 
                            <?php if (Session::isAdmin()): ?>
                                <a href="categorias.php" class="text-blue-600 hover:underline">Criar categorias</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <select 
                            name="categoria" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                        >
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option 
                                    value="<?= htmlspecialchars($cat['slug']) ?>"
                                    <?= ($noticia['categoria'] ?? '') === $cat['slug'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Imagem Destaque -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Imagem de Destaque</h3>
                    
                    <!-- Imagem Selecionada -->
                    <div id="imagem-preview" class="mb-4">
                        <?php if (!empty($noticia['imagem_destaque'])): ?>
                            <img src="../<?= htmlspecialchars($noticia['imagem_destaque']) ?>" alt="" class="w-full rounded-lg border-2 border-green-500">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300">
                                <p class="text-gray-400">Nenhuma imagem selecionada</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="imagem_destaque" id="imagem_destaque_hidden" value="<?= htmlspecialchars($noticia['imagem_destaque'] ?? '') ?>">
                    
                    <!-- Botões de Ação -->
                    <div class="space-y-2">
                        <button 
                            type="button" 
                            onclick="abrirGaleriaImagens()"
                            class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-all font-medium flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                            Galeria de Imagens
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="gerarImagemIA()"
                            class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-pink-600 hover:to-purple-700 transition-all font-medium flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 7H7v6h6V7z"/>
                                <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/>
                            </svg>
                            Gerar Imagem com IA
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="document.getElementById('upload-manual').click()"
                            class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-all font-medium flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Fazer Upload
                        </button>
                        <input 
                            type="file" 
                            id="upload-manual"
                            accept="image/*"
                            multiple
                            class="hidden"
                            onchange="fazerUploadImagens(this.files)"
                        >
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-3 text-center">JPG, PNG ou WebP. Máx 5MB por imagem</p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Galeria de Imagens -->
<div id="modal-galeria" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Galeria de Imagens</h2>
            <button onclick="fecharGaleriaImagens()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
            <div id="galeria-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Imagens serão inseridas aqui via JavaScript -->
            </div>
            
            <div id="galeria-vazia" class="hidden text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
                <p class="text-gray-500 font-medium">Nenhuma imagem na galeria</p>
                <p class="text-gray-400 text-sm mt-2">Faça upload ou gere imagens com IA</p>
            </div>
        </div>
        
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            <button onclick="fecharGaleriaImagens()" class="w-full bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-all font-medium">
                Fechar
            </button>
        </div>
    </div>
</div>


<!-- CKEditor 5 (Gratuito e Open-Source) -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
<script>
let editorInstance;

// Inicializar CKEditor 5
ClassicEditor
    .create(document.querySelector('#editor'), {
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'link', '|',
                'bulletedList', 'numberedList', '|',
                'alignment', '|',
                'blockQuote', 'insertTable', '|',
                'undo', 'redo'
            ]
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Parágrafo', class: 'ck-heading_paragraph' },
                { model: 'heading2', view: 'h2', title: 'Título 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Título 3', class: 'ck-heading_heading3' }
            ]
        },
        language: 'pt-br'
    })
    .then(editor => {
        editorInstance = editor;
        
        // Sincronizar conteúdo do editor com textarea hidden antes de submeter
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            document.getElementById('conteudo-hidden').value = editor.getData();
        });
    })
    .catch(error => {
        console.error('Erro ao carregar CKEditor:', error);
    });

// Gerar slug automaticamente
function gerarSlug() {
    const titulo = document.getElementById('titulo').value;
    const slug = titulo.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Remove acentos
        .replace(/[^a-z0-9\s-]/g, '') // Remove caracteres especiais
        .replace(/\s+/g, '-') // Espaços para hífens
        .replace(/-+/g, '-') // Remove hífens duplos
        .replace(/^-|-$/g, ''); // Remove hífens no início/fim
    document.getElementById('slug').value = slug;
}

// Função para gerar conteúdo com IA (AUTOMÁTICO: Título + Conteúdo + Resumo)
async function gerarConteudoIA() {
    const prompt = document.getElementById('ia-prompt').value;
    if (!prompt.trim()) {
        alert('Por favor, descreva o que você quer que a IA escreva.');
        return;
    }

    const btn = document.getElementById('btn-gerar-ia');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Gerando título...';

    try {
        // 1. Gerar título
        const tituloResp = await fetch('api_ia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt, tipo: 'titulo' })
        });
        const tituloData = await tituloResp.json();
        
        if (tituloData.sucesso) {
            document.getElementById('titulo').value = tituloData.conteudo;
            gerarSlug();
        }

        // 2. Gerar conteúdo
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Gerando conteúdo...';
        
        const conteudoResp = await fetch('api_ia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt, tipo: 'conteudo' })
        });
        const conteudoData = await conteudoResp.json();
        
        if (conteudoData.sucesso) {
            if (editorInstance) {
                editorInstance.setData(conteudoData.conteudo);
            }
            
            // 3. Gerar resumo automaticamente
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Gerando resumo...';
            
            const resumoResp = await fetch('api_ia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: conteudoData.conteudo, tipo: 'resumo' })
            });
            const resumoData = await resumoResp.json();
            
            if (resumoData.sucesso) {
                document.getElementById('resumo').value = resumoData.conteudo;
            }
            
            alert('✅ Notícia completa gerada com sucesso!\n\n✓ Título\n✓ Conteúdo\n✓ Resumo');
        } else {
            alert('Erro: ' + conteudoData.erro);
        }
    } catch (error) {
        alert('Erro ao gerar conteúdo: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg> Gerar com IA';
    }
}

// Gerar título com IA
async function gerarTituloIA() {
    const prompt = document.getElementById('ia-prompt').value;
    if (!prompt.trim()) {
        alert('Por favor, descreva o tema da notícia.');
        return;
    }

    try {
        const response = await fetch('api_ia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt, tipo: 'titulo' })
        });

        const data = await response.json();
        
        if (data.sucesso) {
            document.getElementById('titulo').value = data.conteudo;
            gerarSlug();
        } else {
            alert('Erro: ' + data.erro);
        }
    } catch (error) {
        alert('Erro ao gerar título: ' + error.message);
    }
}

// Gerar resumo com IA
async function gerarResumoIA() {
    if (!editorInstance) {
        alert('Editor ainda não carregado.');
        return;
    }
    
    const conteudo = editorInstance.getData().replace(/<[^>]*>/g, ' ').trim();
    if (!conteudo.trim()) {
        alert('Por favor, escreva o conteúdo primeiro ou use o assistente de IA.');
        return;
    }

    try {
        const response = await fetch('api_ia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: conteudo, tipo: 'resumo' })
        });

        const data = await response.json();
        
        if (data.sucesso) {
            document.getElementById('resumo').value = data.conteudo;
        } else {
            alert('Erro: ' + data.erro);
        }
    } catch (error) {
        alert('Erro ao gerar resumo: ' + error.message);
    }
}

// ============= GALERIA DE IMAGENS =============

// Armazenar imagens carregadas
let imagensGaleria = [];

// Abrir modal da galeria
function abrirGaleriaImagens() {
    document.getElementById('modal-galeria').classList.remove('hidden');
    carregarImagensGaleria();
}

// Fechar modal da galeria
function fecharGaleriaImagens() {
    document.getElementById('modal-galeria').classList.add('hidden');
}

// Carregar imagens existentes na galeria
async function carregarImagensGaleria() {
    try {
        const response = await fetch('/admin/api_galeria.php?acao=listar', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.sucesso && data.imagens.length > 0) {
            imagensGaleria = data.imagens;
            renderizarGaleria();
        } else {
            document.getElementById('galeria-grid').innerHTML = '';
            document.getElementById('galeria-vazia').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Erro ao carregar galeria:', error);
    }
}

// Renderizar galeria
function renderizarGaleria() {
    const grid = document.getElementById('galeria-grid');
    const imagemAtual = document.getElementById('imagem_destaque_hidden').value;
    
    grid.innerHTML = imagensGaleria.map(img => `
        <div class="relative group cursor-pointer" onclick="selecionarImagem('${img.caminho}')">
            <img src="../${img.caminho}" alt="" class="w-full h-40 object-cover rounded-lg border-2 ${imagemAtual === img.caminho ? 'border-green-500' : 'border-gray-200'} group-hover:border-green-400 transition-all">
            ${imagemAtual === img.caminho ? `
                <div class="absolute top-2 right-2 bg-green-500 text-white rounded-full p-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            ` : ''}
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center">
                <span class="text-white opacity-0 group-hover:opacity-100 font-medium">Selecionar</span>
            </div>
            ${img.ia_gerada ? `
                <div class="absolute bottom-2 left-2 bg-purple-600 text-white text-xs px-2 py-1 rounded">
                    <span>🤖 IA</span>
                </div>
            ` : ''}
        </div>
    `).join('');
    
    document.getElementById('galeria-vazia').classList.add('hidden');
}

// Selecionar imagem
function selecionarImagem(caminho) {
    document.getElementById('imagem_destaque_hidden').value = caminho;
    document.getElementById('imagem-preview').innerHTML = `
        <img src="../${caminho}" alt="" class="w-full rounded-lg border-2 border-green-500">
        <p class="text-sm text-green-600 font-medium mt-2">✓ Imagem selecionada</p>
    `;
    
    fecharGaleriaImagens();
}

// Upload manual de imagens
async function fazerUploadImagens(files) {
    console.log('fazerUploadImagens chamada com:', files.length, 'arquivos');
    
    if (files.length === 0) {
        console.log('Nenhum arquivo selecionado');
        return;
    }
    
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        console.log('Adicionando arquivo:', files[i].name, files[i].size, 'bytes');
        formData.append('imagens[]', files[i]);
    }
    
    // Mostrar loading
    const grid = document.getElementById('galeria-grid');
    grid.innerHTML = '<div class="col-span-4 text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div><p class="mt-2 text-gray-600">Enviando imagens...</p></div>';
    
    // Abrir galeria se estiver fechada
    document.getElementById('modal-galeria').classList.remove('hidden');
    
    try {
        console.log('Enviando para API...');
        const response = await fetch('/admin/api_galeria.php?acao=upload', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        
        console.log('Resposta status:', response.status);
        const data = await response.json();
        console.log('Resposta JSON:', data);
        
        if (data.sucesso) {
            alert(`✅ ${data.quantidade} imagem(ns) enviada(s) com sucesso!`);
            await carregarImagensGaleria();
            
            // Se for apenas uma imagem, selecionar automaticamente
            if (data.imagens.length === 1) {
                selecionarImagem(data.imagens[0].caminho);
            }
        } else {
            alert('Erro: ' + data.erro);
            carregarImagensGaleria();
        }
    } catch (error) {
        console.error('Erro no upload:', error);
        alert('Erro ao fazer upload: ' + error.message);
        carregarImagensGaleria();
    }
    
    // Limpar input
    document.getElementById('upload-manual').value = '';
}

// Gerar imagem com IA (DALL-E)
async function gerarImagemIA() {
    const titulo = document.getElementById('titulo').value;
    const prompt = document.getElementById('ia-prompt').value;
    
    const descricao = prompt.trim() || titulo.trim() || 'Imagem institucional para notícia de órgão público';
    
    if (!descricao) {
        alert('Por favor, preencha o título ou a descrição da notícia primeiro.');
        return;
    }
    
    const confirmacao = confirm(`🤖 Gerar imagem com IA?\n\nDescrição: ${descricao.substring(0, 100)}...\n\nIsso irá consumir créditos da API de IA. Deseja continuar?`);
    
    if (!confirmacao) return;
    
    // Mostrar loading
    const grid = document.getElementById('galeria-grid');
    grid.innerHTML = '<div class="col-span-4 text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div><p class="mt-2 text-gray-600">Gerando imagem com IA...</p></div>';
    
    // Abrir galeria se estiver fechada
    document.getElementById('modal-galeria').classList.remove('hidden');
    
    try {
        const response = await fetch('/admin/api_galeria.php?acao=gerar_ia', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ descricao }),
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            alert('✅ Imagem gerada com IA com sucesso!');
            await carregarImagensGaleria();
            selecionarImagem(data.imagem.caminho);
        } else {
            alert('Erro: ' + data.erro);
            carregarImagensGaleria();
        }
    } catch (error) {
        alert('Erro ao gerar imagem: ' + error.message);
        carregarImagensGaleria();
    }
}
</script>


</body>
</html>
