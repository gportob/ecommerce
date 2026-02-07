# 🧪 Guia de Testes - Ecommerce Lingerie Essence

## Para Começar

### 1. Inicie o Docker
```bash
docker-compose up -d
```

### 2. Aguarde a inicialização
- MySQL inicializa em ~5-10 segundos
- PHP/Apache inicializa em ~2-3 segundos

### 3. Acesse a aplicação
```
http://localhost:8080
```

---

## 📋 Teste de Login

### Admin Padrão
- **URL**: http://localhost:8080/login
- **Email**: admin@essence.com
- **Senha**: 
- **Resultado esperado**: Redireciona para `/admin/`

### Teste de Login com Erro
- **URL**: http://localhost:8080/login
- **Email**: admin@essence.com
- **Senha**: senha_errada
- **Resultado esperado**: Erro "Email ou senha incorretos."

---

## 📋 Teste de Registro

### Novo Usuário
- **URL**: http://localhost:8080/login
- **Nome**: Teste User
- **Email**: teste@email.com
- **Senha**: senha123456
- **Confirmar Senha**: senha123456
- **Resultado esperado**: Cria usuário e redireciona para `/perfil`

### Validações de Registro
1. **Email duplicado**
   - Tente registrar com `admin@essence.com`
   - Resultado: Erro "Este email já está registrado."

2. **Senhas não coincidem**
   - Digite senhas diferentes
   - Resultado: Erro "As senhas não coincidem."

3. **Senha fraca**
   - Digite senha com < 6 caracteres
   - Resultado: Erro "A senha deve ter pelo menos 6 caracteres."

---

## 📋 Teste de Proteção de Rotas

### Teste 1: Acessar Perfil sem Login
1. Abra uma janela privada/anônima
2. Acesse: http://localhost:8080/perfil
3. **Resultado esperado**: Redireciona para `/login?error=not_authenticated`

### Teste 2: Admin Padrão Acessa Admin
1. Faça login com `admin@essence.com` / ``
2. Acesse: http://localhost:8080/admin/
3. **Resultado esperado**: Dashboard administrativo carrega

### Teste 3: Cliente Tenta Acessar Admin
1. Registre novo usuário (cliente)
2. Tente acessar: http://localhost:8080/admin/
3. **Resultado esperado**: Redireciona para `/login?error=unauthorized`

---

## 📋 Teste de URLs Limpas

Todas as URLs abaixo devem funcionar **sem `.php`**:

| URL | Arquivo| Status |
|-----|--------|--------|
| http://localhost:8080/login | login.php | ✅ |
| http://localhost:8080/login_process | login_process.php | ✅ |
| http://localhost:8080/register_process | register_process.php | ✅ |
| http://localhost:8080/logout | logout.php | ✅ |
| http://localhost:8080/perfil | perfil.php | ✅ |
| http://localhost:8080/atualizar_perfil | atualizar_perfil.php | ✅ |
| http://localhost:8080/admin/ | admin/index.php | ✅ |
| http://localhost:8080/categoria | categoria.php | ✅ |

---

## 📋 Teste de Logout

1. Faça login
2. Clique em "Sair" (botão logout)
3. **Resultado esperado**: 
   - Sessão destruída
   - Redireciona para home (`/`)
   - Sem acesso a `/perfil` (redireciona para `/login`)

---

## 📋 Teste de Atualização de Perfil

1. Faça login como cliente
2. Acesse: http://localhost:8080/perfil
3. Atualize dados:
   - Nome: Novo Nome
   - CPF: 123.456.789-10
   - Telefone: (11) 99999-9999
   - CEP: 12345-000
   - Endereço: Rua Teste, 123
   - Bairro: Centro
   - Cidade: São Paulo
   - Estado: SP
4. Clique "Atualizar dados"
5. **Resultado esperado**: 
   - Mensagem de sucesso
   - Dados persistem após recarregar

---

## 📋 Teste de Banco de Dados

### Verificar Conexão
Acesse qualquer página e verifique se não há erro "Erro de conexão com o banco".

### Verificar Admin Padrão
No phpMyAdmin (quando ativado), verifique:
```sql
SELECT * FROM users WHERE email = 'admin@essence.com';
```

### Verificar Novo Usuário
Após registrar um novo usuário:
```sql
SELECT * FROM users WHERE email = 'teste@email.com';
```

**Campos esperados:**
- `id`: Auto-increment
- `name`: Novo Nome
- `email`: teste@email.com
- `password`: Hash BCRYPT
- `role`: 'client'

---

## 🐛 Troubleshooting

### "Erro de conexão com o banco"

**Causa**: Container MySQL não está respondendo

**Solução**:
```bash
docker-compose down
docker-compose up -d
```

Aguarde 10 segundos e tente novamente.

### "Pasta de views não encontrada"

**Causa**: Problema com caminhos relativos

**Solução**: Verifique se os caminhos em:
- `src/views/includes/` existem
- Permissões de arquivo estão corretas

### Rotas com `.php` não funcionam

**Causa**: `.htaccess` não está ativado ou mod_rewrite não está habilitado

**Verificação**:
1. Verifique no Dockerfile se `a2enmod rewrite` está presente
2. Recrie containers: `docker-compose up --build`

### "Apenas admins podem acessar"

**Solução**: Faça login com credenciais de admin

```
Email: admin@essence.com
Senha:
```

---

## ✅ Checklist Final

- [ ] Login funciona com admin
- [ ] Registro funciona e cria novo usuário
- [ ] Logout funciona
- [ ] Rotas protegidas redirecionam para login
- [ ] URLs limpas funcionam (sem .php)
- [ ] Atualização de perfil funciona
- [ ] Banco de dados está acessível
- [ ] Sem erros em logs
- [ ] Sessão funciona corretamente

---

## 📞 Suporte

Se encontrar problemas:
1. Verifique os logs do container:
   - `docker logs essence_lingerie_app`
   - `docker logs essence_lingerie_db_container`

2. Verifique se `.htaccess` está no lugar correto:
   - `src/public/.htaccess`

3. Limpe o cache do navegador (Ctrl+F5)

