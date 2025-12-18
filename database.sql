CREATE DATABASE IF NOT EXISTS expense_tracker;
USE expense_tracker;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    monthly_allowance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) DEFAULT '#CCCCCC', /* Hex color for UI */
    user_id INT DEFAULT NULL, /* NULL for system default categories */
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* Seed default categories if not exists - This part is usually run once or handled in app logic. 
   For SQL script, we can use INSERT IGNORE based on unique name constraint if we add one, 
   but for simplicity, I'll just create the table structure. 
*/

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS category_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    limit_amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, category_id)
);

/* Insert Default Categories */
INSERT INTO categories (name, color, user_id) VALUES 
('Food', '#FF5733', NULL),
('Travel', '#33FF57', NULL),
('Bills', '#3357FF', NULL),
('Subscriptions', '#F333FF', NULL),
('Education', '#33FFF5', NULL),
('Entertainment', '#FF33A8', NULL),
('Miscellaneous', '#808080', NULL);
