# 🏛️ FAP Pádua - Sistema de Gerenciamento

Sistema completo de gerenciamento de conteúdo para o Instituto de Previdência dos Servidores do Município de Pádua.

## 📋 Índice

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Sistema de Segurança](#sistema-de-segurança)
- [Uso do Sistema](#uso-do-sistema)
- [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
- [API de Downloads](#api-de-downloads)

## ✨ Características

### Sistema de Autenticação
- ✅ Login seguro com senha hash (bcrypt)
- ✅ Dois níveis de acesso: **Admin** e **Editor**
- ✅ Controle de sessões
- ✅ Log de atividades

### Gerenciamento de Notícias
- ✅ CRUD completo (Criar, Ler, Atualizar, Deletar)
- ✅ Upload de imagem de destaque
- ✅ Categorização de notícias
- ✅ Sistema de rascunhos
- ✅ Contador de visualizações
- ✅ Slug automático para URLs amigáveis

### Sistema de Arquivos Seguro
- ✅ Upload de PDFs e documentos
- ✅ **Acesso por token único** - arquivos não podem ser listados
- ✅ Contador de downloads
- ✅ Controle de acesso via .htaccess
- ✅ Log de downloads

### Segurança
- ✅ Proteção contra listagem de diretórios
- ✅ Acesso a arquivos apenas via token
- ✅ SQL Injection protection (PDO)
- ✅ XSS protection
- ✅ CSRF protection
- ✅ Senhas com hash bcrypt
- ✅ Logs de auditoria

## 🔧 Requisitos

- PHP 8.2+
- MySQL 8.0+
- Apache com mod_rewrite
- Tailwind CSS (via CDN)

## 📦 Instalação

### 1. Banco de Dados

O banco de dados já foi criado e configurado. Credenciais:

```
Host: mysql_mysql.1.tkc717a6k62lynwkon6vwn83o
Database: fap_padua
User: root
Password: BAAE3A32D667F546851BED3777633
```

Tabelas criadas:
- `usuarios` - Usuários do sistema
- `noticias` - Notícias e artigos
- `arquivos` - Documentos e PDFs
- `sessoes` - Controle de sessões
- `logs_atividades` - Auditoria

### 2. Estrutura de Diretórios

```
/root/FAP/
├── admin/                  # Painel administrativo
│   ├── includes/          # Header e footer do admin
│   ├── login.php          # Página de login
│   ├── index.php          # Dashboard
│   ├── noticias.php       # Gerenciar notícias
│   └── logout.php         # Logout
├── config/                # Configurações
│   ├── database.php       # Conexão com banco
│   └── session.php        # Gerenciamento de sessões
├── uploads/               # Arquivos protegidos
│   └── .htaccess          # Bloqueio de acesso direto
├── imagens/               # Imagens públicas
└── download.php           # Script de download seguro
```

## 🔐 Sistema de Segurança

### Proteção de Arquivos

Os arquivos na pasta `/uploads/` **NÃO PODEM SER ACESSADOS DIRETAMENTE**.

**❌ BLOQUEADO:**
```
https://padua.fap.rj.gov.br/uploads/documento.pdf
https://padua.fap.rj.gov.br/uploads/
```

**✅ PERMITIDO (via token):**
```
https://padua.fap.rj.gov.br/download.php?token=a1b2c3d4e5f6...
```

### Como Funciona

1. **Upload do Arquivo**: Ao fazer upload, um token único é gerado
2. **Armazenamento**: Arquivo salvo em `/uploads/` (protegido)
3. **Registro no BD**: Token, nome, caminho salvos na tabela `arquivos`
4. **Acesso**: Apenas via `download.php?token=XXX`

### Configuração .htaccess em /uploads/

```apache
# Bloquear acesso direto a este diretório
Options -Indexes

# Negar acesso a todos os arquivos
<FilesMatch ".*">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

## 👤 Uso do Sistema

### Credenciais de Acesso

**Administrador:**
- Email: `admin@fappadua.com.br`
- Senha: `admin123`
- Permissões: Acesso total

**Editor:**
- Email: `editor@fappadua.com.br`
- Senha: `editor123`
- Permissões: Apenas notícias

### Acessar o Painel

```
https://padua.fap.rj.gov.br/admin/login.php
```

### Alterar Senhas (Recomendado)

Execute no MySQL:

```sql
-- Gerar novo hash de senha
SELECT PASSWORD('sua_senha_nova');

-- Atualizar senha
UPDATE usuarios 
SET senha = '$2y$10$...' 
WHERE email = 'admin@fappadua.com.br';
```

Ou use PHP:

```php
<?php
echo password_hash('sua_senha_nova', PASSWORD_DEFAULT);
?>
```

## 📊 Estrutura do Banco de Dados

### Tabela: usuarios

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID único |
| nome | VARCHAR(100) | Nome completo |
| email | VARCHAR(100) | Email (único) |
| senha | VARCHAR(255) | Hash da senha |
| tipo | ENUM | 'admin' ou 'editor' |
| ativo | BOOLEAN | Usuário ativo |
| criado_em | TIMESTAMP | Data de criação |
| ultimo_acesso | TIMESTAMP | Último login |

### Tabela: noticias

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID único |
| titulo | VARCHAR(255) | Título da notícia |
| slug | VARCHAR(255) | URL amigável (único) |
| resumo | TEXT | Resumo/chamada |
| conteudo | LONGTEXT | Conteúdo completo |
| imagem_destaque | VARCHAR(255) | Caminho da imagem |
| categoria | VARCHAR(50) | Categoria |
| autor_id | INT | ID do autor |
| status | ENUM | 'rascunho', 'publicado', 'arquivado' |
| visualizacoes | INT | Contador de views |
| publicado_em | TIMESTAMP | Data de publicação |

### Tabela: arquivos

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID único |
| titulo | VARCHAR(255) | Título do arquivo |
| nome_arquivo | VARCHAR(255) | Nome original |
| caminho | VARCHAR(500) | Caminho no servidor |
| **token** | VARCHAR(64) | **Token único de acesso** |
| tipo_arquivo | VARCHAR(50) | MIME type |
| tamanho | BIGINT | Tamanho em bytes |
| categoria | VARCHAR(100) | Categoria |
| downloads | INT | Contador de downloads |
| usuario_id | INT | Quem fez upload |

## 📥 API de Downloads

### Endpoint

```
GET /download.php?token={TOKEN}
```

### Exemplo de Uso

**HTML:**
```html
<a href="download.php?token=abc123def456">
    Baixar Documento
</a>
```

**PHP (gerar link):**
```php
<?php
// Buscar token do arquivo
$stmt = $db->prepare("SELECT token FROM arquivos WHERE id = ?");
$stmt->execute([$arquivo_id]);
$token = $stmt->fetchColumn();

// Gerar link
$link_download = "download.php?token=" . $token;
?>

<a href="<?= $link_download ?>">Baixar</a>
```

### Resposta

- **200 OK**: Arquivo enviado para download
- **400 Bad Request**: Token inválido
- **404 Not Found**: Arquivo não encontrado
- **500 Internal Server Error**: Erro no servidor

### Logging

Cada download é registrado em `logs_atividades`:

```sql
SELECT * FROM logs_atividades 
WHERE acao = 'download' 
ORDER BY criado_em DESC;
```

## 🔒 Boas Práticas de Segurança

1. **Alterar senhas padrão imediatamente**
2. **Usar HTTPS** (já configurado via Traefik)
3. **Fazer backup regular** do banco de dados
4. **Monitorar logs de atividades**
5. **Limpar sessões expiradas periodicamente**
6. **Validar todos os uploads** (tipo, tamanho, extensão)

### Limpar Sessões Antigas

```sql
-- Executar diariamente via cron
DELETE FROM sessoes WHERE expira_em < NOW();
```

### Backup do Banco

```bash
# Fazer backup
docker exec mysql_mysql.1.tkc717a6k62lynwkon6vwn83o \
  mysqldump -uroot -pBAAE3A32D667F546851BED3777633 fap_padua \
  > backup_$(date +%Y%m%d).sql

# Restaurar backup
docker exec -i mysql_mysql.1.tkc717a6k62lynwkon6vwn83o \
  mysql -uroot -pBAAE3A32D667F546851BED3777633 fap_padua \
  < backup_20241216.sql
```

## 📝 Próximos Passos

1. ✅ Criar formulário de notícias (CRUD)
2. ✅ Criar sistema de upload de arquivos
3. ⏳ Criar gerenciamento de usuários (admin)
4. ⏳ Implementar editor WYSIWYG para notícias
5. ⏳ Sistema de comentários (opcional)
6. ⏳ Newsletter (opcional)

## 🆘 Suporte

Para suporte técnico, consulte os logs:

```bash
# Ver logs do PHP
tail -f /var/log/apache2/error.log

# Ver logs do MySQL
docker logs mysql_mysql.1.tkc717a6k62lynwkon6vwn83o
```

---

**Desenvolvido para FAP Pádua** | Versão 1.0.0 | Dezembro 2025
