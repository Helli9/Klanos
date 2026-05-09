-- =========================
-- DATABASE
-- =========================
CREATE DATABASE IF NOT EXISTS myapp
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE myapp;

-- =========================
-- USERS TABLE
-- =========================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    player_class ENUM ('dps','heal','tank','none') DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- NEED LIST TABLE
-- =========================
CREATE TABLE need_list (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category VARCHAR(50) NOT NULL,
    item VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    status ENUM('pending', 'done') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_need_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =========================
-- ITEMS TABLE
-- =========================
CREATE TABLE items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50),
    item VARCHAR(100)
);

-- =========================
-- login_attempts 
-- =========================
CREATE TABLE login_attempts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(150) NOT NULL,
    ip          VARCHAR(45)  NOT NULL,
    attempted_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ip    (ip)
);
-- =========================
-- INDEXES (performance)
-- =========================
CREATE INDEX idx_need_user ON need_list(user_id);
CREATE INDEX idx_need_category ON need_list(category);

-- =========================
-- SEED ITEMS
-- =========================
INSERT INTO items (category, item)
VALUES 
('Archboss Weapon', 'Cordy'),
('Archboss Weapon', 'Deluzhnoa'),
('Archboss Weapon', 'Queen bellandir'),

('Belt', 'Belt of claimed trophies'),
('Belt', 'Belt of hypocrisy'),
('Belt', 'Belt of the sun king'),

('Bracelet', 'Bracelet of agony'),
('Bracelet', 'Bracelet scales bracer'),
('Bracelet', 'Binding life bracelet');