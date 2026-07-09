-- =============================================================
-- 7NVENT - Tambah Column Gambar Produk
-- Jalankan dalam phpMyAdmin -> database 7nvent -> tab SQL
-- =============================================================

ALTER TABLE `inventory_items`
  ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL COMMENT 'relative path under public/uploads/items/' AFTER `item_code`;
