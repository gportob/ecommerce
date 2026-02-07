<?php 
session_start();

require_once __DIR__ . '/../config/Database.php';

// Define o caminho absoluto baseado na estrutura interna do Docker
// Como o arquivo está em /src/public, subimos um nível (..) e entramos em /src/views
$viewsPath = realpath(__DIR__ . '/../views');

if (!$viewsPath || !file_exists($viewsPath . '/includes/head.php')) {
    // Fallback de segurança caso a estrutura de pastas src esteja duplicada
    $viewsPath = realpath(__DIR__ . '/../../src/views');
}

if (!$viewsPath) {
    die("Erro Crítico: Pasta de views não encontrada.");
}

require_once $viewsPath . '/includes/head.php';
require_once $viewsPath . '/includes/header.php';
?>

<main class="about-container">
    <section class="about-hero" aria-labelledby="hero-title">
        <div class="hero-content">
            <span class="subtitle">Essência e Cuidado</span>
            <h1 id="hero-title">Beleza que respeita a sua natureza.</h1>
        </div>
    </section>

    <section class="manifesto">
        <div class="container">
            <div class="manifesto-grid">
                <div class="manifesto-text">
                    <h2>Nossa Filosofia</h2>
                    <p>
                        A <strong>Essence</strong> nasceu do desejo de simplificar o autocuidado íntimo. 
                        Acreditamos que a lingerie é a camada mais próxima da sua intimidade e, por isso, 
                        ela deve ser um convite ao conforto, não um obstáculo.
                    </p>
                    <p>
                        Nossa curadoria é focada em mulheres que buscam o básico sofisticado: 
                        tecidos que respiram, cortes que abraçam o corpo sem apertar e designs 
                        atemporais que celebram a saúde íntima em primeiro lugar.
                    </p>
                </div>
                <div class="manifesto-image">
                    <img src="https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?q=80&w=1000&auto=format&fit=crop" alt="Detalhe de tecido de algodão orgânico macio" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <section class="pillars">
        <div class="container">
            <div class="pillar-item">
                <h3>01. Respiração</h3>
                <p>Priorizamos fibras naturais como o modal e o algodão egípcio, garantindo o pH equilibrado da região íntima.</p>
            </div>
            <div class="pillar-item">
                <h3>02. Minimalismo</h3>
                <p>Design limpo. Sem costuras desnecessárias, sem rendas que pinicam. Apenas o essencial.</p>
            </div>
            <div class="pillar-item">
                <h3>03. Consciência</h3>
                <p>Produção ética e materiais sustentáveis que respeitam o meio ambiente e o seu corpo.</p>
            </div>
        </div>
    </section>
</main>

<?php include $viewsPath . '/includes/footer.php'; ?>