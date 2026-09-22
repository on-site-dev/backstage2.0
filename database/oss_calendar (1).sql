-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2026 at 06:32 PM
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
-- Database: `oss_calendar`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings_log`
--

CREATE TABLE `bookings_log` (
  `id` int(11) NOT NULL,
  `google_event_id` varchar(255) NOT NULL,
  `action` enum('created','updated','deleted') NOT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings_log`
--

INSERT INTO `bookings_log` (`id`, `google_event_id`, `action`, `created_by`, `notes`, `created_at`) VALUES
(1, 'u1j9thvq0ir39hkc6fbhkt30us', 'created', 'admin', NULL, '2026-03-31 23:23:17'),
(2, 'i16s9v8v09d8hipgbb310huoc0', 'created', 'admin', NULL, '2026-03-31 23:51:04'),
(3, 'kscmlsgrdf96l2iou07r86p6s4', 'created', 'admin', NULL, '2026-04-01 16:24:31'),
(4, '06bb88f0jevu87vhecar6au7t8', 'created', 'admin', NULL, '2026-04-01 16:24:33'),
(5, '_60q30c1g60o30e1i60o4ac1g60rj8gpl88rj2c1h84s34h9g60s30c1g60o30c1g69142hhm69146ci16gs48ghg64o30c1g60o30c1g60o30c1g60o32c1g60o30c1g6go32c9m8osk6d9p8ks30e9k8h146e258533ce1g84r48gq36oog_20260403T210000Z', 'deleted', 'admin', NULL, '2026-04-01 16:45:20'),
(6, 'i16s9v8v09d8hipgbb310huoc0', 'deleted', 'admin', NULL, '2026-04-01 16:47:14'),
(7, 'u1j9thvq0ir39hkc6fbhkt30us', 'deleted', 'admin', NULL, '2026-04-01 16:48:19'),
(8, '1a2mpm946fljoeqaogbjrupaqg', 'created', 'admin', NULL, '2026-04-01 18:15:23'),
(9, '1a2mpm946fljoeqaogbjrupaqg', 'deleted', 'admin', NULL, '2026-04-01 18:29:05'),
(10, 'f95qhpslhq5erc6tvidrpdgbvg', 'created', 'admin', NULL, '2026-04-01 18:45:08'),
(11, '3m6lvncpeudhgqu6l6g2a7qvck', 'created', 'admin', NULL, '2026-04-01 18:46:27'),
(12, 'gan4pqdpfrk5v7qem615ibv5k8', 'created', 'admin', NULL, '2026-04-03 15:15:07'),
(13, 'ven6u1t4snvnq6vtkqh41dtco0', 'created', 'admin', NULL, '2026-04-03 15:16:07'),
(14, 'ven6u1t4snvnq6vtkqh41dtco0', 'updated', 'admin', NULL, '2026-04-03 16:26:45');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `status` enum('available','in-use','maintenance') DEFAULT 'available',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_resources`
--

CREATE TABLE `event_resources` (
  `id` int(11) NOT NULL,
  `google_event_id` varchar(255) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_resources`
--

INSERT INTO `event_resources` (`id`, `google_event_id`, `resource_id`, `start_time`, `end_time`, `role_id`) VALUES
(5, 'kscmlsgrdf96l2iou07r86p6s4', 3, '2026-04-01 23:25:00', '2026-04-02 12:25:00', NULL),
(6, 'kscmlsgrdf96l2iou07r86p6s4', 5, '2026-04-01 23:25:00', '2026-04-02 12:25:00', NULL),
(7, 'kscmlsgrdf96l2iou07r86p6s4', 8, '2026-04-01 23:25:00', '2026-04-02 12:25:00', NULL),
(8, '06bb88f0jevu87vhecar6au7t8', 3, '2026-04-01 23:25:00', '2026-04-02 12:25:00', NULL),
(9, '06bb88f0jevu87vhecar6au7t8', 5, '2026-04-01 23:25:00', '2026-04-02 12:25:00', NULL),
(10, '06bb88f0jevu87vhecar6au7t8', 8, '2026-04-01 23:25:00', '2026-04-02 12:25:00', NULL),
(14, 'f95qhpslhq5erc6tvidrpdgbvg', 2, '2026-04-13 10:00:00', '2026-04-13 12:00:00', NULL),
(15, 'f95qhpslhq5erc6tvidrpdgbvg', 4, '2026-04-13 10:00:00', '2026-04-13 12:00:00', NULL),
(16, 'f95qhpslhq5erc6tvidrpdgbvg', 7, '2026-04-13 10:00:00', '2026-04-13 12:00:00', NULL),
(17, '3m6lvncpeudhgqu6l6g2a7qvck', 1, '2026-04-13 08:00:00', '2026-04-13 10:00:00', NULL),
(18, '3m6lvncpeudhgqu6l6g2a7qvck', 5, '2026-04-13 08:00:00', '2026-04-13 10:00:00', NULL),
(19, '3m6lvncpeudhgqu6l6g2a7qvck', 8, '2026-04-13 08:00:00', '2026-04-13 10:00:00', NULL),
(20, 'gan4pqdpfrk5v7qem615ibv5k8', 3, '2026-04-13 01:00:00', '2026-04-13 14:00:00', NULL),
(21, 'gan4pqdpfrk5v7qem615ibv5k8', 6, '2026-04-13 01:00:00', '2026-04-13 14:00:00', NULL),
(22, 'gan4pqdpfrk5v7qem615ibv5k8', 10, '2026-04-13 01:00:00', '2026-04-13 14:00:00', NULL),
(26, 'ven6u1t4snvnq6vtkqh41dtco0', 5, '2026-04-16 09:00:00', '2026-04-16 23:00:00', NULL),
(27, 'ven6u1t4snvnq6vtkqh41dtco0', 8, '2026-04-16 09:00:00', '2026-04-16 23:00:00', NULL),
(28, 'ven6u1t4snvnq6vtkqh41dtco0', 1, '2026-04-16 09:00:00', '2026-04-16 23:00:00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Staff',
  `email` varchar(100) DEFAULT NULL,
  `calendar_id` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#0F3460',
  `notes` text DEFAULT NULL,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `name`, `type`, `email`, `calendar_id`, `color`, `notes`, `active`, `created_at`) VALUES
(1, 'Wes', 'Staff', '', '', '#0f3460', '', 1, '2026-03-31 23:02:24'),
(2, 'Videographer', 'Staff', '', NULL, '#0F3460', NULL, 1, '2026-03-31 23:02:24'),
(3, 'Editor', 'Staff', '', NULL, '#0F3460', NULL, 1, '2026-03-31 23:02:24'),
(4, 'Camera Kit A', 'Equipment', '', '', '#fd1717', '', 1, '2026-03-31 23:02:24'),
(5, 'Camera Kit B', 'Equipment', '', NULL, '#E94560', NULL, 1, '2026-03-31 23:02:24'),
(6, 'Tripod Set', 'Equipment', '', NULL, '#E94560', NULL, 1, '2026-03-31 23:02:24'),
(7, 'Edit Suite 1', 'Room', '', 'c_8f888f68699e60b20fe2f5eefb854980ec40ac9ca5b789d6495079c5e090a978@group.calendar.google.com', '#2E8B57', NULL, 1, '2026-03-31 23:02:24'),
(8, 'Edit Suite 2', 'Room', '', 'c_82b695f52dddabd6496aef8f59fc6d104e14934ed99fea5495ccd8640528ee9e@group.calendar.google.com', '#2E8B57', NULL, 1, '2026-03-31 23:02:24'),
(9, 'Edit Suite 3', 'Room', '', 'c_b4ea6a30aef818f862f9dc51963978f44fc2bba40096b053c71697e9551d68aa@group.calendar.google.com', '#0f3460', '', 1, '2026-04-01 19:29:08'),
(10, 'Steve', 'Room', '', 'steve.smith@plexsoftapps.com', '#0f3460', '', 1, '2026-04-02 17:09:21');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'Producer', '2026-04-03 16:21:27'),
(2, 'Editor', '2026-04-03 16:21:27'),
(3, 'Camera Operator', '2026-04-03 16:21:27'),
(4, 'Director', '2026-04-03 16:21:27'),
(5, 'Audio', '2026-04-03 16:21:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings_log`
--
ALTER TABLE `bookings_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_resources`
--
ALTER TABLE `event_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `fk_role` (`role_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings_log`
--
ALTER TABLE `bookings_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_resources`
--
ALTER TABLE `event_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_resources`
--
ALTER TABLE `event_resources`
  ADD CONSTRAINT `event_resources_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`),
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
