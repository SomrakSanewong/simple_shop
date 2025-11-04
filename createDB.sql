CREATE DATABASE IF NOT EXISTS simple_shop_db;
USE simple_shop_db;

-- 1. ตารางสำหรับผู้ดูแลระบบ
CREATE TABLE `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- เพิ่ม admin เริ่มต้น (user: admin, pass: password123)
-- หมายเหตุ: เราใช้ password_hash() เพื่อความปลอดภัย
INSERT INTO `admin` (`username`, `password`) VALUES
('admin', '$2y$10$PmMmKoUGFRISe5wfjy3GB.Mp4bnpAU57yiln4jg64CvKlbge7UqGK');
-- รหัสผ่านคือ 'password123' (PHP 7.3.5 --> https://onlinephp.io/password-hash)

-- 2. ตารางหมวดหมู่สินค้า
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- เพิ่มข้อมูลหมวดหมู่เริ่มต้น
INSERT INTO `categories` (`name`) VALUES
('Electronics'),
('Books'),
('Clothing');

-- 3. ตารางสินค้า
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL,
  `image_url` VARCHAR(255) DEFAULT 'images/no_photo.png',
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- เพิ่มข้อมูลสินค้าเริ่มต้น
INSERT INTO `products` (`category_id`, `name`, `description`, `price`) VALUES
(1, 'Smartphone', 'A smart phone with 6GB RAM', 15000.00),
(1, 'Laptop', 'A powerful laptop for coding', 35000.00),
(2, 'PHP Programming Book', 'Learn PHP from scratch', 450.00);


-- เพิ่มuser table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `role` ENUM('user', 'admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `products` ADD COLUMN `stock` INT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Pending', 'Processing', 'Completed', 'Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


SELECT id, name, stock, price 
FROM products
ORDER BY stock ASC
LIMIT 10;

SELECT o.id, u.email AS user_email, o.total_price, o.status, o.created_at
FROM orders o
JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC
LIMIT 5;

ALTER TABLE orders MODIFY status VARCHAR(20) NOT NULL DEFAULT 'Pending';

-- โปรโมชั่น
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('fixed','percentage') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- รายการโปรด
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

SELECT p.id, p.name, COUNT(w.product_id) AS total_added
FROM wishlist w
JOIN products p ON w.product_id = p.id
GROUP BY w.product_id
ORDER BY total_added DESC
LIMIT 10;
