<?php
// $ano já vem do loop
$stmt2 = $pdo->prepare("SELECT * FROM financeiro_arquivos WHERE ano_id = ? ORDER BY id ASC");
$stmt2->execute([$ano['id']]);
$arquivos = $stmt2->fetchAll();

// Contar total de anos no mesmo grupo
if (isset($anos_sub)) {
    $total_anos = count($anos_sub);
} elseif (isset($anos_secao)) {
    $total_anos = count($anos_secao);
} else {
    $total_anos = 1;
}
?>
<div class="bg-gray-100 rounded-lg p-3 mb-2 border border-gray-200">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="font-semibold text-gray-700" id="ano_<?= $ano['id'] ?>_titulo"><?= $ano['ano'] ?></span>
            <button onclick="editarAno(<?= $ano['id'] ?>, <?= $ano['ano'] ?>)" class="text-gray-400 hover:text-blue-600" title="Editar ano">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($ano_idx > 0): ?>
            <form method="POST" class="inline"><input type="hidden" name="action" value="move_ano"><input type="hidden" name="ano_id" value="<?= $ano['id'] ?>"><input type="hidden" name="direcao" value="up">
                <button class="p-1 text-gray-400 hover:text-blue-600 text-xs" title="Mover para cima">▲</button>
            </form>
            <?php endif; ?>
            <?php if ($ano_idx < $total_anos - 1): ?>
            <form method="POST" class="inline"><input type="hidden" name="action" value="move_ano"><input type="hidden" name="ano_id" value="<?= $ano['id'] ?>"><input type="hidden" name="direcao" value="down">
                <button class="p-1 text-gray-400 hover:text-blue-600 text-xs" title="Mover para baixo">▼</button>
            </form>
            <?php endif; ?>
            <span class="text-gray-300">|</span>
            <button onclick="abrirModalDocumento(<?= $ano['id'] ?>)" class="inline-flex items-center gap-1 bg-green-primary text-white px-3 py-1 rounded text-xs font-semibold hover:bg-green-700 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Doc
            </button>
            <form method="POST" class="inline" onsubmit="return confirm('Excluir ano e todos documentos?')"><input type="hidden" name="action" value="delete_ano"><input type="hidden" name="ano_id" value="<?= $ano['id'] ?>">
                <button class="p-1 text-red-500 hover:text-red-700 text-xs" title="Excluir ano">🗑️</button>
            </form>
        </div>
    </div>
    
    <!-- Documentos do ano -->
    <?php if (!empty($arquivos)): ?>
    <div class="space-y-1 ml-6">
        <?php foreach ($arquivos as $arquivo): ?>
        <div class="flex items-center justify-between text-sm bg-white rounded px-3 py-2 border border-gray-100">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                <span class="text-gray-700"><?= htmlspecialchars($arquivo['titulo']) ?></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= $arquivo['arquivo_path'] ?>" target="_blank" class="text-blue-500 hover:text-blue-700" title="Visualizar documento">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <button onclick="editarDocumento(<?= $arquivo['id'] ?>, '<?= addslashes($arquivo['titulo']) ?>')" class="text-yellow-500 hover:text-yellow-700" title="Editar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <form method="POST" class="inline" onsubmit="return confirm('Excluir documento?')"><input type="hidden" name="action" value="delete_arquivo"><input type="hidden" name="arquivo_id" value="<?= $arquivo['id'] ?>">
                    <button class="text-red-400 hover:text-red-600" title="Excluir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-gray-400 text-xs ml-6 italic">Nenhum documento. Clique em "+ Doc" para adicionar.</p>
    <?php endif; ?>
</div>

<script>
function editarAno(id, anoAtual) {
    const el = document.getElementById('ano_' + id + '_titulo');
    const form = document.createElement('form');
    form.method = 'POST';
    form.className = 'flex items-center gap-2';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_ano">
        <input type="hidden" name="ano_id" value="${id}">
        <input type="number" name="ano" value="${anoAtual}" min="2000" max="2100" class="border rounded px-2 py-1 text-sm w-20" required>
        <button type="submit" class="bg-green-primary text-white px-2 py-1 rounded text-xs hover:bg-green-700">OK</button>
        <button type="button" onclick="location.reload()" class="bg-gray-300 px-2 py-1 rounded text-xs hover:bg-gray-400">X</button>
    `;
    el.replaceWith(form);
    form.querySelector('input[name="ano"]').focus();
}

function editarDocumento(id, tituloAtual) {
    document.getElementById('modal_edit_arquivo_id').value = id;
    document.getElementById('modal_edit_titulo').value = tituloAtual;
    document.getElementById('modalEditDocumento').classList.remove('hidden');
}
</script>
