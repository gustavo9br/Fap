<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Se já estiver logado, redirecionar para dashboard
if (Session::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT id, nome, email, senha, tipo, ativo 
                FROM usuarios 
                WHERE email = ? AND ativo = 1
            ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                Session::set('user_id', $usuario['id']);
                Session::set('user_name', $usuario['nome']);
                Session::set('user_email', $usuario['email']);
                Session::set('user_type', $usuario['tipo']);
                
                // Atualizar último acesso
                $stmt = $db->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?");
                $stmt->execute([$usuario['id']]);
                
                // Registrar log
                $stmt = $db->prepare("
                    INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) 
                    VALUES (?, 'login', 'Login realizado com sucesso', ?)
                ");
                $stmt->execute([$usuario['id'], $_SERVER['REMOTE_ADDR']]);
                
                header('Location: index');
                exit;
            } else {
                $erro = 'Email ou senha inválidos.';
                
                // Registrar tentativa falha
                $stmt = $db->prepare("
                    INSERT INTO logs_atividades (acao, descricao, ip_address) 
                    VALUES ('login_falho', ?, ?)
                ");
                $stmt->execute(["Tentativa de login com email: $email", $_SERVER['REMOTE_ADDR']]);
            }
        } catch (PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
            $erro = 'Erro ao processar login. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo FAP Pádua</title>
    <link rel="icon" type="image/png" href="/imagens/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'green-primary': '#2ecc71',
                        'green-dark': '#27ae60',
                        'teal-primary': '#16a085',
                        'teal-dark': '#0e6655',
                    },
                    fontFamily: {
                        'sans': ['Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo e Título -->
            <div class="text-center mb-8">
                <img src="../imagens/fap logo preto.png" alt="FAP Pádua" class="h-20 mx-auto mb-4">
                <h2 class="text-3xl font-bold text-gray-900">Painel Administrativo</h2>
                <p class="mt-2 text-sm text-gray-600">Acesso restrito a usuários autorizados</p>
            </div>

            <!-- Formulário de Login -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <?php if ($erro): ?>
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?= htmlspecialchars($erro) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            required 
                            autocomplete="email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent transition-all"
                            placeholder="seu.email@fappadua.com.br"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        >
                    </div>

                    <div>
                        <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                            Senha
                        </label>
                        <input 
                            type="password" 
                            name="senha" 
                            id="senha" 
                            required
                            autocomplete="current-password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-primary focus:border-transparent transition-all"
                            placeholder="••••••••"
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="lembrar" 
                                id="lembrar" 
                                class="h-4 w-4 text-green-primary focus:ring-green-primary border-gray-300 rounded"
                            >
                            <label for="lembrar" class="ml-2 block text-sm text-gray-700">
                                Lembrar-me
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="#" class="font-medium text-green-primary hover:text-green-dark">
                                Esqueceu a senha?
                            </a>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-green-primary to-teal-primary text-white py-3 px-4 rounded-lg hover:from-green-dark hover:to-teal-dark transition-all duration-300 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        Entrar no Sistema
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="../" class="text-sm text-gray-600 hover:text-green-primary transition-colors">
                        ← Voltar para o site
                    </a>
                </div>
            </div>

            <!-- Informações de Acesso Padrão (remover em produção) -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-xs text-blue-800 font-semibold mb-2">🔐 Credenciais de teste:</p>
                <p class="text-xs text-blue-700"><strong>Admin:</strong> admin@fappadua.com.br / admin123</p>
                <p class="text-xs text-blue-700"><strong>Editor:</strong> editor@fappadua.com.br / editor123</p>
            </div>
        </div>
    </div>
</body>
</html>
