-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Des 2025 pada 05.50
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
-- Database: `tabunganku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tabungan`
--

CREATE TABLE `tabungan` (
  `id` int(11) NOT NULL,
  `nama` varchar(191) NOT NULL,
  `jenis` varchar(100) NOT NULL,
  `jumlah` bigint(20) NOT NULL DEFAULT 0,
  `tanggal` date NOT NULL,
  `bukti` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tabungan`
--

INSERT INTO `tabungan` (`id`, `nama`, `jenis`, `jumlah`, `tanggal`, `bukti`, `created_at`) VALUES
(1, 'fani', 'Tabungan Pribadi', 20000000, '2025-12-24', '1764732542_kasMasuk__1_.csv', '2025-12-03 03:29:02'),
(2, 'fani', 'Tabungan Kelas', 200000, '2025-12-03', '', '2025-12-03 03:53:35'),
(3, 'simpanan', 'Tabungan Lainnya', 3000000, '2025-12-03', '', '2025-12-03 03:53:57'),
(4, 'fani', 'Tabungan Kelas', 200000, '2025-12-02', '1764734223_tabLainnya.csv', '2025-12-03 03:57:03'),
(5, 'fani', 'Tabungan Pribadi', 100000, '2025-12-04', '1764736425_kasKeluar.csv', '2025-12-03 04:33:45'),
(6, 'fani', 'Tabungan Lainnya', 240000, '2025-12-17', '1764736664_kasKeluar.csv', '2025-12-03 04:37:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('admin','bendahara','pengguna') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `username`, `password`, `level`) VALUES
(1, 'fani', 'admin', 'admin123', 'admin'),
(2, 'fanii', 'bendahara', 'bendahara123', 'bendahara'),
(3, 'amelia', 'pengguna', 'pengguna123', 'pengguna');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tabungan`
--
ALTER TABLE `tabungan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tabungan`
--
ALTER TABLE `tabungan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
