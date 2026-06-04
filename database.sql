-- ============================================================
-- DATABASE CampRent - Sistem Manajemen Penyewaan Alat Camping
-- ============================================================

CREATE DATABASE IF NOT EXISTS camprent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE camprent;

-- ============================================================
-- TABEL UTAMA
-- ============================================================

-- 1. Tabel Pengguna
CREATE TABLE IF NOT EXISTS pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    peran ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
    no_telepon VARCHAR(15),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel Alat Camping
CREATE TABLE IF NOT EXISTS alat_camping (
    id_alat INT AUTO_INCREMENT PRIMARY KEY,
    nama_alat VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    deskripsi TEXT,
    harga_per_hari DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    stok_awal INT NOT NULL DEFAULT 0,
    kondisi ENUM('baik', 'rusak_ringan', 'rusak_berat') DEFAULT 'baik',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabel Penyewaan
CREATE TABLE IF NOT EXISTS penyewaan (
    id_penyewaan INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT,
    tgl_sewa DATE NOT NULL,
    tgl_kembali_seharusnya DATE NOT NULL,
    total_bayar DECIMAL(10,2) DEFAULT 0.00,
    status_penyewaan ENUM('disewa', 'dikembalikan', 'terlambat') DEFAULT 'disewa',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pengguna(id_pengguna) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Tabel Detail Penyewaan
CREATE TABLE IF NOT EXISTS detail_penyewaan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewaan INT,
    id_alat INT,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_penyewaan) REFERENCES penyewaan(id_penyewaan) ON DELETE CASCADE,
    FOREIGN KEY (id_alat) REFERENCES alat_camping(id_alat) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabel Pengembalian
CREATE TABLE IF NOT EXISTS pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewaan INT,
    tgl_dikembalikan DATE NOT NULL,
    denda DECIMAL(10,2) DEFAULT 0.00,
    kondisi_alat ENUM('baik', 'rusak') DEFAULT 'baik',
    keterangan TEXT,
    FOREIGN KEY (id_penyewaan) REFERENCES penyewaan(id_penyewaan) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Tabel Log Backup (untuk rekam backup otomatis)
CREATE TABLE IF NOT EXISTS log_backup (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    waktu_backup TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'sukses',
    keterangan TEXT
) ENGINE=InnoDB;

-- 7. Tabel Log Aktivitas (untuk audit trail)
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT,
    aksi VARCHAR(255),
    detail TEXT,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- FRAGMENTASI: Partisi tabel penyewaan berdasarkan status
-- (Horizontal Fragmentation)
-- Implementasi via VIEW sebagai fragmentasi logis
-- ============================================================

-- Fragmentasi 1: View penyewaan aktif
CREATE OR REPLACE VIEW fragment_penyewaan_aktif AS
    SELECT * FROM penyewaan WHERE status_penyewaan IN ('disewa', 'terlambat');

-- Fragmentasi 2: View penyewaan selesai
CREATE OR REPLACE VIEW fragment_penyewaan_selesai AS
    SELECT * FROM penyewaan WHERE status_penyewaan = 'dikembalikan';

-- Fragmentasi 3: View laporan gabungan (derived fragment)
CREATE OR REPLACE VIEW view_laporan_lengkap AS
    SELECT 
        p.id_penyewaan,
        pg.nama AS nama_pelanggan,
        pg.email,
        pg.no_telepon,
        ac.nama_alat,
        ac.kategori,
        dp.jumlah,
        dp.harga_satuan,
        p.tgl_sewa,
        p.tgl_kembali_seharusnya,
        p.total_bayar,
        p.status_penyewaan,
        k.tgl_dikembalikan,
        k.denda,
        (p.total_bayar + IFNULL(k.denda, 0)) AS grand_total
    FROM penyewaan p
    JOIN pengguna pg ON p.id_pelanggan = pg.id_pengguna
    JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
    JOIN alat_camping ac ON dp.id_alat = ac.id_alat
    LEFT JOIN pengembalian k ON p.id_penyewaan = k.id_penyewaan;

-- ============================================================
-- TRIGGER 1: Otomatis tambah stok saat alat dikembalikan
-- (AFTER UPDATE ON penyewaan)
-- ============================================================
DROP TRIGGER IF EXISTS trg_tambah_stok_setelah_dikembalikan;
DELIMITER $$
CREATE TRIGGER trg_tambah_stok_setelah_dikembalikan
AFTER UPDATE ON penyewaan
FOR EACH ROW
BEGIN
    -- Hanya jalankan jika status berubah MENJADI 'dikembalikan'
    IF NEW.status_penyewaan = 'dikembalikan' AND OLD.status_penyewaan <> 'dikembalikan' THEN
        UPDATE alat_camping ac
        JOIN detail_penyewaan dp ON ac.id_alat = dp.id_alat
        SET ac.stok = ac.stok + dp.jumlah
        WHERE dp.id_penyewaan = NEW.id_penyewaan;
        
        -- Catat di log aktivitas
        INSERT INTO log_aktivitas (aksi, detail)
        VALUES ('TRIGGER_STOK', CONCAT('Stok otomatis bertambah untuk penyewaan ID: ', NEW.id_penyewaan));
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- TRIGGER 2: Otomatis kurangi stok saat penyewaan dibuat
-- (AFTER INSERT ON detail_penyewaan)
-- ============================================================
DROP TRIGGER IF EXISTS trg_kurangi_stok_saat_sewa;
DELIMITER $$
CREATE TRIGGER trg_kurangi_stok_saat_sewa
AFTER INSERT ON detail_penyewaan
FOR EACH ROW
BEGIN
    UPDATE alat_camping 
    SET stok = stok - NEW.jumlah 
    WHERE id_alat = NEW.id_alat;
END$$
DELIMITER ;

-- ============================================================
-- TASK SCHEDULER (MySQL Event Scheduler)
-- Event 1: Cek keterlambatan harian
-- ============================================================
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS evt_cek_keterlambatan_harian;
DELIMITER $$
CREATE EVENT evt_cek_keterlambatan_harian
ON SCHEDULE EVERY 1 DAY
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 00:01:00')
DO
BEGIN
    -- Update status menjadi terlambat jika melewati batas
    UPDATE penyewaan
    SET status_penyewaan = 'terlambat'
    WHERE tgl_kembali_seharusnya < CURDATE() 
      AND status_penyewaan = 'disewa';
    
    -- Catat di log
    INSERT INTO log_aktivitas (aksi, detail)
    VALUES ('SCHEDULER_KETERLAMBATAN', CONCAT('Cek keterlambatan dijalankan pada: ', NOW()));
END$$
DELIMITER ;

-- ============================================================
-- TASK SCHEDULER (MySQL Event Scheduler)
-- Event 2: Backup otomatis harian pukul 00.00
-- (mencatat log backup - eksekusi backup dilakukan via cron PHP)
-- ============================================================
DROP EVENT IF EXISTS evt_backup_harian;
DELIMITER $$
CREATE EVENT evt_backup_harian
ON SCHEDULE EVERY 1 DAY
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 00:00:00')
DO
BEGIN
    INSERT INTO log_backup (status, keterangan)
    VALUES ('sukses', CONCAT('Backup dijadwalkan pada: ', NOW()));
END$$
DELIMITER ;

-- ============================================================
-- DATA DUMMY
-- ============================================================

-- Password untuk semua akun: Admin123! (sudah di-hash dengan password_hash)
INSERT INTO pengguna (nama, email, password, peran, no_telepon, alamat) VALUES
('Admin CampRent', 'admin@camprent.com', '$2y$12$NQxiW96oV.2HxkVkFKdXAuqBV7d9Y0MWk3Lqy.9rFfF9L/LkHuIbK', 'admin', '081234567890', 'Jl. Camping Indah No. 1, Bandung'),
('Budi Santoso', 'budi@gmail.com', '$2y$12$NQxiW96oV.2HxkVkFKdXAuqBV7d9Y0MWk3Lqy.9rFfF9L/LkHuIbK', 'pelanggan', '089876543210', 'Jl. Merdeka No. 5, Jakarta'),
('Siti Rahayu', 'siti@gmail.com', '$2y$12$NQxiW96oV.2HxkVkFKdXAuqBV7d9Y0MWk3Lqy.9rFfF9L/LkHuIbK', 'pelanggan', '082345678901', 'Jl. Pahlawan No. 12, Surabaya');

INSERT INTO alat_camping (nama_alat, kategori, deskripsi, harga_per_hari, stok, stok_awal, kondisi) VALUES
('Tenda Dome 4 Orang', 'Tenda', 'Tenda dome kapasitas 4 orang, waterproof, cocok untuk segala cuaca', 50000.00, 10, 10, 'baik'),
('Tenda Bivak 2 Orang', 'Tenda', 'Tenda ringan 2 orang, sangat portable untuk pendakian', 35000.00, 8, 8, 'baik'),
('Carrier 60L Osprey', 'Tas', 'Carrier gunung kapasitas 60L, ergonomis dan tahan lama', 30000.00, 15, 15, 'baik'),
('Daypack 30L', 'Tas', 'Tas punggung 30L untuk pendakian harian atau day hiking', 20000.00, 12, 12, 'baik'),
('Sleeping Bag -5°C', 'Perlengkapan Tidur', 'Sleeping bag untuk suhu hingga -5°C, cocok untuk gunung tinggi', 25000.00, 20, 20, 'baik'),
('Matras EVA', 'Perlengkapan Tidur', 'Matras foam ringan untuk alas tidur di alam bebas', 10000.00, 25, 25, 'baik'),
('Kompor Portable Gas', 'Memasak', 'Kompor portable berbahan bakar gas, ringan dan efisien', 15000.00, 12, 12, 'baik'),
('Nesting/Cookset Aluminium', 'Memasak', 'Set peralatan masak lengkap dari aluminium, isi 4 pcs', 20000.00, 10, 10, 'baik'),
('Headlamp 500 Lumen', 'Penerangan', 'Lampu kepala LED 500 lumen, tahan air, baterai AA', 10000.00, 30, 30, 'baik'),
('Trekking Pole', 'Aksesoris', 'Tongkat pendakian aluminium, adjustable, per pasang', 15000.00, 18, 18, 'baik');