<?php
// Página da sacola de compras (espelho de carrinho.php, mas para "sacola")
session_start();
require_once __DIR__ . '/../config/Database.php';

try {
    $viewsPath = realpath(__DIR__ . '/../views');
    if (!$viewsPath) {
        throw new Exception("Pasta de views não encontrada");
    }

    // Permitir acesso mesmo deslogado (identificação anônima)
    if (!isset($_SESSION['user_id'])) {
        if (!isset($_SESSION['anon_cart_id'])) {
            $_SESSION['anon_cart_id'] = session_id();
        }
        $userId = 'anon_' . $_SESSION['anon_cart_id'];
    } else {
        $userId = $_SESSION['user_id'];
    }

    $metaTags = [
        'title' => 'Minha Sacola | Essence',
        'description' => 'Revise sua sacola de compras',
        'image' => '',
        'url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/sacola'
    ];

    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // Buscar itens da sacola
    $query = "SELECT c.id, c.product_id, c.quantity, c.selected_size, p.name, p.price, p.image_url, p.stock_by_size
              FROM cart c
              JOIN products p ON c.product_id = p.id
              WHERE c.user_id = :user_id
              ORDER BY c.added_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $cartItemsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cartItems = [];
    foreach ($cartItemsRaw as $item) {
        $item['stock_by_size_decoded'] = json_decode($item['stock_by_size'], true) ?? [];
        $cartItems[] = $item;
    }

    $subtotal = 0;
    $totalItems = 0;
    $frete = 0;
    foreach ($cartItems as &$item) {
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $subtotal += $item['subtotal'];
        $totalItems += $item['quantity'];
    }
    $frete = $subtotal >= 150 ? 0 : 15;
    $total = $subtotal + $frete;

    require_once $viewsPath . '/includes/head.php';
    require_once $viewsPath . '/includes/header.php';

} catch (Exception $e) {
    error_log("Erro em sacola.php: " . $e->getMessage());
    die('<div class="alert alert-danger mt-5 text-center" role="alert">Erro ao carregar sacola. <a href="/">Voltar à loja</a></div>');
}
?>

<main class="carrinho-page py-5">
    <div class="container">
        <h1 class="h2 fw-light mb-4">Minha Sacola</h1>
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                Sua sacola está vazia. <a href="/" class="alert-link">Continue comprando</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Tamanho</th>
                                        <th>Preço</th>
                                        <th>Quantidade</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                    <tr class="align-middle" data-cart-id="<?= $item['id'] ?>">
                                        <td>
                                            <div class="d-flex gap-3">
                                                <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/100') ?>"
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;"
                                                     loading="lazy">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="/produto?id=<?= $item['product_id'] ?>"
                                                           class="text-decoration-none text-dark"
                                                           title="Ir para o produto">
                                                            <?= htmlspecialchars($item['name']) ?>
                                                        </a>
                                                    </h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($item['selected_size'] ?? 'Único') ?>
                                            </span>
                                        </td>
                                        <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-menos"
                                                        data-cart-id="<?= $item['id'] ?>"
                                                        aria-label="Diminuir quantidade">−</button>
                                                <input type="number" class="form-control form-control-sm text-center quantidade"
                                                       value="<?= $item['quantity'] ?>"
                                                       data-cart-id="<?= $item['id'] ?>"
                                                       min="1"
                                                       max="<?= isset($item['stock_by_size_decoded'][$item['selected_size']]) ? $item['stock_by_size_decoded'][$item['selected_size']] : 0 ?>"
                                                       style="width: 60px;"
                                                       aria-label="Quantidade">
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-mais"
                                                        data-cart-id="<?= $item['id'] ?>"
                                                        aria-label="Aumentar quantidade">+</button>
                                            </div>
                                        </td>
                                        <td class="subtotal">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remover"
                                                    data-cart-id="<?= $item['id'] ?>"
                                                    aria-label="Remover da sacola">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="/" class="btn btn-outline-dark">
                            <i class="bi bi-arrow-left"></i> Continuar Comprando
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm" style="top: 20px;">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Resumo da Sacola</h5>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span>Subtotal (<?= $totalItems ?> item<?= $totalItems !== 1 ? 's' : '' ?>):</span>
                                <strong id="subtotalDisplay">R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span>
                                    Frete:
                                    <?php if ($frete === 0): ?>
                                        <span class="badge bg-success ms-2">GRÁTIS</span>
                                    <?php endif; ?>
                                </span>
                                <strong id="freteDisplay">
                                    <?= $frete > 0 ? 'R$ ' . number_format($frete, 2, ',', '.') : 'R$ 0,00' ?>
                                </strong>
                            </div>
                            <?php if ($subtotal < 150): ?>
                            <div class="alert alert-info alert-sm mb-3">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    Frete grátis em compras acima de R$ 150,00
                                </small>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-4 pt-3">
                                <h5 class="mb-0">Total:</h5>
                                <h5 class="mb-0" id="totalDisplay">R$ <?= number_format($total, 2, ',', '.') ?></h5>
                            </div>
                            <button class="btn btn-darkness btn-lg w-100 mb-2" id="btnCheckout">
                                <i class="bi bi-credit-card"></i> Finalizar Compra
                            </button>
                            <a href="/" class="btn btn-outline-dark w-100">
                                <i class="bi bi-shop"></i> Continuar Comprando
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    .btn-darkness {
        background-color: #1a1a1a;
        color: white;
    }
    .btn-darkness:hover {
        background-color: #333;
        color: white;
    }
    .table td {
        vertical-align: middle;
    }
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
</style>

<script>
    const cartItems = <?= json_encode($cartItems) ?>;
    document.querySelectorAll('.btn-mais').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantidade');
            const max = parseInt(input.max);
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
                updateQuantity(input);
            }
        });
    });
    document.querySelectorAll('.btn-menos').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantidade');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateQuantity(input);
            }
        });
    });
    document.querySelectorAll('.quantidade').forEach(input => {
        input.addEventListener('change', function() {
            updateQuantity(this);
        });
    });
    async function updateQuantity(input) {
        const cartId = input.dataset.cartId;
        const quantity = parseInt(input.value);
        if (quantity < 1) return;
        try {
            const formData = new FormData();
            formData.append('cart_id', cartId);
            formData.append('quantity', quantity);
            const response = await fetch('/api/update-cart-quantity.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                const row = input.closest('tr');
                const price = parseFloat(row.cells[2].textContent.replace('R$ ', '').replace(',', '.'));
                const subtotal = price * quantity;
                row.querySelector('.subtotal').textContent = 'R$ ' + subtotal.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                updateCartTotals();
            } else {
                alert('Erro: ' + data.message);
                location.reload();
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao atualizar quantidade');
        }
    }
    document.querySelectorAll('.btn-remover').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Tem certeza que deseja remover este item?')) return;
            const cartId = this.dataset.cartId;
            const row = this.closest('tr');
            try {
                const formData = new FormData();
                formData.append('cart_id', cartId);
                const response = await fetch('/api/remove-from-cart.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    row.style.opacity = '0.5';
                    row.style.pointerEvents = 'none';
                    setTimeout(() => {
                        row.remove();
                        if (document.querySelectorAll('tbody tr').length === 0) {
                            location.reload();
                        } else {
                            updateCartTotals();
                        }
                    }, 300);
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao remover da sacola');
            }
        });
    });
    function updateCartTotals() {
        let subtotal = 0;
        let totalItems = 0;
        document.querySelectorAll('tbody tr').forEach(row => {
            const quantity = parseInt(row.querySelector('.quantidade').value);
            const price = parseFloat(row.cells[2].textContent.replace('R$ ', '').replace(',', '.'));
            subtotal += price * quantity;
            totalItems += quantity;
        });
        const frete = subtotal >= 150 ? 0 : 15;
        const total = subtotal + frete;
        document.getElementById('subtotalDisplay').textContent = 'R$ ' + subtotal.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        document.getElementById('freteDisplay').textContent = frete > 0 ? 'R$ ' + frete.toFixed(2).replace('.', ',') : 'R$ 0,00';
        document.getElementById('totalDisplay').textContent = 'R$ ' + total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    document.getElementById('btnCheckout').addEventListener('click', function() {
        if (<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>) {
            showToast('Sistema de pagamento será implementado em breve!', 'success');
        } else {
            showToast('Você precisa estar logado para finalizar a compra.', 'error');
            setTimeout(() => { window.location.href = '/login'; }, 1800);
        }
    });

    // Toast reutilizável (igual produto.php)
    function showToast(message, type = 'success') {
        let toast = document.createElement('div');
        toast.className = 'essence-toast essence-toast-' + type;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 2500);
    }
</script>

<?php require_once $viewsPath . '/includes/footer.php'; ?>
