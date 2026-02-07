# Essence Lingerie - Documentação Técnica

## 📋 Visão Geral

Plataforma e-commerce moderna para venda de lingerie, com foco em **acessibilidade (WCAG 2.1)**, **SEO otimizado** e **performance**.

### Stack Tecnológico

- **Backend:** PHP 8.2
- **Banco de Dados:** MySQL 8.0
- **Frontend:** Bootstrap 5.3, Vanilla JavaScript
- **Containerização:** Docker & Docker Compose
- **Servidor Web:** Apache com mod_rewrite

---

## 🚀 Inicialização Rápida

### Pré-requisitos
- Docker & Docker Compose instalados
- Git

### Instruções

```bash
# 1. Clone o repositório
git clone <seu-repo>
cd ecommerce-lingerie

# 2. Inicie os containers
docker-compose up -d

# 3. Acesse a aplicação
# Frontend: http://localhost:8080
# phpMyAdmin: http://localhost:8081 (root/root_password)
```

---

## 📁 Estrutura do Projeto

```
src/
├── config/
│   ├── Database.php          # Gerenciador de conexão PDO
│   ├── Auth.php              # Sistema de autenticação
│   ├── StockManager.php       # Gerenciador de estoque por tamanho
│   └── ErrorHandler.php       # Tratamento centralizado de erros
├── public/
│   ├── index.php             # Página inicial
│   ├── login.php             # Autenticação (WCAG 2.1)
│   ├── produto.php           # Página de detalhes (com Schema.org)
│   ├── categoria.php         # Catálogo com lazy loading
│   ├── cart.php              # Carrinho de compras
│   ├── admin/
│   │   ├── index.php         # Dashboard admin
│   │   ├── produtos_cadastrar.php      # Form com stock por tamanho
│   │   ├── produtos_editar.php         # Edição de produtos
│   │   ├── salvar_produto.php          # Endpoint de criação
│   │   └── salvar_produto_edit.php     # Endpoint de atualização
│   ├── .htaccess             # Rewrite rules + Cache headers
│   ├── robots.txt            # Instruções para bots
│   └── sitemap.xml.php       # Sitemap dinâmico
└── views/
    └── includes/
        ├── head.php          # Meta tags SEO + links
        ├── header.php        # Navegação acessível (ARIA)
        └── footer.php        # Rodapé

sql/
└── init.sql                  # Schema com stock_by_size JSON

docker-compose.yml
Dockerfile
```

---

## 🔐 Autenticação & Autorização

### Credenciais Padrão
- **Email:** admin@essence.com
- **Senha:** 
- **Hash:** `$2y$10$cxtlrjvI3BYNBbJQ3hcPDuhlo75BB5pTZ7RN.ONIiSzcdMkiMKkK2` (BCRYPT)

### Classes Importantes

#### `Auth.php`
```php
Auth::isAuthenticated()      // Verifica se usuário está logado
Auth::isAdmin()              // Verifica se é admin
Auth::checkAuth()            // Redireciona se não autenticado
Auth::checkAdmin()           // Redireciona se não é admin
Auth::logout()               // Faz logout
Auth::createSession($user)   // Cria sessão após login
```

---

## 📦 Gerenciamento de Estoque por Tamanho

### Formato de Dados
```json
{
  "sizes": ["PP", "P", "M", "G", "GG", "XG", "XGG"],
  "stock_by_size": {
    "PP": 5,
    "P": 10,
    "M": 15,
    "G": 12,
    "GG": 5,
    "XG": 3,
    "XGG": 1
  }
}
```

### Classe `StockManager`
```php
// Aceita tanto JSON string quanto array
$total = StockManager::getTotalStock($stock_json_or_array);
$qtd = StockManager::getStockBySize($stock, 'M');
$stock_array = StockManager::generateStockFromPost($_POST);
$json = StockManager::arrayToJson($stock_array);
```

---

## ♿ Acessibilidade (WCAG 2.1)

### Implementações

✅ **Navegação Semântica**
- Uso de `<main>`, `<nav>`, `<article>`, `<section>`
- Hierarquia de headings (h1, h2, h3)
- Skip links para navegação

✅ **ARIA Attributes**
- `aria-label` para ícones e botões
- `aria-describedby` para campos de formulário
- `aria-labelledby` para agrupamentos
- `aria-live="polite"` para atualizações dinâmicas
- `role="radio"`, `role="menuitem"` para componentes interativos

✅ **Formulários**
- Labels associadas a inputs via `for` attribute
- Mensagens de validação vinculadas com `aria-describedby`
- Indicadores visuais e textuais de campos obrigatórios

✅ **Imagens**
- Lazy loading com `loading="lazy"`
- Alt text descritivo para todos os produtos
- Ícones com `aria-hidden="true"`

✅ **Cores & Contraste**
- Todos os textos atendem WCAG AA (4.5:1)
- Não usar cor como único meio de comunicação

---

## 🔍 SEO Otimizado

### Meta Tags
```html
<meta name="description" content="...">
<meta name="keywords" content="...">
<meta property="og:title" content="...">
<meta property="og:image" content="...">
<link rel="canonical" href="...">
```

### Schema.org Structured Data
```json
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "...",
  "image": "...",
  "offers": {
    "@type": "Offer",
    "price": "...",
    "priceCurrency": "BRL"
  }
}
```

### Sitemap & Robots
- `robots.txt` - Controla indexação
- `sitemap.xml.php` - Gerado dinamicamente
- Headers de Cache-Control por tipo de arquivo

---

## ⚡ Performance Otimizações

### Lazy Loading
```html
<img src="..." loading="lazy" decoding="async">
```

### Cache Headers (.htaccess)
- HTML: sem cache (revalidar sempre)
- CSS/JS: 1 semana
- Imagens/Fontes: 1 mês
- GZIP compression ativada

### Database Optimization
```sql
-- Índices criados
CREATE INDEX idx_category ON products(category_id);
CREATE INDEX idx_offer ON products(is_offer);
CREATE INDEX idx_user_email ON users(email);
```

### Query Optimization
- Prepared statements em todas as queries
- Charset UTF-8MB4
- JSON para dados variáveis (stock_by_size)

---

## 🛡️ Segurança

### Headers de Segurança
```
X-Frame-Options: SAMEORIGIN          # Previne clickjacking
X-Content-Type-Options: nosniff       # Previne MIME sniffing
X-XSS-Protection: 1; mode=block       # XSS protection
```

### Proteção de Dados
- Senhas com BCRYPT (`password_hash`, `password_verify`)
- SQL Injection prevention (prepared statements)
- CSRF protection em formulários
- Input sanitization & validation

---

## 🧪 Testes Manuais

### 1. Cadastro de Produto com Estoque
```
1. Acesse /admin/
2. Login: admin@essence.com / 
3. Clique em "Cadastrar Novo Produto"
4. Selecione tamanhos e preencha estoque por tamanho
5. Submeta o formulário
```

### 2. Verificação de SEO
```
curl -I http://localhost:8080/produto?id=1
# Verificar headers de cache e segurança
```

### 3. Teste de Acessibilidade
```
- Use leitor de tela (NVDA, JAWS)
- Navegue com Tab/Shift+Tab
- Teste de contraste: https://webaim.org/resources/contrastchecker/
```

---

## 📊 Métricas & Monitoramento

### Logs
- **Erros:** `/logs/errors.log`
- **Auditoria:** `/logs/audit.log`

### Exemplo de Uso
```php
use ErrorHandler;

ErrorHandler::logError("Conexão falhou", "Database::getConnection");
ErrorHandler::logAudit("Login", $user_id, "Sucesso");
```

---

## 🔄 Manutenção

### Backup do Banco de Dados
```bash
docker exec essence_lingerie_db_container \
  mysqldump -u root -proot_password essence_lingerie_db > backup.sql
```

### Restaurar Banco
```bash
docker exec -i essence_lingerie_db_container \
  mysql -u root -proot_password essence_lingerie_db < backup.sql
```

### Limpar Containers
```bash
docker-compose down -v    # Remove volumes também
docker-compose up -d      # Reinicia com dados limpos
```

---

## 📞 Suporte & Contato

Para dúvidas ou issues, abra um ticket no repositório.

---

**Versão:** 2.0.0  
**Última Atualização:** Feb 2026  
**Licença:** Privada
