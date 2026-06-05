mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: camprent
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alat_camping`
--

DROP TABLE IF EXISTS `alat_camping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alat_camping` (
  `id_alat` int NOT NULL AUTO_INCREMENT,
  `nama_alat` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text,
  `harga_per_hari` decimal(10,2) NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `stok_awal` int NOT NULL DEFAULT '0',
  `kondisi` enum('baik','rusak_ringan','rusak_berat') DEFAULT 'baik',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alat_camping`
--

LOCK TABLES `alat_camping` WRITE;
/*!40000 ALTER TABLE `alat_camping` DISABLE KEYS */;
INSERT INTO `alat_camping` VALUES (1,'Tenda Dome 4 Orang','Tenda','Tenda dome kapasitas 4 orang, waterproof, cocok untuk segala cuaca',50000.00,10,10,'baik','2026-06-04 07:19:24'),(2,'Tenda Bivak 2 Orang','Tenda','Tenda ringan 2 orang, sangat portable untuk pendakian',35000.00,0,8,'baik','2026-06-04 07:19:24'),(3,'Carrier 60L Osprey','Tas','Carrier gunung kapasitas 60L, ergonomis dan tahan lama',30000.00,13,15,'baik','2026-06-04 07:19:24'),(4,'Daypack 30L','Tas','Tas punggung 30L untuk pendakian harian atau day hiking',20000.00,10,12,'baik','2026-06-04 07:19:24'),(5,'Sleeping Bag -5??C','Perlengkapan Tidur','Sleeping bag untuk suhu hingga -5??C, cocok untuk gunung tinggi',25000.00,19,20,'baik','2026-06-04 07:19:24'),(6,'Matras EVA','Perlengkapan Tidur','Matras foam ringan untuk alas tidur di alam bebas',10000.00,25,25,'baik','2026-06-04 07:19:24'),(7,'Kompor Portable Gas','Memasak','Kompor portable berbahan bakar gas, ringan dan efisien',15000.00,10,12,'baik','2026-06-04 07:19:24'),(8,'Nesting/Cookset Aluminium','Memasak','Set peralatan masak lengkap dari aluminium, isi 4 pcs',20000.00,9,10,'baik','2026-06-04 07:19:24'),(9,'Headlamp 500 Lumen','Penerangan','Lampu kepala LED 500 lumen, tahan air, baterai AA',10000.00,28,30,'baik','2026-06-04 07:19:24'),(10,'Trekking Pole','Aksesoris','Tongkat pendakian aluminium, adjustable, per pasang',15000.00,16,18,'baik','2026-06-04 07:19:24');
/*!40000 ALTER TABLE `alat_camping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penyewaan`
--

DROP TABLE IF EXISTS `detail_penyewaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penyewaan` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_penyewaan` int DEFAULT NULL,
  `id_alat` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_penyewaan` (`id_penyewaan`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `detail_penyewaan_ibfk_1` FOREIGN KEY (`id_penyewaan`) REFERENCES `penyewaan` (`id_penyewaan`) ON DELETE CASCADE,
  CONSTRAINT `detail_penyewaan_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `alat_camping` (`id_alat`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penyewaan`
--

LOCK TABLES `detail_penyewaan` WRITE;
/*!40000 ALTER TABLE `detail_penyewaan` DISABLE KEYS */;
INSERT INTO `detail_penyewaan` VALUES (1,4,5,1,25000.00),(2,5,8,1,20000.00),(3,6,4,1,20000.00),(4,7,10,1,15000.00),(5,8,9,1,10000.00),(6,9,7,1,15000.00),(7,14,2,1,35000.00),(8,15,2,1,35000.00),(9,16,2,1,35000.00),(10,17,2,1,35000.00),(11,18,3,1,30000.00);
/*!40000 ALTER TABLE `detail_penyewaan` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_kurangi_stok_saat_sewa` AFTER INSERT ON `detail_penyewaan` FOR EACH ROW BEGIN
    UPDATE alat_camping 
    SET stok = stok - NEW.jumlah 
    WHERE id_alat = NEW.id_alat;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `fragment_penyewaan_aktif`
--

DROP TABLE IF EXISTS `fragment_penyewaan_aktif`;
/*!50001 DROP VIEW IF EXISTS `fragment_penyewaan_aktif`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `fragment_penyewaan_aktif` AS SELECT 
 1 AS `id_penyewaan`,
 1 AS `id_pelanggan`,
 1 AS `tgl_sewa`,
 1 AS `tgl_kembali_seharusnya`,
 1 AS `total_bayar`,
 1 AS `status_penyewaan`,
 1 AS `catatan`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `fragment_penyewaan_selesai`
--

DROP TABLE IF EXISTS `fragment_penyewaan_selesai`;
/*!50001 DROP VIEW IF EXISTS `fragment_penyewaan_selesai`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `fragment_penyewaan_selesai` AS SELECT 
 1 AS `id_penyewaan`,
 1 AS `id_pelanggan`,
 1 AS `tgl_sewa`,
 1 AS `tgl_kembali_seharusnya`,
 1 AS `total_bayar`,
 1 AS `status_penyewaan`,
 1 AS `catatan`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `log_aktivitas`
--

DROP TABLE IF EXISTS `log_aktivitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_aktivitas` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_pengguna` int DEFAULT NULL,
  `aksi` varchar(255) DEFAULT NULL,
  `detail` text,
  `waktu` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_aktivitas`
--

LOCK TABLES `log_aktivitas` WRITE;
/*!40000 ALTER TABLE `log_aktivitas` DISABLE KEYS */;
INSERT INTO `log_aktivitas` VALUES (1,NULL,'TRIGGER_STOK','Stok otomatis bertambah untuk penyewaan ID: 4','2026-06-04 14:37:31'),(2,1,'PROSES_KEMBALI','Pengembalian TRX-4, denda: 0','2026-06-04 14:37:31'),(3,NULL,'TRIGGER_STOK','Stok otomatis bertambah untuk penyewaan ID: 5','2026-06-06 14:40:14'),(4,1,'PROSES_KEMBALI','Pengembalian TRX-5, denda: 2000','2026-06-06 14:40:14');
/*!40000 ALTER TABLE `log_aktivitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_backup`
--

DROP TABLE IF EXISTS `log_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_backup` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `waktu_backup` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'sukses',
  `keterangan` text,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_backup`
--

LOCK TABLES `log_backup` WRITE;
/*!40000 ALTER TABLE `log_backup` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengembalian`
--

DROP TABLE IF EXISTS `pengembalian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengembalian` (
  `id_pengembalian` int NOT NULL AUTO_INCREMENT,
  `id_penyewaan` int DEFAULT NULL,
  `tgl_dikembalikan` date NOT NULL,
  `denda` decimal(10,2) DEFAULT '0.00',
  `kondisi_alat` enum('baik','rusak') DEFAULT 'baik',
  `keterangan` text,
  PRIMARY KEY (`id_pengembalian`),
  KEY `id_penyewaan` (`id_penyewaan`),
  CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`id_penyewaan`) REFERENCES `penyewaan` (`id_penyewaan`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengembalian`
--

LOCK TABLES `pengembalian` WRITE;
/*!40000 ALTER TABLE `pengembalian` DISABLE KEYS */;
INSERT INTO `pengembalian` VALUES (1,4,'2026-06-04',0.00,'baik','Dikembalikan tepat waktu'),(2,5,'2026-06-06',2000.00,'baik','Dikembalikan tepat waktu');
/*!40000 ALTER TABLE `pengembalian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengguna` (
  `id_pengguna` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `peran` enum('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  `no_telepon` varchar(15) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengguna`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengguna`
--

LOCK TABLES `pengguna` WRITE;
/*!40000 ALTER TABLE `pengguna` DISABLE KEYS */;
INSERT INTO `pengguna` VALUES (1,'Admin CampRent','admin@camprent.com','$2y$12$NQxiW96oV.2HxkVkFKdXAuqBV7d9Y0MWk3Lqy.9rFfF9L/LkHuIbK','admin','081234567890','Jl. Camping Indah No. 1, Bandung','2026-06-04 07:19:24'),(2,'Budi Santoso','budi@gmail.com','$2y$12$NQxiW96oV.2HxkVkFKdXAuqBV7d9Y0MWk3Lqy.9rFfF9L/LkHuIbK','pelanggan','089876543210','Jl. Merdeka No. 5, Jakarta','2026-06-04 07:19:24'),(3,'Siti Rahayu','siti@gmail.com','$2y$12$NQxiW96oV.2HxkVkFKdXAuqBV7d9Y0MWk3Lqy.9rFfF9L/LkHuIbK','pelanggan','082345678901','Jl. Pahlawan No. 12, Surabaya','2026-06-04 07:19:24');
/*!40000 ALTER TABLE `pengguna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penyewaan`
--

DROP TABLE IF EXISTS `penyewaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penyewaan` (
  `id_penyewaan` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int DEFAULT NULL,
  `tgl_sewa` date NOT NULL,
  `tgl_kembali_seharusnya` date NOT NULL,
  `total_bayar` decimal(10,2) DEFAULT '0.00',
  `status_penyewaan` enum('disewa','dikembalikan','terlambat') DEFAULT 'disewa',
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_penyewaan`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `penyewaan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penyewaan`
--

LOCK TABLES `penyewaan` WRITE;
/*!40000 ALTER TABLE `penyewaan` DISABLE KEYS */;
INSERT INTO `penyewaan` VALUES (1,3,'2026-06-04','2026-06-05',50000.00,'disewa',NULL,'2026-06-04 07:26:02'),(2,3,'2026-06-04','2026-06-05',50000.00,'disewa',NULL,'2026-06-04 14:29:56'),(3,3,'2026-06-04','2026-06-05',25000.00,'disewa',NULL,'2026-06-04 14:30:11'),(4,3,'2026-06-04','2026-06-05',25000.00,'dikembalikan',NULL,'2026-06-04 14:33:01'),(5,3,'2026-06-04','2026-06-05',20000.00,'dikembalikan',NULL,'2026-06-04 14:33:18'),(6,3,'2026-06-04','2026-06-05',20000.00,'disewa',NULL,'2026-06-04 14:33:20'),(7,3,'2026-06-04','2026-06-05',15000.00,'disewa',NULL,'2026-06-04 14:33:23'),(8,3,'2026-06-04','2026-06-05',10000.00,'disewa',NULL,'2026-06-04 14:33:27'),(9,3,'2026-06-04','2026-06-05',15000.00,'disewa',NULL,'2026-06-04 14:33:29'),(10,3,'2026-06-04','2026-06-05',15000.00,'disewa',NULL,'2026-06-04 14:33:53'),(11,2,'2026-06-04','2026-06-05',35000.00,'disewa',NULL,'2026-06-04 14:45:56'),(12,2,'2026-06-04','2026-06-05',35000.00,'disewa',NULL,'2026-06-04 14:47:50'),(13,2,'2026-06-04','2026-06-05',35000.00,'disewa',NULL,'2026-06-04 14:48:51'),(14,2,'2026-06-04','2026-06-05',35000.00,'disewa',NULL,'2026-06-04 14:50:37'),(15,2,'2026-06-04','2026-06-05',35000.00,'disewa',NULL,'2026-06-04 14:52:08'),(16,2,'2026-06-04','2026-06-05',35000.00,'disewa',NULL,'2026-06-04 14:52:50'),(17,2,'2026-06-04','2026-06-05',35000.00,'disewa',NULL,'2026-06-04 14:58:18'),(18,2,'2026-06-04','2026-06-08',120000.00,'disewa',NULL,'2026-06-04 15:05:20');
/*!40000 ALTER TABLE `penyewaan` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_tambah_stok_setelah_dikembalikan` AFTER UPDATE ON `penyewaan` FOR EACH ROW BEGIN
    
    IF NEW.status_penyewaan = 'dikembalikan' AND OLD.status_penyewaan <> 'dikembalikan' THEN
        UPDATE alat_camping ac
        JOIN detail_penyewaan dp ON ac.id_alat = dp.id_alat
        SET ac.stok = ac.stok + dp.jumlah
        WHERE dp.id_penyewaan = NEW.id_penyewaan;
        
        
        INSERT INTO log_aktivitas (aksi, detail)
        VALUES ('TRIGGER_STOK', CONCAT('Stok otomatis bertambah untuk penyewaan ID: ', NEW.id_penyewaan));
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `view_laporan_lengkap`
--

DROP TABLE IF EXISTS `view_laporan_lengkap`;
/*!50001 DROP VIEW IF EXISTS `view_laporan_lengkap`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_laporan_lengkap` AS SELECT 
 1 AS `id_penyewaan`,
 1 AS `nama_pelanggan`,
 1 AS `email`,
 1 AS `no_telepon`,
 1 AS `nama_alat`,
 1 AS `kategori`,
 1 AS `jumlah`,
 1 AS `harga_satuan`,
 1 AS `tgl_sewa`,
 1 AS `tgl_kembali_seharusnya`,
 1 AS `total_bayar`,
 1 AS `status_penyewaan`,
 1 AS `tgl_dikembalikan`,
 1 AS `denda`,
 1 AS `grand_total`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `fragment_penyewaan_aktif`
--

/*!50001 DROP VIEW IF EXISTS `fragment_penyewaan_aktif`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `fragment_penyewaan_aktif` AS select `penyewaan`.`id_penyewaan` AS `id_penyewaan`,`penyewaan`.`id_pelanggan` AS `id_pelanggan`,`penyewaan`.`tgl_sewa` AS `tgl_sewa`,`penyewaan`.`tgl_kembali_seharusnya` AS `tgl_kembali_seharusnya`,`penyewaan`.`total_bayar` AS `total_bayar`,`penyewaan`.`status_penyewaan` AS `status_penyewaan`,`penyewaan`.`catatan` AS `catatan`,`penyewaan`.`created_at` AS `created_at` from `penyewaan` where (`penyewaan`.`status_penyewaan` in ('disewa','terlambat')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `fragment_penyewaan_selesai`
--

/*!50001 DROP VIEW IF EXISTS `fragment_penyewaan_selesai`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `fragment_penyewaan_selesai` AS select `penyewaan`.`id_penyewaan` AS `id_penyewaan`,`penyewaan`.`id_pelanggan` AS `id_pelanggan`,`penyewaan`.`tgl_sewa` AS `tgl_sewa`,`penyewaan`.`tgl_kembali_seharusnya` AS `tgl_kembali_seharusnya`,`penyewaan`.`total_bayar` AS `total_bayar`,`penyewaan`.`status_penyewaan` AS `status_penyewaan`,`penyewaan`.`catatan` AS `catatan`,`penyewaan`.`created_at` AS `created_at` from `penyewaan` where (`penyewaan`.`status_penyewaan` = 'dikembalikan') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_laporan_lengkap`
--

/*!50001 DROP VIEW IF EXISTS `view_laporan_lengkap`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_laporan_lengkap` AS select `p`.`id_penyewaan` AS `id_penyewaan`,`pg`.`nama` AS `nama_pelanggan`,`pg`.`email` AS `email`,`pg`.`no_telepon` AS `no_telepon`,`ac`.`nama_alat` AS `nama_alat`,`ac`.`kategori` AS `kategori`,`dp`.`jumlah` AS `jumlah`,`dp`.`harga_satuan` AS `harga_satuan`,`p`.`tgl_sewa` AS `tgl_sewa`,`p`.`tgl_kembali_seharusnya` AS `tgl_kembali_seharusnya`,`p`.`total_bayar` AS `total_bayar`,`p`.`status_penyewaan` AS `status_penyewaan`,`k`.`tgl_dikembalikan` AS `tgl_dikembalikan`,`k`.`denda` AS `denda`,(`p`.`total_bayar` + ifnull(`k`.`denda`,0)) AS `grand_total` from ((((`penyewaan` `p` join `pengguna` `pg` on((`p`.`id_pelanggan` = `pg`.`id_pengguna`))) join `detail_penyewaan` `dp` on((`p`.`id_penyewaan` = `dp`.`id_penyewaan`))) join `alat_camping` `ac` on((`dp`.`id_alat` = `ac`.`id_alat`))) left join `pengembalian` `k` on((`p`.`id_penyewaan` = `k`.`id_penyewaan`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05 19:41:42
