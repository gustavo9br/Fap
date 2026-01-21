<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

$mensagem = '';
$tipoMensagem = '';
$convenio = null;
$editando = false;

// Se está editando
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $stmt = $pdo->prepare("SELECT * FROM convenios WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $convenio = $stmt->fetch();
    
    if (!$convenio) {
        header('Location: convenios.php');
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id = $_POST['categoria_id'] ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $desconto = trim($_POST['desconto'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $site = trim($_POST['site'] ?? '');
    $ordem = intval($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $logo = $convenio['logo'] ?? '';

    // Upload de logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/convenios/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid('convenio_') . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                // Deletar logo anterior se existir
                if ($logo && file_exists('../' . $logo)) {
                    unlink('../' . $logo);
                }
                $logo = 'uploads/convenios/' . $fileName;
            }
        }
    }

    // Validação
    if (empty($nome)) {
        $mensagem = 'O nome do convênio é obrigatório.';
        $tipoMensagem = 'error';
    } elseif (empty($categoria_id) || !is_numeric($categoria_id)) {
        $mensagem = 'Selecione uma categoria válida.';
        $tipoMensagem = 'error';
    } else {
        try {
            if ($editando) {
                // Atualizar
                $sql = "UPDATE convenios SET 
                        categoria_id = ?, nome = ?, logo = ?, descricao = ?, 
                        desconto = ?, endereco = ?, telefone = ?, email = ?, 
                        site = ?, ordem = ?, ativo = ?, atualizado_em = NOW()
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $categoria_id, $nome, $logo, $descricao, 
                    $desconto, $endereco, $telefone, $email, 
                    $site, $ordem, $ativo, $convenio['id']
                ]);
                $mensagem = 'Convênio atualizado com sucesso!';
            } else {
                // Inserir
                $sql = "INSERT INTO convenios 
                        (categoria_id, nome, logo, descricao, desconto, endereco, telefone, email, site, ordem, ativo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $categoria_id, $nome, $logo, $descricao, 
                    $desconto, $endereco, $telefone, $email, 
                    $site, $ordem, $ativo
                ]);
                $mensagem = 'Convênio cadastrado com sucesso!';
            }
            $tipoMensagem = 'success';
            
            // Redirecionar após sucesso
            header('Location: convenios.php');
            exit;
        } catch (PDOException $e) {
            $mensagem = 'Erro ao salvar convênio: ' . $e->getMessage();
            $tipoMensagem = 'error';
        }
    }
}

// Buscar categorias
$stmtCat = $pdo->query("SELECT * FROM convenios_categorias WHERE ativo = 1 ORDER BY ordem, nome");
$categorias = $stmtCat->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editando ? 'Editar' : 'Novo'; ?> Convênio - Admin FAP</title>
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
            <a href="convenios.php" class="text-gray-600 hover:text-gray-800">
                ← Voltar
            </a>
            <h1 class="text-3xl font-bold text-gray-800">
                <?php echo $editando ? 'Editar' : 'Novo'; ?> Convênio
            </h1>
        </div>

        <?php if ($mensagem): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipoMensagem === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nome do Convênio *
                    </label>
                    <input type="text" 
                           name="nome" 
                           value="<?php echo htmlspecialchars($convenio['nome'] ?? ''); ?>" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Categoria -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Categoria *
                    </label>
                    <select name="categoria_id" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($convenio['categoria_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Logo -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Logo
                    </label>
                    <input type="file" 
                           name="logo" 
                           accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <?php if ($convenio && $convenio['logo']): ?>
                        <div class="mt-2">
                            <img src="../<?php echo htmlspecialchars($convenio['logo']); ?>" 
                                 alt="Logo atual" 
                                 class="h-20 object-contain">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Desconto -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Desconto/Vantagem
                    </label>
                    <input type="text" 
                           name="desconto" 
                           value="<?php echo htmlspecialchars($convenio['desconto'] ?? ''); ?>" 
                           placeholder="Ex: 10% de desconto em todos os produtos"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Descrição -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea name="descricao" 
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary"><?php echo htmlspecialchars($convenio['descricao'] ?? ''); ?></textarea>
                </div>

                <!-- Endereço -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Endereço
                    </label>
                    <input type="text" 
                           name="endereco" 
                           value="<?php echo htmlspecialchars($convenio['endereco'] ?? ''); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Telefone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Telefone
                    </label>
                    <input type="text" 
                           name="telefone" 
                           value="<?php echo htmlspecialchars($convenio['telefone'] ?? ''); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        E-mail
                    </label>
                    <input type="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($convenio['email'] ?? ''); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Site -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Site
                    </label>
                    <input type="url" 
                           name="site" 
                           value="<?php echo htmlspecialchars($convenio['site'] ?? ''); ?>" 
                           placeholder="https://"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                </div>

                <!-- Ordem -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           value="<?php echo htmlspecialchars($convenio['ordem'] ?? 0); ?>" 
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-primary">
                    <p class="text-xs text-gray-500 mt-1">Menor número aparece primeiro</p>
                </div>

                <!-- Status -->
                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="ativo" 
                               <?php echo ($convenio['ativo'] ?? 1) ? 'checked' : ''; ?>
                               class="w-5 h-5 text-green-primary border-gray-300 rounded focus:ring-green-primary">
                        <span class="ml-2 text-sm font-semibold text-gray-700">Ativo</span>
                    </label>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4 mt-8">
                <a href="convenios.php" 
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-primary text-white rounded-lg hover:bg-green-600 transition-colors font-medium">
                    <?php echo $editando ? 'Atualizar' : 'Cadastrar'; ?> Convênio
                </button>
            </div>
        </form>
    </div>
</body>
</html>
