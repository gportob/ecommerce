<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';

// Verifica se usuário está autenticado
Auth::checkAuth();

// Conexão com o banco para buscar dados em tempo real
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Definição das variáveis (Prioriza o Banco, senão usa Sessão, senão Vazio)
$user_nome = $user['name'] ?? $_SESSION['user_name'] ?? '';
$user_email = $user['email'] ?? $_SESSION['user_email'] ?? 'E-mail não disponível';
$user_cpf = $user['cpf'] ?? '';
$user_telefone = $user['telefone'] ?? '';
$user_cep = $user['cep'] ?? '';
$user_endereco = $user['endereco'] ?? '';
$user_numero = $user['numero'] ?? '';
$user_bairro = $user['bairro'] ?? '';
$user_cidade = $user['cidade'] ?? '';
$user_estado = $user['estado'] ?? 'SP';
$user_complemento = $user['complemento'] ?? '';

$viewsPath = realpath(__DIR__ . '/../views');
require_once $viewsPath . '/includes/head.php';
require_once $viewsPath . '/includes/header.php';
?>

<main class="container my-5" role="main" aria-label="Área do perfil do cliente">
    <div class="row">
        <nav class="col-md-3 mb-4" aria-label="Menu do perfil">
            <div class="list-group list-group-flush border shadow-sm" id="perfilTabs" role="tablist">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'];
                    unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <a class="list-group-item list-group-item-action d-flex align-items-center active" id="tab-dados-link"
                    data-bs-toggle="list" href="#tab-dados" role="tab" aria-controls="tab-dados" aria-selected="true" tabindex="0">
                    <i class="bi bi-person-gear me-2" aria-hidden="true"></i> <span>Meus Dados</span>
                </a>
                <a class="list-group-item list-group-item-action d-flex align-items-center" id="tab-sacola-link"
                    data-bs-toggle="list" href="#tab-sacola" role="tab" aria-controls="tab-sacola" aria-selected="false" tabindex="0">
                    <i class="bi bi-bag-heart me-2" aria-hidden="true"></i> <span>Minha Sacola</span>
                </a>
                <a class="list-group-item list-group-item-action d-flex align-items-center" id="tab-favoritos-link"
                    data-bs-toggle="list" href="#tab-favoritos" role="tab" aria-controls="tab-favoritos" aria-selected="false" tabindex="0">
                    <i class="bi bi-heart me-2" aria-hidden="true"></i> <span>Meus Favoritos</span>
                </a>
                <a class="list-group-item list-group-item-action d-flex align-items-center" id="tab-pedidos-link"
                    data-bs-toggle="list" href="#tab-pedidos" role="tab" aria-controls="tab-pedidos" aria-selected="false" tabindex="0">
                    <i class="bi bi-box-seam me-2" aria-hidden="true"></i> <span>Meus Pedidos</span>
                </a>
                <a href="/logout" class="list-group-item list-group-item-action text-danger" aria-label="Sair da conta">
                    <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i> <span>Sair</span>
                </a>
            </div>
        </nav>

        <section class="col-md-9" aria-label="Conteúdo do perfil">
            <div class="bg-white p-4 border shadow-sm tab-content" id="nav-tabContent">

                <div class="tab-pane fade show active" id="tab-dados" role="tabpanel" aria-labelledby="tab-dados-link">
                    <h3 class="fw-light mb-4">Minha Conta</h3>
                    <form action="/atualizar_perfil" method="POST" class="essence-form" aria-label="Formulário de atualização de dados do perfil">

                        <div class="mb-5">
                            <h5 class="small text-uppercase letter-spacing-2 text-muted mb-3 border-bottom pb-2">
                                Identificação</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase" for="perfil-nome">Nome Completo</label>
                                    <input type="text" id="perfil-nome" name="nome" value="<?= $user_nome ?>" class="form-control" required autocomplete="name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase" for="perfil-email">E-mail</label>
                                    <input type="email" id="perfil-email" value="<?= htmlspecialchars($user['email'] ?? $user_email) ?>" class="form-control bg-light" readonly autocomplete="email">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase" for="cpf">CPF</label>
                                    <input type="text" name="cpf" id="cpf" value="<?= $user_cpf ?>" placeholder="000.000.000-00" class="form-control" required autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase" for="telefone">Telefone / WhatsApp</label>
                                    <input type="tel" name="telefone" id="telefone" value="<?= $user_telefone ?>" placeholder="(00) 00000-0000" class="form-control" required autocomplete="tel">
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h5 class="small text-uppercase letter-spacing-2 text-muted mb-3 border-bottom pb-2">Meu
                                Endereço (Residencial)</h5>
                            <p class="text-muted small mb-3">Este endereço é usado para o seu cadastro pessoal.</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small text-uppercase" for="cep">CEP</label>
                                    <input type="text" name="cep" id="cep" value="<?= $user_cep ?>" class="form-control" placeholder="00000-000" required autocomplete="postal-code">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase" for="endereco">Logradouro</label>
                                    <input type="text" name="endereco" id="endereco" value="<?= $user_endereco ?>" class="form-control" required autocomplete="address-line1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-uppercase" for="perfil-numero">Nº</label>
                                    <input type="text" id="perfil-numero" name="numero" value="<?= $user_numero ?>" class="form-control" required autocomplete="address-line2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-uppercase" for="bairro">Bairro</label>
                                    <input type="text" name="bairro" id="bairro" value="<?= $user_bairro ?>" class="form-control" required autocomplete="address-level3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-uppercase" for="cidade">Cidade</label>
                                    <input type="text" name="cidade" id="cidade" value="<?= $user_cidade ?>" class="form-control" required autocomplete="address-level2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-uppercase" for="uf">Estado</label>
                                    <select name="estado" id="uf" class="form-select" required autocomplete="address-level1">
                                        <option value="">Selecione</option>
                                        <?php
                                        $estados = [
                                            'AC' => 'Acre',
                                            'AL' => 'Alagoas',
                                            'AP' => 'Amapá',
                                            'AM' => 'Amazonas',
                                            'BA' => 'Bahia',
                                            'CE' => 'Ceará',
                                            'DF' => 'Distrito Federal',
                                            'ES' => 'Espírito Santo',
                                            'GO' => 'Goiás',
                                            'MA' => 'Maranhão',
                                            'MT' => 'Mato Grosso',
                                            'MS' => 'Mato Grosso do Sul',
                                            'MG' => 'Minas Gerais',
                                            'PA' => 'Pará',
                                            'PB' => 'Paraíba',
                                            'PR' => 'Paraná',
                                            'PE' => 'Pernambuco',
                                            'PI' => 'Piauí',
                                            'RJ' => 'Rio de Janeiro',
                                            'RN' => 'Rio Grande do Norte',
                                            'RS' => 'Rio Grande do Sul',
                                            'RO' => 'Rondônia',
                                            'RR' => 'Roraima',
                                            'SC' => 'Santa Catarina',
                                            'SP' => 'São Paulo',
                                            'SE' => 'Sergipe',
                                            'TO' => 'Tocantins'
                                        ];
                                        foreach ($estados as $sigla => $nome) {
                                            $selected = ($user_estado == $sigla) ? 'selected' : '';
                                            echo "<option value=\"$sigla\" $selected>$nome</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-uppercase" for="perfil-complemento">Complemento (Opcional)</label>
                                    <input type="text" id="perfil-complemento" name="complemento" value="<?= $user_complemento ?>" class="form-control" placeholder="Apto, bloco, referência..." autocomplete="address-line3">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="small text-uppercase letter-spacing-2 text-muted mb-3 border-bottom pb-2">
                                Segurança</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase" for="nova_senha">Nova Senha</label>
                                    <input type="password" name="nova_senha" id="nova_senha" class="form-control" placeholder="Deixe em branco para manter" autocomplete="new-password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase" for="confirma_senha">Confirmar Nova Senha</label>
                                    <input type="password" name="confirma_senha" id="confirma_senha" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-top">
                            <button type="submit" class="btn btn-dark px-5" aria-label="Salvar alterações do perfil">Salvar Cadastro</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="tab-sacola" role="tabpanel" aria-labelledby="tab-sacola-link">
                    <h3 class="fw-light mb-4">Minha Sacola</h3>
                    <?php
                    $cartQuery = $db->prepare('SELECT c.id, c.product_id, c.quantity, c.selected_size, p.name, p.price, p.image_url, p.stock_by_size FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = :user_id ORDER BY c.added_at DESC');
                    $cartQuery->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $cartQuery->execute();
                    $cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (empty($cartItems)): ?>
                        <div class="text-center py-5" role="alert" aria-live="polite">
                            <i class="bi bi-bag-x display-1 text-light" aria-hidden="true"></i>
                            <p class="mt-3 text-muted">A tua sacola está vazia.</p>
                            <a href="/" class="btn btn-outline-dark btn-sm" aria-label="Ir para a loja">Ir para a Loja</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive mb-4">
                            <table class="table align-middle">
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
                                <?php
                                $subtotal = 0;
                                $totalItems = 0;
                                foreach ($cartItems as $item):
                                    $stock_by_size = $item['stock_by_size'] ? json_decode($item['stock_by_size'], true) : [];
                                    $itemSubtotal = $item['price'] * $item['quantity'];
                                    $subtotal += $itemSubtotal;
                                    $totalItems += $item['quantity'];
                                ?>
                                    <tr data-cart-id="<?= $item['id'] ?>">
                                        <td>
                                            <div class="d-flex gap-2 align-items-center">
                                                <img src="<?= htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/80') ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">
                                                <a href="/produto?id=<?= $item['product_id'] ?>" class="text-decoration-none text-dark ms-2"> <?= htmlspecialchars($item['name']) ?> </a>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($item['selected_size'] ?: 'Único') ?></span></td>
                                        <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>R$ <?= number_format($itemSubtotal, 2, ',', '.') ?></td>
                                        <td>
                                            <a href="/produto?id=<?= $item['product_id'] ?>" class="btn btn-outline-dark btn-sm">Ver Produto</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <div>
                                <strong>Total de itens:</strong> <?= $totalItems ?><br>
                                <strong>Subtotal:</strong> R$ <?= number_format($subtotal, 2, ',', '.') ?>
                            </div>
                            <a href="/sacola" class="btn btn-darkness">Finalizar Compra</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="tab-favoritos" role="tabpanel" aria-labelledby="tab-favoritos-link">
                    <h3 class="fw-light mb-4">Meus Favoritos</h3>
                    <?php
                    // Buscar favoritos do usuário
                    $favQuery = $db->prepare('SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = :user_id');
                    $favQuery->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $favQuery->execute();
                    $favoritos = $favQuery->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (empty($favoritos)): ?>
                        <p class="text-muted text-center py-5" role="alert" aria-live="polite">Nenhum favorito encontrado.</p>
                    <?php else: ?>
                        <div class="row g-4">
                        <?php foreach ($favoritos as $produto): ?>
                            <div class="col-md-4">
                                <div class="card h-100 shadow-sm">
                                    <img src="<?= htmlspecialchars($produto['image_url'] ?? 'https://via.placeholder.com/300') ?>" class="card-img-top" alt="<?= htmlspecialchars($produto['name']) ?>">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-2"><?= htmlspecialchars($produto['name']) ?></h5>
                                        <p class="card-text text-muted mb-2">R$ <?= number_format($produto['price'], 2, ',', '.') ?></p>
                                        <div class="mt-auto d-grid gap-2">
                                            <a href="/produto?id=<?= $produto['id'] ?>" class="btn btn-outline-dark btn-sm">Ver Produto</a>
                                            <button class="btn btn-darkness btn-sm btn-add-fav-to-cart" data-product-id="<?= $produto['id'] ?>">Adicionar à Sacola</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="tab-pedidos" role="tabpanel" aria-labelledby="tab-pedidos-link">
                    <h3 class="fw-light mb-4">Meus Pedidos</h3>
                    <p class="text-muted text-center py-5" role="alert" aria-live="polite">Nenhum pedido realizado ainda.</p>
                </div>

            </div>
        </section>
    </div>
</main>


<script>
document.querySelectorAll('.btn-add-fav-to-cart').forEach(btn => {
    btn.addEventListener('click', async function() {
        const productId = this.dataset.productId;
        // Para produtos com tamanho, pode-se abrir modal ou selecionar padrão
        // Aqui, adiciona 1 unidade, sem tamanho
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        formData.append('size', '');
        this.disabled = true;
        this.textContent = 'Adicionando...';
        try {
            const response = await fetch('/api/add-to-cart.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showToast('Adicionado à sacola com sucesso!', 'success');
                document.getElementById('cartCount').textContent = data.cart_count;
            } else {
                showToast('Erro: ' + data.message, 'error');
            }
        } catch (e) {
            showToast('Erro ao adicionar à sacola', 'error');
        } finally {
            this.disabled = false;
            this.textContent = 'Adicionar à Sacola';
        }
    });
});
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

<script>
    document.getElementById('cep').addEventListener('blur', function () {
        let cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            document.getElementById('endereco').value = "...";
            document.getElementById('bairro').value = "...";
            document.getElementById('cidade').value = "...";
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(r => r.json())
                .then(dados => {
                    if (!dados.erro) {
                        document.getElementById('endereco').value = dados.logradouro;
                        document.getElementById('bairro').value = dados.bairro;
                        document.getElementById('cidade').value = dados.localidade;
                        document.getElementById('uf').value = dados.uf;
                    }
                });
        }
    });
</script>