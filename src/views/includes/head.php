<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $metaTags['description'] ?? 'Essence: Lingerie de qualidade, comfort e estilo.' ?>">
    <meta name="keywords" content="<?= $metaTags['keywords'] ?? 'lingerie, saúde íntima, conforto, moda feminina, bem-estar' ?>">
    <meta name="author" content="Essence Lingerie">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1a1a1a">
    
    <!-- Open Graph para redes sociais -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Essence">
    <meta property="og:locale" content="pt_BR">
    <?php if (isset($metaTags['title'])): ?>
        <meta property="og:title" content="<?= htmlspecialchars($metaTags['title']) ?>">
    <?php endif; ?>
    <?php if (isset($metaTags['description'])): ?>
        <meta property="og:description" content="<?= htmlspecialchars($metaTags['description']) ?>">
    <?php endif; ?>
    <?php if (isset($metaTags['image'])): ?>
        <meta property="og:image" content="<?= htmlspecialchars($metaTags['image']) ?>">
    <?php endif; ?>
    <?php if (isset($metaTags['url'])): ?>
        <meta property="og:url" content="<?= htmlspecialchars($metaTags['url']) ?>">
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    
    <title><?= $metaTags['title'] ?? 'Essence | Lingerie & Saúde Íntima' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@200;300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Preload para melhor performance -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@200;300;400&display=swap" as="style">
    
    <!-- Canonical para evitar duplicate content -->
    <link rel="canonical" href="<?= $metaTags['url'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    
    <!-- Schema.org Structured Data para Product (se estiver em página de produto) -->
    <?php if (isset($product) && isset($product['name'])): ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "<?= htmlspecialchars($product['name']) ?>",
      "description": "<?= htmlspecialchars(substr($product['description'] ?? '', 0, 500)) ?>",
      "image": "<?= htmlspecialchars($product['image_url'] ?? '') ?>",
      "brand": {
        "@type": "Brand",
        "name": "Essence"
      },
      "offers": {
        "@type": "Offer",
        "price": "<?= number_format($product['price'], 2, '.', '') ?>",
        "priceCurrency": "BRL",
        "availability": "https://schema.org/InStock"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.5",
        "reviewCount": "24"
      }
    }
    </script>
    <?php endif; ?>
</head>

<body class="bg-light">
</body>