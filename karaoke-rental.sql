-- Create the database
CREATE DATABASE IF NOT EXISTS karaoke_rental;
USE karaoke_rental;

-- --------------------------------------------------
-- Table: users
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user'
);

-- Sample admin and user (default passwords use MD5 for testing only)
INSERT INTO users (name, email, password, phone, address, role) VALUES
('Admin', 'admin@example.com', MD5('admin123'), '09171234567', 'Admin Address', 'admin'),
('Juan Dela Cruz', 'juan@example.com', MD5('password'), '09179876543', 'Manila', 'user');

-- --------------------------------------------------
-- Table: settings
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY,
    total_units INT NOT NULL
);

-- Initial number of karaoke units
INSERT INTO settings (id, total_units) VALUES (1, 10);

-- --------------------------------------------------
-- Table: bookings
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rental_date DATE NOT NULL,
    duration_days INT NOT NULL,
    units_requested INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    units_returned BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
