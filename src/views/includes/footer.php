<footer class="bg-white border-top pt-5 pb-4 mt-5" role="contentinfo" aria-label="Rodapé Essence">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="text-uppercase small fw-bold mb-4" style="letter-spacing: 2px;">Essence</h5>
                <p class="text-secondary small pe-lg-5">
                    Mais que uma loja de lingeries, um movimento em prol da saúde feminina e do conforto consciente.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="text-dark" aria-label="Instagram Essence" title="Instagram Essence"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                    <a href="#" class="text-dark" aria-label="WhatsApp Essence" title="WhatsApp Essence"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="text-uppercase small fw-bold mb-4" style="letter-spacing: 1px;">Explorar</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/novidades.php" class="text-secondary text-decoration-none">Novidades</a></li>
                    <li class="mb-2"><a href="/quem-somos.php" class="text-secondary text-decoration-none">Nossa Filosofia</a></li>
                    <li class="mb-2"><a href="/ofertas.php" class="text-secondary text-decoration-none">Ofertas</a></li>
                    <li class="mb-2"><a href="/contato.php" class="text-secondary text-decoration-none">Atendimento</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="text-uppercase small fw-bold mb-4" style="letter-spacing: 1px;">Suporte</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Trocas e Devoluções</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Guia de Medidas</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Política de Privacidade</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h5 class="text-uppercase small fw-bold mb-4" style="letter-spacing: 1px;">Acompanhe-nos</h5>
                <p class="text-secondary small">Cadastre-se para receber conteúdos sobre saúde íntima e novidades.</p>
                <form class="mt-3" aria-label="Formulário de newsletter Essence">
                    <div class="input-group">
                        <label for="newsletter-email" class="visually-hidden">E-mail</label>
                        <input type="email" id="newsletter-email" class="form-control rounded-0 border-dark small" placeholder="Seu melhor e-mail" aria-label="E-mail">
                        <button class="btn btn-dark rounded-0 text-uppercase small" type="button" aria-label="Assinar newsletter">Assinar</button>
                    </div>
                </form>
            </div>
        </div>

        <hr class="my-5 opacity-10">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="small text-muted mb-0">&copy; <?= date('Y') ?> Essence Lingerie. Todos os direitos reservados.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <span class="small text-muted me-3">Pagamento seguro:</span>
                <span class="badge bg-light text-dark border p-2">Cartão de crédito</span>
                <span class="badge bg-light text-dark border p-2">Pix</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  const swiper = new Swiper('.swiper-novidades', {
    slidesPerView: 1,
    spaceBetween: 20,
    pagination: { el: '.swiper-pagination', clickable: true },
    breakpoints: {
      640: { slidesPerView: 2 },
      1024: { slidesPerView: 4 }
    }
  });
  
</script>
</body>
</html>