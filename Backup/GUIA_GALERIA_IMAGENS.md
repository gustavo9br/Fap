# 📸 Sistema de Galeria de Imagens com IA

## ✨ Funcionalidades

### 1. **Upload Manual de Imagens**
- Suporta múltiplos arquivos simultaneamente
- Formatos aceitos: JPG, JPEG, PNG, WebP, GIF
- Tamanho máximo: 5MB por imagem
- Todas as imagens ficam disponíveis na galeria

### 2. **Geração de Imagens com IA (DALL-E 3)**
- Gera imagens profissionais baseadas no título/descrição da notícia
- Usa OpenAI DALL-E 3 (modelo mais avançado)
- Qualidade: 1024x1024 pixels
- Estilo: Fotografia institucional profissional
- Imagens geradas ficam marcadas com ícone 🤖

### 3. **Galeria Interativa**
- Visualize todas as imagens enviadas
- Selecione qual imagem usar como destaque
- Imagem selecionada fica destacada com borda verde
- Grid responsivo (2/3/4 colunas)
- Modal elegante com scroll

### 4. **IA Automática Completa**
Ao clicar em **"Gerar com IA"**, o sistema cria automaticamente:
- ✅ **Título** da notícia
- ✅ **Conteúdo** completo formatado em HTML
- ✅ **Resumo** de 2-3 frases

## 🎯 Como Usar

### Criando Notícia com IA (Processo Completo)

1. **Digite a descrição** no campo "Assistente de IA":
   ```
   Escreva uma notícia sobre a nova sede da FAP Pádua que será inaugurada em janeiro de 2026, com 3 andares, auditório para 200 pessoas e área de atendimento moderna
   ```

2. **Clique em "Gerar com IA"**
   - ⏳ Aguarde enquanto a IA gera título, conteúdo e resumo
   - ✅ Tudo será preenchido automaticamente

3. **Gerar Imagem com IA** (opcional):
   - Clique no botão **"Gerar Imagem com IA"**
   - A IA criará uma imagem baseada no título/descrição
   - Imagem será automaticamente selecionada como destaque

4. **Ou fazer upload manual**:
   - Clique em **"Fazer Upload"**
   - Selecione uma ou mais imagens do computador
   - Se enviar apenas 1 imagem, ela será selecionada automaticamente

5. **Escolher imagem da galeria**:
   - Clique em **"Galeria de Imagens"**
   - Navegue pelas imagens já enviadas
   - Clique na imagem desejada para selecioná-la

6. **Publicar**:
   - Revise os campos gerados
   - Escolha a categoria
   - Selecione o status (Rascunho/Publicado)
   - Clique em **"Publicar"**

## ⚙️ Configuração Necessária

### Para usar geração de imagens com IA:

1. Acesse **Admin → Configurações**
2. Configure:
   - **Provider**: `openai`
   - **API Key**: Sua chave da OpenAI ([obter aqui](https://platform.openai.com/api-keys))
   - **Modelo**: `gpt-4o-mini` (para texto) ou `dall-e-3` (para imagem)

### Custos OpenAI (referência):
- **DALL-E 3** (1024x1024): ~$0.04 por imagem
- **GPT-4o-mini**: ~$0.0001 por 1000 tokens (muito barato para textos)

## 🗂️ Estrutura de Arquivos

```
/root/FAP/
├── admin/
│   ├── noticia_form.php        # Editor de notícias com galeria
│   ├── api_galeria.php         # API para gerenciar galeria e IA
│   └── api_ia.php              # API para geração de texto
├── uploads/
│   └── noticias/               # Imagens das notícias (chmod 777)
└── database (tabela arquivos)  # Metadados das imagens
```

## 🔒 Segurança

- ✅ Apenas usuários autenticados (admin/editor) podem acessar
- ✅ Validação de tipos de arquivo (apenas imagens)
- ✅ Limite de tamanho (5MB)
- ✅ Nomes de arquivo únicos (evita sobrescrita)
- ✅ Tokens únicos para cada arquivo
- ✅ Logs de atividade para geração de imagens IA

## 📊 Banco de Dados

As imagens são registradas na tabela `arquivos`:

```sql
SELECT * FROM arquivos WHERE tipo_arquivo LIKE 'image/%';
```

Campos importantes:
- `caminho`: uploads/noticias/xxxxx.png
- `tipo_arquivo`: image/png, image/jpeg, etc
- `descricao`: Para imagens IA, contém "IA: descrição original"
- `usuario_id`: Quem enviou/gerou
- `criado_em`: Data/hora do upload

## 🎨 Interface

### Botões Disponíveis:
1. **🖼️ Galeria de Imagens** (roxo) - Abre modal com todas as imagens
2. **🤖 Gerar Imagem com IA** (gradiente rosa/roxo) - Cria imagem com DALL-E
3. **📤 Fazer Upload** (verde) - Envia imagens do computador

### Modal da Galeria:
- Grid 2x2 em mobile, 3x3 em tablet, 4x4 em desktop
- Hover mostra "Selecionar"
- Imagens IA têm badge "🤖 IA"
- Imagem selecionada tem ✅ verde

## 🚀 Melhorias Futuras Sugeridas

- [ ] Edição de imagens (crop, resize)
- [ ] Filtros e tags para organizar galeria
- [ ] Busca por palavra-chave
- [ ] Deletar imagens não utilizadas
- [ ] Compressão automática (WebP)
- [ ] Suporte para outras IAs (Stable Diffusion, Midjourney)
- [ ] Pré-visualização antes de gerar com IA

## 🐛 Solução de Problemas

### Erro de permissão ao fazer upload
```bash
docker exec 2e7686cf19f8 chmod -R 777 /var/www/html/uploads
```

### Galeria vazia
Verifique se há imagens no banco:
```sql
SELECT COUNT(*) FROM arquivos WHERE tipo_arquivo LIKE 'image/%';
```

### Erro ao gerar imagem com IA
- Verifique API Key em Configurações
- Confirme que o provider é `openai`
- Verifique saldo de créditos na OpenAI
- Veja logs: `SELECT * FROM logs_atividades WHERE acao = 'gerar_imagem_ia'`

---

**Desenvolvido para FAP Pádua**  
Sistema de notícias com IA integrada
