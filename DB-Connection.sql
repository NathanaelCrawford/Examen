-- Set up the environment and start the transaction
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Set character sets
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create the database if not exists and use it
CREATE DATABASE IF NOT EXISTS `schooldatabase`;
USE `schooldatabase`;

-- Create table `klassen`
CREATE TABLE `klassen` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `mentor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `klassen`
INSERT INTO `klassen` (`id`, `class_name`, `mentor_id`) VALUES
(1, '1', 3),
(2, '2', NULL);

-- Create table `mentor_gesprekken`
CREATE TABLE `mentor_gesprekken` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `conversation` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `mentor_gesprekken`
INSERT INTO `mentor_gesprekken` (`id`, `student_id`, `mentor_id`, `conversation`, `created_at`) VALUES
(1, 4, 3, 'sdf', '2024-08-30 21:37:34');

-- Create table `permissions`
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `permissions`
INSERT INTO `permissions` (`id`, `permission_name`) VALUES
(12, 'add_class'),
(27, 'add_conversation'),
(3, 'add_docent'),
(7, 'add_roster'),
(16, 'add_student'),
(20, 'add_subject'),
(14, 'delete_class'),
(30, 'delete_conversation'),
(5, 'delete_docent'),
(18, 'delete_student'),
(22, 'delete_subject'),
(13, 'edit_class'),
(29, 'edit_conversation'),
(4, 'edit_docent'),
(11, 'edit_own_info'),
(8, 'edit_roster'),
(17, 'edit_student'),
(21, 'edit_subject'),
(9, 'generate_roster'),
(26, 'link_self_to_class_as_mentor'),
(25, 'link_self_to_subject'),
(1, 'login'),
(2, 'logout'),
(15, 'view_classes'),
(24, 'view_colleagues'),
(28, 'view_conversation'),
(6, 'view_docents'),
(10, 'view_own_info'),
(31, 'view_schedule'),
(19, 'view_students'),
(23, 'view_subjects');

-- Create table `roles`
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `roles`
INSERT INTO `roles` (`id`, `role_name`) VALUES
(3, 'Docent'),
(1, 'Manager'),
(4, 'Mentor'),
(2, 'Roostermaker'),
(5, 'Student');

-- Create table `role_permissions`
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `role_permissions`
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(2, 1),
(2, 2),
(2, 8),
(2, 9),
(2, 10),
(3, 1),
(3, 2),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(3, 21),
(3, 22),
(3, 23),
(3, 24),
(3, 25),
(3, 26),
(4, 27),
(4, 28),
(4, 29),
(4, 30),
(5, 1),
(5, 2),
(5, 31);

-- Create table `roosters`
CREATE TABLE `roosters` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `time_slot` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `roosters`
INSERT INTO `roosters` (`id`, `class_id`, `day`, `time_slot`, `subject_id`, `teacher_id`) VALUES
(25, 1, 1, 8, 2, 2),
(26, 2, 1, 8, 2, 3);

-- Create table `studenten`
CREATE TABLE `studenten` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table `subjects`
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `teacher_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `subjects`
INSERT INTO `subjects` (`id`, `subject_name`, `teacher_id`) VALUES
(1, 'math', '3'),
(2, 'English', '2,3'),
(3, 'Science', NULL),
(4, 'History', NULL),
(5, 'Geography', NULL),
(6, 'math2', NULL);

-- Create table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `users`
INSERT INTO `users` (`id`, `username`, `password`, `email`, `role_id`, `created_at`, `class_id`) VALUES
(1, 'wim', '$2y$10$yo0gLB7.fuVxadnaI9x0o.FWdsErBIE.uET5pUPMW5j6CJ3LLvrry', 'wim@wim.nl', 5, '2024-08-29 11:21:05', NULL),
(2, 'jayden', '$2y$10$oe9rBin0Al0LwQ.2sNSMn.F2JxtjQT3OZdJrVpw8jMUg7NYj4vGTO', 'admin@gmail.com', 3, '2024-08-29 11:59:05', NULL),
(3, 'gavieree', '$2y$10$bdcyvf8DInXk1vzU3aLvk.gaBpkqnaOfX7D9gmOeSDHjrEOo13kIW', 'gaviere@gmail.com', 3, '2024-08-30 18:27:18', NULL),
(4, 'deshuan', '$2y$10$gNHVQ0/mg/oxWirKfrKMaO1Py5g0g/XkpMeFMWxHvmLXLN.EGe4Ya', 'deshaun@gmail.com', 4, '2024-08-30 20:57:50', 1);

-- Set primary keys and indexes for all tables
ALTER TABLE `klassen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_name` (`class_name`);

ALTER TABLE `mentor_gesprekken`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mentoring` (`student_id`,`mentor_id`),
  ADD KEY `mentor_id` (`mentor_id`);

ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

ALTER TABLE `roosters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rooster` (`class_id`,`day`,`time_slot`);

ALTER TABLE `studenten`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `class_id` (`class_id`);

ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

-- Set AUTO_INCREMENT values for tables
ALTER TABLE `klassen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `mentor_gesprekken`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `roosters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

ALTER TABLE `studenten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- Add foreign key constraints
ALTER TABLE `mentor_gesprekken`
  ADD CONSTRAINT `mentor_gesprekken_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mentor_gesprekken_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`);

ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

ALTER TABLE `studenten`
  ADD CONSTRAINT `studenten_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `studenten_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `klassen` (`id`);

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

-- Commit the transaction
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
