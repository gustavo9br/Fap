<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

$mensagem = '';
$tipoMensagem = '';
$categoria = null;
$editando = false;

// Se está editando
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $stmt = $pdo->prepare("SELECT * FROM convenios_categorias WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $categoria = $stmt->fetch();
    
    if (!$categoria) {
        header('Location: categorias_convenios.php');
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $cor = trim($_POST['cor'] ?? '#4A90E2');
    $ordem = intval($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Validação
    if (empty($nome)) {
        $mensagem = 'O nome da categoria é obrigatório.';
        $tipoMensagem = 'error';
    } elseif (empty($slug)) {
        $mensagem = 'O slug da categoria é obrigatório.';
        $tipoMensagem = 'error';
    } else {
        // Verificar slug único
        if ($editando) {
            $stmtCheck = $pdo->prepare("SELECT id FROM convenios_categorias WHERE slug = ? AND id != ?");
            $stmtCheck->execute([$slug, $categoria['id']]);
        } else {
            $stmtCheck = $pdo->prepare("SELECT id FROM convenios_categorias WHERE slug = ?");
            $stmtCheck->execute([$slug]);
        }
        
        if ($stmtCheck->fetch()) {
            $mensagem = 'Já existe uma categoria com este slug.';
            $tipoMensagem = 'error';
        } else {
            try {
                if ($editando) {
                    // Atualizar
                    $sql = "UPDATE convenios_categorias SET 
                            nome = ?, slug = ?, cor = ?, ordem = ?, ativo = ?
                            WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nome, $slug, $cor, $ordem, $ativo, $categoria['id']]);
                    $mensagem = 'Categoria atualizada com sucesso!';
                } else {
                    // Inserir
                    $sql = "INSERT INTO convenios_categorias (nome, slug, cor, ordem, ativo) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nome, $slug, $cor, $ordem, $ativo]);
                    $mensagem = 'Categoria cadastrada com sucesso!';
                }
                $tipoMensagem = 'success';
                
                // Redirecionar após sucesso
                header('Location: categorias_convenios.php');
                exit;
            } catch (PDOException $e) {
                $mensagem = 'Erro ao salvar categoria: ' . $e->getMessage();
                $tipoMensagem = 'error';
            }
        }
    }
}

// Função para gerar slug
function gerarSlug($texto) {
    $texto = strtolower($texto);
    $texto = preg_replace('/[áàâã]/u', 'a', $texto);
    $texto = preg_replace('/[éèê]/u', 'e', $texto);
    $texto = preg_replace('/[íì]/u', 'i', $texto);
    $texto = preg_replace('/[óòôõ]/u', 'o', $texto);
    $texto = preg_replace('/[úù]/u', 'u', $texto);
    $texto = preg_replace('/[ç]/u', 'c', $texto);
    $texto = preg_replace('/[^a-z0-9]+/i', '-', $texto);
    $texto = trim($texto, '-');
    return $texto;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editando ? 'Editar' : 'Nova'; ?> Categoria - Admin FAP</title>
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

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-6">
            <a href="categorias_convenios.php" class="text-gray-600 hover:text-gray-800">
                ← Voltar
            </a>
            <h1 class="text-3xl font-bold text-gray-800">
                <?php echo $editando ? 'Editar' : 'Nova'; ?> Categoria
            </h1>
        </div>

        <?php if ($mensagem): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipoMensagem === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-lg shadow-md p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nome da Categoria *
                    </label>
                    <input type="text" 
                           name="nome" 
                           id="nome"
                           value="<?php echo htmlspecialchars($categoria['nome'] ?? ''); ?>" 
                           required
                           oninput="atualizarSlug()"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Slug -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Slug *
                        <span class="text-xs font-normal text-gray-500">(usado na URL, sem espaços ou caracteres especiais)</span>
                    </label>
                    <input type="text" 
                           name="slug" 
                           id="slug"
                           value="<?php echo htmlspecialchars($categoria['slug'] ?? ''); ?>" 
                           required
                           pattern="[a-z0-9-]+"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <p class="text-xs text-gray-500 mt-1">Exemplo: alimentacao, planos-odontologicos</p>
                </div>

                <!-- Cor -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Cor da Categoria *
                    </label>
                    <div class="flex gap-3 items-center">
                        <input type="color" 
                               name="cor" 
                               id="cor"
                               value="<?php echo htmlspecialchars($categoria['cor'] ?? '#4A90E2'); ?>" 
                               class="w-20 h-10 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               id="cor-hex"
                               value="<?php echo htmlspecialchars($categoria['cor'] ?? '#4A90E2'); ?>" 
                               readonly
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-mono">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Será usada para destacar a categoria nos cards</p>
                </div>

                <!-- Ordem -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           value="<?php echo htmlspecialchars($categoria['ordem'] ?? 0); ?>" 
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <p class="text-xs text-gray-500 mt-1">Menor número aparece primeiro</p>
                </div>

                <!-- Status -->
                <div class="md:col-span-2 flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="ativo" 
                               <?php echo ($categoria['ativo'] ?? 1) ? 'checked' : ''; ?>
                               class="w-5 h-5 text-green-primary border-gray-300 rounded focus:ring-green-primary">
                        <span class="ml-2 text-sm font-semibold text-gray-700">Categoria Ativa</span>
                    </label>
                </div>

                <!-- Preview da Cor -->
                <div class="md:col-span-2 mt-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Preview da Badge:</p>
                    <div class="flex items-center gap-3">
                        <span id="preview-badge" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-white" style="background-color: <?php echo htmlspecialchars($categoria['cor'] ?? '#4A90E2'); ?>">
                            <?php echo htmlspecialchars($categoria['nome'] ?? 'Nome da Categoria'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4 mt-8">
                <a href="categorias_convenios.php" 
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-primary text-white rounded-lg hover:bg-green-600 transition-colors font-medium">
                    <?php echo $editando ? 'Atualizar' : 'Cadastrar'; ?> Categoria
                </button>
            </div>
        </form>
    </div>

    <script>
        // Atualizar slug automaticamente
        function atualizarSlug() {
            const nome = document.getElementById('nome').value;
            const slugInput = document.getElementById('slug');
            
            <?php if (!$editando): ?>
            if (!slugInput.dataset.manual) {
                slugInput.value = gerarSlug(nome);
            }
            <?php endif; ?>
            
            atualizarPreview();
        }

        // Gerar slug
        function gerarSlug(texto) {
            return texto
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        // Marcar slug como editado manualmente
        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.manual = 'true';
        });

        // Sincronizar cor
        document.getElementById('cor').addEventListener('input', function() {
            document.getElementById('cor-hex').value = this.value;
            atualizarPreview();
        });

        // Atualizar preview
        function atualizarPreview() {
            const nome = document.getElementById('nome').value || 'Nome da Categoria';
            const cor = document.getElementById('cor').value;
            const badge = document.getElementById('preview-badge');
            
            badge.textContent = nome;
            badge.style.backgroundColor = cor;
        }

        // Atualizar preview no carregamento
        document.addEventListener('DOMContentLoaded', atualizarPreview);
    </script>
</body>
</html>
