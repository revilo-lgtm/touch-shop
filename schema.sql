CREATE DATABASE IF NOT EXISTS documents_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE documents_shop;

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
('Documents No.1 - Ink', 'Hương gỗ tuyết tùng, mực in và hổ phách', 1250000),
('Documents No.2 - Paper', 'Hương xạ hương trắng, giấy mới và hoa lily', 1180000),
('Documents No.3 - Archive', 'Hương da thuộc, vani và gỗ đàn hương', 1450000),
('Documents No.4 - Linen', 'Hương cam bergamot, vải lanh và trà xanh', 990000),
('Documents No.5 - Library', 'Hương trầm, sách cũ và hoắc hương', 1520000),
('Documents No.6 - Dawn', 'Hương hoa nhài, đào và xạ hương', 1090000);
