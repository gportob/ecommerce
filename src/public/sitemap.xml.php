<?php
header('Content-Type: application/xml; charset=UTF-8');

require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Buscar todas as páginas públicas
    $categoriasStmt = $db->query("SELECT DISTINCT category_id FROM products WHERE category_id IS NOT NULL");
    $categorias = $categoriasStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $produtosStmt = $db->query("SELECT id, updated_at FROM products ORDER BY updated_at DESC");
    $produtos = $produtosStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die('Erro ao gerar sitemap');
}

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// URLs principais
$mainPages = [
    ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['url' => '/categoria', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/quem-somos', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['url' => '/contato', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['url' => '/login', 'priority' => '0.6', 'changefreq' => 'never'],
];

foreach ($mainPages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . $page['url']) . "</loc>\n";
    echo "    <priority>" . $page['priority'] . "</priority>\n";
    echo "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
    echo "  </url>\n";
}

// Categorias
foreach ($categorias as $catId) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . '/categoria?id=' . $catId) . "</loc>\n";
    echo "    <priority>0.8</priority>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "  </url>\n";
}

// Produtos
foreach ($produtos as $produto) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . '/produto?id=' . $produto['id']) . "</loc>\n";
    echo "    <lastmod>" . date('Y-m-d', strtotime($produto['updated_at'])) . "</lastmod>\n";
    echo "    <priority>0.7</priority>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "  </url>\n";
}

echo '</urlset>' . "\n";
?>
