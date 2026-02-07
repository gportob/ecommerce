<?php
session_start(); // Linha 1: Essencial para evitar o erro de headers

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/StockManager.php';

// Define o caminho absoluto baseado na estrutura interna do Docker
$viewsPath = realpath(__DIR__ . '/../src/views');

if (!$viewsPath || !file_exists($viewsPath . '/includes/head.php')) {
    $viewsPath = realpath(__DIR__ . '/../../src/views');
}

if (!$viewsPath) {
    die("Erro Crítico: Pasta de views não encontrada.");
}

// Buscar produtos recentes/novidades do banco
$produtos_novidades = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              ORDER BY p.created_at DESC LIMIT 10");
        $stmt->execute();
        $produtos_novidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Erro ao buscar novidades: " . $e->getMessage());
}

require_once $viewsPath . '/includes/head.php';
require_once $viewsPath . '/includes/header.php';
?>

<main>
    <section class="hero-section bg-white position-relative overflow-hidden" style="min-height: 70vh;">
    <div class="container h-100">
        <div class="row align-items-center h-100">
            <div class="col-lg-5 py-5 z-index-2">
                <span class="hero-subtitle text-uppercase text-muted small mb-2 d-block">Essencial & Orgânico</span>
                <h1 class="hero-title display-4 fw-light mb-3">A segunda pele que cuida de <span class="fst-italic">você</span>.</h1>
                <p class="hero-text text-secondary mb-4 fw-light">Lingeries feitas com fibras naturais que respeitam o seu corpo e o seu descanso.</p>
                <div class="d-flex gap-3">
                    <a href="/categoria?tipo=novidades" class="btn btn-essence-dark">Explorar Loja</a>
                    <a href="/quem-somos" class="btn btn-essence-outline">Nossa História</a>
                </div>
            </div>

            <div class="col-lg-7 h-100 position-absolute end-0 top-0 d-none d-lg-block">
                <div class="hero-full-image h-100">
                    <img src="https://images.unsplash.com/photo-1582533561751-ef6f6ab93a2e?q=80&w=2000&auto=format&fit=crop" 
                         class="img-fluid h-100 w-100 object-fit-cover shadow-sm" 
                         alt="Mulher confortável com roupa íntima Essence">
                </div>
            </div>
        </div>
    </div>
</section>

    <section class="news-carousel py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-light">Novidades</h2>
                    <p class="text-muted small mb-0">As últimas tendências em tecidos sustentáveis</p>
                </div>
                <a href="/categoria?tipo=novidades" class="text-dark text-decoration-none small fw-bold">Ver tudo →</a>
            </div>

            <div class="swiper swiperNovidades">
                <div class="swiper-wrapper">
                    <?php if (count($produtos_novidades) > 0): ?>
                        <?php foreach ($produtos_novidades as $produto): ?>
                            <?php 
                                $stock_by_size = $produto['stock_by_size'] ? json_decode($produto['stock_by_size'], true) : [];
                                $total_stock = StockManager::getTotalStock($stock_by_size);
                            ?>
                            <div class="swiper-slide slide-news">
                                <div class="product-card border-0">
                                    <a href="/produto?id=<?= $produto['id'] ?>" class="product-card-link text-decoration-none">
                                        <div class="product-image-wrapper position-relative overflow-hidden mb-3">
                                            <img src="<?= htmlspecialchars($produto['image_url'] ?? 'https://via.placeholder.com/400x500') ?>"
                                                class="img-fluid product-img" 
                                                alt="<?= htmlspecialchars($produto['name']) ?>"
                                                loading="lazy"
                                                decoding="async">

                                            <?php if ($produto['is_offer']): ?>
                                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Oferta</span>
                                            <?php endif; ?>

                                            <div class="product-overlay d-flex align-items-center justify-content-center">
                                                <span class="btn-ver-detalhes">Ver Detalhes</span>
                                            </div>
                                        </div>

                                        <div class="product-info text-center">
                                            <p class="text-muted small mb-1"><?= htmlspecialchars($produto['category_name'] ?? 'Lingerie') ?></p>
                                            <h3 class="product-name h6 fw-normal mb-2 text-dark">
                                                <?= htmlspecialchars(substr($produto['name'], 0, 40)) ?>
                                            </h3>
                                            <span class="product-price text-accent-gold fw-bold">
                                                R$ <?= number_format($produto['price'], 2, ',', '.') ?>
                                            </span>
                                            <?php if ($total_stock > 0): ?>
                                                <div class="text-success small mt-1" role="status" aria-live="polite">
                                                    ✓ Em estoque
                                                </div>
                                            <?php else: ?>
                                                <div class="text-danger small mt-1" role="status" aria-live="polite">
                                                    ✗ Fora de estoque
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="swiper-slide">
                            <div class="text-center py-5 text-muted">
                                <p>Nenhum produto disponível no momento</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="swiper-pagination mt-4"></div>
            </div>
        </div>
    </section>

    <section class="did-you-know py-5 bg-light">
        <div class="container py-4">
            <div class="row align-items-center geral-did-you-know">
                <div class="col-md-5 mb-4 mb-md-0 img-did-you-know">
                    <img src="https://picsum.photos/seed/nature/500/500" class="img-fluid rounded-circle shadow-sm"
                        alt="Fibras naturais">
                </div>
                <div class="col-md-7 ps-lg-5 text-did-you-know">
                    <span class="text-uppercase text-muted small letter-spacing-2">Bem-estar</span>
                    <h2 class="display-6 fw-light my-3">Você sabia?</h2>
                    <p class="lead text-secondary">Tecidos sintéticos podem abafar a região íntima, alterando o pH
                        natural.</p>
                    <p class="text-muted">Na Essence, utilizamos apenas algodão e fibras de bambu, que permitem a
                        transpiração total da pele, prevenindo alergias e desconfortos. É saúde em forma de lingerie.
                    </p>
                    <a href="/quem-somos" class="btn btn-essence-outline mt-3">Nossa Filosofia</a>
                </div>
            </div>
        </div>
    </section>

    <section class="instagram-section py-5">
        <div class="container text-center">
            <h2 class="fw-light mb-2">@essence.lingerie</h2>
            <p class="text-muted mb-5">Siga nossa jornada minimalista no Instagram</p>

            <div class="row g-2">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="col-4 col-md-2">
                        <a href="#" class="insta-post">
                            <img src="https://picsum.photos/seed/insta<?= $i ?>/300/300" class="img-fluid"
                                alt="Instagram post">
                        </a>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
</main>

<script>
    const swiper = new Swiper('.swiperNovidades', {
        slidesPerView: 2,
        spaceBetween: 20,
        pagination: { el: '.swiper-pagination', clickable: true },
        breakpoints: {
            768: { slidesPerView: 3 },
            1024: { slidesPerView: 4 }
        }
    });
</script>

<?php require_once $viewsPath . '/includes/footer.php'; ?>