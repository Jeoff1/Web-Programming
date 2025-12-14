-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 01:04 PM
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
-- Database: `baranggay_blotter_3nf`
--

-- --------------------------------------------------------

--
-- Table structure for table `blotter_case`
--

CREATE TABLE `blotter_case` (
  `blotter_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blotter_case`
--

INSERT INTO `blotter_case` (`blotter_id`, `admin_id`, `category_id`, `incident_date`, `incident_time`, `location`, `description`, `photo`, `created_at`, `updated_at`) VALUES
(5, 2, 3, '2025-12-13', '14:30:00', 'branch one of', 'description of test test', NULL, '2025-12-14 16:30:56', '2025-12-14 16:30:56'),
(6, 2, 6, '2025-12-13', '13:47:00', 'malayo', 'description of this is this', NULL, '2025-12-14 16:48:02', '2025-12-14 16:48:02'),
(7, 3, 9, '2025-12-12', '12:12:00', '12th streeeet', 'description of descp des', NULL, '2025-12-14 16:57:08', '2025-12-14 16:57:08'),
(8, 3, 8, '2025-12-12', '18:34:00', 'legends lang ang mayroong alam', 'where yea', NULL, '2025-12-14 18:51:04', '2025-12-14 18:51:04'),
(9, 3, 11, '2025-12-01', '21:38:00', 'place of where land dispute', 'description of the the yea', 'blt_693ea1c3dc523.jpg', '2025-12-14 19:38:43', '2025-12-14 19:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `blotter_person`
--

CREATE TABLE `blotter_person` (
  `blotter_person_id` int(11) NOT NULL,
  `blotter_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `role_type` enum('Complainant','Respondent','Witness') NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blotter_person`
--

INSERT INTO `blotter_person` (`blotter_person_id`, `blotter_id`, `person_id`, `role_type`, `notes`) VALUES
(14, 6, 15, 'Complainant', NULL),
(15, 6, 14, 'Respondent', NULL),
(20, 7, 19, 'Complainant', NULL),
(21, 7, 18, 'Respondent', NULL),
(30, 8, 25, 'Complainant', NULL),
(31, 8, 21, 'Respondent', NULL),
(32, 9, 26, 'Complainant', NULL),
(33, 9, 27, 'Respondent', NULL),
(34, 5, 28, 'Complainant', NULL),
(35, 5, 8, 'Respondent', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `case_status`
--

CREATE TABLE `case_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `status_description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `case_status`
--

INSERT INTO `case_status` (`status_id`, `status_name`, `status_description`, `is_active`) VALUES
(1, 'Pending', 'Case has been filed and awaiting initial review', 1),
(2, 'Active', 'Case is currently being investigated', 1),
(3, 'Under Investigation', 'Case is under active investigation', 1),
(4, 'Resolved', 'Case has been resolved', 1),
(5, 'Referred to Police', 'Case has been referred to police authorities', 1),
(6, 'Closed', 'Case has been closed', 1);

-- --------------------------------------------------------

--
-- Table structure for table `case_status_history`
--

CREATE TABLE `case_status_history` (
  `history_id` int(11) NOT NULL,
  `blotter_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `status_reason` text DEFAULT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `case_status_history`
--

INSERT INTO `case_status_history` (`history_id`, `blotter_id`, `status_id`, `changed_by`, `status_reason`, `changed_at`) VALUES
(1, 5, 1, 2, 'Case created', '2025-12-14 16:31:03'),
(5, 5, 4, 2, 'Case created', '2025-12-14 16:41:55'),
(6, 6, 1, 2, 'Case created', '2025-12-14 16:48:04'),
(7, 6, 4, 3, 'Case created', '2025-12-14 16:48:52'),
(8, 5, 3, 3, 'Hi baby I love you', '2025-12-14 16:51:38'),
(9, 7, 1, 3, 'Case created', '2025-12-14 16:57:08'),
(10, 7, 4, 3, 'Case Resolved, hihi bayad bayad', '2025-12-14 17:01:28'),
(11, 8, 1, 3, 'Case created', '2025-12-14 18:51:04'),
(12, 8, 4, 3, 'This is resolved because', '2025-12-14 19:29:28'),
(13, 5, 3, 3, 'hello hi hih i hi hi hi', '2025-12-14 19:33:47'),
(14, 8, 4, 3, 'This is resolved because because of this isi si sis is i', '2025-12-14 19:34:43'),
(15, 9, 1, 3, 'Case created', '2025-12-14 19:38:43'),
(16, 5, 3, 3, 'hello hi hih i hi hi hi\n[Evidence/Document: status_693ea2f479e3b.jpg]', '2025-12-14 19:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(10, 'Business Dispute'),
(5, 'Domestic Violence'),
(8, 'Found Item'),
(11, 'Land Dispute'),
(7, 'Lost Item'),
(6, 'Noise Complaint'),
(12, 'Other'),
(3, 'Physical Assault'),
(2, 'Property Dispute'),
(1, 'Theft'),
(9, 'Traffic Incident'),
(4, 'Verbal Dispute');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `blotter_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type_id`, `blotter_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 1, NULL, 'Case #5 status has been updated to: Resolved', 0, '2025-12-14 16:42:01'),
(2, 2, 1, NULL, 'New case #0 has been added.', 0, '2025-12-14 16:48:07'),
(3, 3, 1, NULL, 'Case #6 status has been updated to: Resolved', 0, '2025-12-14 16:48:56'),
(4, 3, 1, NULL, 'Case #5 status has been updated to: Under Investigation', 0, '2025-12-14 16:51:42'),
(5, 3, 1, NULL, 'New case #7 has been added.', 0, '2025-12-14 16:57:08'),
(6, 3, 1, NULL, 'Case #7 status has been updated to: Resolved', 0, '2025-12-14 17:01:31'),
(7, 3, 1, NULL, 'New case #8 has been added.', 0, '2025-12-14 18:51:04'),
(8, 3, 1, NULL, 'Case #8 status has been updated to: Resolved', 0, '2025-12-14 19:29:32'),
(9, 3, 2, NULL, 'Case #5 has been edited.', 0, '2025-12-14 19:33:48'),
(10, 3, 2, NULL, 'Case #8 has been edited.', 0, '2025-12-14 19:34:43'),
(11, 3, 1, NULL, 'Case #8 status has been updated to: Settled', 0, '2025-12-14 19:35:51'),
(12, 3, 1, NULL, 'New case #9 has been added.', 0, '2025-12-14 19:38:43'),
(13, 3, 2, NULL, 'Case #5 has been edited.', 0, '2025-12-14 19:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `notification_type`
--

CREATE TABLE `notification_type` (
  `type_id` int(11) NOT NULL,
  `type_code` varchar(50) NOT NULL,
  `type_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_type`
--

INSERT INTO `notification_type` (`type_id`, `type_code`, `type_description`) VALUES
(1, 'case_added', 'New case has been added to the system'),
(2, 'case_edited', 'Case information has been modified'),
(3, 'case_resolved', 'Case has been marked as resolved'),
(4, 'case_deleted', 'Case has been deleted from the system'),
(5, 'pending_old', 'Case has been pending for an extended period'),
(6, 'status_changed', 'Case status has been updated');

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE `person` (
  `person_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `person`
--

INSERT INTO `person` (`person_id`, `full_name`, `email`, `created_at`) VALUES
(7, 'complainant tao test', 'sailelaprincessshairamae@gmail.com', '2025-12-14 16:30:56'),
(8, 'respondent respoo, another respo', NULL, '2025-12-14 16:30:56'),
(12, 'complainant tao test', 'sailelaprincessshairamae@gmail.com', '2025-12-14 16:41:55'),
(13, 'new test pls work', 'jeoffnikko@gmail.com', '2025-12-14 16:48:00'),
(14, 'respon test, respo test more', NULL, '2025-12-14 16:48:00'),
(15, 'new test pls work', 'jeoffnikko@gmail.com', '2025-12-14 16:48:51'),
(16, 'complainant tao test', 'sailelaprincessshairamae@gmail.com', '2025-12-14 16:51:38'),
(17, 'Shaira', 'jeoffnikko@gmail.com', '2025-12-14 16:57:08'),
(18, 'Mae', NULL, '2025-12-14 16:57:08'),
(19, 'Shaira', 'jeoffnikko@gmail.com', '2025-12-14 17:01:28'),
(20, 'jeof', 'ricafort.rutherford2023@gmail.com', '2025-12-14 18:51:04'),
(21, 'tao respondent', NULL, '2025-12-14 18:51:04'),
(22, 'jeof', 'ricafort.rutherford2023@gmail.com', '2025-12-14 19:29:28'),
(23, 'complainant tao test', 'sailelaprincessshairamae@gmail.com', '2025-12-14 19:33:44'),
(24, 'jeof', 'ricafort.rutherford2023@gmail.com', '2025-12-14 19:34:43'),
(25, 'jeof', 'ricafort.rutherford2023@gmail.com', '2025-12-14 19:35:34'),
(26, 'land test, another complainant', 'ricafort.rutherford2023@gmail.com', '2025-12-14 19:38:43'),
(27, 'respondent land, land respondent', NULL, '2025-12-14 19:38:43'),
(28, 'complainant tao test', 'sailelaprincessshairamae@gmail.com', '2025-12-14 19:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `firstname`, `lastname`, `password`, `role`, `is_active`) VALUES
(1, 'username', 'Jeoff Nikko', 'Ricafort', 'password', 'Staff', 1),
(2, 'john@email.com', 'John', 'Smith', '$2y$10$8nnYHRdd6GOIVoAcRZYrse2ZY7kRSYxSrVakMKcFbJnZS5cSSPRLu', 'Staff', 1),
(3, 'jayman', 'Jaydee', 'Ballaho', '$2y$10$Xrori8OEpwWPmdfvfZC04e/.bM19ZDadNxN9ha/t5D9wUEujOl0Ee', 'Admin', 1),
(4, 'jop', 'Jeoff', 'Ricafort', '$2y$10$yD5qfE.fBzs33wEKIu/TUeA4CrY0gxl0SkABYYM1EkCisUOCeRMS6', 'Admin', 1),
(5, 'jeoffadmin', 'Jeop Nikko', 'Ricafort', '$2y$10$j5FlIyZqoh/hDobbNIeWfumuHu5mPlPlJ2H5YYn.jVl49nIfw182q', 'Admin', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blotter_case`
--
ALTER TABLE `blotter_case`
  ADD PRIMARY KEY (`blotter_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_incident_date` (`incident_date`);

--
-- Indexes for table `blotter_person`
--
ALTER TABLE `blotter_person`
  ADD PRIMARY KEY (`blotter_person_id`),
  ADD KEY `idx_blotter_id` (`blotter_id`),
  ADD KEY `idx_person_id` (`person_id`),
  ADD KEY `idx_role_type` (`role_type`);

--
-- Indexes for table `case_status`
--
ALTER TABLE `case_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `case_status_history`
--
ALTER TABLE `case_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_blotter_id` (`blotter_id`),
  ADD KEY `idx_status_id` (`status_id`),
  ADD KEY `idx_changed_by` (`changed_by`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_type_id` (`type_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_blotter_id` (`blotter_id`);

--
-- Indexes for table `notification_type`
--
ALTER TABLE `notification_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`person_id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blotter_case`
--
ALTER TABLE `blotter_case`
  MODIFY `blotter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `blotter_person`
--
ALTER TABLE `blotter_person`
  MODIFY `blotter_person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `case_status`
--
ALTER TABLE `case_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `case_status_history`
--
ALTER TABLE `case_status_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notification_type`
--
ALTER TABLE `notification_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `person`
--
ALTER TABLE `person`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blotter_case`
--
ALTER TABLE `blotter_case`
  ADD CONSTRAINT `fk_case_admin` FOREIGN KEY (`admin_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_case_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON UPDATE CASCADE;

--
-- Constraints for table `blotter_person`
--
ALTER TABLE `blotter_person`
  ADD CONSTRAINT `fk_bp_blotter` FOREIGN KEY (`blotter_id`) REFERENCES `blotter_case` (`blotter_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bp_person` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON UPDATE CASCADE;

--
-- Constraints for table `case_status_history`
--
ALTER TABLE `case_status_history`
  ADD CONSTRAINT `fk_csh_blotter` FOREIGN KEY (`blotter_id`) REFERENCES `blotter_case` (`blotter_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_csh_status` FOREIGN KEY (`status_id`) REFERENCES `case_status` (`status_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_csh_user` FOREIGN KEY (`changed_by`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_blotter` FOREIGN KEY (`blotter_id`) REFERENCES `blotter_case` (`blotter_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notif_type` FOREIGN KEY (`type_id`) REFERENCES `notification_type` (`type_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
