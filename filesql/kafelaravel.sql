-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Apr 2025 pada 12.23
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kafelaravel`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `idkategori` int(11) NOT NULL,
  `namakategori` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`idkategori`, `namakategori`, `created_at`, `updated_at`) VALUES
(1, 'Makanan', '2023-08-14 02:54:58', '2023-10-31 02:18:37'),
(2, 'Minuman', NULL, '2023-10-31 02:18:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `meja`
--

CREATE TABLE `meja` (
  `idmeja` int(11) NOT NULL,
  `nomeja` text NOT NULL,
  `hargameja` varchar(15) DEFAULT NULL,
  `fotomeja` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `meja`
--

INSERT INTO `meja` (`idmeja`, `nomeja`, `hargameja`, `fotomeja`, `deskripsi`) VALUES
(6, 'VIP 2', '100000', 'meja vip.jpg', '<p>Include :</p>\r\n\r\n<ol>\r\n	<li>Jus Mangga</li>\r\n	<li>Nasi Goreng</li>\r\n	<li>Bakwan</li>\r\n</ol>'),
(7, 'VIP 1', '100000', 'meja vip.jpg', '<p>Include :</p>\r\n\r\n<ol>\r\n	<li>Jus Mangga</li>\r\n	<li>Nasi Goreng</li>\r\n	<li>Bakwan</li>\r\n</ol>'),
(8, 'Reguler 3', '50000', 'mejareguler.jpg', '<p>Include :</p>\r\n\r\n<ol>\r\n	<li>Es Teh</li>\r\n	<li>Nasi</li>\r\n</ol>'),
(9, 'Reguler 2', '50000', 'mejareguler.jpg', '<p>Include :</p>\r\n\r\n<ol>\r\n	<li>Es Teh</li>\r\n	<li>Nasi</li>\r\n</ol>'),
(10, 'Reguler 1', '50000', 'mejareguler.jpg', '<p>Include :</p>\r\n\r\n<ol>\r\n	<li>Es Teh</li>\r\n	<li>Nasi</li>\r\n</ol>'),
(14, '001', NULL, 'default.jpg', NULL),
(15, '002', NULL, 'default.jpg', NULL),
(16, '003', NULL, 'default.jpg', NULL),
(17, '004', NULL, 'default.jpg', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `idpembelian` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `idmeja` int(11) DEFAULT NULL,
  `notransaksi` text NOT NULL,
  `tanggalbeli` date NOT NULL,
  `totalbeli` text NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nohp` varchar(25) NOT NULL,
  `metodepembayaran` text NOT NULL,
  `statusbeli` text NOT NULL,
  `catatan` text DEFAULT NULL,
  `statusbayar` varchar(255) NOT NULL,
  `snaptoken` varchar(255) DEFAULT NULL,
  `waktu` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembelian`
--

INSERT INTO `pembelian` (`idpembelian`, `id`, `idmeja`, `notransaksi`, `tanggalbeli`, `totalbeli`, `nama`, `nohp`, `metodepembayaran`, `statusbeli`, `catatan`, `statusbayar`, `snaptoken`, `waktu`) VALUES
(1, 4, NULL, 'TP20240610080328', '2024-06-10', '15000', 'Sugeng', '08591285912', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Sudah Bayar', '3d7bb9f7-31d9-4b66-8f7a-b2a9eb4ec3d3', '2024-06-10 08:03:28'),
(2, 5, NULL, 'TP20240610081931', '2024-06-10', '50000', 'Lia', '0859125125', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Sudah Bayar', '1dc8019d-2248-48dd-8777-9fbfb05be6bf', '2024-06-10 08:19:31'),
(3, NULL, NULL, 'TP20250418074238', '2025-04-18', '24000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-18 07:42:38'),
(4, NULL, NULL, 'TP20250418074920', '2025-04-18', '63000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-18 07:49:20'),
(5, NULL, NULL, 'TP20250418081724', '2025-04-18', '124000', 'Tes', '089690746848', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Belum Bayar', 'b39aac87-bc68-42d5-933e-f3ae452e7296', '2025-04-18 08:17:24'),
(6, NULL, NULL, 'TP20250418085752', '2025-04-18', '15000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-18 08:57:52'),
(7, NULL, 14, 'TP20250418091954', '2025-04-18', '24000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-18 09:19:54'),
(8, NULL, 15, 'TP20250418092640', '2025-04-18', '39000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-18 09:26:40'),
(9, NULL, 16, 'TP20250418093019', '2025-04-18', '15000', 'Tes', '089690746848', 'QRIS / Transfer Virtual Account', 'Selesai', NULL, 'Sudah Di Bayar', 'f2f4d5d1-4266-4584-b5cc-c6f76d2f72a9', '2025-04-18 09:30:19'),
(10, NULL, 14, 'TP20250418095346', '2025-04-18', '15000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-18 09:53:46'),
(11, NULL, 15, 'TP20250418095916', '2025-04-18', '24000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-18 09:59:16'),
(12, NULL, 14, 'TP20250419044025', '2025-04-19', '24000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-19 04:40:25'),
(13, NULL, 16, 'TP20250419044305', '2025-04-19', '15000', 'Tes', '089690746848', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-19 04:43:05'),
(14, NULL, 14, 'TP20250419092423', '2025-04-19', '24000', 'Tes', '089690746848', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Belum Bayar', '18dd218c-5728-4dcd-b8d8-39cdf1e4291c', '2025-04-19 09:24:23'),
(16, NULL, 16, 'TP20250419103924', '2025-04-19', '24000', 'Tes', '089690746848', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Sudah Bayar', '060034b2-af96-41eb-9354-dad91b5da8a3', '2025-04-19 10:39:24'),
(18, NULL, 14, 'TP20250419105440', '2025-04-19', '48000', 'Tes', '089690746848', 'QRIS / Transfer Virtual Account', 'Selesai', NULL, 'Sudah Di Bayar', '243ba389-be7a-430c-9c58-16e2e30b940a', '2025-04-19 10:54:40'),
(19, NULL, 14, 'TP20250419110003', '2025-04-19', '18000', 'Tes', '089690746848', 'QRIS / Transfer Virtual Account', 'Selesai', NULL, 'Sudah Di Bayar', '955fd809-61f5-4f21-a936-0fdc3f9eb781', '2025-04-19 11:00:03'),
(20, NULL, 14, 'TP20250421025114', '2025-04-21', '39000', 'Tes', '089690746848', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Belum Bayar', '16f2a721-368f-480e-8406-290a11fd1d21', '2025-04-21 02:51:14'),
(21, NULL, 15, 'TP20250423034222', '2025-04-23', '24000', 'tes', '0800', 'Kasir', 'Belum di Konfirmasi', NULL, 'Belum Bayar', NULL, '2025-04-23 03:42:22'),
(27, NULL, 14, 'TP20250423084051', '2025-04-23', '94000', 'Tes', '0800', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Belum Bayar', '78c74d9f-8f51-4368-a954-0d4224e2b281', '2025-04-23 08:40:51'),
(28, NULL, 15, 'TP20250423084649', '2025-04-23', '54000', 'Tes', '0800', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Belum Bayar', '5072877f-069c-410a-8eac-c86dc26585b9', '2025-04-23 08:46:49'),
(29, NULL, 14, 'TP20250423085439', '2025-04-23', '30000', 'Tes', '0800', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Belum Bayar', 'eb2e3e31-93d0-4138-ba6d-7fa6255b975c', '2025-04-23 08:54:39'),
(30, NULL, 14, 'TP20250423085946', '2025-04-23', '49000', 'Tes', '0800', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Sudah Bayar', 'f118e61d-f2ac-4c44-bb04-ec9ddfcb5412', '2025-04-23 08:59:46'),
(31, NULL, 14, 'TP20250423090206', '2025-04-23', '45000', 'Tes', '0800', 'QRIS / Transfer Virtual Account', 'Belum di Konfirmasi', NULL, 'Sudah Bayar', '3ac49c49-bc6e-456e-87d4-a4ca090930c9', '2025-04-23 09:02:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembeliandetail`
--

CREATE TABLE `pembeliandetail` (
  `idpembeliandetail` int(11) NOT NULL,
  `idpembelian` int(11) NOT NULL,
  `idproduk` int(11) NOT NULL,
  `nama` text NOT NULL,
  `harga` text NOT NULL,
  `subharga` text NOT NULL,
  `jumlah` text NOT NULL,
  `statusulasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembeliandetail`
--

INSERT INTO `pembeliandetail` (`idpembeliandetail`, `idpembelian`, `idproduk`, `nama`, `harga`, `subharga`, `jumlah`, `statusulasan`) VALUES
(1, 1, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(2, 3, 1, 'Nasi Bakar Goreng', '24000', '24000', '1', NULL),
(3, 4, 10, 'Kopi Susu Banana', '24000', '48000', '2', NULL),
(4, 4, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(5, 5, 1, 'Nasi Bakar Goreng', '24000', '24000', '1', NULL),
(6, 6, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(7, 7, 1, 'Nasi Bakar Goreng', '24000', '24000', '1', NULL),
(8, 8, 10, 'Kopi Susu Banana', '24000', '24000', '1', NULL),
(9, 8, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(10, 9, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(11, 10, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(12, 11, 1, 'Nasi Bakar Goreng', '24000', '24000', '1', NULL),
(13, 12, 10, 'Kopi Susu Banana', '24000', '24000', '1', NULL),
(14, 13, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(15, 14, 10, 'Kopi Susu Banana', '24000', '24000', '1', NULL),
(17, 16, 10, 'Kopi Susu Banana', '24000', '24000', '1', NULL),
(19, 18, 10, 'Kopi Susu Banana', '24000', '24000', '1', NULL),
(20, 18, 1, 'Nasi Bakar Goreng', '24000', '24000', '1', NULL),
(21, 19, 13, 'Americano', '18000', '18000', '1', NULL),
(22, 20, 3, 'Mie Tumis', '15000', '15000', '1', NULL),
(23, 20, 1, 'Nasi Bakar Goreng', '24000', '24000', '1', NULL),
(24, 21, 1, 'Nasi Bakar Goreng', '24000', '24000', '1', NULL),
(27, 27, 1, 'Nasi Bakar Goreng - Reguler', '24000', '24000', '1', NULL),
(28, 27, 1, 'Nasi Bakar Goreng - Spesial', '35000', '70000', '2', NULL),
(29, 28, 10, 'Kopi Susu Banana - Reguler', '24000', '24000', '1', NULL),
(30, 28, 10, 'Kopi Susu Banana - Large', '30000', '30000', '1', NULL),
(31, 29, 35, 'Matcha - Medium', '30000', '30000', '1', NULL),
(32, 30, 1, 'Nasi Bakar Goreng - Reguler', '24000', '24000', '1', NULL),
(33, 30, 35, 'Matcha - Reguler', '25000', '25000', '1', NULL),
(34, 31, 3, 'Mie Tumis - Reguler', '15000', '15000', '1', NULL),
(35, 31, 35, 'Matcha - Medium', '30000', '30000', '1', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelianmejadetail`
--

CREATE TABLE `pembelianmejadetail` (
  `idpembelianmejadetail` int(11) NOT NULL,
  `idpembelian` int(11) NOT NULL,
  `idmeja` int(11) NOT NULL,
  `nomeja` text NOT NULL,
  `harga` text NOT NULL,
  `tanggal` date NOT NULL,
  `jam` varchar(5) NOT NULL,
  `statusulasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembelianmejadetail`
--

INSERT INTO `pembelianmejadetail` (`idpembelianmejadetail`, `idpembelian`, `idmeja`, `nomeja`, `harga`, `tanggal`, `jam`, `statusulasan`) VALUES
(1, 2, 10, 'Reguler 1', '50000', '2024-06-11', '17:00', NULL),
(2, 5, 6, 'VIP 2', '100000', '2025-04-30', '19:00', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` text NOT NULL,
  `alamat` text NOT NULL,
  `nohp` varchar(15) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `level` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `alamat`, `nohp`, `email`, `password`, `level`) VALUES
(1, 'admin', '', '', 'admin@gmail.com', 'admin', 'Admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `idproduk` int(11) NOT NULL,
  `idsubkategori` int(11) NOT NULL,
  `namaproduk` text NOT NULL,
  `hargaproduk` text NOT NULL,
  `fotoproduk` text NOT NULL,
  `deskripsiproduk` text NOT NULL,
  `ketersediaanproduk` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`idproduk`, `idsubkategori`, `namaproduk`, `hargaproduk`, `fotoproduk`, `deskripsiproduk`, `ketersediaanproduk`) VALUES
(1, 1, 'Nasi Bakar Goreng', '24000', 'resep-nasi-goreng-bakar.jpg', '<p>Reguler -&nbsp;24000</p>\r\n\r\n<p>Spesial -&nbsp;35000</p>', 'Tersedia'),
(3, 6, 'Mie Tumis', '15000', 'mietumis.jpg', '<p>-</p>\r\n\r\n<p>&nbsp;</p>', 'Tersedia'),
(10, 5, 'Kopi Susu Banana', '24000', 'kopisusubanana.webp', '<p>-</p>', 'Tersedia'),
(13, 3, 'Americano', '18000', 'americano.jpg', '<p>-</p>', 'Tidak Tersedia'),
(35, 4, 'Matcha', '25000', 'americano.jpg', '<p>Reguler -&nbsp;25000</p>\r\n\r\n<p>Medium -&nbsp;30000</p>', 'Tersedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produkjenis`
--

CREATE TABLE `produkjenis` (
  `idprodukjenis` int(11) NOT NULL,
  `idproduk` int(11) NOT NULL,
  `namaprodukjenis` varchar(255) NOT NULL,
  `hargaproduk` double(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produkjenis`
--

INSERT INTO `produkjenis` (`idprodukjenis`, `idproduk`, `namaprodukjenis`, `hargaproduk`) VALUES
(1, 1, 'Reguler', 24000.00),
(2, 1, 'Spesial', 35000.00),
(3, 10, 'Reguler', 24000.00),
(4, 10, 'Large', 30000.00),
(5, 35, 'Reguler', 25000.00),
(6, 35, 'Medium', 30000.00),
(7, 3, 'Reguler', 15000.00),
(8, 3, 'Special', 20000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `subkategori`
--

CREATE TABLE `subkategori` (
  `idsubkategori` int(11) NOT NULL,
  `idkategori` int(11) NOT NULL,
  `namasubkategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `subkategori`
--

INSERT INTO `subkategori` (`idsubkategori`, `idkategori`, `namasubkategori`) VALUES
(1, 1, 'Nasi'),
(2, 1, 'Gorengan'),
(3, 2, 'Teh'),
(4, 2, 'Jus'),
(5, 2, 'Kopi'),
(6, 1, 'Mie');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`idkategori`);

--
-- Indeks untuk tabel `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`idmeja`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`idpembelian`);

--
-- Indeks untuk tabel `pembeliandetail`
--
ALTER TABLE `pembeliandetail`
  ADD PRIMARY KEY (`idpembeliandetail`),
  ADD KEY `idpembelian` (`idpembelian`,`idproduk`),
  ADD KEY `idproduk` (`idproduk`);

--
-- Indeks untuk tabel `pembelianmejadetail`
--
ALTER TABLE `pembelianmejadetail`
  ADD PRIMARY KEY (`idpembelianmejadetail`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`idproduk`),
  ADD KEY `idkategori` (`idsubkategori`),
  ADD KEY `idsubkategori` (`idsubkategori`);

--
-- Indeks untuk tabel `produkjenis`
--
ALTER TABLE `produkjenis`
  ADD PRIMARY KEY (`idprodukjenis`);

--
-- Indeks untuk tabel `subkategori`
--
ALTER TABLE `subkategori`
  ADD PRIMARY KEY (`idsubkategori`),
  ADD KEY `idkategori` (`idkategori`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `idkategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `meja`
--
ALTER TABLE `meja`
  MODIFY `idmeja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `idpembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `pembeliandetail`
--
ALTER TABLE `pembeliandetail`
  MODIFY `idpembeliandetail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `pembelianmejadetail`
--
ALTER TABLE `pembelianmejadetail`
  MODIFY `idpembelianmejadetail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `idproduk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `produkjenis`
--
ALTER TABLE `produkjenis`
  MODIFY `idprodukjenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `subkategori`
--
ALTER TABLE `subkategori`
  MODIFY `idsubkategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pembeliandetail`
--
ALTER TABLE `pembeliandetail`
  ADD CONSTRAINT `pembeliandetail_ibfk_1` FOREIGN KEY (`idpembelian`) REFERENCES `pembelian` (`idpembelian`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pembeliandetail_ibfk_2` FOREIGN KEY (`idproduk`) REFERENCES `produk` (`idproduk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`idsubkategori`) REFERENCES `subkategori` (`idsubkategori`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `subkategori`
--
ALTER TABLE `subkategori`
  ADD CONSTRAINT `subkategori_ibfk_1` FOREIGN KEY (`idkategori`) REFERENCES `kategori` (`idkategori`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
