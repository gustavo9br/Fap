# SISTEMA DE CONSELHOS E COMITÊS - DOCUMENTAÇÃO

## 📋 RESUMO DO SISTEMA

Sistema completo para gerenciamento de Conselhos e Comitês com painel administrativo integrado.

## 🗄️ ESTRUTURA DO BANCO DE DADOS

### Tabelas Criadas

1. **conselhos**
   - id, nome, slug, descricao, cor_banner, icone, ordem, ativo
   - 4 conselhos pré-cadastrados

2. **conselho_secoes**
   - id, conselho_id, titulo, tipo (calendario/atas/documentos/outros), ordem, ativo
   - Permite criar seções ilimitadas (ex: Calendário de Reuniões, Atas, etc.)

3. **conselho_anos**
   - id, secao_id, ano, ordem
   - Anos organizados por seção

4. **conselho_arquivos**
   - id, ano_id, titulo, arquivo_path, data_upload, ordem
   - Arquivos vinculados a cada ano

## 📁 ARQUIVOS CRIADOS

### Front-end (Site Público)

1. **conselhos-e-comites.php** (SIMPLIFICADO)
   - Grid de 3 colunas com cards
   - Busca dinâmica dos conselhos no banco
   - Botão "Acessar" que leva para página individual

2. **conselho.php** (Página Individual)
   - Banner com cor personalizável por conselho
   - Breadcrumb dinâmico
   - Descrição do conselho
   - Seções com accordion (anos expansíveis)
   - Lista de arquivos por ano com ícone de download
   - Layout inspirado em: https://goianiaprev.go.gov.br/cfp/

### Admin (Painel Administrativo)

1. **admin/conselhos.php**
   - Lista todos os conselhos em grid
   - Click para editar cada um

2. **admin/editar-conselho.php**
   - Gerenciamento completo do conselho
   - Adicionar/remover seções
   - Adicionar/remover anos
   - Upload de arquivos (PDF, DOC, DOCX, XLS, XLSX)
   - Excluir arquivos
   - Interface intuitiva com formulários

3. **admin/includes/header.php**
   - Adicionado menu "Conselhos" com ícone de pessoas

## 🎨 CONSELHOS PRÉ-CADASTRADOS

1. **Conselho Municipal Previdenciário – CMP**
   - Slug: conselho-administrativo
   - Cor: from-blue-600 to-blue-800
   - Ícone: 👥

2. **Conselho Fiscal – CF**
   - Slug: conselho-fiscal
   - Cor: from-green-600 to-green-800
   - Ícone: 📊

3. **Comitê de Investimentos**
   - Slug: comite-investimentos
   - Cor: from-purple-600 to-purple-800
   - Ícone: 💰

4. **Comitê de Auditoria**
   - Slug: comite-auditoria
   - Cor: from-orange-600 to-orange-800
   - Ícone: 🔍

## 🔄 FLUXO DE TRABALHO DO ADMIN

1. Admin acessa: `/admin/conselhos`
2. Clica no conselho que deseja gerenciar
3. Na tela de edição:
   - **Adiciona seções** (ex: "Calendário de Reuniões 2025")
   - Para cada seção, **adiciona anos** (ex: 2025, 2024, 2023)
   - Para cada ano, **faz upload de arquivos** com títulos descritivos
4. Os arquivos são salvos em `/uploads/conselhos/`
5. Automaticamente aparecem no site público

## 🌐 URLs DO SISTEMA

### Públicas
- `/conselhos-e-comites.php` - Lista de conselhos
- `/conselho.php?slug=conselho-fiscal` - Página do Conselho Fiscal
- `/conselho.php?slug=conselho-administrativo` - Página do CMP
- `/conselho.php?slug=comite-investimentos` - Página do Comitê
- `/conselho.php?slug=comite-auditoria` - Página do Comitê

### Admin
- `/admin/conselhos` - Gerenciar conselhos
- `/admin/editar-conselho?id=2` - Editar Conselho Fiscal

## ✨ FUNCIONALIDADES

### Para o Admin
- ✅ Criar seções ilimitadas por conselho
- ✅ Adicionar anos a cada seção
- ✅ Upload de arquivos (PDF, DOC, XLS)
- ✅ Excluir arquivos/anos/seções
- ✅ Organizar ordem das seções
- ✅ Interface drag-free (sem complicações)

### Para o Usuário Final
- ✅ Visualização limpa em cards
- ✅ Accordion expansível por ano
- ✅ Download direto dos arquivos
- ✅ Contadores de documentos
- ✅ Banner colorido personalizado
- ✅ Breadcrumb funcional
- ✅ Responsivo mobile

## 🎯 PRÓXIMOS PASSOS SUGERIDOS

1. Adicionar editor WYSIWYG para descrições dos conselhos
2. Permitir reordenação de arquivos (drag and drop)
3. Adicionar filtros por ano na página pública
4. Estatísticas de downloads no admin
5. Notificações quando novos documentos são adicionados

## 📝 OBSERVAÇÕES TÉCNICAS

- Todos os uploads vão para `/uploads/conselhos/`
- Arquivos são renomeados com timestamp para evitar conflitos
- DELETE CASCADE garante que ao excluir ano, arquivos são removidos
- Validação de tipos de arquivo no backend
- Sistema de mensagens de sucesso após operações
- Confirmação antes de excluir (JavaScript)

## 🔒 SEGURANÇA

- Verificação de sessão admin em todas as páginas
- Validação de tipos de arquivo permitidos
- Proteção contra SQL injection (prepared statements)
- Sanitização de HTML (htmlspecialchars)

---

**Data de Implementação:** 21 de dezembro de 2025
**Desenvolvido para:** FAP PADUA - Sistema de Gerenciamento
