<?php 
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';

// Se já está autenticado, redireciona
if (Auth::isAuthenticated()) {
    if (Auth::isAdmin()) {
        header("Location: /admin/");
    } else {
        header("Location: /perfil");
    }
    exit();
}

// Mapear mensagens de erro
$errorMessages = [
    'invalid_credentials' => 'Email ou senha incorretos.',
    'email_exists' => 'Este email já está registrado.',
    'weak_password' => 'A senha deve ter pelo menos 6 caracteres.',
    'password_mismatch' => 'As senhas não coincidem.',
    'empty_fields' => 'Preencha todos os campos obrigatórios.',
    'invalid_input' => 'Dados inválidos foram fornecidos.',
    'registration_failed' => 'Erro ao registrar. Tente novamente.',
    'system_error' => 'Erro no sistema. Tente novamente mais tarde.'
];

$error = $_GET['error'] ?? null;
$errorMsg = $errorMessages[$error] ?? null;

// Caminho direto para as views saindo de public/
$viewsPath = realpath(__DIR__ . '/../views');

if (!$viewsPath) {
    die("Erro: Pasta de views não encontrada em: " . __DIR__ . '/../views');
}

require_once $viewsPath . '/includes/head.php';
require_once $viewsPath . '/includes/header.php';
?>

<main class="auth-page py-5">
    <div class="container">
        <?php if ($errorMsg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erro!</strong> <?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <div class="row g-5 justify-content-center">
            
            <div class="col-lg-5">
                <div class="auth-card p-4 p-md-5">
                    <div class="auth-header mb-4">
                        <span class="hero-subtitle">Já sou cliente</span>
                        <h1 class="fw-light" style="font-size: 1.8rem;">Acessar Conta</h1>
                    </div>
                    
                    <form action="/login_process" method="POST" class="essence-form" novalidate>
                        <div class="form-group mb-3">
                            <label for="login-email" class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" 
                                   id="login-email" 
                                   name="email" 
                                   required 
                                   placeholder="seu@email.com"
                                   aria-label="Endereço de e-mail para login"
                                   aria-describedby="email-hint"
                                   class="form-control">
                            <small id="email-hint" class="form-text text-muted">Deve ser um e-mail válido</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <label for="login-pass" class="form-label">Senha <span class="text-danger">*</span></label>
                                <a href="/recuperar" class="small text-muted text-decoration-none" title="Recuperar senha">Esqueceu?</a>
                            </div>
                            <input type="password" 
                                   id="login-pass" 
                                   name="password" 
                                   required
                                   aria-label="Sua senha"
                                   class="form-control">
                        </div>

                        <button type="submit" class="btn-essence-dark w-100" aria-label="Entrar na sua conta">Entrar na Essence</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-1 d-none d-lg-flex align-items-center justify-content-center">
                <div class="vr" style="height: 70%; color: var(--border-light);" aria-hidden="true"></div>
            </div>

            <div class="col-lg-5">
                <div class="auth-card p-4 p-md-5">
                    <div class="auth-header mb-4">
                        <span class="hero-subtitle">Nova por aqui?</span>
                        <h1 class="fw-light" style="font-size: 1.8rem;">Criar Cadastro</h1>
                    </div>
                    
                    <form action="/register_process" method="POST" class="essence-form" novalidate>
                        <div class="form-group mb-3">
                            <label for="reg-name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="reg-name" 
                                   name="name" 
                                   required 
                                   placeholder="Como prefere ser chamada?"
                                   aria-label="Seu nome completo"
                                   class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="reg-email" class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" 
                                   id="reg-email" 
                                   name="email" 
                                   required
                                   aria-label="Seu endereço de e-mail"
                                   aria-describedby="reg-email-hint"
                                   class="form-control">
                            <small id="reg-email-hint" class="form-text text-muted">Será usado para login</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="reg-pass" class="form-label">Crie uma Senha <span class="text-danger">*</span></label>
                            <input type="password" 
                                   id="reg-pass" 
                                   name="password" 
                                   required 
                                   minlength="6"
                                   aria-label="Sua senha"
                                   aria-describedby="pass-hint"
                                   class="form-control">
                            <small id="pass-hint" class="form-text text-muted">Mínimo 6 caracteres</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="reg-pass-confirm" class="form-label">Confirme sua Senha <span class="text-danger">*</span></label>
                            <input type="password" 
                                   id="reg-pass-confirm" 
                                   name="password_confirm" 
                                   required 
                                   minlength="6"
                                   aria-label="Confirme sua senha"
                                   class="form-control">
                        </div>

                        <div class="form-check mb-4 small">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="newsletter" 
                                   checked
                                   aria-label="Desejo receber novidades e dicas">
                            <label class="form-check-label text-muted" for="newsletter">
                                Desejo receber novidades e dicas de saúde íntima.
                            </label>
                        </div>

                        <button type="submit" class="btn-essence-outline w-100" aria-label="Criar minha conta">Cadastrar e Explorar</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once $viewsPath . '/includes/footer.php'; ?>