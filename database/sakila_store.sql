-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 08:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sakila_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `nama`, `email`, `no_hp`, `alamat`) VALUES
(1, 'a', 'mahasiswi02@gmail.com', '008967655686', 'jln'),
(2, 'aini', 'aini@gmail.com', '089799367684', 'jalanin aja dulu'),
(3, 'dira', 'aini@gmail.com', '089799367684', 'jln'),
(4, 'dira', 'aini@gmail.com', '089799367684', 'jln'),
(5, 'dira', 'aini@gmail.com', '089799367684', 'jln'),
(6, 'dira', 'aini@gmail.com', '089799367684', 'jln mawar'),
(7, 'dira', 'aini@gmail.com', '089799367684', 'jln'),
(8, 'dira', 'aini@gmail.com', '089799367684', 'jln'),
(9, 'dira', 'aini@gmail.com', '089799367684', 'jln'),
(10, 'dira', 'aini@gmail.com', '089799367684', 'jln'),
(11, 'najla melanda', 'melanda@gmail.com', '089577845321', 'jln mawar no 3'),
(12, 'melanda', 'melanda09@gmail.com', '08957735412', 'jalan mawar'),
(13, 'anjani', 'aini@gmail.com', '089799367684', 'jln mawar');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `items` text DEFAULT NULL,
  `jumlah_produk` int(11) DEFAULT NULL,
  `total_price` decimal(12,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `alamat`, `items`, `jumlah_produk`, `total_price`, `payment_method`, `order_date`) VALUES
(1, 'melanda', 'jalan mawar', 'hijab pasmina ', 1, 37000.00, 'QRIS', '2026-06-09 16:31:19'),
(2, 'anjani', 'jln mawar', 'hijab pasmina , hijab paris premium', 2, 144000.00, 'QRIS', '2026-06-09 16:48:26');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `price`, `image`, `category`, `stock`, `created_at`) VALUES
(6, 'hijab pasmina ', 37000, 'admin/upload/hijab pasmina.jpeg', 'hijab', 10, '2026-05-23 07:06:03'),
(7, 'hijab paris premium', 35000, 'admin/upload/hijab paris premium.jpeg', 'hijab', 11, '2026-05-23 07:07:10'),
(8, 'hijab motif', 45000, 'admin/upload/hijab motif.jpeg', 'hijab', 15, '2026-05-23 07:07:38'),
(9, 'hijab diamond', 50000, 'admin/upload/hijab diamond.jpeg', 'hijab', 10, '2026-05-23 07:08:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
