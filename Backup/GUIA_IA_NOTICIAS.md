# 🤖 Sistema de IA para Criação de Notícias

## ✅ Sistema Implementado!

Agora você tem um **editor de notícias profissional** igual ao WordPress, com integração de **Inteligência Artificial** para gerar conteúdo automaticamente!

## 📝 Como Criar Notícias

### 1. Acessar o Editor

```
https://padua.fap.rj.gov.br/admin/noticias.php
```

Clique em **"+ Nova Notícia"**

### 2. Configurar API de IA (PRIMEIRO PASSO - Apenas Admin)

```
https://padua.fap.rj.gov.br/admin/configuracoes.php
```

**Opções de IA disponíveis:**

#### 🔵 OpenAI (ChatGPT) - RECOMENDADO
- **Provider:** `openai`
- **Modelo:** `gpt-4o-mini` (mais barato) ou `gpt-4o` (mais inteligente)
- **API Key:** Obtenha em [platform.openai.com/api-keys](https://platform.openai.com/api-keys)
- **Custo:** ~$0.15 por 1M tokens (gpt-4o-mini) ou ~$2.50 por 1M tokens (gpt-4o)

#### 🟣 Anthropic Claude
- **Provider:** `anthropic`
- **Modelo:** `claude-3-5-sonnet-20241022`
- **API Key:** Obtenha em [console.anthropic.com](https://console.anthropic.com)
- **Custo:** ~$3 por 1M tokens

#### 🔴 Google Gemini
- **Provider:** `gemini`
- **Modelo:** `gemini-pro`
- **API Key:** Obtenha em [makersuite.google.com/app/apikey](https://makersuite.google.com/app/apikey)
- **Custo:** GRATUITO até 60 requisições/minuto

### 3. Usar o Assistente de IA

No editor de notícias, você verá um **painel roxo** com o Assistente de IA:

#### 📝 Gerar Notícia Completa

Digite no campo:
```
Escreva uma notícia sobre a nova lei de aposentadoria dos servidores 
públicos de Pádua, destacando os principais benefícios e mudanças.
```

Clique em **"Gerar com IA"** → Conteúdo completo será criado!

#### 🎯 Gerar Apenas Título

Digite o tema e clique em **"Gerar Título"**:
```
Nova lei de aposentadoria dos servidores
```

Resultado:
```
Servidores de Pádua terão novos benefícios com lei de aposentadoria
```

#### 📋 Gerar Apenas Resumo

Escreva o conteúdo primeiro, depois clique em **"Gerar Resumo"** → IA resume automaticamente!

## 🎨 Editor de Texto (TinyMCE)

Igual ao WordPress! Ferramentas disponíveis:

- **Formatação:** Negrito, Itálico, Sublinhado
- **Títulos:** H2, H3, H4
- **Listas:** Numeradas e com marcadores
- **Links:** Inserir links externos
- **Imagens:** Upload e inserção de imagens no texto
- **Tabelas:** Criar tabelas
- **Código fonte:** Editar HTML diretamente
- **Alinhamento:** Esquerda, centro, direita, justificado
- **Cores:** Mudar cor do texto e fundo

## 📸 Imagem de Destaque

1. Na sidebar direita, seção **"Imagem de Destaque"**
2. Clique em **"Escolher arquivo"**
3. Selecione JPG, PNG ou WebP (máx 5MB)
4. Imagem aparecerá na listagem de notícias

## 🔄 Status da Notícia

- **Rascunho:** Não aparece no site (apenas você vê)
- **Publicado:** Visível para todos no site
- **Arquivado:** Oculto mas não deletado

## 🏷️ Categorias

Organize suas notícias:
- Institucional
- Servidores
- Aposentados
- Legislação
- Eventos

## 📊 Exemplos Práticos

### Exemplo 1: Notícia sobre Evento

**No Assistente de IA, digite:**
```
Escreva uma notícia sobre o seminário de educação previdenciária 
que acontecerá no dia 20 de janeiro de 2025 no auditório da 
prefeitura de Pádua. O evento é gratuito e aberto aos servidores.
```

**IA gera:**
```html
<p>A FAPPádua realizará no dia 20 de janeiro de 2025 o 1º 
Seminário de Educação Previdenciária, evento gratuito destinado 
aos servidores públicos municipais.</p>

<p>O seminário acontecerá no auditório da Prefeitura Municipal 
de Pádua, das 8h às 17h, e abordará temas como planejamento 
de aposentadoria, cálculo de benefícios e direitos previdenciários.</p>

<h2>Inscrições</h2>
<p>As inscrições estão abertas e podem ser feitas pelo site 
ou presencialmente na sede da FAPPádua...</p>
```

### Exemplo 2: Comunicado Institucional

**Prompt:**
```
Comunicado sobre a suspensão do atendimento presencial nos 
dias 24 e 25 de dezembro devido ao recesso de fim de ano.
```

### Exemplo 3: Mudança de Legislação

**Prompt:**
```
Notícia sobre a aprovação da nova lei municipal que altera 
as regras de contribuição previdenciária dos servidores efetivos.
```

## 🔐 Permissões

### Editor
- ✅ Criar notícias
- ✅ Editar suas próprias notícias
- ✅ Usar IA
- ❌ Não pode acessar configurações

### Admin
- ✅ Tudo que o Editor pode
- ✅ Editar notícias de outros
- ✅ Deletar notícias
- ✅ Configurar API de IA
- ✅ Gerenciar usuários

## 💡 Dicas de Uso

### Para melhores resultados com IA:

1. **Seja específico:** Inclua detalhes importantes no prompt
2. **Contexto:** Mencione data, local, valores, nomes
3. **Tom:** Especifique se quer formal, informal, técnico
4. **Tamanho:** "Escreva uma notícia curta/média/longa sobre..."

### Exemplos de prompts ruins vs bons:

❌ **Ruim:** "Notícia sobre aposentadoria"

✅ **Bom:** "Escreva uma notícia de 300 palavras explicando como funciona a aposentadoria por tempo de contribuição para servidores municipais de Pádua, incluindo requisitos e documentação necessária."

## 🚨 Troubleshooting

### "API Key da IA não configurada"
→ Admin precisa configurar em **Configurações**

### "Erro OpenAI: Invalid API key"
→ Verifique se a API Key está correta (começa com `sk-`)

### "Botão Gerar não funciona"
→ Verifique console do navegador (F12) para erros

### "Imagem não faz upload"
→ Verifique se o arquivo é menor que 5MB e é JPG/PNG/WebP

## 📈 Custos Estimados

**OpenAI GPT-4o-mini (Recomendado):**
- 1 notícia completa ≈ $0.001 (menos de 1 centavo!)
- 1000 notícias ≈ $1.00
- **MUITO BARATO!**

**OpenAI GPT-4o (Mais inteligente):**
- 1 notícia completa ≈ $0.02
- 1000 notícias ≈ $20.00

**Google Gemini:**
- GRATUITO! (até 60 requisições/minuto)

## 🎯 Próximos Passos

1. ✅ Configure a API de IA em **Configurações**
2. ✅ Crie sua primeira notícia teste
3. ✅ Experimente o Assistente de IA
4. ✅ Publique notícias reais!

---

**Suporte:** Em caso de dúvidas, consulte o arquivo [README_ADMIN.md](/root/FAP/README_ADMIN.md)
