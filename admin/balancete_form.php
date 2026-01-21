<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

$id = $_GET['id'] ?? null;
$categoria_preselect = $_GET['categoria'] ?? null;
$balancete = null;
$mensagem = '';
$tipoMensagem = '';

// Buscar balancete para edição
if ($id && is_numeric($id)) {
    $stmt = $pdo->prepare("SELECT * FROM balancetes WHERE id = ?");
    $stmt->execute([$id]);
    $balancete = $stmt->fetch();
    
    if (!$balancete) {
        header('Location: balancetes_index.php');
        exit;
    }
    // Se editando, usar categoria do balancete para redirect
    $categoria_preselect = $balancete['categoria_id'];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id = $_POST['categoria_id'] ?? '';
    $titulo = trim($_POST['titulo'] ?? '');
    $data_documento = $_POST['data_documento'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $ordem = $_POST['ordem'] ?? 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $arquivo_atual = $_POST['arquivo_atual'] ?? '';
    
    $erros = [];
    
    // Validações
    if (empty($categoria_id) || !is_numeric($categoria_id)) {
        $erros[] = 'Selecione uma categoria válida.';
    }
    
    if (empty($titulo)) {
        $erros[] = 'O título é obrigatório.';
    }
    
    if (empty($data_documento)) {
        $erros[] = 'A data do documento é obrigatória.';
    }
    
    if (empty($ano)) {
        $erros[] = 'O ano é obrigatório.';
    }
    
    // Upload de arquivo
    $arquivo_path = $arquivo_atual;
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['arquivo'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        // Validar PDF
        if ($extensao !== 'pdf') {
            $erros[] = 'Apenas arquivos PDF são permitidos.';
        }
        
        // Validar mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $arquivo['tmp_name']);
        finfo_close($finfo);
        
        if ($mimeType !== 'application/pdf') {
            $erros[] = 'O arquivo enviado não é um PDF válido.';
        }
        
        // Validar tamanho (10MB max)
        if ($arquivo['size'] > 10 * 1024 * 1024) {
            $erros[] = 'O arquivo não pode ter mais de 10MB.';
        }
        
        if (empty($erros)) {
            // Criar diretório se não existir
            $uploadDir = '../uploads/balancetes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Gerar nome único
            $nomeArquivo = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $arquivo['name']);
            $caminhoCompleto = $uploadDir . $nomeArquivo;
            
            if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
                // Deletar arquivo antigo se houver
                if ($arquivo_atual && file_exists('../' . $arquivo_atual)) {
                    unlink('../' . $arquivo_atual);
                }
                $arquivo_path = 'uploads/balancetes/' . $nomeArquivo;
            } else {
                $erros[] = 'Erro ao fazer upload do arquivo.';
            }
        }
    } elseif (!$id) {
        $erros[] = 'O arquivo PDF é obrigatório.';
    }
    
    // Salvar no banco
    if (empty($erros)) {
        try {
            if ($id) {
                // Atualizar
                $stmt = $pdo->prepare("
                    UPDATE balancetes 
                    SET categoria_id = ?, titulo = ?, arquivo = ?, data_documento = ?, 
                        ano = ?, ordem = ?, ativo = ?, atualizado_em = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $categoria_id, $titulo, $arquivo_path, $data_documento,
                    $ano, $ordem, $ativo, $id
                ]);
                $mensagem = 'Balancete atualizado com sucesso!';
            } else {
                // Inserir
                $stmt = $pdo->prepare("
                    INSERT INTO balancetes 
                    (categoria_id, titulo, arquivo, data_documento, ano, ordem, ativo, criado_em, atualizado_em)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $categoria_id, $titulo, $arquivo_path, $data_documento,
                    $ano, $ordem, $ativo
                ]);
                $mensagem = 'Balancete criado com sucesso!';
            }
            
            $tipoMensagem = 'success';
            
            // Redirecionar para a lista da categoria
            $redirect_url = $categoria_preselect ? "balancetes_lista.php?categoria=$categoria_preselect" : "balancetes_index.php";
            header("refresh:2;url=$redirect_url");
        } catch (PDOException $e) {
            $erros[] = 'Erro ao salvar: ' . $e->getMessage();
        }
    }
    
    if (!empty($erros)) {
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'error';
    }
}

// Buscar categorias
$stmtCat = $pdo->query("SELECT * FROM categorias_balancetes WHERE ativo = 1 ORDER BY ordem, nome");
$categorias = $stmtCat->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Editar' : 'Novo'; ?> Balancete - Admin FAP</title>
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

    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="<?php echo $categoria_preselect ? "balancetes_lista.php?categoria=$categoria_preselect" : "balancetes_index.php"; ?>" class="text-blue-primary hover:text-blue-800 font-medium">
                ← Voltar para balancetes
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">
                <?php echo $id ? 'Editar' : 'Novo'; ?> Balancete
            </h1>

            <?php if ($mensagem): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $tipoMensagem === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <?php if ($id): ?>
                    <input type="hidden" name="arquivo_atual" value="<?php echo htmlspecialchars($balancete['arquivo']); ?>">
                <?php endif; ?>

                <!-- Categoria -->
                <div>
                    <label for="categoria_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Categoria <span class="text-red-500">*</span>
                    </label>
                    <select name="categoria_id" 
                            id="categoria_id" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo (($balancete && $balancete['categoria_id'] == $cat['id']) || ($categoria_preselect == $cat['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Título -->
                <div>
                    <label for="titulo" class="block text-sm font-semibold text-gray-700 mb-2">
                        Título do Documento <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="titulo" 
                           id="titulo" 
                           value="<?php echo $balancete ? htmlspecialchars($balancete['titulo']) : ''; ?>"
                           required
                           placeholder="Ex: Balancete de Janeiro/2024"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Arquivo PDF -->
                <div>
                    <label for="arquivo" class="block text-sm font-semibold text-gray-700 mb-2">
                        Arquivo PDF <span class="text-red-500">*</span>
                    </label>
                    
                    <?php if ($balancete && $balancete['arquivo']): ?>
                        <div class="mb-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-gray-700">Arquivo atual:</span>
                                    <a href="../<?php echo htmlspecialchars($balancete['arquivo']); ?>" 
                                       target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                        Ver PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="file" 
                           name="arquivo" 
                           id="arquivo" 
                           accept=".pdf"
                           <?php echo !$id ? 'required' : ''; ?>
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <p class="mt-2 text-sm text-gray-600">
                        <?php if ($id): ?>
                            Deixe em branco para manter o arquivo atual. 
                        <?php endif; ?>
                        Tamanho máximo: 10MB. Apenas arquivos PDF.
                    </p>
                </div>

                <!-- Data e Ano -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="data_documento" class="block text-sm font-semibold text-gray-700 mb-2">
                            Data do Documento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="data_documento" 
                               id="data_documento" 
                               value="<?php echo $balancete ? $balancete['data_documento'] : ''; ?>"
                               required
                               onchange="atualizarAno()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    </div>

                    <div>
                        <label for="ano" class="block text-sm font-semibold text-gray-700 mb-2">
                            Ano <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="ano" 
                               id="ano" 
                               value="<?php echo $balancete ? $balancete['ano'] : date('Y'); ?>"
                               required
                               min="1900"
                               max="2100"
                               placeholder="<?php echo date('Y'); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                        <p class="mt-2 text-sm text-gray-600">
                            Preenchido automaticamente com base na data do documento.
                        </p>
                    </div>
                </div>

                <!-- Ordem -->
                <div>
                    <label for="ordem" class="block text-sm font-semibold text-gray-700 mb-2">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           id="ordem" 
                           value="<?php echo $balancete ? $balancete['ordem'] : 0; ?>"
                           min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <p class="mt-2 text-sm text-gray-600">
                        Número menor aparece primeiro. Padrão: 0
                    </p>
                </div>

                <!-- Status Ativo -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="ativo" 
                           id="ativo" 
                           <?php echo (!$balancete || $balancete['ativo']) ? 'checked' : ''; ?>
                           class="w-5 h-5 text-green-primary focus:ring-green-primary border-gray-300 rounded">
                    <label for="ativo" class="ml-2 text-sm font-medium text-gray-700">
                        Balancete ativo (visível no site)
                    </label>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t">
                    <button type="submit" 
                            class="flex-1 bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors font-medium">
                        <?php echo $id ? 'Atualizar' : 'Criar'; ?> Balancete
                    </button>
                    <a href="balancetes" 
                       class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors font-medium text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function atualizarAno() {
            const dataInput = document.getElementById('data_documento');
            const anoInput = document.getElementById('ano');
            
            if (dataInput.value) {
                const data = new Date(dataInput.value + 'T00:00:00');
                anoInput.value = data.getFullYear();
            }
        }

        // Auto-preencher ano ao carregar se houver data
        document.addEventListener('DOMContentLoaded', function() {
            const dataInput = document.getElementById('data_documento');
            if (dataInput.value) {
                atualizarAno();
            }
        });
    </script>
</body>
</html>
