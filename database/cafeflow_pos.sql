-- ==========================================================
-- Database Schema & Seed Data for CafeFlow POS
-- Modern Cafe POS & Sales Management System
-- Database: cafeflow_pos
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `refunds`;
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `inventory_movements`;
DROP TABLE IF EXISTS `payment_events`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_recipes`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `expense_categories`;
DROP TABLE IF EXISTS `tables`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'kasir', 'dapur') NOT NULL DEFAULT 'kasir',
  `phone` VARCHAR(25) NULL,
  `avatar` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CATEGORIES TABLE
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(60) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'bi-cup-hot',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. INGREDIENTS TABLE (Bahan Baku)
CREATE TABLE `ingredients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `stock` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `unit` VARCHAR(20) NOT NULL, -- gram, ml, pcs, lembar, dll
  `cost_per_unit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `min_stock` DECIMAL(12,2) NOT NULL DEFAULT 100.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLES (Meja Kafe)
CREATE TABLE `tables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `table_number` VARCHAR(20) NOT NULL UNIQUE,
  `name` VARCHAR(60) NOT NULL,
  `capacity` INT NOT NULL DEFAULT 4,
  `status` ENUM('available', 'occupied', 'reserved') NOT NULL DEFAULT 'available',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. EXPENSE CATEGORIES
CREATE TABLE `expense_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. SETTINGS TABLE
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(60) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. PRODUCTS TABLE
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `sku` VARCHAR(40) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `cost_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `stock_type` ENUM('direct', 'recipe') NOT NULL DEFAULT 'direct',
  `stock` INT NOT NULL DEFAULT 0,
  `min_stock` INT NOT NULL DEFAULT 5,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'porsi',
  `image` VARCHAR(255) NULL,
  `status` ENUM('tersedia', 'habis') NOT NULL DEFAULT 'tersedia',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. PRODUCT RECIPES TABLE
CREATE TABLE `product_recipes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `ingredient_id` INT NOT NULL,
  `quantity` DECIMAL(12,2) NOT NULL, -- Pemakaian bahan per 1 unit produk
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_recipe_product_ingredient` (`product_id`, `ingredient_id`),
  CONSTRAINT `fk_recipes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recipes_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ORDERS TABLE
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(100) NOT NULL DEFAULT 'Pelanggan Walk-in',
  `order_type` ENUM('dine_in', 'takeaway') NOT NULL DEFAULT 'dine_in',
  `table_id` INT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `service_charge` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('unpaid', 'paid', 'refunded', 'expired', 'failed') NOT NULL DEFAULT 'unpaid',
  `order_status` ENUM('pending', 'processing', 'ready', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` ENUM('cash', 'qris', 'none') NOT NULL DEFAULT 'none',
  `stock_deducted` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` TEXT NULL,
  `user_id` INT NOT NULL,
  `paid_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_orders_created_at` (`created_at`),
  INDEX `idx_orders_payment_status` (`payment_status`),
  INDEX `idx_orders_order_status` (`order_status`),
  CONSTRAINT `fk_orders_table` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. ORDER ITEMS TABLE
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `cost_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. PAYMENTS TABLE
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_method` ENUM('cash', 'qris') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `tendered_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `transaction_id` VARCHAR(100) NULL UNIQUE,
  `qr_string` TEXT NULL,
  `qr_url` VARCHAR(500) NULL,
  `status` ENUM('pending', 'paid', 'expired', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `expired_at` DATETIME NULL,
  `paid_at` DATETIME NULL,
  `cashier_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_payments_method_status` (`payment_method`, `status`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_cashier` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. PAYMENT EVENTS TABLE (Webhook and Gateway Logs)
CREATE TABLE `payment_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `payment_id` INT NULL,
  `order_id` INT NULL,
  `gateway` VARCHAR(50) NOT NULL DEFAULT 'midtrans',
  `event_type` VARCHAR(50) NOT NULL,
  `signature_received` VARCHAR(255) NULL,
  `signature_valid` TINYINT(1) NOT NULL DEFAULT 0,
  `payload` LONGTEXT NOT NULL,
  `ip_address` VARCHAR(50) NULL,
  `processed_status` ENUM('success', 'ignored', 'failed') NOT NULL DEFAULT 'success',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_pe_order_id` (`order_id`),
  CONSTRAINT `fk_pe_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pe_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. INVENTORY MOVEMENTS TABLE
CREATE TABLE `inventory_movements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_type` ENUM('product', 'ingredient') NOT NULL,
  `item_id` INT NOT NULL,
  `movement_type` ENUM('in', 'out', 'adjustment', 'sale', 'refund') NOT NULL,
  `quantity` DECIMAL(12,2) NOT NULL,
  `previous_stock` DECIMAL(12,2) NOT NULL,
  `new_stock` DECIMAL(12,2) NOT NULL,
  `reference_type` VARCHAR(50) NULL,
  `reference_id` VARCHAR(100) NULL,
  `notes` TEXT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_inv_item` (`item_type`, `item_id`),
  CONSTRAINT `fk_inv_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. EXPENSES TABLE (Pengeluaran Operasional)
CREATE TABLE `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `description` TEXT NULL,
  `receipt_proof` VARCHAR(255) NULL,
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_expenses_date` (`expense_date`),
  CONSTRAINT `fk_expenses_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_expenses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. REFUNDS TABLE
CREATE TABLE `refunds` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_id` INT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `reason` TEXT NOT NULL,
  `refunded_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_refunds_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_refunds_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_refunds_user` FOREIGN KEY (`refunded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. ACTIVITY LOGS TABLE
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NULL,
  `entity_id` VARCHAR(50) NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(50) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_act_user` (`user_id`),
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA
-- ==========================================================

-- USERS (Password: admin123, kasir123, dapur123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`) VALUES
(1, 'Administrator', 'admin@cafeflow.com', '$2y$10$dRQIMogZam2Kann3ED37NeoUPtCbxoQJuUrRy0kbExJmEmYpX.vEi', 'admin', '081234567890'),
(2, 'Sarah Amelia', 'kasir@cafeflow.com', '$2y$10$QX2vXCKDJRc3x5cQuXNlreUjhxS6HrHhi1oYs.AyT098oR1ItKYpK', 'kasir', '081234567891'),
(3, 'Budi Santoso', 'dapur@cafeflow.com', '$2y$10$BNGpRWHY9HHUkfINxp20mur8QFvgSnkarnwdLq8HpqJsy9JN8m9gC', 'dapur', '081234567892');

-- CATEGORIES
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`) VALUES
(1, 'Coffee', 'coffee', 'bi-cup-hot-fill', 1),
(2, 'Non-Coffee & Tea', 'non-coffee', 'bi-cup-straw', 2),
(3, 'Makanan Berat', 'makanan-berat', 'bi-egg-fried', 3),
(4, 'Snacks & Bites', 'snacks', 'bi-pie-chart', 4),
(5, 'Pastry & Dessert', 'pastry-dessert', 'bi-cake2', 5);

-- TABLES
INSERT INTO `tables` (`id`, `table_number`, `name`, `capacity`, `status`) VALUES
(1, 'T-01', 'Meja Indoor 01', 4, 'available'),
(2, 'T-02', 'Meja Indoor 02', 4, 'available'),
(3, 'T-03', 'Meja Indoor 03 (Sofa)', 6, 'available'),
(4, 'T-04', 'Meja Indoor 04', 2, 'available'),
(5, 'T-05', 'Meja Outdoor 05', 4, 'available'),
(6, 'T-06', 'Meja Outdoor 06', 4, 'available'),
(7, 'T-07', 'Meja Bar 01', 1, 'available'),
(8, 'T-08', 'Meja Bar 02', 1, 'available');

-- EXPENSE CATEGORIES
INSERT INTO `expense_categories` (`id`, `name`, `description`) VALUES
(1, 'Bahan Baku & Dapur', 'Pembelian biji kopi, susu, sirup, beras, daging, dsb.'),
(2, 'Gaji & Upah Karyawan', 'Gaji bulanan barista, kasir, dan staf dapur'),
(3, 'Listrik, Air & Internet', 'Tagihan utilitas bulanan dan WiFi kafe'),
(4, 'Sewa & Pemeliharaan', 'Sewa gedung, perbaikan mesin espresso & grinder'),
(5, 'Peralatan & Perlengkapan', 'Cup takeaway, sedotan ramah lingkungan, kantong'),
(6, 'Pemasaran & Operasional Lain', 'Iklan media sosial, promosi, dan biaya tak terduga');

-- INGREDIENTS
INSERT INTO `ingredients` (`id`, `code`, `name`, `stock`, `unit`, `cost_per_unit`, `min_stock`) VALUES
(1, 'ING-BEANS', 'Biji Kopi Arabica House Blend', 4500.00, 'gram', 0.28, 500.00), -- Rp 280/gram (Rp 280.000/kg)
(2, 'ING-MILK', 'Susu UHT Fresh Milk Diamond', 18500.00, 'ml', 0.02, 2000.00), -- Rp 20/ml (Rp 20.000/liter)
(3, 'ING-PALM', 'Sirup Gula Aren Organik', 3200.00, 'ml', 0.04, 500.00),
(4, 'ING-CARAMEL', 'Sirup Karamel Gourmet', 1800.00, 'ml', 0.06, 300.00),
(5, 'ING-MATCHA', 'Bubuk Matcha Uji Premium', 950.00, 'gram', 0.40, 200.00),
(6, 'ING-TEA-EG', 'Teh Artisan Earl Grey', 400.00, 'gram', 0.35, 100.00),
(7, 'ING-RICE', 'Beras Wangi Pulen', 25000.00, 'gram', 0.016, 5000.00),
(8, 'ING-CHICKEN', 'Daging Ayam Fillet', 8500.00, 'gram', 0.065, 1000.00),
(9, 'ING-FRIES', 'Kentang Potong Beku Premium', 9000.00, 'gram', 0.045, 1500.00),
(10, 'ING-EGG', 'Telur Ayam Segar', 120.00, 'pcs', 2200.00, 30.00);

-- PRODUCTS
INSERT INTO `products` (`id`, `category_id`, `sku`, `name`, `description`, `price`, `cost_price`, `stock_type`, `stock`, `min_stock`, `unit`, `image`, `status`) VALUES
(1, 1, 'CF-KOPISUSU', 'Kopi Susu Gula Aren', 'Espresso double shot dipadukan dengan fresh milk dan sirup gula aren asli khas CafeFlow.', 22000.00, 6800.00, 'recipe', 0, 5, 'cup', 'kopi-susu.jpg', 'tersedia'),
(2, 1, 'CF-AMERICANO', 'Iced Americano Single Origin', 'Ekstraksi espresso arabica segar disajikan dengan air mineral dingin dan es batu kristal.', 18000.00, 3500.00, 'recipe', 0, 5, 'cup', 'americano.jpg', 'tersedia'),
(3, 1, 'CF-LATTE', 'Caffe Latte Art Hot/Iced', 'Espresso seimbang dengan microfoam susu lembut yang creamy.', 25000.00, 7200.00, 'recipe', 0, 5, 'cup', 'latte.jpg', 'tersedia'),
(4, 1, 'CF-CARAMELMAC', 'Caramel Macchiato', 'Espresso dengan steamed milk beraroma vanilla dan drizzle saus karamel lezat di atasnya.', 28000.00, 8900.00, 'recipe', 0, 5, 'cup', 'caramel-macchiato.jpg', 'tersedia'),
(5, 2, 'NC-MATCHA', 'Matcha Latte Japanese Grade', 'Bubuk matcha murni dari Uji Jepang dengan perpaduan fresh milk manis gurih.', 26000.00, 8200.00, 'recipe', 0, 5, 'cup', 'matcha-latte.jpg', 'tersedia'),
(6, 2, 'NC-EARLGREY', 'Artisan Earl Grey Tea Pot', 'Teh hitam beraroma minyak bergamot yang harum dan menenangkan.', 20000.00, 4200.00, 'recipe', 0, 5, 'cup', 'earl-grey.jpg', 'tersedia'),
(7, 3, 'MB-NASGOR', 'Nasi Goreng CafeFlow Spesial', 'Nasi goreng bumbu racikan cafe dengan telur ceplok, suwiran ayam gurih, dan kerupuk.', 35000.00, 14000.00, 'recipe', 0, 5, 'porsi', 'nasgor-spesial.jpg', 'tersedia'),
(8, 3, 'MB-CHICKENKATSU', 'Chicken Katsu Curry Rice', 'Ayam katsu renyah berbalut tepung panko disajikan dengan saus kari Jepang dan nasi hangat.', 40000.00, 16500.00, 'recipe', 0, 5, 'porsi', 'katsu-curry.jpg', 'tersedia'),
(9, 4, 'SN-FRIES', 'Truffle French Fries', 'Kentang goreng renyah dengan taburan garam laut dan truffle dipping sauce.', 22000.00, 8000.00, 'recipe', 0, 5, 'porsi', 'french-fries.jpg', 'tersedia'),
(10, 4, 'SN-WINGS', 'Crispy Garlic Wings (6 pcs)', 'Sayap ayam goreng gurih renyah dengan saus bawang putih karamelisasi spesial.', 28000.00, 12000.00, 'recipe', 0, 5, 'porsi', 'chicken-wings.jpg', 'tersedia'),
(11, 5, 'PS-CROISSANT', 'Butter Almond Croissant', 'Croissant berlapis renyah dipanggang dengan krim almond dan taburan irisan almond.', 26000.00, 11000.00, 'direct', 14, 5, 'pcs', 'croissant.jpg', 'tersedia'),
(12, 5, 'PS-CHEESECAKE', 'Basque Burnt Cheesecake', 'Kue keju khas Spanyol dengan tekstur lembut lumer dan permukaan karamel harum.', 32000.00, 13500.00, 'direct', 8, 3, 'slice', 'cheesecake.jpg', 'tersedia');

-- PRODUCT RECIPES (Link Produk ke Bahan Baku)
-- Kopi Susu: 18g beans, 140ml milk, 25ml gula aren
INSERT INTO `product_recipes` (`product_id`, `ingredient_id`, `quantity`) VALUES
(1, 1, 18.00),
(1, 2, 140.00),
(1, 3, 25.00),
-- Americano: 18g beans
(2, 1, 18.00),
-- Latte: 18g beans, 180ml milk
(3, 1, 18.00),
(3, 2, 180.00),
-- Caramel Macchiato: 18g beans, 160ml milk, 20ml caramel
(4, 1, 18.00),
(4, 2, 160.00),
(4, 4, 20.00),
-- Matcha Latte: 15g matcha powder, 180ml milk
(5, 5, 15.00),
(5, 2, 180.00),
-- Earl Grey: 6g tea
(6, 6, 6.00),
-- Nasgor: 200g beras/nasi, 100g ayam, 1 butir telur
(7, 7, 200.00),
(7, 8, 100.00),
(7, 10, 1.00),
-- Chicken Katsu Curry: 200g beras, 150g ayam
(8, 7, 200.00),
(8, 8, 150.00),
-- Truffle Fries: 200g kentang beku
(9, 9, 200.00),
-- Crispy Wings: 250g ayam
(10, 8, 250.00);

-- SETTINGS
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('cafe_name', 'CafeFlow Coffee & Eatery', 'Nama resmi kafe'),
('cafe_tagline', 'Artisan Brews & Cozy Vibes', 'Tagline atau slogan kafe'),
('cafe_phone', '0812-3456-7890', 'Nomor telepon / WhatsApp kafe'),
('cafe_address', 'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan', 'Alamat lengkap kafe'),
('currency_symbol', 'Rp', 'Simbol mata uang'),
('tax_percent', '10', 'Pajak Pertambahan Nilai (PPN %)'),
('service_charge_percent', '0', 'Biaya Layanan (%)'),
('receipt_footer', 'Terima kasih atas kunjungan Anda!\nFollow IG: @cafeflow.eatery', 'Catatan kaki pada struk belanja'),
('midtrans_is_production', '0', '0 = Sandbox, 1 = Production'),
('midtrans_server_key', 'SB-Mid-server-TEST-KEY-cafeflow', 'Midtrans Server Key'),
('midtrans_client_key', 'SB-Mid-client-TEST-KEY-cafeflow', 'Midtrans Client Key'),
('midtrans_merchant_id', 'G987654321', 'Midtrans Merchant ID');

-- SAMPLE EXPENSES
INSERT INTO `expenses` (`id`, `category_id`, `title`, `amount`, `expense_date`, `description`, `user_id`) VALUES
(1, 1, 'Restock Biji Kopi Arabica 10kg', 2800000.00, CURDATE(), 'Pembelian biji kopi dari Roastery Gayo Aceh', 1),
(2, 1, 'Susu UHT Fresh Milk 24 Kotak', 480000.00, CURDATE(), 'Pasokan susu segar harian', 1),
(3, 3, 'Token Listrik PLN 3500VA', 500000.00, CURDATE() - INTERVAL 2 DAY, 'Listrik operasional mesin kopi dan chiller', 1),
(4, 5, 'Cup Takeaway & Straw 500 pcs', 375000.00, CURDATE() - INTERVAL 4 DAY, 'Cup sablon logo CafeFlow ramah lingkungan', 1),
(5, 4, 'Service & Kalibrasi Mesin Espresso', 650000.00, CURDATE() - INTERVAL 6 DAY, 'Penggantian gasket dan kalibrasi pressure', 1);

-- SAMPLE INITIAL ORDERS & PAYMENTS (FOR REAL-TIME DASHBOARD & REPORTS)
-- Order 1: Cash Paid Today
INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `order_type`, `table_id`, `subtotal`, `discount_percent`, `discount_amount`, `tax_amount`, `service_charge`, `total_cost`, `total_amount`, `payment_status`, `order_status`, `payment_method`, `stock_deducted`, `notes`, `user_id`, `paid_at`, `created_at`) VALUES
(1, 'ORD-20261013-0001', 'Dimas Prasetyo', 'dine_in', 1, 72000.00, 0.00, 0.00, 7200.00, 0.00, 22600.00, 79200.00, 'paid', 'completed', 'cash', 1, 'Dine-in meja sofa 1', 2, NOW() - INTERVAL 3 HOUR, NOW() - INTERVAL 3 HOUR);

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `unit_price`, `cost_price`, `quantity`, `subtotal`, `notes`) VALUES
(1, 1, 'Kopi Susu Gula Aren', 22000.00, 6800.00, 2, 44000.00, 'Less ice, normal sugar'),
(1, 10, 'Crispy Garlic Wings (6 pcs)', 28000.00, 12000.00, 1, 28000.00, 'Saus dipisah');

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `tendered_amount`, `change_amount`, `transaction_id`, `status`, `paid_at`, `cashier_id`, `created_at`) VALUES
(1, 1, 'cash', 79200.00, 100000.00, 20800.00, 'CASH-ORD-20261013-0001', 'paid', NOW() - INTERVAL 3 HOUR, 2, NOW() - INTERVAL 3 HOUR);

-- Order 2: QRIS Paid Today
INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `order_type`, `table_id`, `subtotal`, `discount_percent`, `discount_amount`, `tax_amount`, `service_charge`, `total_cost`, `total_amount`, `payment_status`, `order_status`, `payment_method`, `stock_deducted`, `notes`, `user_id`, `paid_at`, `created_at`) VALUES
(2, 'ORD-20261013-0002', 'Clarissa Devina', 'dine_in', 2, 98000.00, 10.00, 9800.00, 8820.00, 0.00, 31900.00, 97020.00, 'paid', 'processing', 'qris', 1, 'Meja 02', 2, NOW() - INTERVAL 1 HOUR, NOW() - INTERVAL 1 HOUR);

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `unit_price`, `cost_price`, `quantity`, `subtotal`, `notes`) VALUES
(2, 4, 'Caramel Macchiato', 28000.00, 8900.00, 1, 28000.00, 'Oat milk if possible'),
(2, 7, 'Nasi Goreng CafeFlow Spesial', 35000.00, 14000.00, 2, 70000.00, 'Pedas sedang');

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `tendered_amount`, `change_amount`, `transaction_id`, `qr_string`, `status`, `paid_at`, `cashier_id`, `created_at`) VALUES
(2, 2, 'qris', 97020.00, 97020.00, 0.00, 'TRX-MID-20261013-0002', '00020101021226590014ID.LINKAJA.WWW011893600914382026101300025204581253033605406970205802ID5913CafeFlow POS6007Jakarta62170713ORD20261013026304ABCD', 'paid', NOW() - INTERVAL 1 HOUR, 2, NOW() - INTERVAL 1 HOUR);

INSERT INTO `payment_events` (`payment_id`, `order_id`, `gateway`, `event_type`, `signature_received`, `signature_valid`, `payload`, `processed_status`) VALUES
(2, 2, 'midtrans', 'settlement', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0', 1, '{"transaction_time":"2026-10-13 14:30:00","transaction_status":"settlement","transaction_id":"TRX-MID-20261013-0002","status_message":"Midtrans payment notification","status_code":"200","signature_key":"a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0","payment_type":"qris","order_id":"ORD-20261013-0002","gross_amount":"97020.00","currency":"IDR","channel_response_message":"Approved"}', 'success');

-- Order 3: Cash Takeaway Today
INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `order_type`, `table_id`, `subtotal`, `discount_percent`, `discount_amount`, `tax_amount`, `service_charge`, `total_cost`, `total_amount`, `payment_status`, `order_status`, `payment_method`, `stock_deducted`, `notes`, `user_id`, `paid_at`, `created_at`) VALUES
(3, 'ORD-20261013-0003', 'Rian Hidayat', 'takeaway', NULL, 48000.00, 0.00, 0.00, 4800.00, 0.00, 16400.00, 52800.00, 'paid', 'ready', 'cash', 1, 'Takeaway kantong kertas', 2, NOW() - INTERVAL 25 MINUTE, NOW() - INTERVAL 25 MINUTE);

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `unit_price`, `cost_price`, `quantity`, `subtotal`, `notes`) VALUES
(3, 5, 'Matcha Latte Japanese Grade', 26000.00, 8200.00, 1, 26000.00, 'Less ice, normal sweetness'),
(3, 9, 'Truffle French Fries', 22000.00, 8000.00, 1, 22000.00, 'Truffle mayo extra');

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `tendered_amount`, `change_amount`, `transaction_id`, `status`, `paid_at`, `cashier_id`, `created_at`) VALUES
(3, 3, 'cash', 52800.00, 60000.00, 7200.00, 'CASH-ORD-20261013-0003', 'paid', NOW() - INTERVAL 25 MINUTE, 2, NOW() - INTERVAL 25 MINUTE);

-- Activity Logs
INSERT INTO `activity_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`) VALUES
(1, 'System Initialized', 'system', '1', 'CafeFlow POS database seeded and configured successfully.', '127.0.0.1'),
(2, 'Login Kasir', 'user', '2', 'Kasir Sarah Amelia logged into POS.', '127.0.0.1');
