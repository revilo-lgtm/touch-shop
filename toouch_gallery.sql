USE touch_shop;
INSERT INTO products (id, name, description, price) VALUES (7, 'Creamy Tide', 'Vải, đào và sữa gạo béo mịn — chậm rãi như buổi chiều sông nước miền Tây.', 2090000)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), price = VALUES(price);
