<section class="novidades-carousel py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="subtitle">Lançamentos</span>
            <h2 class="section-title mt-2">Novidades da Estação</h2>
            <div class="title-divider mx-auto"></div>
        </div>

        <div class="swiper swiper-novidades">
            <div class="swiper-wrapper">
                
                <?php
                for ($i = 1; $i <= 10; $i++): 
                    // Usando picsum.photos com seed para imagens diferentes e consistentes
                    $imageUrl = "https://picsum.photos/seed/essence{$i}/400/533";
                ?>
                <div class="swiper-slide">
                    <a href="produto-detalhes.php?id=<?= $i ?>" class="product-card-link text-decoration-none">
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <img src="<?= $imageUrl ?>" 
                                     alt="Produto Essence <?= $i ?>" 
                                     class="product-img"
                                     loading="lazy">
                                <div class="product-badge">Novo</div>
                            </div>
                            <div class="product-info text-center pt-3">
                                <h3 class="product-name text-dark">Peça Essence N° <?= $i ?></h3>
                                <p class="product-price">R$ <?= number_format(rand(99, 189) + 0.90, 2, ',', '.') ?></p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endfor; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>

        <div class="text-center mt-5">
            <a href="/novidades.php" class="btn-essence-outline">Ver Todas as Novidades</a>
        </div>
    </div>
</section>