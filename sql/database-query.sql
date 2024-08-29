CREATE DATABASE blemish_inventory;
USE blemish_inventory;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password CHAR(64) NOT NULL,  -- SHA-256 produces a 64-character hexadecimal string
    role ENUM('admin', 'user') NOT NULL
);

-- Permissions Table
CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    permission_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Supplier Table
CREATE TABLE supplier (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(191) NOT NULL,
    address VARCHAR(200) NOT NULL,
    PRIMARY KEY (id)
);

-- Categories Table
CREATE TABLE categories (
    categories_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    categories_name VARCHAR(255) NOT NULL
);

-- Product Table
CREATE TABLE product (
    product_id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    product_name VARCHAR(255) NOT NULL,
    product_des VARCHAR(255) NOT NULL,
    categories_id INT(11) NOT NULL,
    sellPrice VARCHAR(255) NOT NULL,
    FOREIGN KEY (categories_id) REFERENCES categories(categories_id) ON DELETE CASCADE
);

-- Inventory Table
CREATE TABLE inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    product_name VARCHAR(255) NOT NULL,    
    quantity_in_stock INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES product(product_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- Orders Table (Modified with 'supplier_id' column)
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    supplier_id INT,
    FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);


-- Sales Table
CREATE TABLE sales (
    sales_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL
);

-- Sales Items Table
CREATE TABLE sales_items (
    sales_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sales_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sales_id) REFERENCES sales(sales_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);