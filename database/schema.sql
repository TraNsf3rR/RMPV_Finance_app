CREATE DATABASE IF NOT EXISTS finance_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finance_tracker;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_transactions_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

CREATE INDEX idx_transactions_user_date ON transactions(user_id, transaction_date);
CREATE INDEX idx_transactions_user_type ON transactions(user_id, type);
CREATE INDEX idx_categories_type ON categories(type);

INSERT INTO categories (user_id, name, type, is_default)
SELECT NULL, 'Work', 'income', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE user_id IS NULL AND name = 'Work' AND type = 'income');

INSERT INTO categories (user_id, name, type, is_default)
SELECT NULL, 'Freelance', 'income', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE user_id IS NULL AND name = 'Freelance' AND type = 'income');

INSERT INTO categories (user_id, name, type, is_default)
SELECT NULL, 'Side-job', 'income', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE user_id IS NULL AND name = 'Side-job' AND type = 'income');

INSERT INTO categories (user_id, name, type, is_default)
SELECT NULL, 'Food', 'expense', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE user_id IS NULL AND name = 'Food' AND type = 'expense');

INSERT INTO categories (user_id, name, type, is_default)
SELECT NULL, 'Bills', 'expense', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE user_id IS NULL AND name = 'Bills' AND type = 'expense');

INSERT INTO categories (user_id, name, type, is_default)
SELECT NULL, 'Transportations', 'expense', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE user_id IS NULL AND name = 'Transportations' AND type = 'expense');

INSERT INTO categories (user_id, name, type, is_default)
SELECT NULL, 'Entertainment', 'expense', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE user_id IS NULL AND name = 'Entertainment' AND type = 'expense');
