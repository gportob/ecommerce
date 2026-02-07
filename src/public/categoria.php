<?php
session_start();
require_once __DIR__ . '/../config/Database.php';

try {
    $viewsPath = realpath(__DIR__ . '/../views');

    if (!$viewsPath) {
        throw new Exception("Pasta de views não encontrada");
    }

    // 1. Conexão com o banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // 2. Identificar a categoria pela URL
    $categoriaSlug = isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : 'todos';

    // 3. Buscar produtos (Filtrar por categoria ou mostrar todos)
    if ($categoriaSlug !== 'todos' && $categoriaSlug !== 'novidades') {
        $query = "SELECT p.* FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE c.name = :cat_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':cat_name', $categoriaSlug, PDO::PARAM_STR);
    } else {
        // Se for 'novidades' ou 'todos', busca os últimos cadastrados
        $query = "SELECT * FROM products ORDER BY id DESC";
        $stmt = $db->prepare($query);
    }

    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once $viewsPath . '/includes/head.php';
    require_once $viewsPath . '/includes/header.php';
} catch (Exception $e) {
    error_log("Erro em categoria.php: " . $e->getMessage());
    die("Erro ao carregar categoria. Tente novamente mais tarde.");
}
?>

<main class="category-page py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title text-uppercase fw-light">Categoria: <?= htmlspecialchars(ucfirst($categoriaSlug)) ?></h1>
            <p class="text-muted">Explore nossa coleção exclusiva</p>
        </div>

        <div class="row g-4">
            <?php if (count($produtos) > 0): ?>
                <?php foreach ($produtos as $produto): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <article class="card h-100 border-0 shadow-sm product-card" role="article">
                            <div class="position-relative overflow-hidden" style="height: 250px;">
                                <img src="<?= htmlspecialchars($produto['image_url'] ?? '') ?>" 
                                     class="w-100 h-100" 
                                     alt="<?= htmlspecialchars($produto['name']) ?>"
                                     loading="lazy"
                                     decoding="async"
                                     style="object-fit: cover;">
                                <?php if ($produto['is_offer']): ?>
                                    <span class="badge bg-danger position-absolute top-2 end-2" aria-label="Produto em oferta">OFERTA</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h2 class="card-title mb-1 flex-grow-1" style="font-size: 1rem;">
                                    <?= htmlspecialchars($produto['name']) ?>
                                </h2>
                                <p class="text-dark fw-bold mb-2" aria-label="Preço">
                                    R$ <?= number_format($produto['price'], 2, ',', '.') ?>
                                </p>
                                
                                <!-- Tamanhos disponíveis -->
                                <?php 
                                $sizes = [];
                                if ($produto['sizes']) {
                                    $sizes = json_decode($produto['sizes'], true) ?? [];
                                }
                                if (count($sizes) > 0): 
                                ?>
                                    <small class="text-muted d-block mb-3" aria-label="Tamanhos disponíveis">
                                        Tamanhos: <?= htmlspecialchars(implode(', ', $sizes)) ?>
                                    </small>
                                <?php endif; ?>
                                
                                <a href="/produto?id=<?= $produto['id'] ?>" class="btn btn-outline-dark btn-sm" aria-label="Ver detalhes de <?= htmlspecialchars($produto['name']) ?>">Ver Detalhes</a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-light"></i>
                    <p class="mt-3 text-muted">Nenhum produto encontrado nesta categoria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once $viewsPath . '/includes/footer.php'; ?>