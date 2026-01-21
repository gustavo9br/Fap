<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se é admin
if (!Session::isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Gerenciar Usuários';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();
        $acao = $_POST['acao'] ?? '';
        
        if ($acao === 'criar') {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $tipo = $_POST['tipo'] ?? 'editor';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if (empty($nome) || empty($email) || empty($senha)) {
                throw new Exception('Nome, email e senha são obrigatórios');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            
            if (strlen($senha) < 6) {
                throw new Exception('A senha deve ter no mínimo 6 caracteres');
            }
            
            // Verificar se email já existe
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Este email já está cadastrado');
            }
            
            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, tipo, ativo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha_hash, $tipo, $ativo]);
            
            // Log de atividade
            $stmt = $db->prepare("INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) VALUES (?, 'criar_usuario', ?, ?)");
            $stmt->execute([Session::getUserId(), "Criou usuário: $nome ($email)", $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
            
            Session::setFlash('mensagem', 'Usuário criado com sucesso!');
            Session::setFlash('tipo_mensagem', 'sucesso');
            
        } elseif ($acao === 'editar') {
            $id = $_POST['id'] ?? 0;
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $tipo = $_POST['tipo'] ?? 'editor';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if (empty($nome) || empty($email)) {
                throw new Exception('Nome e email são obrigatórios');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            
            // Verificar se email já existe em outro usuário
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                throw new Exception('Este email já está cadastrado para outro usuário');
            }
            
            // Atualizar usuário
            if (!empty($senha)) {
                if (strlen($senha) < 6) {
                    throw new Exception('A senha deve ter no mínimo 6 caracteres');
                }
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, tipo = ?, ativo = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $senha_hash, $tipo, $ativo, $id]);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET nome = ?, email = ?, tipo = ?, ativo = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $tipo, $ativo, $id]);
            }
            
            // Log de atividade
            $stmt = $db->prepare("INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) VALUES (?, 'editar_usuario', ?, ?)");
            $stmt->execute([Session::getUserId(), "Editou usuário: $nome ($email)", $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
            
            Session::setFlash('mensagem', 'Usuário atualizado com sucesso!');
            Session::setFlash('tipo_mensagem', 'sucesso');
            
        } elseif ($acao === 'deletar') {
            $id = $_POST['id'] ?? 0;
            
            // Não permitir auto-exclusão
            if ($id == Session::getUserId()) {
                throw new Exception('Você não pode excluir seu próprio usuário!');
            }
            
            // Buscar nome do usuário antes de deletar
            $stmt = $db->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Verificar se há notícias do usuário
            $stmt = $db->prepare("SELECT COUNT(*) FROM noticias WHERE autor_id = ?");
            $stmt->execute([$id]);
            $total_noticias = $stmt->fetchColumn();
            
            if ($total_noticias > 0) {
                // Transferir notícias para o admin atual
                $stmt = $db->prepare("UPDATE noticias SET autor_id = ? WHERE autor_id = ?");
                $stmt->execute([Session::getUserId(), $id]);
            }
            
            // Deletar usuário
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            // Log de atividade
            $stmt = $db->prepare("INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) VALUES (?, 'deletar_usuario', ?, ?)");
            $stmt->execute([Session::getUserId(), "Deletou usuário: {$usuario['nome']} ({$usuario['email']})", $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
            
            Session::setFlash('mensagem', "Usuário deletado com sucesso! {$total_noticias} notícia(s) transferida(s) para você.");
            Session::setFlash('tipo_mensagem', 'sucesso');
        }
        
        header('Location: usuarios.php');
        exit;
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar usuários
$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM noticias WHERE autor_id = u.id) as total_noticias,
           (SELECT MAX(criado_em) FROM logs_atividades WHERE usuario_id = u.id) as ultimo_acesso
    FROM usuarios u 
    ORDER BY u.criado_em DESC
");
$usuarios = $stmt->fetchAll();

// Mensagens flash
$mensagem = Session::getFlash('mensagem');
$tipo_mensagem = Session::getFlash('tipo_mensagem');

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gerenciar Usuários</h1>
            <p class="text-gray-600 mt-2">Gerencie os usuários com acesso ao painel administrativo</p>
        </div>
        <button onclick="abrirModal()" class="bg-green-primary text-white px-6 py-3 rounded-lg hover:bg-green-dark transition-all font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
            </svg>
            Novo Usuário
        </button>
    </div>

    <?php if ($mensagem): ?>
        <div class="mb-6 p-4 rounded-lg <?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($erro)): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <!-- Lista de Usuários -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notícias</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($usuarios as $user): ?>
                    <tr class="hover:bg-gray-50 <?= $user['id'] == Session::getUserId() ? 'bg-blue-50' : '' ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($user['nome'], 0, 2)) ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        <?= htmlspecialchars($user['nome']) ?>
                                        <?php if ($user['id'] == Session::getUserId()): ?>
                                            <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Você</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($user['ultimo_acesso']): ?>
                                        <div class="text-sm text-gray-500">
                                            Último acesso: <?= date('d/m/Y H:i', strtotime($user['ultimo_acesso'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($user['tipo'] === 'admin'): ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    👑 Administrador
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    ✏️ Editor
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900"><?= $user['total_noticias'] ?> notícia<?= $user['total_noticias'] != 1 ? 's' : '' ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($user['ativo']): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    ✓ Ativo
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    ✗ Inativo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('d/m/Y', strtotime($user['criado_em'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick='editarUsuario(<?= json_encode($user) ?>)' class="text-blue-600 hover:text-blue-900 mr-3">
                                Editar
                            </button>
                            <?php if ($user['id'] != Session::getUserId()): ?>
                                <button onclick="deletarUsuario(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nome'], ENT_QUOTES) ?>', <?= $user['total_noticias'] ?>)" class="text-red-600 hover:text-red-900">
                                    Deletar
                                </button>
                            <?php else: ?>
                                <span class="text-gray-400 cursor-not-allowed" title="Você não pode deletar seu próprio usuário">
                                    Deletar
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <span class="text-2xl">👑</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Administradores</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?= count(array_filter($usuarios, fn($u) => $u['tipo'] === 'admin')) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span class="text-2xl">✏️</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Editores</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?= count(array_filter($usuarios, fn($u) => $u['tipo'] === 'editor')) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <span class="text-2xl">✓</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Usuários Ativos</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?= count(array_filter($usuarios, fn($u) => $u['ativo'])) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Criar/Editar Usuário -->
<div id="modal-usuario" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full">
        <form method="POST" id="form-usuario">
            <input type="hidden" name="acao" id="acao" value="criar">
            <input type="hidden" name="id" id="usuario-id">
            
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800" id="modal-titulo">Novo Usuário</h2>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                    <input type="text" name="nome" id="usuario-nome" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                           placeholder="Ex: João Silva">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" id="usuario-email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                           placeholder="usuario@fappadua.com.br">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Senha <span id="senha-opcional" class="text-gray-500 text-xs">(deixe em branco para não alterar)</span>
                    </label>
                    <input type="password" name="senha" id="usuario-senha"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary"
                           placeholder="Mínimo 6 caracteres">
                    <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Usuário</label>
                        <select name="tipo" id="usuario-tipo"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary">
                            <option value="editor">✏️ Editor (pode criar notícias)</option>
                            <option value="admin">👑 Administrador (acesso total)</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="ativo" id="usuario-ativo" value="1" checked class="w-5 h-5 text-green-primary rounded">
                            <span class="text-sm font-medium text-gray-700">Usuário ativo</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-t border-gray-200 bg-gray-50 flex gap-3">
                <button type="submit" class="flex-1 bg-green-primary text-white px-6 py-2 rounded-lg hover:bg-green-dark transition-all font-medium">
                    Salvar
                </button>
                <button type="button" onclick="fecharModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto para deletar -->
<form id="form-deletar" method="POST" style="display: none;">
    <input type="hidden" name="acao" value="deletar">
    <input type="hidden" name="id" id="deletar-id">
</form>

<script>
function abrirModal() {
    document.getElementById('modal-usuario').classList.remove('hidden');
    document.getElementById('modal-titulo').textContent = 'Novo Usuário';
    document.getElementById('acao').value = 'criar';
    document.getElementById('form-usuario').reset();
    document.getElementById('usuario-ativo').checked = true;
    document.getElementById('senha-opcional').classList.add('hidden');
    document.getElementById('usuario-senha').required = true;
}

function fecharModal() {
    document.getElementById('modal-usuario').classList.add('hidden');
}

function editarUsuario(user) {
    document.getElementById('modal-usuario').classList.remove('hidden');
    document.getElementById('modal-titulo').textContent = 'Editar Usuário';
    document.getElementById('acao').value = 'editar';
    document.getElementById('usuario-id').value = user.id;
    document.getElementById('usuario-nome').value = user.nome;
    document.getElementById('usuario-email').value = user.email;
    document.getElementById('usuario-senha').value = '';
    document.getElementById('usuario-tipo').value = user.tipo;
    document.getElementById('usuario-ativo').checked = user.ativo == 1;
    document.getElementById('senha-opcional').classList.remove('hidden');
    document.getElementById('usuario-senha').required = false;
}

function deletarUsuario(id, nome, totalNoticias) {
    let mensagem = `Tem certeza que deseja deletar o usuário "${nome}"?\n\n`;
    
    if (totalNoticias > 0) {
        mensagem += `⚠️ Este usuário possui ${totalNoticias} notícia(s) que serão transferidas para você.\n\n`;
    }
    
    mensagem += 'Esta ação não pode ser desfeita.';
    
    if (confirm(mensagem)) {
        document.getElementById('deletar-id').value = id;
        document.getElementById('form-deletar').submit();
    }
}

// Fechar modal ao clicar fora
document.getElementById('modal-usuario').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
