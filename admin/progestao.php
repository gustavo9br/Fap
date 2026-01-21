<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar se está logado
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Gerenciar Pró-Gestão';
include 'includes/header.php';

$db = Database::getInstance()->getConnection();

// Buscar todas as seções com seus cards
try {
    $stmt = $db->query("
        SELECT s.*, 
               (SELECT COUNT(*) FROM progestao_cards WHERE secao_id = s.id AND ativo = 1) as total_cards
        FROM progestao_secoes s
        ORDER BY s.ordem ASC, s.id ASC
    ");
    $secoes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar seções: " . $e->getMessage());
    $secoes = [];
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Cabeçalho -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Pró-Gestão</h1>
            <p class="text-gray-600 mt-2">Gerenciar seções e cards da página Transparência Pró-Gestão</p>
        </div>
        <button onclick="window.location.href='progestao_secao_form.php'" 
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Seção
        </button>
    </div>

    <!-- Prévia do site -->
    <div class="mb-6">
        <a href="/progestao.php" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Ver página no site
        </a>
    </div>

    <!-- Mensagem de sucesso -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><?= htmlspecialchars($_SESSION['success_message']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (empty($secoes)): ?>
        <!-- Estado vazio -->
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Nenhuma seção cadastrada</h3>
            <p class="text-gray-500 mb-6">Comece criando sua primeira seção para organizar o conteúdo da página Pró-Gestão</p>
            <button onclick="window.location.href='progestao_secao_form.php'" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg">
                Criar Primeira Seção
            </button>
        </div>
    <?php else: ?>
        <!-- Lista de seções -->
        <div class="space-y-4">
            <?php foreach ($secoes as $secao): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Cabeçalho da seção -->
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col gap-1">
                                <button onclick="moverSecao(<?= $secao['id'] ?>, 'cima')" 
                                        class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>
                                <button onclick="moverSecao(<?= $secao['id'] ?>, 'baixo')" 
                                        class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($secao['titulo']) ?></h3>
                                <p class="text-sm text-gray-500"><?= $secao['total_cards'] ?> card(s)</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-sm <?= $secao['ativo'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= $secao['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                            <button onclick="window.location.href='progestao_card_form.php?secao_id=<?= $secao['id'] ?>'" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                + Adicionar Card
                            </button>
                            <button onclick="window.location.href='progestao_secao_form.php?id=<?= $secao['id'] ?>'" 
                                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                                Editar
                            </button>
                            <button onclick="excluirSecao(<?= $secao['id'] ?>)" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                                Excluir
                            </button>
                        </div>
                    </div>

                    <!-- Cards da seção -->
                    <?php
                    $stmt_cards = $db->prepare("
                        SELECT * FROM progestao_cards 
                        WHERE secao_id = ? 
                        ORDER BY ordem ASC, id ASC
                    ");
                    $stmt_cards->execute([$secao['id']]);
                    $cards = $stmt_cards->fetchAll();
                    ?>
                    
                    <?php if (empty($cards)): ?>
                        <div class="p-6 text-center text-gray-500">
                            <p>Nenhum card cadastrado nesta seção</p>
                        </div>
                    <?php else: ?>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($cards as $card): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="text-3xl">
                                                    <?= $card['icone'] ?: '📄' ?>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-gray-800 text-sm">
                                                        <?= htmlspecialchars($card['titulo']) ?>
                                                    </h4>
                                                    <p class="text-xs text-gray-500">
                                                        <?= $card['tipo_conteudo'] === 'link' ? 'Link externo' : 'Arquivo PDF' ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <button onclick="window.location.href='progestao_card_form.php?id=<?= $card['id'] ?>'" 
                                                        class="text-blue-600 hover:text-blue-800 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button onclick="excluirCard(<?= $card['id'] ?>)" 
                                                        class="text-red-600 hover:text-red-800 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <?php if ($card['tipo_conteudo'] === 'link'): ?>
                                            <a href="<?= htmlspecialchars($card['link']) ?>" 
                                               target="_blank" 
                                               class="text-xs text-blue-600 hover:underline block truncate">
                                                <?= htmlspecialchars($card['link']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-600 block truncate">
                                                <?= htmlspecialchars($card['arquivo']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function moverSecao(id, direcao) {
    if (confirm('Deseja mover esta seção?')) {
        const formData = new FormData();
        formData.append('action', 'mover_secao');
        formData.append('id', id);
        formData.append('direcao', direcao);

        fetch('progestao_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao mover seção');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar requisição');
        });
    }
}

function excluirSecao(id) {
    if (confirm('Deseja realmente excluir esta seção? Todos os cards associados serão excluídos também.')) {
        const formData = new FormData();
        formData.append('action', 'excluir_secao');
        formData.append('id', id);

        fetch('progestao_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao excluir seção');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar requisição');
        });
    }
}

function excluirCard(id) {
    if (confirm('Deseja realmente excluir este card?')) {
        const formData = new FormData();
        formData.append('action', 'excluir_card');
        formData.append('id', id);

        fetch('progestao_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao excluir card');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao processar requisição');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
