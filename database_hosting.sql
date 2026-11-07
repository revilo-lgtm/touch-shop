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
(1, 'Silent Heirloom', 'Hương gỗ đàn hương, nhựa thơm và vani êm — ấm, trầm, như kỷ vật được giữ rất lâu.', 2090000),
(2, 'Purple Rice', 'Khoai môn, xôi nếp và hoa violet phấn mịn, kết sữa dừa — mộc mạc mà thanh nhã.', 2090000),
(3, 'Naughty Paper', 'Da thuộc, nhang và cedar ấm — sắc, hơi bướng, như trang giấy vừa viết xong.', 2090000),
(4, 'Lush Garden', 'Hoa súng, huệ trắng và đất sau mưa — xanh mát, trong trẻo như góc vườn vừa tạnh.', 2090000),
(5, 'Innocent Skin', 'Xạ hương trắng, gỗ đàn hương và hoa đào — sạch, gần da, như làn da vừa dưỡng.', 2090000),
(6, 'L''Amant', 'Neroli, hoa cam và hổ phách ấm — tươi sáng ban ngày, dịu lại khi chiều về.', 420000),
(7, 'Creamy Tide', 'Vải, đào và sữa gạo béo mịn — chậm rãi như buổi chiều sông nước miền Tây.', 2090000);
