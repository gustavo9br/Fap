# Sistema de Gerenciamento Pró-Gestão

## Descrição

Sistema completo para gerenciamento do conteúdo da página "Transparência Pró-Gestão" no site FAP Pádua. O administrador pode criar seções personalizadas com cards contendo ícones, títulos e conteúdo (links externos ou arquivos PDF).

## Características

### Frontend (Página Pública)
- **URL**: https://padua.fap.rj.gov.br/progestao
- Banner com breadcrumb e título em padrão azul
- Seções com cores alternadas automaticamente (#ebeced e #f5f5f5)
- Cards responsivos com ícones e links/arquivos
- Design similar à imagem de referência fornecida

### Painel Administrativo
- **URL**: /admin/progestao.php
- Gerenciamento completo de seções e cards
- Arrastar e soltar para reordenar seções
- Upload de arquivos PDF
- Links externos
- Ícones personalizáveis (emojis)
- Ativação/desativação de conteúdo

## Estrutura do Banco de Dados

### Tabela: `progestao_secoes`
- `id` - Identificador único
- `titulo` - Título da seção
- `ordem` - Ordem de exibição
- `ativo` - Status (1 = ativo, 0 = inativo)
- `criado_em` - Data de criação
- `atualizado_em` - Data de atualização

### Tabela: `progestao_cards`
- `id` - Identificador único
- `secao_id` - Referência à seção
- `titulo` - Título do card
- `icone` - Emoji do card
- `tipo_conteudo` - 'link' ou 'arquivo'
- `link` - URL (quando tipo_conteudo = 'link')
- `arquivo` - Nome do arquivo PDF (quando tipo_conteudo = 'arquivo')
- `ordem` - Ordem de exibição
- `ativo` - Status (1 = ativo, 0 = inativo)
- `criado_em` - Data de criação
- `atualizado_em` - Data de atualização

## Arquivos Criados/Modificados

### Banco de Dados
- `/database/progestao_schema.sql` - Schema do banco de dados

### Admin
- `/admin/progestao.php` - Listagem de seções e cards
- `/admin/progestao_secao_form.php` - Formulário de seção
- `/admin/progestao_card_form.php` - Formulário de card
- `/admin/progestao_ajax.php` - Endpoints AJAX para ações
- `/admin/includes/header.php` - Adicionado link no menu lateral

### Frontend
- `/progestao.php` - Página pública atualizada com conteúdo dinâmico

### Uploads
- `/uploads/progestao/` - Diretório para armazenar arquivos PDF

## Como Usar

### Criar uma Seção

1. Acesse o painel admin: `/admin/progestao.php`
2. Clique em "Nova Seção"
3. Preencha o título (ex: "Regimentos internos, atas e cronograma das reuniões")
4. Marque como ativa
5. Clique em "Criar Seção"

### Adicionar Cards à Seção

1. Na listagem de seções, clique em "+ Adicionar Card"
2. Selecione a seção
3. Digite o título do card
4. Escolha um ícone (ou cole um emoji personalizado)
5. Selecione o tipo de conteúdo:
   - **Link Externo**: Cole a URL completa
   - **Arquivo PDF**: Faça upload do arquivo
6. Marque como ativo
7. Clique em "Criar Card"

### Reordenar Seções

Use as setas ↑ ↓ ao lado de cada seção para alterar a ordem de exibição

### Editar/Excluir

- **Seções**: Use os botões "Editar" ou "Excluir" no cabeçalho da seção
- **Cards**: Use os ícones de lápis (editar) ou lixeira (excluir) em cada card

## Comportamento Automático

### Cores Alternadas
As seções alternam automaticamente entre as cores:
- Primeira seção: #ebeced (cinza escuro)
- Segunda seção: #f5f5f5 (cinza claro)
- Terceira seção: #ebeced
- E assim sucessivamente...

### Ordenação
- Novas seções são adicionadas ao final
- Novos cards são adicionados ao final de cada seção
- É possível reordenar posteriormente

### Visibilidade
- Apenas seções e cards marcados como "ativos" aparecem no site
- Seções sem cards ativos não são exibidas

## Ícones Sugeridos

O sistema oferece uma paleta com ícones comuns:
- 📄 Documento
- 📊 Gráfico/Relatório
- ✅ Verificação
- 📋 Clipboard
- 💰 Financeiro
- 🏛️ Governança
- 📁 Pasta
- 📈 Crescimento
- 🔍 Lupa/Busca
- ⚖️ Balança/Justiça
- 👥 Pessoas
- 🎯 Alvo/Meta
- 📝 Nota/Edição
- 💼 Pasta executiva
- 🔐 Segurança
- 📌 Pin

Você também pode usar qualquer emoji personalizado!

## Segurança

- Apenas usuários logados no admin podem gerenciar o conteúdo
- Upload aceita apenas arquivos PDF
- Validação de URLs para links externos
- Proteção contra SQL Injection via prepared statements
- Arquivos são salvos com nomes únicos (evita sobrescrita)

## Exemplo de Uso

**Exemplo de estrutura típica:**

**Seção 1**: "Regimentos internos, atas e cronograma das reuniões"
- Card: "Conselho Municipal Previdenciário - CMP" (📄, PDF)
- Card: "Conselho Fiscal" (📋, Link)
- Card: "Comitê de Investimentos" (💼, PDF)

**Seção 2**: "Demonstrações financeiras e contábeis"
- Card: "Balancete de Verificação" (✅, PDF)
- Card: "Balancete Financeiro" (💰, PDF)
- Card: "Comparativo de Despesa" (📊, PDF)

**Seção 3**: "Informações Complementares"
- Card: "Diretoria executiva" (👥, Link)
- Card: "Conselho Deliberativo" (🏛️, PDF)
- Card: "Definição de limites" (⚖️, Link)

## Suporte

Para dúvidas ou problemas, verifique os logs de erro do PHP ou entre em contato com o desenvolvedor.
