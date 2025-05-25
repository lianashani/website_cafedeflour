-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 25, 2025 at 06:48 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_cafedeflour`
--

-- --------------------------------------------------------

--
-- Table structure for table `kasir`
--

CREATE TABLE `kasir` (
  `id_kasir` int NOT NULL,
  `nama_kasir` varchar(20) DEFAULT NULL,
  `alamat` varchar(25) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `jenis_kelamin` enum('laki-laki','perempuan') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kasir`
--

INSERT INTO `kasir` (`id_kasir`, `nama_kasir`, `alamat`, `password`, `jenis_kelamin`) VALUES
(11, 'bilqif', 'las vegas kopo', 'bill_010203', 'laki-laki'),
(16, 'fahis', 'jl marken', 'fahis600iu', 'laki-laki'),
(17, 'sani', 'kopo jeruk', '111123', 'perempuan'),
(22, 'rachel', 'las vegas kopo', '12345', 'perempuan'),
(23, 'risqi', 'jl marken', 'bilkifgemoy', 'laki-laki');

-- --------------------------------------------------------

--
-- Table structure for table `kode_promo`
--

CREATE TABLE `kode_promo` (
  `id` int NOT NULL,
  `kode` varchar(50) NOT NULL,
  `jenis` enum('persen','nominal') NOT NULL,
  `nilai` int NOT NULL,
  `aktif` tinyint(1) DEFAULT '1',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_akhir` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kode_promo`
--

INSERT INTO `kode_promo` (`id`, `kode`, `jenis`, `nilai`, `aktif`, `tanggal_mulai`, `tanggal_akhir`) VALUES
(1, 'HEMAT10', 'persen', 10, 1, '2025-05-21', '2025-05-31'),
(2, 'DISC2000', 'nominal', 2000, 1, '2025-05-21', '2025-06-15'),
(3, 'HAPPYHOUR21', 'persen', 20, 1, '2025-05-22', '2025-05-31');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int NOT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `deskripsi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `kategori` varchar(30) DEFAULT NULL,
  `nama_menu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `stok` int NOT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `harga`, `deskripsi`, `kategori`, `nama_menu`, `stok`, `gambar`) VALUES
(4, '20000.00', 'plain wheat bread hh', 'bakery', 'Wheat Bread', 0, 'roti.jpg'),
(5, '20000.00', 'toast bread french', 'bakery', 'Croissant', 10, 'croissants-black-background.jpg'),
(7, '25000.00', 'coffee with steamed milk', 'kopi', 'Latte ', 1000, 'menu-2.jpg'),
(8, '10000.00', 'coffee with hot water', 'kopi', 'Americano', 100, 'americano.jpg'),
(23, '15000.00', 'a coffee with 20% of milk', 'kopi', 'Espresso', 200, 'espresso.jpg'),
(24, '25000.00', 'Thai tea mixed with milk', 'kopi', 'Chai Latte', 222, 'chailatte.jpg'),
(26, '25000.00', 'Coffee with idk', 'kopi', 'Cappucino', 222, 'cappucino.jpg'),
(38, '20000.00', 'Soft waffle topped with fresh strawberries', 'bakery', 'Strawberry Waffle', 100, 'cake1.jpg'),
(39, '22000.00', 'Sweet tart with raspberry topping', 'bakery', 'Raspberry Tart', 100, 'cake2.jpg'),
(40, '25000.00', 'Classic apple pie with crispy crust', 'bakery', 'Apple Pie', 80, 'cake3.jpg'),
(41, '18000.00', 'Flaky pastry with cinnamon flavor', 'bakery', 'Cinnamon Twist', 90, 'cake4.jpg'),
(42, '20000.00', 'Buttery croissant filled with chocolate', 'bakery', 'Chocolate Croissant', 85, 'cake5.jpg'),
(43, '27000.00', 'Creamy cheesecake with raspberry sauce', 'bakery', 'Raspberry Cheesecake', 70, 'cake6.jpg'),
(44, '22000.00', 'Chocolate cupcake topped with Ferrero Rocher', 'bakery', 'Ferrero Cupcake', 60, 'cupcake.jpg'),
(46, '15000.00', 'Cookies packed with chocolate chips', 'bakery', 'Chocolate Chip Cookies', 120, 'mae-mu-kID9sxbJ3BQ-unsplash.jpg'),
(47, '23000.00', 'Cold matcha green tea with milk and ice', 'kopi', 'Iced Matcha Latte', 90, 'matcha.jpg'),
(48, '23000.00', 'Warm matcha latte with latte art', 'kopi', 'Hot Matcha Latte', 90, 'matchalatte.jpg'),
(49, '19000.00', 'Choux pastry filled with vanilla cream', 'bakery', 'Vanilla Cream Puff', 222, 'cake7.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int NOT NULL,
  `id_menu` int DEFAULT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `catatan` text,
  `waktu_pesan` datetime DEFAULT NULL,
  `status` enum('Menunggu','Diproses','Selesai','Tidak Selesai') DEFAULT 'Menunggu',
  `waktu_diproses` datetime DEFAULT NULL,
  `id_transaksi` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id_review` int NOT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `komentar` text,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(255) DEFAULT NULL,
  `parent_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`id_review`, `nama_pelanggan`, `rating`, `komentar`, `tanggal`, `foto`, `parent_id`) VALUES
(18, 'carisa', 5, 'enak bgt ', '2025-05-22 00:00:00', '1747887145_682ea429ab065.jpg', NULL),
(19, 'dara', 5, 'bakal kesini lg hh', '2025-05-22 00:00:00', '1747887175_682ea447ab7bd.jpg', NULL),
(20, 'shanii', 5, 'tempatnya bagus', '2025-05-22 00:00:00', '1747887196_682ea45c28151.jpg', NULL),
(22, 'carisa', 5, 'okk', '2025-05-22 00:00:00', '1747887608_682ea5f898a6d.jpg', NULL),
(24, 'doni', 0, 'dmmn ', '2025-05-22 11:45:35', '', 18),
(25, 'amel', 0, 'itu mahal ga', '2025-05-22 11:49:34', '', 19),
(29, 'car', 0, 'enak g', '2025-05-22 21:06:00', '', 21),
(30, 'carisa', 5, 'RAMAH', '2025-05-22 00:00:00', '1747924757_682f3715867f2.jpg', NULL),
(31, 'carisa', 5, 'ramah', '2025-05-22 00:00:00', '1747924777_682f3729b7d76.jpg', NULL),
(32, 'salfa', 3, 'tdk ramah', '2025-05-22 00:00:00', '', NULL),
(34, 'balqis', 5, 'kok pait', '2025-05-22 00:00:00', '1747929986_682f4b82d4e5d.jpg', NULL),
(35, 'salfa', 0, 'ya iy kan americano', '2025-05-22 23:07:16', '', 34),
(37, 'balqis', 2, 'amerikano nya pait', '2025-05-22 00:00:00', '', NULL),
(38, 'acel', 5, 'enakk banget', '2025-05-23 00:00:00', '', NULL),
(39, 'balqis', 5, 'love it', '2025-05-25 00:00:00', '', NULL),
(40, 'sani', 0, 'enak', '2025-05-25 13:10:41', '', 19);

-- --------------------------------------------------------

--
-- Table structure for table `special_members`
--

CREATE TABLE `special_members` (
  `id_member` int NOT NULL,
  `nama_member` varchar(50) DEFAULT NULL,
  `nomor_hp` varchar(15) DEFAULT NULL,
  `tingkatan` enum('Bronze','Silver','Gold','Platinum') DEFAULT 'Bronze',
  `total_transaksi` decimal(12,2) DEFAULT '0.00',
  `poin` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `special_members`
--

INSERT INTO `special_members` (`id_member`, `nama_member`, `nomor_hp`, `tingkatan`, `total_transaksi`, `poin`) VALUES
(1, 'tarnoo', '08589266', 'Bronze', '20000.00', 2),
(2, 'putri kecoa', '08828822', 'Bronze', '39000.00', 40);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `kode_transaksi` varchar(50) DEFAULT NULL,
  `id_pelanggan` int DEFAULT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `id_kasir` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `lokasi` enum('dine in','take away') DEFAULT NULL,
  `total_harga` int DEFAULT NULL,
  `bayar` int DEFAULT NULL,
  `kembali` int DEFAULT NULL,
  `catatan` text,
  `metode` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT 'tunai',
  `kode_promo` varchar(50) DEFAULT NULL,
  `diskon` int DEFAULT '0',
  `total_setelah_diskon` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `kode_transaksi`, `id_pelanggan`, `nama_pelanggan`, `id_kasir`, `tanggal`, `waktu`, `lokasi`, `total_harga`, `bayar`, `kembali`, `catatan`, `metode`, `status`, `metode_pembayaran`, `kode_promo`, `diskon`, `total_setelah_diskon`) VALUES
(128, 'TRX1748139625', 2, NULL, 11, '2025-05-25', '02:20:25', 'dine in', 19000, 20000, 1000, '', NULL, 'Belum Diproses', 'tunai', NULL, 0, 0),
(129, 'TRX1748139670', 2, NULL, 11, '2025-05-25', '02:21:10', 'take away', 20000, 20000, 0, 's', NULL, 'Belum Diproses', 'tunai', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id` int NOT NULL,
  `id_transaksi` int DEFAULT NULL,
  `id_menu` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `harga_saat_transaksi` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id`, `id_transaksi`, `id_menu`, `jumlah`, `harga_saat_transaksi`) VALUES
(148, 128, 49, 1, 19000),
(149, 129, 4, 1, 20000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`id_kasir`);

--
-- Indexes for table `kode_promo`
--
ALTER TABLE `kode_promo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pesanan_menu` (`id_menu`),
  ADD KEY `fk_pesanan_transaksi` (`id_transaksi`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id_review`);

--
-- Indexes for table `special_members`
--
ALTER TABLE `special_members`
  ADD PRIMARY KEY (`id_member`),
  ADD UNIQUE KEY `nomor_hp` (`nomor_hp`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_kasir` (`id_kasir`),
  ADD KEY `transaksi_ibfk_1` (`id_pelanggan`);

--
-- Indexes for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `transaksi_detail_ibfk_1` (`id_transaksi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kasir`
--
ALTER TABLE `kasir`
  MODIFY `id_kasir` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `kode_promo`
--
ALTER TABLE `kode_promo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id_review` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `special_members`
--
ALTER TABLE `special_members`
  MODIFY `id_member` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pesanan_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `special_members` (`id_member`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_kasir`) REFERENCES `kasir` (`id_kasir`);

--
-- Constraints for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
