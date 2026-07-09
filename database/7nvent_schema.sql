-- =============================================================
-- 7NVENT - Hotel Inventory Management System
-- Database Schema v2.0
-- Developer: Muhammad Syukri Bin Zainal Abidin (BCS2402-042)
-- Course: CSC2854 | KPM Beranang | Session 1 2026/2027
-- =============================================================

CREATE DATABASE IF NOT EXISTS `7nvent` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `7nvent`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS inventory_items;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS settings;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------
-- Table: roles
-- -----------------------------------------------------------
CREATE TABLE `roles` (
  `role_id`     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_name`   VARCHAR(50) NOT NULL,
  `permissions` JSON DEFAULT NULL,
  `description` TEXT,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: users
-- -----------------------------------------------------------
CREATE TABLE `users` (
  `user_id`      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`     VARCHAR(50)  NOT NULL UNIQUE,
  `password`     VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed',
  `full_name`    VARCHAR(100) NOT NULL,
  `email`        VARCHAR(100) NOT NULL UNIQUE,
  `role_id`      INT UNSIGNED NOT NULL,
  `department`   VARCHAR(50)  DEFAULT NULL,
  `access_level` ENUM('Full Admin','Update','PO Manager','Spectator','Approval Only','System Admin') DEFAULT 'Spectator',
  `status`       ENUM('Active','Inactive') DEFAULT 'Active',
  `last_login`   DATETIME DEFAULT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: suppliers
-- -----------------------------------------------------------
CREATE TABLE `suppliers` (
  `supplier_id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `supplier_name`     VARCHAR(150) NOT NULL,
  `company_reg_no`    VARCHAR(30)  DEFAULT NULL COMMENT 'SSM Registration Number',
  `category`          VARCHAR(100) DEFAULT NULL,
  `contact_person`    VARCHAR(100) DEFAULT NULL,
  `phone`             VARCHAR(20)  DEFAULT NULL,
  `email`             VARCHAR(100) DEFAULT NULL,
  `address`           TEXT DEFAULT NULL,
  `city`              VARCHAR(50)  DEFAULT NULL,
  `state`             VARCHAR(50)  DEFAULT NULL,
  `postcode`          VARCHAR(10)  DEFAULT NULL,
  `website`           VARCHAR(150) DEFAULT NULL,
  `rating`            DECIMAL(2,1) DEFAULT 0.0,
  `lead_time_days`    DECIMAL(3,1) DEFAULT 0.0,
  `ytd_orders_value`  DECIMAL(12,2) DEFAULT 0.00,
  `payment_terms`     VARCHAR(50)  DEFAULT '30 days',
  `status`            ENUM('Active','Inactive','On Hold') DEFAULT 'Active',
  `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: locations
-- -----------------------------------------------------------
CREATE TABLE `locations` (
  `location_id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `location_name` VARCHAR(100) NOT NULL,
  `location_type` ENUM('Storage','Floor Pantry','F&B','Linen','Minibar') NOT NULL,
  `floor_area`    VARCHAR(50)  DEFAULT NULL,
  `capacity`      INT DEFAULT 0,
  `current_items` INT DEFAULT 0,
  `status`        ENUM('Operational','Partial Low','Low Stock') DEFAULT 'Operational',
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: inventory_items
-- -----------------------------------------------------------
CREATE TABLE `inventory_items` (
  `item_id`      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `item_name`    VARCHAR(100) NOT NULL,
  `item_code`    VARCHAR(20)  DEFAULT NULL COMMENT 'SKU/barcode',
  `image_path`   VARCHAR(255) DEFAULT NULL COMMENT 'relative path under public/uploads/items/',
  `category`     ENUM('Toiletries','F&B','Linens','Cleaning','Minibar') NOT NULL,
  `location_id`  INT UNSIGNED NOT NULL,
  `supplier_id`  INT UNSIGNED DEFAULT NULL,
  `quantity`     INT DEFAULT 0,
  `par_level`    INT DEFAULT 0,
  `unit_price`   DECIMAL(10,2) DEFAULT 0.00,
  `status`       ENUM('In-Stock','Low Stock','Out of Stock') DEFAULT 'In-Stock',
  `expiry_date`  DATE DEFAULT NULL,
  `date_added`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_updated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by`   INT UNSIGNED DEFAULT NULL,
  FOREIGN KEY (`location_id`) REFERENCES `locations`(`location_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`supplier_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: purchase_orders
-- -----------------------------------------------------------
CREATE TABLE `purchase_orders` (
  `po_id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `po_number`       VARCHAR(20)  NOT NULL UNIQUE,
  `supplier_id`     INT UNSIGNED NOT NULL,
  `total_items`     INT DEFAULT 0,
  `total_value`     DECIMAL(12,2) DEFAULT 0.00,
  `raised_by`       INT UNSIGNED NOT NULL,
  `po_date`         DATE NOT NULL,
  `expected_delivery` DATE DEFAULT NULL,
  `actual_delivery`   DATE DEFAULT NULL,
  `status`          ENUM('Pending','In Transit','Delivered','Cancelled') DEFAULT 'Pending',
  `approval_status` ENUM('Auto','Manual','Approved') DEFAULT 'Manual',
  `notes`           TEXT DEFAULT NULL,
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`supplier_id`),
  FOREIGN KEY (`raised_by`)   REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: purchase_order_items
-- -----------------------------------------------------------
CREATE TABLE `purchase_order_items` (
  `poi_id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `po_id`            INT UNSIGNED NOT NULL,
  `item_id`          INT UNSIGNED NOT NULL,
  `quantity_ordered` INT  NOT NULL,
  `unit_price`       DECIMAL(10,2) NOT NULL,
  `subtotal`         DECIMAL(12,2) GENERATED ALWAYS AS (`quantity_ordered` * `unit_price`) STORED,
  FOREIGN KEY (`po_id`)   REFERENCES `purchase_orders`(`po_id`),
  FOREIGN KEY (`item_id`) REFERENCES `inventory_items`(`item_id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: alerts
-- -----------------------------------------------------------
CREATE TABLE `alerts` (
  `alert_id`      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `alert_type`    ENUM('Critical','Warning','Info') NOT NULL,
  `title`         VARCHAR(200) NOT NULL,
  `description`   TEXT NOT NULL,
  `item_id`       INT UNSIGNED DEFAULT NULL,
  `location_id`   INT UNSIGNED DEFAULT NULL,
  `triggered_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status`        ENUM('Active','Approved','Dismissed','Resolved') DEFAULT 'Active',
  `auto_generated` TINYINT(1) DEFAULT 1,
  `resolved_by`   INT UNSIGNED DEFAULT NULL,
  `resolved_at`   DATETIME DEFAULT NULL,
  FOREIGN KEY (`item_id`)     REFERENCES `inventory_items`(`item_id`),
  FOREIGN KEY (`location_id`) REFERENCES `locations`(`location_id`),
  FOREIGN KEY (`resolved_by`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: audit_logs
-- -----------------------------------------------------------
CREATE TABLE `audit_logs` (
  `log_id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL,
  `action`       VARCHAR(100) NOT NULL,
  `target_table` VARCHAR(50)  DEFAULT NULL,
  `target_id`    INT DEFAULT NULL,
  `description`  TEXT DEFAULT NULL,
  `timestamp`    DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ip_address`   VARCHAR(45) DEFAULT NULL,
  `device_id`    VARCHAR(100) DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Table: settings
-- -----------------------------------------------------------
CREATE TABLE `settings` (
  `setting_id`    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` VARCHAR(255) DEFAULT NULL,
  `description`   TEXT DEFAULT NULL,
  `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =============================================================
-- SEED DATA
-- =============================================================

-- Roles
INSERT INTO `roles` (`role_name`, `permissions`, `description`) VALUES
('Inventory Manager',   '["all"]',                                                           'Full Admin — manages entire system, all CRUD access'),
('Housekeeping Manager','["inventory.view","inventory.update","alerts.view"]',               'Updates housekeeping stock; no PO or user management'),
('Procurement Officer', '["po.create","po.update","suppliers.view","alerts.approve"]',       'Manages purchase orders and supplier relationships'),
('IT Administrator',    '["users.manage","settings.manage","backup.manage","audit.view"]',   'System admin — user accounts and technical settings'),
('Hotel GM',            '["reports.view","dashboard.view","analytics.view","po.approve"]',   'Executive view-only; approves high-value POs'),
('Supervisor',          '["inventory.view","locations.view","alerts.view","reports.view"]',  'Auditor/observer — read-only monitoring access');

-- =============================================================
-- USERS — Each role has a UNIQUE password
-- Login credentials clearly listed in README
-- =============================================================
-- elizabeth.lee  → password: Admin@7nvent    (Inventory Manager)
-- alvin.yuan     → password: House@7nvent    (Housekeeping Manager)
-- sarah.qinn     → password: PO@7nvent123    (Procurement Officer)
-- abdul.hakim    → password: ITadmin@7nvent  (IT Administrator)
-- farah.nabilah  → password: GM@7nvent2026   (Hotel GM)
-- melissa.yee    → password: Super@7nvent    (Supervisor)
INSERT INTO `users` (`username`,`password`,`full_name`,`email`,`role_id`,`department`,`access_level`,`status`,`last_login`) VALUES
('elizabeth.lee', '$2y$10$ujsZCfuihj/Sh/zY1998JO5ypKYbvqyCwD.sMmvnZyBAdUXNAh5NO', 'Elizabeth Lee',   'elizabeth.lee@hotel7nvent.com',   1, 'Operations',   'Full Admin',    'Active', NOW()),
('alvin.yuan',    '$2y$10$bjznGCQlbgHG1hS3F7fvMOtygorvSsRNmD8o8v0YgJNVBbnTsAc5K', 'Alvin Yuan',      'alvin.yuan@hotel7nvent.com',      2, 'Housekeeping', 'Update',        'Active', NOW()),
('sarah.qinn',    '$2y$10$xlTeVp6/h46/T1n1UJVgEu47cfKvIRmwCIasmHywvUAnFkxbD9O5S', 'Sarah Qinn',      'sarah.qinn@hotel7nvent.com',      3, 'Procurement',  'PO Manager',    'Active', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('abdul.hakim',   '$2y$10$3XouDWdoloAAjD05H7Z1M.zF/YLo9j2EyBsCLN2sQ4.69m6RkbvKq', 'Abdul Hakim',     'abdul.hakim@hotel7nvent.com',     4, 'IT',           'System Admin',  'Active', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('farah.nabilah', '$2y$10$f0Qf4H8TXd7jtNzSGvxKW.2uxpPjJ.yCsArGwaNrHbGr/uxj0fw4y', 'Farah Nabilah',  'farah.nabilah@hotel7nvent.com',   5, 'Executive',    'Approval Only', 'Active', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('melissa.yee',   '$2y$10$vlXcUrBW268eKgHLXoMgueBSA36cQKQ1IWxbxCkLxtj/J5cBaVIda', 'Melissa Yee',     'melissa.yee@hotel7nvent.com',     6, 'Operations',   'Spectator',     'Active', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- =============================================================
-- SUPPLIERS — Real Malaysian Companies (SSM Registered)
-- =============================================================
INSERT INTO `suppliers` (`supplier_name`,`company_reg_no`,`category`,`contact_person`,`phone`,`email`,`address`,`city`,`state`,`postcode`,`website`,`rating`,`lead_time_days`,`ytd_orders_value`,`payment_terms`,`status`) VALUES

-- 1. Nestle Products Sdn Bhd
('Nestle Products Sdn Bhd',
 '110943-W',
 'F&B, Minibar, Beverages & Snacks',
 'Ahmad Faizal bin Rahmat',
 '03-2168 9999',
 'trade@my.nestle.com',
 'No. 4, Lorong Persiaran Puchong, Batu 9, Jalan Cheras',
 'Kuala Lumpur', 'Wilayah Persekutuan', '56100',
 'www.nestle.com.my',
 4.8, 2.1, 62000.00, 'Net 30', 'Active'),

-- 2. Cellini (Malaysia) Sdn Bhd
('Cellini (Malaysia) Sdn Bhd',
 '296508-H',
 'Linens, Towels, Bedding & Furnishings',
 'Nurul Hana binti Zulkifli',
 '03-5565 4321',
 'hospitality@cellini.com.my',
 'Lot 6, Jalan Teknologi 3/4, Taman Sains Selangor',
 'Shah Alam', 'Selangor', '40300',
 'www.cellini.com.my',
 4.5, 5.0, 38000.00, 'Net 30', 'Active'),

-- 3. P&G Malaysia Sdn Bhd (Procter & Gamble)
('P&G Malaysia Sdn Bhd',
 '263605-T',
 'Toiletries, Cleaning Products & Personal Care',
 'Jason Lim Kai Shen',
 '03-7956 1200',
 'pg.b2b.malaysia@pg.com',
 'Level 12, Menara Sentral Vista, No. 150, Jalan Sultan Abdul Samad, Brickfields',
 'Kuala Lumpur', 'Wilayah Persekutuan', '50470',
 'www.pg.com/en_MY',
 4.9, 2.9, 24000.00, 'Net 45', 'Active'),

-- 4. Merck (Malaysia) Sdn Bhd
('Merck (Malaysia) Sdn Bhd',
 '82024-T',
 'Pharmaceutical, Medicine & Aromatherapy Products',
 'Dr. Rajan Subramaniam',
 '03-2080 2900',
 'info.malaysia@merckgroup.com',
 'Suite 6.01, Level 6, Wisma UOA Damansara, 50 Jalan Dungun, Damansara Heights',
 'Kuala Lumpur', 'Wilayah Persekutuan', '50490',
 'www.merckgroup.com/my',
 5.0, 6.2, 77000.00, 'Net 30', 'Active'),

-- 5. Unilever (Malaysia) Holdings Sdn Bhd
('Unilever (Malaysia) Holdings Sdn Bhd',
 '5532-D',
 'Personal Care, F&B, Cleaning Supplies',
 'Linda Tan Sook Yee',
 '03-7727 5678',
 'trade.malaysia@unilever.com',
 'Unilever Tower, 1 Maxis Boulevard, Bukit Jalil',
 'Kuala Lumpur', 'Wilayah Persekutuan', '57000',
 'www.unilever.com.my',
 4.6, 8.3, 62000.00, 'Net 45', 'Active'),

-- 6. Amway (Malaysia) Holdings Sdn Bhd
('Amway (Malaysia) Holdings Sdn Bhd',
 '13235-W',
 'Cleaning Supplies, Chemicals & Home Care',
 'Kamal Hassan bin Mohd Noor',
 '03-2270 8888',
 'business@amway.com.my',
 'No. 6, Jalan 225, Section 51A, Petaling Jaya',
 'Petaling Jaya', 'Selangor', '46100',
 'www.amway.com.my',
 4.3, 7.1, 50000.00, 'Net 30', 'Active'),

-- 7. Kimberley-Clark Malaysia Sdn Bhd (Kleenex, Scott)
('Kimberley-Clark Malaysia Sdn Bhd',
 '30577-W',
 'Paper Products, Tissue & Hygiene Supplies',
 'Siti Rahayu binti Hamdan',
 '03-5545 3300',
 'kc.malaysia@kcc.com',
 'Kompleks Antarabangsa, Level 10, Jalan Sultan Ismail',
 'Kuala Lumpur', 'Wilayah Persekutuan', '50250',
 'www.kimberly-clark.com',
 4.7, 3.5, 29500.00, 'Net 30', 'Active'),

-- 8. Petronas Chemicals Marketing Sdn Bhd (cleaning chemicals)
('Petronas Chemicals Marketing Sdn Bhd',
 '381834-H',
 'Industrial Chemicals & Cleaning Agents',
 'Mohd Rizal bin Ahmad Kushairi',
 '03-2331 2600',
 'chemicals.marketing@petronas.com.my',
 'Tower 1, Petronas Twin Towers, Kuala Lumpur City Centre',
 'Kuala Lumpur', 'Wilayah Persekutuan', '50088',
 'www.petronaschemicals.com',
 4.4, 5.8, 18000.00, 'Net 60', 'Active'),

-- 9. Dutch Lady Milk Industries Bhd
('Dutch Lady Milk Industries Bhd',
 '4405-W',
 'Dairy Products, Beverages & Minibar Supplies',
 'Hazlinda binti Zakaria',
 '03-7958 8881',
 'supply@dutchlady.com.my',
 'No 1, Jalan Tandang, Off Jalan Klang Lama',
 'Petaling Jaya', 'Selangor', '46050',
 'www.dutchlady.com.my',
 4.6, 4.0, 31500.00, 'Net 30', 'Active'),

-- 10. Carlsberg Brewery Malaysia Bhd
('Carlsberg Brewery Malaysia Bhd',
 '5318-X',
 'Beverages, Minibar Alcoholic & Non-Alcoholic',
 'Chong Wei Loong',
 '03-7861 8700',
 'trade@carlsberg.com.my',
 'No 55, Persiaran Selangor, Shah Alam Industrial Estate',
 'Shah Alam', 'Selangor', '40150',
 'www.carlsbergmalaysia.com.my',
 4.5, 3.0, 45000.00, 'Net 30', 'Active');

-- Locations
INSERT INTO `locations` (`location_name`,`location_type`,`floor_area`,`capacity`,`current_items`,`status`) VALUES
('Main Warehouse',    'Storage',      'Basement',    2000, 1204, 'Operational'),
('Floor 1-2 Pantry',  'Floor Pantry', 'Floor 1 & 2',  800,  312, 'Partial Low'),
('Floor 5 Pantry',    'Floor Pantry', 'Floor 3 & 4',  600,  287, 'Operational'),
('Minibar Storage',   'F&B',          'Floor 5',       500,  245, 'Operational'),
('Laundry Store',     'Linen',        'Floor 2',      1000,  510, 'Low Stock'),
('Floor 3-4 Pantry',  'Floor Pantry', 'Basement',      900,  777, 'Operational');

-- Inventory Items
INSERT INTO `inventory_items` (`item_name`,`item_code`,`category`,`location_id`,`supplier_id`,`quantity`,`par_level`,`unit_price`,`status`,`expiry_date`) VALUES
('Shampoo (50 ml)',              'ITM-TLT-001', 'Toiletries', 1, 3,  840, 200, 1.50, 'In-Stock',     NULL),
('Conditioner (50 ml)',          'ITM-TLT-002', 'Toiletries', 1, 3,  720, 200, 1.50, 'In-Stock',     NULL),
('Lotion (30 ml)',               'ITM-TLT-003', 'Toiletries', 2, 3,  120, 150, 2.00, 'In-Stock',     NULL),
('Bath Soap',                    'ITM-TLT-004', 'Toiletries', 3, 6,    0, 100, 1.20, 'Out of Stock', NULL),
('Dental Kit',                   'ITM-TLT-005', 'Toiletries', 1, 3,  240, 300, 3.50, 'Low Stock',    NULL),
('Shower Cap',                   'ITM-TLT-006', 'Toiletries', 1, 3,  500, 200, 0.30, 'In-Stock',     NULL),
('Nescafe Sachet 3-in-1',        'ITM-FNB-001', 'F&B',        1, 1,  300, 150, 0.80, 'In-Stock',     '2026-06-30'),
('Milo Sachet 33g',              'ITM-FNB-002', 'F&B',        1, 1,  280, 150, 1.20, 'In-Stock',     '2026-08-31'),
('Red Bull (250ml)',              'ITM-FNB-003', 'F&B',        1, 1,   12, 120, 4.50, 'Low Stock',    '2026-08-15'),
('Mineral Water (330ml)',         'ITM-FNB-004', 'F&B',        2, 1,   24, 200, 0.60, 'Low Stock',    '2026-05-22'),
('Dutch Lady UHT Full Cream',    'ITM-FNB-005', 'F&B',        1, 9,  180,  80, 2.50, 'In-Stock',     '2026-07-15'),
('Kitkat 4 Finger',              'ITM-MNB-001', 'Minibar',    4, 1,   70, 100, 3.20, 'Low Stock',    '2026-10-31'),
('Pringles Original (165g)',      'ITM-MNB-002', 'Minibar',    4, 5,   18,  60, 6.80, 'Low Stock',    '2026-09-30'),
('Twisties Cheese (60g)',         'ITM-MNB-003', 'Minibar',    4, 1,   95,  60, 2.50, 'In-Stock',     '2026-11-30'),
('Carlsberg Can (330ml)',         'ITM-MNB-004', 'Minibar',    4, 10, 120, 100, 5.50, 'In-Stock',     '2027-01-31'),
('Bath Towel (Hotel Grade)',      'ITM-LNN-001', 'Linens',     5, 2,   95, 200, 25.00,'Low Stock',    NULL),
('Hand Towel',                   'ITM-LNN-002', 'Linens',     5, 2,  180, 300, 12.00,'Low Stock',    NULL),
('Bed Sheet King (260TC)',        'ITM-LNN-003', 'Linens',     5, 2,   45, 100, 65.00,'Low Stock',    NULL),
('Pillow Case',                  'ITM-LNN-004', 'Linens',     5, 2,  220, 200, 8.50, 'In-Stock',     NULL),
('Multi-Purpose Spray 500ml',    'ITM-CLN-001', 'Cleaning',   1, 6,    9,  40, 8.50, 'Low Stock',    NULL),
('Floor Cleaner 5L',             'ITM-CLN-002', 'Cleaning',   1, 8,   45,  30, 22.00,'In-Stock',     '2027-06-01'),
('Toilet Bowl Cleaner 1L',       'ITM-CLN-003', 'Cleaning',   1, 6,   30,  30, 12.00,'In-Stock',     '2027-04-01'),
('Dishwashing Liquid 1L',        'ITM-CLN-004', 'Cleaning',   1, 5,   60,  40, 9.80, 'In-Stock',     '2027-03-01'),
('Latex Gloves (Box 100pcs)',     'ITM-CLN-005', 'Cleaning',   1, 6,   25,  30, 18.50,'Low Stock',    NULL);

-- Purchase Orders
INSERT INTO `purchase_orders` (`po_number`,`supplier_id`,`total_items`,`total_value`,`raised_by`,`po_date`,`expected_delivery`,`actual_delivery`,`status`,`approval_status`,`notes`) VALUES
('PO-2026-0001', 1, 690, 4500.00, 1, '2026-04-15', '2026-04-18', '2026-04-17', 'Delivered',  'Auto',   'Urgent restock - Nescafe & Milo running low'),
('PO-2026-0002', 3, 600, 7800.00, 3, '2026-04-15', '2026-04-20', NULL,         'In Transit', 'Manual', 'Monthly toiletries restock from P&G'),
('PO-2026-0003', 2, 900, 9200.00, 3, '2026-04-13', '2026-04-16', '2026-04-15', 'Delivered',  'Manual', 'Linen restock for Ramadan peak season'),
('PO-2026-0004', 5, 200, 2300.00, 3, '2026-04-12', '2026-04-15', '2026-04-14', 'Delivered',  'Manual', 'Unilever personal care bulk order'),
('PO-2026-0005', 6, 50,  1200.00, 3, '2026-04-10', '2026-04-14', NULL,         'Cancelled',  'Manual', 'Cancelled — switched to Petronas supplier'),
('PO-2026-0006', 4, 200, 6200.00, 3, '2026-04-09', '2026-04-13', NULL,         'In Transit', 'Manual', 'Merck aromatherapy & wellness products'),
('PO-2026-0007', 10,120, 1100.00, 3, '2026-04-06', '2026-04-10', NULL,         'Cancelled',  'Manual', 'Carlsberg minibar restock - cancelled (budget freeze)'),
('PO-2026-0008', 9, 300, 3500.00, 1, '2026-05-01', '2026-05-05', NULL,         'Pending',    'Auto',   'Dutch Lady auto-reorder - par level triggered'),
('PO-2026-0009', 7, 500, 8900.00, 3, '2026-05-10', '2026-05-15', NULL,         'Pending',    'Manual', 'Kimberly-Clark paper products Q2 order');

-- Alerts
INSERT INTO `alerts` (`alert_type`,`title`,`description`,`item_id`,`location_id`,`status`,`auto_generated`) VALUES
('Critical', 'F&B - Red Bull Stock at 10%',              'Current stock 12 units. Par level: 120 units. Auto PO generated and waiting for approval. Location: Main Warehouse Bay 3.',                                   9, 1, 'Active', 1),
('Warning',  'LOW STOCK: Minibar - Pringles Original',   'Current stock 18 units. Par level: 60 units. Stock is at 30% of par level. Last order: 14 Apr 2026.',                                                       13, 4, 'Active', 1),
('Warning',  'LOW STOCK: Cleaning - Multi-Purpose Spray','Current: 9 bottles. Par level: 40 units. Used by housekeeping for daily room turnover. Urgent restocking required.',                                         20, 1, 'Active', 1),
('Critical', 'Minibar - Kitkat 4 Finger Low',            '70 units remaining vs par level of 100 units.',                                                                                                             12, 4, 'Active', 1),
('Warning',  'Mineral Water Expiry Warning',              '24 pcs expiring in 3 days — 2026-05-22. Consume or dispose per FIFO policy.',                                                                               10, 2, 'Active', 1),
('Critical', 'Bath Soap - Floor 5 Pantry OUT OF STOCK',  '0 units in Floor 5 Pantry. Guest complaint risk HIGH. Request immediate transfer from Main Warehouse or raise emergency PO.',                                4, 3, 'Active', 1),
('Warning',  'Laundry Store Low Linen Stock',             'Bath Towels: 95 units (par: 200). Hand Towels: 180 units (par: 300). Bed Sheets: 45 units (par: 100). Request PO to Cellini (Malaysia) Sdn Bhd.',         16, 5, 'Active', 0),
('Info',     'PO-2026-0001 Delivered Early',              'Nestle Products Sdn Bhd delivered 690 items worth RM 4,500 one day ahead of schedule. Items have been received and logged.',                                NULL, 1, 'Active', 1),
('Warning',  'Dental Kit Below Par Level',                'Current: 240 units. Par level: 300 units. 80% of par reached. Consider placing order with P&G Malaysia before next audit.',                                5, 1, 'Active', 0);

-- Settings
INSERT INTO `settings` (`setting_key`,`setting_value`,`description`) VALUES
('system_name',                '7NVENT',    'System display name'),
('automated_reorder_alerts',   '1',         'Auto-generate POs when stock hits par level'),
('expiry_notifications',       '1',         'Alert when items are within 7 days of expiry'),
('fifo_enforcement',           '1',         'Enforce First-In-First-Out for perishables'),
('data_backup_frequency',      '1',         'Automated database backup interval'),
('pdpa_compliance_mode',       '0',         'Enable data privacy protections (PDPA 2010)'),
('backup_retention_days',      '30',        'Number of days to retain backups'),
('session_timeout_minutes',    '30',        'Auto logout after inactivity'),
('low_stock_threshold_percent','25',        'Percentage of par level to trigger warning alert'),
('hotel_name',                 '7NVENT Hotel & Resort', 'Hotel name for reports'),
('hotel_address',              'Kuala Lumpur, Malaysia', 'Hotel address for reports');
