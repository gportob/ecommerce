<?php
session_start();
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/../../config/StockManager.php';

// Proteção: Garante que apenas admins acessem
Auth::checkAdmin();

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // Busca estatísticas simples
    $totalProdutos = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalCategorias = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $produtosOferta = $db->query("SELECT COUNT(*) FROM products WHERE is_offer = 1")->fetchColumn();

    // Pesquisa de produtos
    $search = trim($_GET['search'] ?? '');
    $searchSql = '';
    $searchParams = [];
    if ($search !== '') {
        $searchSql = 'WHERE (p.name LIKE :search OR p.codigo LIKE :search OR c.name LIKE :search)';
        $searchParams[':search'] = "%$search%";
    }
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          $searchSql
                          ORDER BY p.id DESC LIMIT 20");
    foreach ($searchParams as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $ultimosProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Erro ao carregar admin: " . $e->getMessage());
    header("Location: /login?error=system_error");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Essence Lingerie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4" role="navigation" aria-label="Navegação administrativa">
    <div class="container">
        <span class="navbar-brand mb-0 h1">Essence Admin</span>
        <div class="d-flex">
            <a href="/" class="btn btn-outline-light btn-sm me-2">Ver Loja</a>
            <a href="/logout" class="btn btn-danger btn-sm">Sair</a>
        </div>
    </div>
</nav>

<main class="container" role="main" aria-label="Painel administrativo">
    <div class="row mb-4" aria-label="Resumo de estatísticas">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <i class="bi bi-box-seam h1 text-primary"></i>
                <h5 class="text-muted">Produtos</h5>
                <h2 class="fw-bold"><?= $totalProdutos ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <i class="bi bi-tags h1 text-success"></i>
                <h5 class="text-muted">Categorias</h5>
                <h2 class="fw-bold"><?= $totalCategorias ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <i class="bi bi-percent h1 text-warning"></i>
                <h5 class="text-muted">Em Oferta</h5>
                <h2 class="fw-bold"><?= $produtosOferta ?></h2>
            </div>
        </div>
    </div>

    <div class="row" aria-label="Ações e produtos">
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-light" id="acoes-rapidas">Ações Rápidas</h4>
            </div>
            <div class="d-flex gap-2" aria-labelledby="acoes-rapidas">
                <a href="/admin/produtos_cadastrar" class="btn btn-dark" aria-label="Cadastrar novo produto">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Novo Produto
                </a>
                <a href="/admin/categorias_gerenciar" class="btn btn-outline-dark" aria-label="Gerenciar categorias">
                    <i class="bi bi-folder-plus" aria-hidden="true"></i> Gerenciar Categorias
                </a>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card border-0 shadow-sm" aria-label="Tabela de produtos">
                <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0" id="produtos-titulo">Produtos</h5>
                    <form class="d-flex" method="get" action="" role="search" aria-label="Pesquisar produtos">
                        <label for="admin-search" class="visually-hidden">Pesquisar produtos</label>
                        <input type="text" id="admin-search" class="form-control form-control-sm me-2" name="search" placeholder="Pesquisar por nome, código ou categoria" value="<?= htmlspecialchars($search ?? '') ?>">
                        <button class="btn btn-outline-dark btn-sm" type="submit" aria-label="Pesquisar"><i class="bi bi-search" aria-hidden="true"></i> Pesquisar</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" aria-describedby="produtos-titulo">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nome</th>
                                <th scope="col">Código</th>
                                <th scope="col">Categoria</th>
                                <th scope="col">Preço</th>
                                <th scope="col">Estoque</th>
                                <th scope="col" class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($ultimosProdutos) > 0): ?>
                                <?php foreach ($ultimosProdutos as $p): ?>
                                <tr tabindex="0">
                                    <td>#<?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['codigo'] ?? '') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['category_name'] ?? 'Sem categoria') ?></span></td>
                                    <td>R$ <?= number_format($p['price'], 2, ',', '.') ?></td>
                                    <td><?php 
                                        $stock_by_size = $p['stock_by_size'] ? json_decode($p['stock_by_size'], true) : [];
                                        $total_stock = StockManager::getTotalStock($stock_by_size);
                                        echo $total_stock;
                                    ?> un.</td>
                                    <td class="text-end">
                                        <a href="/admin/produtos_editar?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" aria-label="Editar produto <?= htmlspecialchars($p['name']) ?>"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-deletar" 
                                                data-id="<?= $p['id'] ?>" data-nome="<?= htmlspecialchars($p['name']) ?>" aria-label="Deletar produto <?= htmlspecialchars($p['name']) ?>">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted" role="alert" aria-live="polite">Nenhum produto cadastrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


</main>
</body>
</html>

<script>
    // Função para deletar produto
    document.querySelectorAll('.btn-deletar').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.nome;
            
            if (confirm(`Tem certeza que deseja deletar o produto "${productName}"? Esta ação não pode ser desfeita.`)) {
                window.location.href = `/admin/produtos_deletar?id=${productId}`;
            }
        });
    });

    // Mostrar mensagens de sucesso
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        const success = urlParams.get('success');
        let message = '';
        
        if (success === 'product_updated') {
            message = 'Produto atualizado com sucesso!';
        } else if (success === 'product_deleted') {
            message = 'Produto deletado com sucesso!';
        } else if (success === '1') {
            message = 'Produto cadastrado com sucesso!';
        }
        
        if (message) {
            alert(message);
            // Limpar URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    // Mostrar mensagens de erro
    if (urlParams.get('error')) {
        alert('Erro: ' + urlParams.get('error'));
    }
</script>
