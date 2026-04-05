-- Create bookings table for product rental system
CREATE DATABASE IF NOT EXISTS oscilloscope_catalog;
USE oscilloscope_catalog;

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_status (status)
);

-- Insert sample data 
INSERT INTO bookings (user_id, product_id, start_date, end_date, notes) 
VALUES (
    1, 
    1, 
    '2025-09-22', 
    '2025-10-22', 
    'Test'
) ON DUPLICATE KEY UPDATE id=id;
