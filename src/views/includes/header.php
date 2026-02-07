
<header class="main-header bg-white border-bottom sticky-top" role="banner" aria-label="Cabeçalho principal">
    <section class="top-bar" aria-label="Barra superior e promoção do site">
        <div class="container d-flex justify-content-center justify-content-md-between align-items-center">
            <div class="top-bar-message small fw-light" aria-live="polite">
                Frete grátis acima de R$ 199! Cupom: <strong>FRETEGRATIS</strong>
            </div>
            <div class="top-bar-links small d-none d-md-block">
                <a href="/contato" class="text-white text-decoration-none me-3 opacity-75" title="Entre em contato conosco" aria-label="Ir para página de contato">Atendimento</a>
                <a href="/quem-somos" class="text-white text-decoration-none opacity-75" title="Conheça nossa história" aria-label="Ir para página sobre nós">Sobre Nós</a>
            </div>
        </div>
    </section>

    <nav class="navbar navbar-expand-lg navbar-light py-3" role="navigation" aria-label="Navegação principal do site">
        <div class="container">
            <a class="navbar-brand essence-logo" href="/" title="Voltar à página inicial" aria-label="Página inicial Essence" tabindex="0">ESSENCE</a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavEssence"
                    aria-controls="navbarNavEssence" aria-expanded="false" aria-label="Expandir menu de navegação">
                <span class="navbar-toggler-icon" aria-hidden="true"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavEssence">
                
                <div class="header-actions d-flex align-items-center justify-content-around justify-content-lg-end gap-3 order-1 order-lg-2 ms-lg-auto py-3 py-lg-0 mb-3 mb-lg-0 border-lg-0" aria-label="Ações do usuário e sacola">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-dark small fw-bold d-flex align-items-center" 
                               href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false"
                               title="Menu do usuário">
                                <i class="bi bi-person me-1" aria-hidden="true"></i>
                                Olá, <?= htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm" aria-labelledby="userMenu">
    <li>
        <a class="dropdown-item small" href="/perfil" title="Acessar sua conta">
            <i class="bi bi-person-gear me-2" aria-hidden="true"></i>Minha conta
        </a>
    </li>
    
    <li><hr class="dropdown-divider"></li>
    
    <li>
        <a class="dropdown-item small text-danger" href="/logout" title="Fazer logout">
            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sair
        </a>
    </li>
</ul>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="btn btn-login-essence" title="Fazer login ou criar conta">Entrar</a>
                    <?php endif; ?>

                    <a href="/sacola" class="cart-icon-wrapper" aria-label="Ver sacola de compras" title="Abrir sacola">
                        <i class="bi bi-bag" aria-hidden="true" title="Ícone de sacola"></i>
                        <span class="cart-badge" id="cartCount" aria-label="Quantidade de itens na sacola" tabindex="0">0</span>
                    </a>
                </div>

                <ul class="navbar-nav mx-auto nav-links-essence order-2 order-lg-1 text-center collapse" id="menuEssenceMobile" role="menubar" aria-label="Menu principal">
                    <li class="nav-item" role="none"><a class="nav-link" href="/categoria?tipo=novidades" role="menuitem" tabindex="0">Novidades</a></li>
                    <li class="nav-item" role="none"><a class="nav-link" href="/categoria?tipo=lingeries" role="menuitem" tabindex="0">Lingeries</a></li>
                    <li class="nav-item" role="none"><a class="nav-link" href="/categoria?tipo=conjuntos" role="menuitem" tabindex="0">Conjuntos</a></li>
                    <li class="nav-item" role="none"><a class="nav-link" href="/categoria?tipo=pijamas" role="menuitem" tabindex="0">Pijamas</a></li>
                    <li class="nav-item" role="none"><a class="nav-link" href="/categoria?tipo=bodys" role="menuitem" tabindex="0">Bodys</a></li>
                    <li class="nav-item" role="none"><a class="nav-link nav-link-offers" href="/categoria?tipo=ofertas" role="menuitem" tabindex="0">Ofertas</a></li>
                </ul>

            </div>
        </div>
    </nav>
</header>

<script>

    // Carregar contagem da sacola ao iniciar
    async function updateCartCountDisplay() {
        try {
            const response = await fetch('/api/get-cart-count.php');
            const data = await response.json();
            const badge = document.getElementById('cartCount');
            if (badge) {
                badge.textContent = data.cart_count;
            }
        } catch (error) {
            console.error('Erro ao atualizar contador:', error);
        }
    }

    // Atualizar ao carregar a página
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateCartCountDisplay);
    } else {
        updateCartCountDisplay();
    }
</script>