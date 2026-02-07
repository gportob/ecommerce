<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/../../config/StockManager.php';

// Proteção: Apenas admins
Auth::checkAdmin();

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // Validar ID do produto
    $productId = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
    if (!$productId) {
        throw new Exception("Produto não encontrado");
    }

    // Buscar produto
    $query = "SELECT * FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Produto não encontrado");
    }

    // Decodificar tamanhos
    $sizes = [];
    if ($product['sizes']) {
        $sizes = json_decode($product['sizes'], true) ?? [];
    }

    // Decodificar estoque por tamanho
    $stock_by_size = [];
    if ($product['stock_by_size']) {
        $stock_by_size = json_decode($product['stock_by_size'], true) ?? [];
    }

    // Buscar categorias
    $catQuery = "SELECT * FROM categories ORDER BY name ASC";
    $catStmt = $db->prepare($catQuery);
    $catStmt->execute();
    $categorias = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // Tamanhos disponíveis
    $tamanhos_disponiveis = ['PP', 'P', 'M', 'G', 'GG', 'XG', 'XGG'];

} catch (Exception $e) {
    error_log("Erro ao carregar produto: " . $e->getMessage());
    header("Location: /admin/?error=product_not_found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1">Essence Admin</span>
        <div class="d-flex">
            <a href="/admin/" class="btn btn-outline-light btn-sm me-2">Voltar</a>
            <a href="/logout" class="btn btn-danger btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Editar Produto: <?= htmlspecialchars($product['name']) ?></h4>
                </div>
                <div class="card-body">
                    <form action="/admin/salvar_produto_edit" method="POST">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Nome do Produto</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="codigo">Código do Produto <span class="text-muted">(opcional)</span></label>
                            <input type="text" id="codigo" name="codigo" class="form-control" value="<?= htmlspecialchars($product['codigo'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoria</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                            <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preço (Ex: 99.90)</label>
                                <input type="number" step="0.01" name="price" class="form-control" 
                                       value="<?= number_format($product['price'], 2, '.', '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL da Imagem</label>
                            <input type="text" name="image_url" class="form-control" 
                                   value="<?= htmlspecialchars($product['image_url'] ?? '') ?>"
                                   placeholder="http://... ou /assets/img/...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Seleção de Tamanhos e Estoque por Tamanho -->
                        <div class="mb-3">
                            <label class="form-label">Tamanhos e Estoque</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 40%;">Tamanho</th>
                                            <th scope="col" style="width: 60%;">Quantidade em Estoque</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        foreach ($tamanhos_disponiveis as $tam): 
                                            $isChecked = in_array($tam, $sizes);
                                            $currentStock = $stock_by_size[$tam] ?? 0;
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input tamanho-checkbox" type="checkbox" 
                                                               name="sizes[]" value="<?= $tam ?>" id="size_<?= $tam ?>"
                                                               <?= $isChecked ? 'checked' : '' ?>
                                                               aria-label="Selecionar tamanho <?= $tam ?>">
                                                        <label class="form-check-label" for="size_<?= $tam ?>">
                                                            <strong><?= $tam ?></strong>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" name="stock_<?= $tam ?>" class="form-control form-control-sm stock-input" 
                                                           value="<?= $currentStock ?>" min="0" placeholder="0" 
                                                           aria-label="Estoque para tamanho <?= $tam ?>"
                                                           <?= !$isChecked ? 'disabled' : '' ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">💡 Selecione os tamanhos e preencha a quantidade em estoque</small>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_offer" 
                                           value="1" id="is_offer" <?= $product['is_offer'] ? 'checked' : '' ?>
                                           aria-label="Marcar como oferta">
                                    <label class="form-check-label" for="is_offer">
                                        Produto em Oferta?
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark text-uppercase">Salvar Alterações</button>
                            <a href="/admin/" class="btn btn-link text-muted">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle stock inputs based on size selection
    document.querySelectorAll('.tamanho-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const stockInput = this.closest('tr').querySelector('.stock-input');
            stockInput.disabled = !this.checked;
            if (this.checked) stockInput.focus();
            else stockInput.value = '0';
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const tamanhos = document.querySelectorAll('.tamanho-checkbox:checked').length;
        if (tamanhos === 0) {
            e.preventDefault();
            alert('Por favor, selecione pelo menos um tamanho disponível!');
            return;
        }

        // Validate stock quantities
        const selectedRows = document.querySelectorAll('.tamanho-checkbox:checked');
        let hasValidStock = false;
        selectedRows.forEach(checkbox => {
            const stockInput = checkbox.closest('tr').querySelector('.stock-input');
            const stockValue = parseInt(stockInput.value) || 0;
            if (stockValue > 0) hasValidStock = true;
        });

        if (!hasValidStock) {
            e.preventDefault();
            alert('Por favor, preencha a quantidade em estoque para pelo menos um tamanho!');
        }
    });
</script>

</body>
</html>
