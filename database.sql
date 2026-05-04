-- SwiftCivic Database SQL
-- Import this file to InfinityFree phpMyAdmin
-- Database: if0_41810514_swiftcivic
-- Host: sql309.infinityfree.com

DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(50),
    address TEXT,
    role ENUM('citizen', 'admin') DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tracking_number VARCHAR(50) NOT NULL UNIQUE,
    doc_type VARCHAR(100) NOT NULL,
    delivery_address TEXT NOT NULL,
    contact_number VARCHAR(50),
    status ENUM('Awaiting Payment', 'Verification Pending', 'Processing', 'Released', 'Action Required', 'Cancelled') DEFAULT 'Awaiting Payment',
    document_path VARCHAR(500),
    id_path VARCHAR(500),
    receipt_path VARCHAR(500),
    admin_notes TEXT,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    receipt_path VARCHAR(500),
    status ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    verified_by INT,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Admin User (password: EvilfNrfQ0W)
-- Replace HASH below with output from generate_hash.php
INSERT INTO users (full_name, email, password, role) VALUES 
('Admin', 'admin@swiftcivic.com', '$2y$10$8tDzR7bqJmGpNX1KzVbqKOQx5vQxQxQxQxQxQxQxQxQxQxQxQ', 'admin');
