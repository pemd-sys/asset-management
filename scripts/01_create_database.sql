-- Create database and tables for oscilloscope catalog
CREATE DATABASE IF NOT EXISTS oscilloscope_catalog;
USE oscilloscope_catalog;

-- Create brands table
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    model VARCHAR(100) NOT NULL,
    description TEXT,
    brand_id INT,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    image_url VARCHAR(255),
    stock_status ENUM('in_stock', 'low_stock', 'out_of_stock') DEFAULT 'in_stock',
    stock_quantity INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 0,
    review_count INT DEFAULT 0,
    bandwidth VARCHAR(50),
    channels INT,
    sample_rate VARCHAR(50),
    is_featured BOOLEAN DEFAULT FALSE,
    is_on_sale BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_brand (brand_id),
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    INDEX idx_stock (stock_status)
);

-- Create product specifications table for flexible attributes
CREATE TABLE IF NOT EXISTS product_specifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    spec_name VARCHAR(100) NOT NULL,
    spec_value VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
);
