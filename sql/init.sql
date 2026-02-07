CREATE DATABASE IF NOT EXISTS essence_lingerie_db;
USE essence_lingerie_db;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE -- Adicionado UNIQUE para evitar categorias duplicadas
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    sizes JSON DEFAULT NULL,
    stock_by_size JSON DEFAULT NULL,
    is_offer BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_offer (is_offer)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') DEFAULT 'client',
    cpf VARCHAR(14),
    telefone VARCHAR(20),
    cep VARCHAR(10),
    endereco VARCHAR(255),
    numero VARCHAR(20),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),
    complemento VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Carrinho
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    selected_size VARCHAR(10),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_size (user_id, product_id, selected_size),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
);

-- Tabela de Wishlist (Favoritos)
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
);

-- INSERIR USUÁRIO ADMIN PADRÃO (Senha: admin123)
-- Isso garante que toda vez que você subir o Docker, o admin exista
INSERT IGNORE INTO users (name, email, password, role) 
VALUES ('Administrador Essence', 'admin@essence.com', '$2y$10$cxtlrjvI3BYNBbJQ3hcPDuhlo75BB5pTZ7RN.ONIiSzcdMkiMKkK2', 'admin');

-- INSERIR CATEGORIAS INICIAIS (Opcional, mas ajuda nos testes)
INSERT IGNORE INTO categories (name) VALUES ('Lingerie'), ('Conjuntos'), ('Bodys'), ('Novidades'), ('Ofertas'), ('Pijamas');

-- INSERIR PRODUTOS DE EXEMPLO
INSERT IGNORE INTO products (category_id, name, description, price, image_url, sizes, stock_by_size, is_offer)
VALUES
(1, 'Sutiã Básico Premium', 'Sutiã clássico em algodão orgânico com suporte confortável', 89.90, 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=800&auto=format&fit=crop', '["PP", "P", "M", "G", "GG", "XG"]', '{"PP": 5, "P": 10, "M": 15, "G": 12, "GG": 5, "XG": 3}', 0),
(2, 'Conjunto Rosa Blush', 'Conjunto completo em tom rosa suave com renda delicada', 129.90, 'https://images.unsplash.com/photo-1548690312-5639d0873e0b?q=80&w=800&auto=format&fit=crop', '["PP", "P", "M", "G", "GG"]', '{"PP": 8, "P": 12, "M": 10, "G": 5, "GG": 3}', 0),
(3, 'Body Preto Elegante', 'Body alongado em crepe com acabamento em renda', 99.90, 'https://images.unsplash.com/photo-1515621684202-f0f14c65f660?q=80&w=800&auto=format&fit=crop', '["PP", "P", "M", "G", "GG", "XG", "XGG"]', '{"PP": 3, "P": 8, "M": 12, "G": 10, "GG": 4, "XG": 2, "XGG": 1}', 1),
(4, 'Pijama Seda Cinza', 'Pijama em seda artificial super macio e elegante', 149.90, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?q=80&w=800&auto=format&fit=crop', '["P", "M", "G", "GG"]', '{"P": 5, "M": 8, "G": 4, "GG": 2}', 0);