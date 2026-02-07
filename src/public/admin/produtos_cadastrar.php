<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';

// Proteção: Apenas admins
Auth::checkAdmin();

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // Busca categorias para o <select>
    $query = "SELECT * FROM categories ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao carregar formulário: " . $e->getMessage());
    header("Location: /admin/?error=system_error");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Cadastrar Produto</title>
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
                    <h4 class="mb-0">Cadastrar Novo Produto</h4>
                </div>
                <div class="card-body">
                    <form action="/admin/salvar_produto" method="POST">
                        <div class="mb-3">
                            <label class="form-label" for="name">Nome do Produto <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   aria-label="Nome do produto"
                                   aria-describedby="name_help"
                                   placeholder="Ex: Sutiã Básico Premium"
                                   minlength="3"
                                   maxlength="100">
                            <small id="name_help" class="text-muted">Mínimo 3 caracteres</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="codigo">Código do Produto <span class="text-muted">(opcional)</span></label>
                            <input type="text" id="codigo" name="codigo" class="form-control" aria-label="Código do produto">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="category_id">Categoria <span class="text-danger">*</span></label>
                                <select id="category_id" name="category_id" class="form-select" required
                                        aria-label="Selecionar categoria do produto"
                                        aria-describedby="category_help">
                                    <option value="">-- Selecione uma categoria --</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small id="category_help" class="text-muted">Escolha a categoria do produto</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="price">Preço (R$) <span class="text-danger">*</span></label>
                                <input type="number" id="price" step="0.01" name="price" class="form-control" required 
                                       aria-label="Preço do produto"
                                       aria-describedby="price_help"
                                       placeholder="Ex: 89.90"
                                       min="0.01"
                                       max="999999.99">
                                <small id="price_help" class="text-muted">Formato: 99.90</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="image_url">URL da Imagem</label>
                            <input type="url" id="image_url" name="image_url" class="form-control" 
                                   aria-label="URL da imagem do produto"
                                   aria-describedby="image_help"
                                   placeholder="https://exemplo.com/imagem.jpg">
                            <small id="image_help" class="text-muted">Use uma URL válida (HTTP/HTTPS)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Descrição</label>
                            <textarea id="description" name="description" class="form-control" rows="3"
                                      aria-label="Descrição detalhada do produto"
                                      aria-describedby="desc_help"
                                      placeholder="Descreva os principais características do produto..."
                                      maxlength="1000"></textarea>
                            <small id="desc_help" class="text-muted">Máximo 1000 caracteres</small>
                        </div>

                        <!-- Seleção de Tamanhos com Estoque -->
                        <div class="mb-4">
                            <label class="form-label">Tamanhos e Estoque</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50%;">Tamanho</th>
                                            <th style="width: 50%;">Quantidade em Estoque</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $tamanhos_disponiveis = ['PP', 'P', 'M', 'G', 'GG', 'XG', 'XGG'];
                                        foreach ($tamanhos_disponiveis as $tam): 
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input tamanho-checkbox" type="checkbox" 
                                                               name="sizes[]" value="<?= $tam ?>" id="size_<?= $tam ?>"
                                                               aria-label="Selecionar tamanho <?= $tam ?>">
                                                        <label class="form-check-label" for="size_<?= $tam ?>">
                                                            <strong><?= $tam ?></strong>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" name="stock_<?= $tam ?>" class="form-control form-control-sm stock-input" 
                                                           value="0" min="0" placeholder="0" 
                                                           aria-label="Estoque para tamanho <?= $tam ?>"
                                                           disabled>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">💡 Selecione os tamanhos e preencha a quantidade em estoque</small>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_offer" value="1" id="is_offer"
                                           aria-label="Marcar este produto como oferta especial">
                                    <label class="form-check-label" for="is_offer">Produto em Oferta?</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark text-uppercase">Cadastrar Produto</button>
                            <a href="/admin/" class="btn btn-link text-muted">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

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