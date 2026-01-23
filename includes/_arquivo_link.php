<?php
// Componente de link para arquivo
// Variável disponível: $arquivo
if (!function_exists('normalizar_caminho_arquivo')) {
    require_once __DIR__ . '/functions.php';
}
?>
<a href="<?php echo htmlspecialchars(normalizar_caminho_arquivo($arquivo['arquivo_path'])); ?>" target="_blank" 
   class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100 hover:border-green-300 transition-all group">
    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <div class="font-medium text-gray-800 group-hover:text-green-700"><?php echo htmlspecialchars($arquivo['titulo']); ?></div>
        <div class="text-xs text-gray-400">PDF</div>
    </div>
    <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
</a>
