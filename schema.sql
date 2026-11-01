CREATE DATABASE IF NOT EXISTS touch_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE touch_shop;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255),
  price INT NOT NULL
);

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
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_order VARCHAR(30) NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price INT NOT NULL,
  FOREIGN KEY (id_order) REFERENCES orders(id_order),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO products (name, description, price) VALUES
('Silent Heirloom', 'Eau de Parfum, hương nhựa gỗ, gỗ đàn hương và vani êm dịu', 2090000),
('Purple Rice', 'Eau de Parfum, sữa gạo, cánh hoa tím và iris', 2090000),
('Naughty Paper', 'Eau de Parfum, da thuộc, nhang và gỗ cedar ấm', 2090000),
('Lush Garden', 'Eau de Parfum, bergamot, trà xanh và lá sung', 2090000),
('Innocent Skin', 'Eau de Parfum, musk trắng, gỗ đàn hương và hoa đào', 2090000),
('L''Amant', 'Eau de Toilette, neroli, hoa cam và hổ phách ấm', 420000);
