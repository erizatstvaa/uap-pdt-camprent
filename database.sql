-- Database CampRent
CREATE DATABASE IF NOT EXISTS camprent;
USE camprent;

-- 1. Tabel Pengguna
CREATE TABLE IF NOT EXISTS pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    peran ENUM('admin', 'pelanggan') NOT NULL,
    no_telepon VARCHAR(15)
) ENGINE=InnoDB;

-- 2. Tabel Alat Camping
CREATE TABLE IF NOT EXISTS alat_camping (
    id_alat INT AUTO_INCREMENT PRIMARY KEY,
    nama_alat VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    harga_per_hari DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL
) ENGINE=InnoDB;

-- 3. Tabel Penyewaan
CREATE TABLE IF NOT EXISTS penyewaan (
    id_penyewaan INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT,
    tgl_sewa DATE NOT NULL,
    tgl_kembali_seharusnya DATE NOT NULL,
    total_bayar DECIMAL(10,2) DEFAULT 0.00,
    status_penyewaan ENUM('dipesan', 'disewa', 'dikembalikan', 'terlambat') DEFAULT 'dipesan',
    FOREIGN KEY (id_pelanggan) REFERENCES pengguna(id_pengguna) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Tabel Detail Penyewaan
CREATE TABLE IF NOT EXISTS detail_penyewaan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewaan INT,
    id_alat INT,
    jumlah INT NOT NULL,
    FOREIGN KEY (id_penyewaan) REFERENCES penyewaan(id_penyewaan) ON DELETE CASCADE,
    FOREIGN KEY (id_alat) REFERENCES alat_camping(id_alat) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabel Pengembalian
CREATE TABLE IF NOT EXISTS pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewaan INT,
    tgl_dikembalikan DATE NOT NULL,
    denda DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_penyewaan) REFERENCES penyewaan(id_penyewaan) ON DELETE CASCADE
) ENGINE=InnoDB;

-- IMPLEMENTASI TRIGGER
DROP TRIGGER IF EXISTS otomatis_tambah_stok_kembali;
DELIMITER $$
CREATE TRIGGER otomatis_tambah_stok_kembali
AFTER UPDATE ON penyewaan
FOR EACH ROW
BEGIN
    IF NEW.status_penyewaan = 'dikembalikan' AND OLD.status_penyewaan <> 'dikembalikan' THEN
        UPDATE alat_camping ac
        JOIN detail_penyewaan dp ON ac.id_alat = dp.id_alat
        SET ac.stok = ac.stok + dp.jumlah
        WHERE dp.id_penyewaan = NEW.id_penyewaan;
    END IF;
END$$
DELIMITER ;

-- IMPLEMENTASI TASK SCHEDULER (EVENT)
SET GLOBAL event_scheduler = ON;
DROP EVENT IF EXISTS cek_keterlambatan_harian;
DELIMITER $$
CREATE EVENT cek_keterlambatan_harian
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY + INTERVAL 1 HOUR
DO
BEGIN
    UPDATE penyewaan
    SET status_penyewaan = 'terlambat'
    WHERE tgl_kembali_seharusnya < CURDATE() 
      AND status_penyewaan = 'disewa';
END$$
DELIMITER ;

-- Data Dummy untuk Uji Coba
INSERT INTO pengguna (nama, email, password, peran, no_telepon) VALUES
('Admin CampRent', 'admin@camprent.com', '$2y$10$O8Kx6O.bS4Xm9GgT7e9vIe6mQ7yvU5e5W8uX1k2Z3A4b5c6d7e8f9', 'admin', '081234567890'),
('Budi Pelanggan', 'budi@gmail.com', '$2y$10$O8Kx6O.bS4Xm9GgT7e9vIe6mQ7yvU5e5W8uX1k2Z3A4b5c6d7e8f9', 'pelanggan', '089876543210');

INSERT INTO alat_camping (nama_alat, kategori, harga_per_hari, stok) VALUES
('Tenda Dome 4 Orang', 'Tenda', 50000.00, 10),
('Carrier 60L', 'Tas', 30000.00, 15),
('Sleeping Bag Premium', 'Perlengkapan Tidur', 15000.00, 20),
('Kompor Portable', 'Memasak', 10000.00, 12);
