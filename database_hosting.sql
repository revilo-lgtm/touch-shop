-- Nhập file này vào database đã tạo sẵn trên hosting (phpMyAdmin > Import)
SET NAMES utf8mb4;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255),
  price INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id_order VARCHAR(30) PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address VARCHAR(255) NOT NULL,
  amount INT NOT NULL,
  payment_method VARCHAR(20) NOT NULL,
  status ENUM('pending','paid','failed') DEFAULT 'pending',
  transaction_id VARCHAR(50) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_order VARCHAR(30) NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price INT NOT NULL,
  FOREIGN KEY (id_order) REFERENCES orders(id_order),
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (id, name, description, price) VALUES
(1, 'Silent Heirloom', 'Eau de Parfum', 2090000),
(2, 'Purple Rice', 'Eau de Parfum', 2090000),
(3, 'Naughty Paper', 'Eau de Parfum', 2090000),
(4, 'Lush Garden', 'Eau de Parfum', 2090000),
(5, 'Innocent Skin', 'Eau de Parfum', 2090000),
(6, 'L''Amant', 'Eau de Toilette', 420000),
(7, 'Creamy Tide', 'Eau de Parfum', 2090000);
