<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/StockManager.php';

// Variáveis para SEO
$metaTags = [
    'title' => 'Essence',
    'description' => 'Lingerie de qualidade',
    'image' => '',
    'url' => ''
];

try {
    $viewsPath = realpath(__DIR__ . '/../views');

    if (!$viewsPath) {
        throw new Exception("Pasta de views não encontrada");
    }

    // Validar ID do produto
    $productId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
    if (!$productId) {
        throw new Exception("Produto não encontrado");
    }

    // Conexão com o banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // Buscar produto
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Produto não encontrado");
    }

    // Decodificar tamanhos se existir
    $sizes = [];
    if ($product['sizes']) {
        $sizes = json_decode($product['sizes'], true) ?? [];
    }

    // Preparar meta tags para SEO
    $metaTags['title'] = htmlspecialchars($product['name']) . ' | Essence';
    $metaTags['description'] = htmlspecialchars(substr($product['description'] ?? $product['name'], 0, 160));
    $metaTags['image'] = htmlspecialchars($product['image_url'] ?? '');
    $metaTags['url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/produto?id=' . $product['id'];
    $metaTags['keywords'] = htmlspecialchars($product['name'] . ', ' . $product['category_name'] . ', lingerie');
    
    require_once $viewsPath . '/includes/head.php';
    require_once $viewsPath . '/includes/header.php';

} catch (Exception $e) {
    error_log("Erro em produto.php: " . $e->getMessage());
    die('<div class="alert alert-danger mt-5 text-center" role="alert">Produto não encontrado ou erro ao carregar. <a href="/">Voltar à loja</a></div>');
}
?>

<main class="produto-detalhes py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Imagem do Produto -->
            <div class="col-lg-6">
                <div class="product-image-container">
                    <img 
                        src="<?= htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/500') ?>" 
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="img-fluid rounded shadow-sm"
                        loading="lazy"
                        decoding="async"
                        style="width: 100%; max-height: 600px; object-fit: cover;">
                </div>
            </div>

            <!-- Informações do Produto -->
            <div class="col-lg-6">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item"><a href="/categoria?tipo=<?= htmlspecialchars($product['category_name']) ?>">
                            <?= htmlspecialchars($product['category_name']) ?></a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
                    </ol>
                </nav>

                <h1 class="h2 fw-light mb-2"><?= htmlspecialchars($product['name']) ?></h1>

                <!-- Avaliação (placeholder) -->
                <div class="mb-3">
                    <span class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                        <span class="ms-2 text-muted">(24 avaliações)</span>
                    </span>
                </div>

                <!-- Preço -->
                <div class="mb-4">
                    <span class="h3 fw-bold text-dark">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                    <?php if ($product['is_offer']): ?>
                        <span class="badge bg-danger ms-2">EM OFERTA</span>
                    <?php endif; ?>
                </div>

                <!-- Descrição -->
                <div class="mb-4">
                    <h5 class="text-muted">Descrição</h5>
                    <p class="text-secondary"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>
                </div>

                <!-- Tamanhos -->
                <?php if (count($sizes) > 0): ?>
                <div class="mb-4" role="group" aria-labelledby="tamanho_label">
                    <h5 id="tamanho_label" class="text-muted mb-3">Selecione o Tamanho <span class="text-danger">*</span></h5>
                    <div class="d-flex gap-2 flex-wrap" role="radiogroup" aria-describedby="tamanho_help">
                        <?php foreach ($sizes as $size): ?>
                            <button type="button" class="btn btn-outline-dark btn-tamanho" 
                                    data-size="<?= htmlspecialchars($size) ?>"
                                    role="radio"
                                    aria-label="Tamanho <?= htmlspecialchars($size) ?>"
                                    aria-checked="false">
                                <?= htmlspecialchars($size) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedSize" value="" aria-hidden="true">
                    <small id="tamanho_help" class="text-muted d-block mt-2">Escolha um tamanho antes de adicionar ao carrinho</small>
                </div>
                <?php endif; ?>

                <!-- Estoque e Quantidade -->
                <div class="mb-4" role="group" aria-labelledby="qtd_label">
                    <h5 id="qtd_label" class="text-muted mb-3">Quantidade</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="btnMenos"
                                aria-label="Diminuir quantidade">-</button>
                        <input type="number" id="quantidade" value="1" min="1" 
                               max="<?php $stock_by_size = $product['stock_by_size'] ? json_decode($product['stock_by_size'], true) : []; echo array_sum($stock_by_size); ?>" 
                               class="form-control text-center" style="max-width: 80px;"
                               aria-label="Quantidade de itens"
                               aria-describedby="qtd_help">
                        <button type="button" class="btn btn-outline-secondary" id="btnMais"
                                aria-label="Aumentar quantidade">+</button>
                        <span id="qtd_help" class="text-muted ms-3" aria-live="polite" aria-atomic="true">
                            <?php 
                                $stock_by_size = $product['stock_by_size'] ? json_decode($product['stock_by_size'], true) : [];
                                $total_stock = array_sum($stock_by_size);
                                if ($total_stock > 0): ?>
                                <i class="bi bi-check-circle text-success" aria-hidden="true"></i>
                                <span><?= $total_stock ?> em estoque</span>
                            <?php else: ?>
                                <i class="bi bi-x-circle text-danger" aria-hidden="true"></i>
                                <span>Fora de estoque</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Botões -->
                <div class="mb-4 d-grid gap-2">
                    <button type="button" class="btn btn-darkness btn-lg" id="btnAdicionar"
                            <?php $total_stock = array_sum($stock_by_size ?? []); echo $total_stock <= 0 ? 'disabled' : ''; ?>
                            aria-label="Adicionar item selecionado à sacola">
                        <i class="bi bi-bag-heart" aria-hidden="true"></i> Adicionar à Sacola
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-lg" id="btnWishlist"
                            aria-label="Adicionar item aos favoritos">
                        <i class="bi bi-heart" aria-hidden="true"></i> <span id="wishlistText">Adicionar aos Favoritos</span>
                    </button>
                </div>

                <!-- Características -->
                <div class="border-top pt-4">
                    <h5 class="text-muted mb-3">Características</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check text-success"></i>
                            <strong>Compatível com sutiãs:</strong> Todos os modelos
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success"></i>
                            <strong>Material:</strong> Algodão premium + renda
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success"></i>
                            <strong>Cuidados:</strong> Lavar em água fria
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success"></i>
                            <strong>Frete grátis:</strong> Em compras acima de R$ 150
                        </li>
                    </ul>
                </div>

                <!-- Compartilhar -->
                <div class="border-top pt-4 mt-4">
                    <h5 class="text-muted mb-3">Compartilhar</h5>
                    <a href="#" class="btn btn-sm btn-outline-dark"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-dark"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-dark"><i class="bi bi-whatsapp"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-dark"><i class="bi bi-envelope"></i></a>
                </div>
            </div>
        </div>

        <!-- Produtos Relacionados -->
        <div class="row mt-5 pt-5 border-top">
            <div class="col-12 mb-4">
                <h3 class="h4 fw-light">Produtos Relacionados</h3>
            </div>
            <!-- Aqui você pode carregar mais produtos da mesma categoria -->
        </div>
    </div>
</main>

<style>
    .btn-tamanho.active {
        background-color: #333;
        color: white;
        border-color: #333;
    }

    .btn-darkness {
        background-color: #1a1a1a;
        color: white;
    }

    .btn-darkness:hover {
        background-color: #333;
        color: white;
    }

    .product-image-container {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
    }
</style>

<script>
    const productId = <?= $product['id'] ?>;
    const userLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

    // Tamanhos
    document.querySelectorAll('.btn-tamanho').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.btn-tamanho').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('selectedSize').value = this.dataset.size;
        });
    });

    // Quantidade
    document.getElementById('btnMais').addEventListener('click', function() {
        const input = document.getElementById('quantidade');
        const max = parseInt(input.max);
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
        }
    });

    document.getElementById('btnMenos').addEventListener('click', function() {
        const input = document.getElementById('quantidade');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    });

    // Adicionar à sacola
    document.getElementById('btnAdicionar').addEventListener('click', async function() {
        const size = document.getElementById('selectedSize').value;
        const sizes = <?= json_encode($sizes) ?>;
        if (sizes.length > 0 && !size) {
            showToast('Por favor, selecione um tamanho', 'error');
            return;
        }
        const quantidade = document.getElementById('quantidade').value;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Adicionando...';
        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('size', size);
            formData.append('quantity', quantidade);
            const response = await fetch('/api/add-to-cart.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showCartToast(data.message, data.cart_count);
            } else if (data.message && data.message.toLowerCase().includes('logado')) {
                // Se precisa logar para finalizar, redireciona ao finalizar
                showCartToast('Produto adicionado à sacola. Faça login para finalizar a compra.', null, true);
            } else {
                showToast('Erro: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showToast('Erro ao adicionar à sacola', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bag-heart" aria-hidden="true"></i> Adicionar à Sacola';
        }
    });

    // Toggle Favoritos
    document.getElementById('btnWishlist').addEventListener('click', async function() {
        if (!userLoggedIn) {
            showToast('Você precisa estar logado para adicionar aos favoritos', 'error');
            setTimeout(() => { window.location.href = '/login'; }, 1200);
            return;
        }
        const btn = this;
        const icon = btn.querySelector('i');
        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            const response = await fetch('/api/toggle-wishlist.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                if (data.action === 'added') {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    btn.classList.remove('btn-outline-dark');
                    btn.classList.add('btn-danger');
                    document.getElementById('wishlistText').textContent = 'Remover dos Favoritos';
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-dark');
                    document.getElementById('wishlistText').textContent = 'Adicionar aos Favoritos';
                }
                showToast(data.message, 'success');
            } else {
                showToast('Erro: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showToast('Erro ao atualizar favoritos', 'error');
        }
    });

    // Verificar se já está nos favoritos ao carregar a página
    async function checkWishlist() {
        if (!userLoggedIn) return;
        try {
            const response = await fetch('/api/check-wishlist.php?product_id=' + productId);
            const data = await response.json();
            if (data.is_favorite) {
                const btn = document.getElementById('btnWishlist');
                const icon = btn.querySelector('i');
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                btn.classList.remove('btn-outline-dark');
                btn.classList.add('btn-danger');
                document.getElementById('wishlistText').textContent = 'Remover dos Favoritos';
            }
        } catch (error) {
            console.error('Erro ao verificar favoritos:', error);
        }
    }
    document.addEventListener('DOMContentLoaded', checkWishlist);

    // Toasts
    function showToast(message, type = 'success') {
        let toast = document.createElement('div');
        toast.className = 'essence-toast essence-toast-' + type;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 2500);
    }

    function showCartToast(message, cartCount = null, requireLogin = false) {
        let toast = document.createElement('div');
        toast.className = 'essence-toast essence-toast-success';
        toast.innerHTML = `<div>${message}</div><div class="mt-2 d-flex gap-2">` +
            `<button class='btn btn-sm btn-darkness' onclick='window.location.href="/sacola"'>Finalizar Compra</button>` +
            `<button class='btn btn-sm btn-outline-dark' onclick='closeCartToast(this)'>Continuar Comprando</button></div>`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        if (cartCount !== null) {
            document.getElementById('cartCount').textContent = cartCount;
        }
        if (requireLogin) {
            setTimeout(() => { window.location.href = '/login'; }, 2000);
        }
    }
    function closeCartToast(btn) {
        let toast = btn.closest('.essence-toast');
        if (toast) toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }
</script>

<?php require_once $viewsPath . '/includes/footer.php'; ?>
