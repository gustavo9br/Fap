<?php
require_once '../config/database.php';
require_once '../config/session.php';

Session::requireLogin();

$pdo = Database::getInstance()->getConnection();

// Obter categoria_id da URL
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

if (!$categoria_id) {
    header("Location: demonstrativos_index.php");
    exit;
}

// Buscar categoria
$stmt = $pdo->prepare("SELECT * FROM categorias_demonstrativos WHERE id = ?");
$stmt->execute([$categoria_id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header("Location: demonstrativos_index.php");
    exit;
}

// Processar ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Adicionar Ano
    if ($_POST['action'] === 'add_ano') {
        $ano = (int)$_POST['ano'];
        header("Location: editar-demonstrativo.php?categoria=$categoria_id&success=ano_ok&novo_ano=$ano");
        exit;
    }
    
    // Adicionar Arquivo/Demonstrativo
    if ($_POST['action'] === 'add_arquivo') {
        $ano = (int)$_POST['ano'];
        $titulo = trim($_POST['titulo']);
        
        $erros = [];
        $arquivo_path = '';
        
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['arquivo'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            
            if ($extensao !== 'pdf') {
                $erros[] = 'Apenas arquivos PDF são permitidos.';
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $arquivo['tmp_name']);
            finfo_close($finfo);
            
            if ($mimeType !== 'application/pdf') {
                $erros[] = 'O arquivo enviado não é um PDF válido.';
            }
            
            if ($arquivo['size'] > 10 * 1024 * 1024) {
                $erros[] = 'O arquivo não pode ter mais de 10MB.';
            }
            
            if (empty($erros)) {
                $uploadDir = '../uploads/demonstrativos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $nomeArquivo = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $arquivo['name']);
                $caminhoCompleto = $uploadDir . $nomeArquivo;
                
                if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
                    $arquivo_path = '/uploads/demonstrativos/' . $nomeArquivo;
                } else {
                    $erros[] = 'Erro ao fazer upload do arquivo.';
                }
            }
        } else {
            $erros[] = 'É necessário enviar um arquivo PDF.';
        }
        
        if (empty($erros)) {
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(ordem), 0) + 1 as proxima FROM demonstrativos WHERE categoria_id = ? AND ano = ?");
            $stmt->execute([$categoria_id, $ano]);
            $ordem = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO demonstrativos (categoria_id, titulo, arquivo, data_documento, ano, ordem, ativo, criado_em) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->execute([$categoria_id, $titulo, $arquivo_path, date('Y-m-d'), $ano, $ordem]);
            
            header("Location: editar-demonstrativo.php?categoria=$categoria_id&success=arquivo_added");
            exit;
        } else {
            header("Location: editar-demonstrativo.php?categoria=$categoria_id&error=" . urlencode(implode(' ', $erros)));
            exit;
        }
    }
    
    // Excluir Arquivo
    if ($_POST['action'] === 'delete_arquivo') {
        $arquivo_id = (int)$_POST['arquivo_id'];
        
        $stmt = $pdo->prepare("SELECT arquivo FROM demonstrativos WHERE id = ? AND categoria_id = ?");
        $stmt->execute([$arquivo_id, $categoria_id]);
        $arquivo = $stmt->fetch();
        
        if ($arquivo && $arquivo['arquivo'] && file_exists('../' . ltrim($arquivo['arquivo'], '/'))) {
            unlink('../' . ltrim($arquivo['arquivo'], '/'));
        }
        
        $stmt = $pdo->prepare("DELETE FROM demonstrativos WHERE id = ? AND categoria_id = ?");
        $stmt->execute([$arquivo_id, $categoria_id]);
        
        header("Location: editar-demonstrativo.php?categoria=$categoria_id&success=arquivo_deleted");
        exit;
    }
    
    // Excluir Ano inteiro
    if ($_POST['action'] === 'delete_ano') {
        $ano = (int)$_POST['ano'];
        
        $stmt = $pdo->prepare("SELECT arquivo FROM demonstrativos WHERE categoria_id = ? AND ano = ?");
        $stmt->execute([$categoria_id, $ano]);
        $arquivos = $stmt->fetchAll();
        
        foreach ($arquivos as $arq) {
            if ($arq['arquivo'] && file_exists('../' . $arq['arquivo'])) {
                unlink('../' . $arq['arquivo']);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM demonstrativos WHERE categoria_id = ? AND ano = ?");
        $stmt->execute([$categoria_id, $ano]);
        
        header("Location: editar-demonstrativo.php?categoria=$categoria_id&success=ano_deleted");
        exit;
    }
    
    // Atualizar Ano
    if ($_POST['action'] === 'update_ano') {
        $ano_id = (int)$_POST['ano_id'];
        $novo_ano = (int)$_POST['ano'];
        
        $stmt = $pdo->prepare("UPDATE demonstrativos SET ano = ? WHERE id = ? AND categoria_id = ?");
        $stmt->execute([$novo_ano, $ano_id, $categoria_id]);
        
        header("Location: editar-demonstrativo.php?categoria=$categoria_id&success=ano_updated");
        exit;
    }
    
    // Mover Arquivo (ordenação)
    if ($_POST['action'] === 'move_arquivo') {
        $arquivo_id = (int)$_POST['arquivo_id'];
        $direcao = $_POST['direcao'];
        
        $stmt = $pdo->prepare("SELECT ano, ordem FROM demonstrativos WHERE id = ? AND categoria_id = ?");
        $stmt->execute([$arquivo_id, $categoria_id]);
        $arquivo_atual = $stmt->fetch();
        
        if ($arquivo_atual) {
            $ano = $arquivo_atual['ano'];
            $ordem_atual = $arquivo_atual['ordem'];
            
            if ($direcao === 'up') {
                // Trocar com o arquivo anterior
                $stmt = $pdo->prepare("SELECT id, ordem FROM demonstrativos WHERE categoria_id = ? AND ano = ? AND ordem < ? ORDER BY ordem DESC LIMIT 1");
                $stmt->execute([$categoria_id, $ano, $ordem_atual]);
            } else {
                // Trocar com o próximo arquivo
                $stmt = $pdo->prepare("SELECT id, ordem FROM demonstrativos WHERE categoria_id = ? AND ano = ? AND ordem > ? ORDER BY ordem ASC LIMIT 1");
                $stmt->execute([$categoria_id, $ano, $ordem_atual]);
            }
            
            $arquivo_troca = $stmt->fetch();
            
            if ($arquivo_troca) {
                // Trocar as ordens
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE demonstrativos SET ordem = ? WHERE id = ?");
                $stmt->execute([$arquivo_troca['ordem'], $arquivo_id]);
                $stmt->execute([$ordem_atual, $arquivo_troca['id']]);
                $pdo->commit();
            }
        }
        
        header("Location: editar-demonstrativo.php?categoria=$categoria_id&success=ordem_updated");
        exit;
    }
}

// Buscar todos os anos distintos para esta categoria
$stmt = $pdo->prepare("SELECT DISTINCT ano FROM demonstrativos WHERE categoria_id = ? ORDER BY ano DESC");
$stmt->execute([$categoria_id]);
$anos = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$anos) {
    $anos = [];
}

// Verificar se há um novo ano sendo adicionado
$novo_ano = isset($_GET['novo_ano']) ? (int)$_GET['novo_ano'] : null;
if ($novo_ano && !in_array($novo_ano, $anos)) {
    array_unshift($anos, $novo_ano);
}

$meses = [
    '01' => 'Janeiro',
    '02' => 'Fevereiro',
    '03' => 'Março',
    '04' => 'Abril',
    '05' => 'Maio',
    '06' => 'Junho',
    '07' => 'Julho',
    '08' => 'Agosto',
    '09' => 'Setembro',
    '10' => 'Outubro',
    '11' => 'Novembro',
    '12' => 'Dezembro'
];

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="demonstrativos_index.php" class="text-blue-600 hover:underline mb-4 inline-block">← Voltar para Demonstrativos</a>
        <h1 class="text-3xl font-bold text-gray-800">Editar <?php echo htmlspecialchars($categoria['nome']); ?></h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        Operação realizada com sucesso!
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
    <?php endif; ?>

    <!-- Adicionar Novo Ano -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Adicionar Novo Ano</h2>
        <form method="POST" class="flex gap-4">
            <input type="hidden" name="action" value="add_ano">
            <input type="number" name="ano" placeholder="Ano (ex: 2025)" required min="2000" max="2100" 
                   class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 flex-grow">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-600">
                Adicionar Ano
            </button>
        </form>
    </div>

    <!-- Anos -->
    <?php 
    $ano_index = 0;
    foreach ($anos as $ano): 
        // Buscar arquivos deste ano
        $stmt = $pdo->prepare("SELECT * FROM demonstrativos WHERE categoria_id = ? AND ano = ? ORDER BY ordem ASC");
        $stmt->execute([$categoria_id, $ano]);
        $arquivos = $stmt->fetchAll();
        $ano_index++;
    ?>
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-gray-700" id="ano_titulo_<?php echo $ano; ?>">
                    Ano: <?php echo $ano; ?>
                </h3>
                <button onclick="editarAno(<?php echo $ano; ?>, this)" 
                        class="text-gray-400 hover:text-blue-600 transition-colors" 
                        title="Editar ano"
                        id="btn_edit_ano_<?php echo $ano; ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
                <!-- Form de edição inline (oculto) -->
                <form method="POST" style="display: none;" id="form_edit_ano_<?php echo $ano; ?>" class="flex items-center gap-2">
                    <input type="hidden" name="action" value="update_ano">
                    <input type="hidden" name="ano_id" value="<?php echo $arquivos[0]['id'] ?? 0; ?>">
                    <span class="text-lg font-bold text-gray-700">Ano:</span>
                    <input type="number" 
                           name="ano" 
                           value="<?php echo $ano; ?>" 
                           required 
                           min="2000" 
                           max="2100"
                           class="border border-blue-500 rounded px-2 py-1 w-24 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="input_ano_<?php echo $ano; ?>">
                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm">
                        Salvar
                    </button>
                    <button type="button" onclick="cancelarEditarAno(<?php echo $ano; ?>)" class="bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 text-sm">
                        Cancelar
                    </button>
                </form>
            </div>
            <div class="flex items-center gap-2">
                <!-- Botões de Ordenação do Ano -->
                <?php if ($ano_index > 1): ?>
                <button onclick="moverAno(<?php echo $ano; ?>, 'up')" class="text-gray-600 hover:text-blue-600 transition-colors" title="Mover para cima">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                </button>
                <?php endif; ?>
                <?php if ($ano_index < count($anos)): ?>
                <button onclick="moverAno(<?php echo $ano; ?>, 'down')" class="text-gray-600 hover:text-blue-600 transition-colors" title="Mover para baixo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <?php endif; ?>
                <span class="text-gray-300 mx-2">|</span>
                <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este ano e todos seus arquivos?')" class="inline">
                    <input type="hidden" name="action" value="delete_ano">
                    <input type="hidden" name="ano" value="<?php echo $ano; ?>">
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Excluir Ano</button>
                </form>
            </div>
        </div>

        <!-- Upload de Arquivo -->
        <form method="POST" enctype="multipart/form-data" class="bg-gray-50 rounded-lg p-4 mb-4">
            <input type="hidden" name="action" value="add_arquivo">
            <input type="hidden" name="ano" value="<?php echo $ano; ?>">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <select name="titulo" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 w-full bg-white" id="select_mes_<?php echo $ano; ?>" onchange="checkMesOutro(<?php echo $ano; ?>)" required>
                        <option value="">Selecione o título *</option>
                        <?php foreach ($meses as $num => $nome): ?>
                        <option value="<?php echo $nome; ?>"><?php echo $nome; ?></option>
                        <?php endforeach; ?>
                        <option value="outro">✏️ Personalizado...</option>
                    </select>
                    <input type="text" placeholder="Digite o título..." id="input_mes_<?php echo $ano; ?>" style="display: none;" 
                           class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 w-full mt-2">
                </div>
                <input type="file" name="arquivo" required accept=".pdf" 
                       class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 bg-white">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-600">
                    Upload
                </button>
            </div>
        </form>

        <!-- Lista de Arquivos -->
        <div class="space-y-2">
            <?php 
            $arquivo_index = 0;
            foreach ($arquivos as $arquivo): 
                $arquivo_index++;
            ?>
            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-gray-700"><?php echo htmlspecialchars($arquivo['titulo']); ?></span>
                    <a href="../<?php echo htmlspecialchars($arquivo['arquivo']); ?>" target="_blank" class="text-blue-600 hover:underline text-sm">Ver arquivo</a>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Botões de Ordenação -->
                    <?php if ($arquivo_index > 1): ?>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="move_arquivo">
                        <input type="hidden" name="arquivo_id" value="<?php echo $arquivo['id']; ?>">
                        <input type="hidden" name="direcao" value="up">
                        <button type="submit" class="text-gray-600 hover:text-blue-600 transition-colors" title="Mover para cima">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if ($arquivo_index < count($arquivos)): ?>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="move_arquivo">
                        <input type="hidden" name="arquivo_id" value="<?php echo $arquivo['id']; ?>">
                        <input type="hidden" name="direcao" value="down">
                        <button type="submit" class="text-gray-600 hover:text-blue-600 transition-colors" title="Mover para baixo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </form>
                    <?php endif; ?>
                    <span class="text-gray-300 mx-1">|</span>
                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este arquivo?')" class="inline">
                        <input type="hidden" name="action" value="delete_arquivo">
                        <input type="hidden" name="arquivo_id" value="<?php echo $arquivo['id']; ?>">
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Excluir</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (count($arquivos) === 0): ?>
            <p class="text-gray-500 text-center py-4">Nenhum arquivo adicionado ainda</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (count($anos) === 0): ?>
    <div class="bg-white rounded-xl shadow-md p-8 text-center text-gray-500">
        Nenhum ano criado ainda. Adicione um ano acima para começar.
    </div>
    <?php endif; ?>
</div>

<script>
function editarAno(ano, btn) {
    document.getElementById('ano_titulo_' + ano).style.display = 'none';
    document.getElementById('btn_edit_ano_' + ano).style.display = 'none';
    const form = document.getElementById('form_edit_ano_' + ano);
    form.style.display = 'flex';
    const input = document.getElementById('input_ano_' + ano);
    input.focus();
    input.select();
}

function cancelarEditarAno(ano) {
    document.getElementById('ano_titulo_' + ano).style.display = 'block';
    document.getElementById('btn_edit_ano_' + ano).style.display = 'block';
    document.getElementById('form_edit_ano_' + ano).style.display = 'none';
}

function checkMesOutro(ano) {
    const select = document.getElementById('select_mes_' + ano);
    const input = document.getElementById('input_mes_' + ano);
    
    if (select.value === 'outro') {
        input.style.display = 'block';
        input.setAttribute('name', 'titulo');
        input.required = true;
        input.focus();
        select.removeAttribute('name');
    } else {
        input.style.display = 'none';
        input.removeAttribute('name');
        input.required = false;
        input.value = '';
        select.setAttribute('name', 'titulo');
    }
}

function moverAno(ano, direcao) {
    // Reordenar anos na interface (reload da página fará a ordenação real)
    const anos = <?php echo json_encode($anos); ?>;
    const index = anos.indexOf(ano);
    
    if (direcao === 'up' && index > 0) {
        window.location.href = `editar-demonstrativo.php?categoria=<?php echo $categoria_id; ?>&move_ano=${ano}&dir=up`;
    } else if (direcao === 'down' && index < anos.length - 1) {
        window.location.href = `editar-demonstrativo.php?categoria=<?php echo $categoria_id; ?>&move_ano=${ano}&dir=down`;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
