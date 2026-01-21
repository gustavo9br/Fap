<?php
// Script para gerar hashes de senha corretos

// Senha para admin
$senha_admin = 'admin123';
$hash_admin = password_hash($senha_admin, PASSWORD_DEFAULT);
echo "Hash Admin: $hash_admin\n\n";

// Senha para editor
$senha_editor = 'editor123';
$hash_editor = password_hash($senha_editor, PASSWORD_DEFAULT);
echo "Hash Editor: $hash_editor\n\n";

// Testar se funciona
if (password_verify($senha_admin, $hash_admin)) {
    echo "✓ Hash admin OK\n";
}
if (password_verify($senha_editor, $hash_editor)) {
    echo "✓ Hash editor OK\n";
}
