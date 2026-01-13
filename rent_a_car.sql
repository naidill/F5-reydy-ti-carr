-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 07:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rent_a_car`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL COMMENT 'รหัสรถยนต์',
  `license_plate` varchar(20) NOT NULL COMMENT 'ทะเบียนรถ',
  `make` varchar(50) NOT NULL COMMENT 'ยี่ห้อ',
  `model` varchar(50) NOT NULL COMMENT 'รุ่น',
  `year` year(4) NOT NULL COMMENT 'ปีที่ผลิต',
  `color` varchar(30) NOT NULL COMMENT 'สี',
  `daily_rate` decimal(10,2) NOT NULL COMMENT 'อัตราค่าเช่าต่อวัน',
  `status` enum('Available','Rented','Maintenance') NOT NULL COMMENT 'สถานะ (ว่าง, ถูกเช่า, ซ่อมบำรุง)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL COMMENT 'รหัสลูกค้า',
  `name` varchar(100) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล',
  `address` varchar(255) NOT NULL COMMENT 'ที่อยู่',
  `phone_number` varchar(15) NOT NULL COMMENT 'เบอร์โทรศัพท์',
  `email` varchar(100) NOT NULL COMMENT 'อีเมล',
  `license_number` varchar(50) NOT NULL COMMENT 'เลขที่ใบขับขี่'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL COMMENT 'รหัสพนักงาน',
  `name` varchar(100) NOT NULL COMMENT 'ชื่อ-นามสกุล',
  `job_title` varchar(50) NOT NULL COMMENT 'ตำแหน่ง',
  `phone_number` varchar(15) NOT NULL COMMENT 'เบอร์โทรศัพท์'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL COMMENT 'รหัสสัญญาเช่า',
  `customer_id` int(11) NOT NULL COMMENT 'รหัสลูกค้าที่เช่า',
  `car_id` int(11) NOT NULL COMMENT 'รหัสรถยนต์ที่ถูกเช่า',
  `rental_date` date NOT NULL COMMENT 'วันที่เริ่มเช่า',
  `return_date` date NOT NULL COMMENT 'วันที่กำหนดคืน',
  `actual_return_date` date NOT NULL COMMENT 'วันที่คืนจริง',
  `total_cost` decimal(10,2) NOT NULL COMMENT 'ค่าเช่ารวม',
  `status` enum('Pending','Active','Completed','Canceled') NOT NULL COMMENT 'สถานะการเช่า'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
