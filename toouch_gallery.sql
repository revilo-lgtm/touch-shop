USE touch_shop;
INSERT INTO products (id, name, description, price) VALUES (7, 'Creamy Tide', 'Eau de Parfum', 2090000)
ON DUPLICATE KEY UPDATE name = VALUES(name);
