<?php
// Arquivo de processamento de formulário de contato

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar dados
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // Validar campos obrigatórios
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        header('Location: contato.php?error=campos_obrigatorios');
        exit;
    }
    
    // Validar e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: contato.php?error=email_invalido');
        exit;
    }
    
    // Aqui você pode:
    // 1. Enviar e-mail
    // 2. Salvar no banco de dados
    // 3. Integrar com sistema de tickets
    
    // Exemplo de envio de e-mail
    $to = 'contato@previdencia.gov.br';
    $email_subject = "Contato do Site - $subject";
    $email_body = "Nome: $name\n";
    $email_body .= "E-mail: $email\n";
    $email_body .= "Telefone: $phone\n\n";
    $email_body .= "Mensagem:\n$message";
    
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    // Descomentar para enviar e-mail real
    // mail($to, $email_subject, $email_body, $headers);
    
    // Redirecionar com sucesso
    header('Location: contato.php?success=1');
    exit;
} else {
    header('Location: contato.php');
    exit;
}
