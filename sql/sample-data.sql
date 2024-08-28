USE blemish_inventory;

-- Insert Sample Users
INSERT INTO users (username, password, role) VALUES
('admin', SHA2('admin123', 256), 'admin'),
('user1', SHA2('user1password', 256), 'user');

-- Insert Sample Permissions
INSERT INTO permissions (user_id, permission_name) VALUES
(1, 'manage_users'),
(1, 'manage_inventory'),
(2, 'view_products');

-- Insert sample data into the Categories Table
INSERT INTO categories (categories_name) VALUES
('T-Shirts'), ('Jeans'), ('Dresses'), ('Jackets'), ('Shorts'),
('Sweaters'), ('Hats'), ('Shoes'), ('Socks'), ('Accessories'),
('Belts'), ('Sunglasses'), ('Watches'), ('Bags'), ('Scarves'),
('Underwear'), ('Sportswear'), ('Nightwear'), ('Suits'), ('Ties'),
('Gloves'), ('Swimwear'), ('Formal Wear'), ('Casual Wear'), ('Workwear'),
('Outerwear'), ('Loungewear'), ('Footwear'), ('Jewelry'), ('Activewear');

-- Insert sample data into the Supplier Table
INSERT INTO supplier (name, email, phone, address) VALUES
('ABC Clothing Co.', 'contact@abcclothing.com', '555-1234', '123 Fashion St, NY'),
('Trendy Threads', 'info@trendythreads.com', '555-5678', '456 Style Ave, LA'),
('Fashion Hub', 'support@fashionhub.com', '555-9101', '789 Chic Blvd, SF'),
('Urban Outfitters', 'sales@urbanoutfitters.com', '555-1122', '101 Trendy Ln, TX'),
('Classic Apparel', 'contact@classicapparel.com', '555-3141', '202 Elegant Rd, FL');

-- Insert sample data into the Product Table
INSERT INTO product (product_name, product_des, categories_id, sellPrice) VALUES
('Basic White T-Shirt', '100% cotton, unisex', 1, '15.00'),
('Skinny Jeans', 'Slim fit, stretchable', 2, '40.00'),
('Floral Dress', 'Summer dress with floral print', 3, '55.00'),
('Leather Jacket', 'Genuine leather, biker style', 4, '150.00'),
('Denim Shorts', 'High-waisted, ripped', 5, '30.00'),
('Wool Sweater', 'Cozy and warm, crew neck', 6, '60.00'),
('Baseball Cap', 'Adjustable size, various colors', 7, '20.00'),
('Running Shoes', 'Breathable and lightweight', 8, '75.00'),
('Crew Socks', 'Pack of 5, various colors', 9, '12.00'),
('Leather Belt', 'Classic buckle, genuine leather', 11, '25.00'),
('Aviator Sunglasses', 'UV protection, metal frame', 12, '50.00'),
('Digital Watch', 'Water-resistant, multiple functions', 13, '80.00'),
('Backpack', 'Spacious and durable', 14, '45.00'),
('Wool Scarf', 'Soft and warm, various colors', 15, '25.00'),
('Boxer Briefs', 'Pack of 3, comfortable fit', 16, '18.00'),
('Yoga Pants', 'Stretchable and breathable', 17, '35.00'),
('Silk Pajamas', 'Luxury sleepwear, smooth feel', 18, '65.00'),
('Tailored Suit', 'Modern fit, premium fabric', 19, '250.00'),
('Silk Tie', 'Elegant, various patterns', 20, '30.00'),
('Leather Gloves', 'Warm and stylish', 21, '45.00'),
('Bikini Set', 'Two-piece, vibrant colors', 22, '40.00'),
('Evening Gown', 'Formal wear, floor length', 23, '180.00'),
('Casual Blazer', 'Smart-casual, versatile', 24, '120.00'),
('Work Boots', 'Durable and comfortable', 25, '90.00'),
('Winter Coat', 'Heavy-duty, insulated', 26, '200.00'),
('Lounge Set', 'Comfortable, casual wear', 27, '50.00'),
('Sneakers', 'Casual, everyday wear', 28, '65.00'),
('Diamond Earrings', 'Elegant, sparkling diamonds', 29, '300.00'),
('Compression Shorts', 'Supportive, breathable', 30, '25.00');

-- Insert sample data into the Inventory Table
INSERT INTO inventory (product_id, product_name, quantity_in_stock) VALUES
(1, 'Basic White T-Shirt', 150),
(2, 'Skinny Jeans', 100),
(3, 'Floral Dress', 80),
(4, 'Leather Jacket', 50),
(5, 'Denim Shorts', 120),
(6, 'Wool Sweater', 60),
(7, 'Baseball Cap', 200),
(8, 'Running Shoes', 90),
(9, 'Crew Socks', 300),
(10, 'Leather Belt', 110),
(11, 'Aviator Sunglasses', 75),
(12, 'Digital Watch', 40),
(13, 'Backpack', 95),
(14, 'Wool Scarf', 130),
(15, 'Boxer Briefs', 150),
(16, 'Yoga Pants', 85),
(17, 'Silk Pajamas', 70),
(18, 'Tailored Suit', 40),
(19, 'Silk Tie', 100),
(20, 'Leather Gloves', 80),
(21, 'Bikini Set', 110),
(22, 'Evening Gown', 30),
(23, 'Casual Blazer', 60),
(24, 'Work Boots', 90),
(25, 'Winter Coat', 50),
(26, 'Lounge Set', 140),
(27, 'Sneakers', 130),
(28, 'Diamond Earrings', 20),
(29, 'Compression Shorts', 120);

-- Insert sample data into the Orders Table
INSERT INTO orders (order_date, total_amount, supplier_id) VALUES
('2024-08-01', 450.00, 1),
('2024-08-05', 320.00, 2),
('2024-08-10', 675.00, 3),
('2024-08-15', 550.00, 4),
('2024-08-20', 720.00, 5);

-- Insert sample data into the Order Items Table
INSERT INTO order_items (order_id, product_id, quantity, price, total_price) VALUES
(1, 1, 20, 15.00, 300.00),
(1, 7, 10, 20.00, 200.00),
(1, 9, 15, 12.00, 180.00),
(2, 4, 5, 150.00, 750.00),
(2, 6, 8, 60.00, 480.00),
(2, 10, 12, 25.00, 300.00),
(3, 2, 15, 40.00, 600.00),
(3, 11, 7, 50.00, 350.00),
(3, 13, 3, 80.00, 240.00),
(4, 3, 10, 55.00, 550.00),
(4, 8, 7, 75.00, 525.00),
(4, 14, 12, 25.00, 300.00),
(5, 5, 12, 30.00, 360.00),
(5, 15, 15, 18.00, 270.00),
(5, 12, 5, 50.00, 250.00);
