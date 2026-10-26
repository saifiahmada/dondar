-- MariaDB dump 10.19  Distrib 10.4.27-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: db_dondar_polanka
-- ------------------------------------------------------
-- Server version	10.4.27-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `kegiatan_donor`
--

DROP TABLE IF EXISTS `kegiatan_donor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kegiatan_donor` (
  `id_kegiatan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kegiatan` varchar(150) NOT NULL,
  `tanggal_kegiatan` date NOT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `penyelenggara` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_kegiatan`),
  KEY `idx_kegiatan_tanggal` (`tanggal_kegiatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kegiatan_donor`
--

LOCK TABLES `kegiatan_donor` WRITE;
/*!40000 ALTER TABLE `kegiatan_donor` DISABLE KEYS */;
/*!40000 ALTER TABLE `kegiatan_donor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pemeriksaan_donor`
--

DROP TABLE IF EXISTS `pemeriksaan_donor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pemeriksaan_donor` (
  `id_pemeriksaan` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_pendonor` bigint(20) unsigned NOT NULL,
  `id_kegiatan` bigint(20) unsigned NOT NULL,
  `tanggal_pemeriksaan` datetime NOT NULL DEFAULT current_timestamp(),
  `tekanan_sistolik` smallint(5) unsigned DEFAULT NULL,
  `tekanan_diastolik` smallint(5) unsigned DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `gula_darah` decimal(6,2) DEFAULT NULL,
  `layak_donor` tinyint(1) NOT NULL DEFAULT 0,
  `obat_diberikan` varchar(255) DEFAULT NULL,
  `catatan_pemeriksaan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pemeriksaan`),
  KEY `idx_pemeriksaan_pendonor` (`id_pendonor`),
  KEY `idx_pemeriksaan_kegiatan` (`id_kegiatan`),
  KEY `idx_pemeriksaan_kelayakan` (`layak_donor`),
  KEY `idx_pemeriksaan_tanggal` (`tanggal_pemeriksaan`),
  CONSTRAINT `fk_pemeriksaan_kegiatan` FOREIGN KEY (`id_kegiatan`) REFERENCES `kegiatan_donor` (`id_kegiatan`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pemeriksaan_pendonor` FOREIGN KEY (`id_pendonor`) REFERENCES `pendonor` (`id_pendonor`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemeriksaan_donor`
--

LOCK TABLES `pemeriksaan_donor` WRITE;
/*!40000 ALTER TABLE `pemeriksaan_donor` DISABLE KEYS */;
/*!40000 ALTER TABLE `pemeriksaan_donor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendonor`
--

DROP TABLE IF EXISTS `pendonor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pendonor` (
  `id_pendonor` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `golongan_darah` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pendonor`),
  KEY `idx_pendonor_nama` (`nama_lengkap`),
  KEY `idx_pendonor_golongan_darah` (`golongan_darah`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendonor`
--

LOCK TABLES `pendonor` WRITE;
/*!40000 ALTER TABLE `pendonor` DISABLE KEYS */;
INSERT INTO `pendonor` VALUES (1,'Burhan','Brangas','2022-02-28','L','B+','081255701925','2026-10-26 07:17:17','2026-10-26 07:17:17');
/*!40000 ALTER TABLE `pendonor` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26 17:10:33
