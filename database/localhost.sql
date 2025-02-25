-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 25, 2025 at 12:27 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nettbutikk`
--
CREATE DATABASE IF NOT EXISTS `nettbutikk` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `nettbutikk`;

-- --------------------------------------------------------

--
-- Table structure for table `brukarar`
--

CREATE TABLE `brukarar` (
  `brukar_id` int(11) NOT NULL,
  `brukarnamn` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `passord` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `epost` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `er_admin` tinyint(1) DEFAULT '0',
  `opprettet_dato` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `brukarar`
--

INSERT INTO `brukarar` (`brukar_id`, `brukarnamn`, `passord`, `epost`, `er_admin`, `opprettet_dato`) VALUES
(1, 'admin', '$2y$10$6POyg6Vc/vFGNmHHxn9ka.1.oa29dWhm.i0iiSyn1o4IE7dVKyX86', 'admin@butikk.no', 1, '2025-02-18 19:18:04'),
(8, 'oleo', '$2y$10$Jzs4T..EyJqLsi3ErJLu7.ZccFP.lUmmolvkj/V8WSAFJ6PKiYU6C', 'oleo@epost.no', 0, '2025-02-19 17:54:02');

-- --------------------------------------------------------

--
-- Table structure for table `ordrar`
--

CREATE TABLE `ordrar` (
  `ordre_id` int(11) NOT NULL,
  `brukar_id` int(11) DEFAULT NULL,
  `ordre_dato` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_pris` decimal(10,2) NOT NULL,
  `status` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'ny'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ordrar`
--

INSERT INTO `ordrar` (`ordre_id`, `brukar_id`, `ordre_dato`, `total_pris`, `status`) VALUES
(1, 1, '2025-02-19 15:33:43', '6000.00', 'ny'),
(2, 8, '2025-02-19 17:55:27', '6000.00', 'ny'),
(3, 8, '2025-02-19 17:58:55', '6000.00', 'ny'),
(4, 8, '2025-02-25 12:01:52', '24000.00', 'ny');

-- --------------------------------------------------------

--
-- Table structure for table `ordre_detaljar`
--

CREATE TABLE `ordre_detaljar` (
  `ordre_detalj_id` int(11) NOT NULL,
  `ordre_id` int(11) DEFAULT NULL,
  `produkt_id` int(11) DEFAULT NULL,
  `antal` int(11) NOT NULL,
  `pris_per_stk` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ordre_detaljar`
--

INSERT INTO `ordre_detaljar` (`ordre_detalj_id`, `ordre_id`, `produkt_id`, `antal`, `pris_per_stk`) VALUES
(1, 1, 8, 1, '6000.00'),
(2, 2, 8, 1, '6000.00'),
(3, 3, 8, 1, '6000.00'),
(4, 4, 8, 4, '6000.00');

-- --------------------------------------------------------

--
-- Table structure for table `produkt`
--

CREATE TABLE `produkt` (
  `produkt_id` int(11) NOT NULL,
  `namn` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `pris` decimal(10,2) NOT NULL,
  `lager_antal` int(11) NOT NULL,
  `beskriving` text COLLATE utf8_unicode_ci,
  `bilde_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `produkt`
--

INSERT INTO `produkt` (`produkt_id`, `namn`, `pris`, `lager_antal`, `beskriving`, `bilde_url`) VALUES
(8, 'Laptop', '6000.00', 23, 'Ein grei laptop?', '67b5f99e7232f.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brukarar`
--
ALTER TABLE `brukarar`
  ADD PRIMARY KEY (`brukar_id`),
  ADD UNIQUE KEY `brukarnamn` (`brukarnamn`),
  ADD UNIQUE KEY `epost` (`epost`);

--
-- Indexes for table `ordrar`
--
ALTER TABLE `ordrar`
  ADD PRIMARY KEY (`ordre_id`),
  ADD KEY `brukar_id` (`brukar_id`);

--
-- Indexes for table `ordre_detaljar`
--
ALTER TABLE `ordre_detaljar`
  ADD PRIMARY KEY (`ordre_detalj_id`),
  ADD KEY `ordre_id` (`ordre_id`),
  ADD KEY `produkt_id` (`produkt_id`);

--
-- Indexes for table `produkt`
--
ALTER TABLE `produkt`
  ADD PRIMARY KEY (`produkt_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brukarar`
--
ALTER TABLE `brukarar`
  MODIFY `brukar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ordrar`
--
ALTER TABLE `ordrar`
  MODIFY `ordre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ordre_detaljar`
--
ALTER TABLE `ordre_detaljar`
  MODIFY `ordre_detalj_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produkt`
--
ALTER TABLE `produkt`
  MODIFY `produkt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ordrar`
--
ALTER TABLE `ordrar`
  ADD CONSTRAINT `ordrar_ibfk_1` FOREIGN KEY (`brukar_id`) REFERENCES `brukarar` (`brukar_id`);

--
-- Constraints for table `ordre_detaljar`
--
ALTER TABLE `ordre_detaljar`
  ADD CONSTRAINT `ordre_detaljar_ibfk_1` FOREIGN KEY (`ordre_id`) REFERENCES `ordrar` (`ordre_id`),
  ADD CONSTRAINT `ordre_detaljar_ibfk_2` FOREIGN KEY (`produkt_id`) REFERENCES `produkt` (`produkt_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
