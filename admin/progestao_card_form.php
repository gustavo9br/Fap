<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$secao_id = isset($_GET['secao_id']) ? (int)$_GET['secao_id'] : 0;
$card = null;
$erros = [];

// Se está editando, buscar dados do card ANTES do POST
if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    try {
        $stmt = $db->prepare("SELECT * FROM progestao_cards WHERE id = ?");
        $stmt->execute([$id]);
        $card = $stmt->fetch();
        
        if (!$card) {
            header('Location: progestao.php');
            exit;
        }
        
        $secao_id = $card['secao_id'];
    } catch (PDOException $e) {
        error_log("Erro ao buscar card: " . $e->getMessage());
        header('Location: progestao.php');
        exit;
    }
}

// Processar formulário ANTES de incluir o header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $secao_id = (int)($_POST['secao_id'] ?? 0);
    $icone = trim($_POST['icone'] ?? '');
    $tipo_conteudo = $_POST['tipo_conteudo'] ?? 'link';
    $link = trim($_POST['link'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    if (empty($titulo)) {
        $erros[] = "O título é obrigatório";
    }
    
    if ($secao_id <= 0) {
        $erros[] = "Selecione uma seção";
    }
    
    // Buscar arquivo atual se estiver editando
    $arquivo_nome = null;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT arquivo FROM progestao_cards WHERE id = ?");
        $stmt->execute([$id]);
        $card_atual = $stmt->fetch();
        $arquivo_nome = $card_atual['arquivo'] ?? null;
    }
    
    if ($tipo_conteudo === 'link') {
        if (empty($link)) {
            $erros[] = "O link é obrigatório";
        } elseif (!filter_var($link, FILTER_VALIDATE_URL)) {
            $erros[] = "Link inválido";
        }
    } else {
        // Upload de arquivo
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
            
            if ($extensao !== 'pdf') {
                $erros[] = "Apenas arquivos PDF são permitidos";
            } else {
                $nome_arquivo = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['arquivo']['name']);
                $destino = '../uploads/progestao/' . $nome_arquivo;
                
                // Criar diretório se não existir
                if (!is_dir('../uploads/progestao')) {
                    mkdir('../uploads/progestao', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
                    // Remover arquivo antigo se existir
                    if ($arquivo_nome && file_exists('../uploads/progestao/' . $arquivo_nome)) {
                        unlink('../uploads/progestao/' . $arquivo_nome);
                    }
                    $arquivo_nome = $nome_arquivo;
                } else {
                    $erros[] = "Erro ao fazer upload do arquivo";
                }
            }
        } elseif (!$id) {
            $erros[] = "O arquivo PDF é obrigatório";
        }
    }
    
    if (empty($erros)) {
        try {
            if ($id > 0) {
                // Atualizar
                $stmt = $db->prepare("
                    UPDATE progestao_cards 
                    SET secao_id = ?, titulo = ?, icone = ?, tipo_conteudo = ?, link = ?, arquivo = ?, ativo = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $secao_id, 
                    $titulo, 
                    $icone, 
                    $tipo_conteudo, 
                    $tipo_conteudo === 'link' ? $link : null,
                    $tipo_conteudo === 'arquivo' ? $arquivo_nome : null,
                    $ativo, 
                    $id
                ]);
                
                $_SESSION['success_message'] = "Card atualizado com sucesso!";
            } else {
                // Buscar próxima ordem
                $stmt = $db->prepare("SELECT COALESCE(MAX(ordem), 0) + 1 as proxima_ordem FROM progestao_cards WHERE secao_id = ?");
                $stmt->execute([$secao_id]);
                $proxima_ordem = $stmt->fetch()['proxima_ordem'];
                
                // Inserir
                $stmt = $db->prepare("
                    INSERT INTO progestao_cards (secao_id, titulo, icone, tipo_conteudo, link, arquivo, ordem, ativo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $secao_id, 
                    $titulo, 
                    $icone, 
                    $tipo_conteudo,
                    $tipo_conteudo === 'link' ? $link : null,
                    $tipo_conteudo === 'arquivo' ? $arquivo_nome : null,
                    $proxima_ordem, 
                    $ativo
                ]);
                
                $_SESSION['success_message'] = "Card criado com sucesso!";
            }
            
            header('Location: progestao.php');
            exit;
            
        } catch (PDOException $e) {
            error_log("Erro ao salvar card: " . $e->getMessage());
            $erros[] = "Erro ao salvar card. Tente novamente.";
        }
    }
}

// Buscar seções disponíveis
try {
    $stmt = $db->query("SELECT id, titulo FROM progestao_secoes ORDER BY ordem ASC");
    $secoes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar seções: " . $e->getMessage());
    $secoes = [];
}

if (empty($secoes)) {
    header('Location: progestao_secao_form.php');
    exit;
}

// Lista de ícones sugeridos
$icones_sugeridos = [
    '📄' => 'Documento',
    '📊' => 'Gráfico/Relatório',
    '✅' => 'Verificação',
    '📋' => 'Clipboard',
    '💰' => 'Financeiro',
    '🏛️' => 'Governança',
    '📁' => 'Pasta',
    '📈' => 'Crescimento',
    '🔍' => 'Lupa/Busca',
    '⚖️' => 'Balança/Justiça',
    '👥' => 'Pessoas',
    '🎯' => 'Alvo/Meta',
    '📝' => 'Nota/Edição',
    '💼' => 'Pasta executiva',
    '🔐' => 'Segurança',
    '📌' => 'Pin',
];

$pageTitle = 'Formulário de Card - Pró-Gestão';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-3xl">
    <!-- Cabeçalho -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="progestao.php" class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-800">
                <?= $id > 0 ? 'Editar Card' : 'Novo Card' ?>
            </h1>
        </div>
        <p class="text-gray-600">Preencha os dados do card abaixo</p>
    </div>

    <?php if (!empty($erros)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulário -->
    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-8">
        <div class="mb-6">
            <label for="secao_id" class="block text-sm font-medium text-gray-700 mb-2">
                Seção <span class="text-red-500">*</span>
            </label>
            <select id="secao_id" 
                    name="secao_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required>
                <option value="">Selecione uma seção</option>
                <?php foreach ($secoes as $secao): ?>
                    <option value="<?= $secao['id'] ?>" <?= ($secao_id == $secao['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($secao['titulo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                Título do Card <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="titulo" 
                   name="titulo" 
                   value="<?= htmlspecialchars($card['titulo'] ?? '') ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Ex: Conselho Municipal Previdenciário - CMP"
                   required>
        </div>

        <div class="mb-6">
            <label for="icone" class="block text-sm font-medium text-gray-700 mb-2">
                Ícone (Emoji)
            </label>
            <input type="text" 
                   id="icone" 
                   name="icone" 
                   value="<?= htmlspecialchars($card['icone'] ?? '') ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-3"
                   placeholder="Ex: 📄">
            
            <!-- Ícones sugeridos -->
            <div class="grid grid-cols-8 gap-2">
                <?php foreach ($icones_sugeridos as $emoji => $descricao): ?>
                    <button type="button" 
                            onclick="document.getElementById('icone').value = '<?= $emoji ?>'"
                            class="text-2xl p-2 border border-gray-200 rounded hover:bg-gray-100 hover:border-blue-400 transition-colors"
                            title="<?= $descricao ?>">
                        <?= $emoji ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <p class="text-sm text-gray-500 mt-2">Clique em um ícone ou cole um emoji personalizado</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
                Tipo de Conteúdo <span class="text-red-500">*</span>
            </label>
            <div class="space-y-3">
                <label class="flex items-center gap-3 p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" 
                           name="tipo_conteudo" 
                           value="link" 
                           <?= ($card['tipo_conteudo'] ?? 'link') === 'link' ? 'checked' : '' ?>
                           onchange="toggleTipoConteudo()"
                           class="w-4 h-4 text-blue-600">
                    <div>
                        <div class="font-medium text-gray-900">Link Externo</div>
                        <div class="text-sm text-gray-500">O card abrirá um link em nova aba</div>
                    </div>
                </label>
                
                <label class="flex items-center gap-3 p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" 
                           name="tipo_conteudo" 
                           value="arquivo" 
                           <?= ($card['tipo_conteudo'] ?? '') === 'arquivo' ? 'checked' : '' ?>
                           onchange="toggleTipoConteudo()"
                           class="w-4 h-4 text-blue-600">
                    <div>
                        <div class="font-medium text-gray-900">Arquivo PDF</div>
                        <div class="text-sm text-gray-500">Upload de um arquivo PDF</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Campo de Link -->
        <div id="campo_link" class="mb-6" style="display: <?= ($card['tipo_conteudo'] ?? 'link') === 'link' ? 'block' : 'none' ?>">
            <label for="link" class="block text-sm font-medium text-gray-700 mb-2">
                Link <span class="text-red-500" id="link_required">*</span>
            </label>
            <input type="url" 
                   id="link" 
                   name="link" 
                   value="<?= htmlspecialchars($card['link'] ?? '') ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="https://exemplo.com">
        </div>

        <!-- Campo de Arquivo -->
        <div id="campo_arquivo" class="mb-6" style="display: <?= ($card['tipo_conteudo'] ?? '') === 'arquivo' ? 'block' : 'none' ?>">
            <label for="arquivo" class="block text-sm font-medium text-gray-700 mb-2">
                Arquivo PDF <?= !$id ? '<span class="text-red-500">*</span>' : '(deixe vazio para manter o arquivo atual)' ?>
            </label>
            <input type="file" 
                   id="arquivo" 
                   name="arquivo" 
                   accept=".pdf"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <?php if ($id && isset($card['arquivo']) && $card['arquivo']): ?>
                <p class="text-sm text-gray-600 mt-2">
                    Arquivo atual: <a href="/uploads/progestao/<?= htmlspecialchars($card['arquivo']) ?>" 
                                      target="_blank" 
                                      class="text-blue-600 hover:underline">
                        <?= htmlspecialchars($card['arquivo']) ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <div class="mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" 
                       name="ativo" 
                       value="1"
                       <?= ($card['ativo'] ?? 1) ? 'checked' : '' ?>
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700">Card ativo</span>
            </label>
            <p class="text-sm text-gray-500 mt-1 ml-6">Se desmarcado, o card não aparecerá no site</p>
        </div>

        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <a href="progestao.php" class="text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
            <button type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium">
                <?= $id > 0 ? 'Atualizar Card' : 'Criar Card' ?>
            </button>
        </div>
    </form>
</div>

<script>
function toggleTipoConteudo() {
    const tipoSelecionado = document.querySelector('input[name="tipo_conteudo"]:checked').value;
    const campoLink = document.getElementById('campo_link');
    const campoArquivo = document.getElementById('campo_arquivo');
    const inputLink = document.getElementById('link');
    const inputArquivo = document.getElementById('arquivo');
    const linkRequired = document.getElementById('link_required');
    
    if (tipoSelecionado === 'link') {
        campoLink.style.display = 'block';
        campoArquivo.style.display = 'none';
        inputLink.required = true;
        inputArquivo.required = false;
        linkRequired.style.display = 'inline';
    } else {
        campoLink.style.display = 'none';
        campoArquivo.style.display = 'block';
        inputLink.required = false;
        <?php if (!$id): ?>
        inputArquivo.required = true;
        <?php endif; ?>
        linkRequired.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
