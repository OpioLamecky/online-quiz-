-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 15, 2026 at 10:41 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kokcs_oqs`
--

-- --------------------------------------------------------

--
-- Table structure for table `attempt_answers`
--

DROP TABLE IF EXISTS `attempt_answers`;
CREATE TABLE IF NOT EXISTS `attempt_answers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `student_answer` text,
  `is_correct` tinyint(1) DEFAULT NULL,
  `marks_awarded` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attempt_answers`
--

INSERT INTO `attempt_answers` (`id`, `attempt_id`, `question_id`, `student_answer`, `is_correct`, `marks_awarded`) VALUES
(1, 1, 1, '4', 1, 5),
(2, 1, 2, 'False', 1, 5),
(3, 1, 3, 'multiple of two x', 0, 0),
(4, 2, 1, '4', 1, 5),
(5, 2, 2, 'True', 0, 0),
(6, 2, 3, '2X+c', 0, 0),
(7, 3, 1, '4', 1, 20),
(8, 3, 2, 'False', 1, 16),
(9, 3, 3, '2x', 1, 40),
(10, 3, 4, NULL, 0, 0),
(11, 3, 5, NULL, 0, 0),
(12, 5, 6, 'ago, apple, fish, mango', 1, 20),
(13, 5, 7, 'True', 1, 10),
(14, 5, 8, 'False', 1, 10),
(15, 6, 6, 'apple, fish, mango, ago', 0, 0),
(16, 6, 8, 'False', 1, 5),
(17, 7, 6, '', 0, 0),
(18, 7, 8, 'True', 0, 0),
(19, 10, 13, 'False', 0, 0),
(20, 10, 14, 'False', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_id` int NOT NULL,
  `subject` varchar(100) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL,
  `question_text` text NOT NULL,
  `type` enum('multiple_choice','true_false','short_answer') NOT NULL,
  `options_json` json DEFAULT NULL,
  `correct_answer` text NOT NULL,
  `explanation` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `teacher_id`, `subject`, `topic`, `difficulty`, `question_text`, `type`, `options_json`, `correct_answer`, `explanation`, `created_at`) VALUES
(1, 3, 'Mathematics', 'Algebra', 'easy', 'What is 2 + 2?', 'multiple_choice', '[\"1\", \"2\", \"3\", \"4\"]', '4', 'Basic addition.', '2026-04-26 15:48:15'),
(2, 3, 'Mathematics', 'Geometry', 'medium', 'A triangle has 4 sides.', 'true_false', NULL, 'False', 'A triangle always has 3 sides.', '2026-04-26 15:48:15'),
(3, 3, 'Mathematics', 'Calculus', 'hard', 'What is the derivative of x^2?', 'short_answer', NULL, '2x', 'Power rule of differentiation.', '2026-04-26 15:48:15'),
(4, 3, 'Mathematics', 'Trig', 'hard', 'sinx+cosx=0', 'multiple_choice', '[null, null, null, null]', 'sinx', '', '2026-04-26 16:15:40'),
(5, 3, 'Mathematics', 'Calculus', 'hard', 'Differentiate 2x from first principles', 'multiple_choice', '[null, null, null, null]', '2', '', '2026-04-26 17:01:10'),
(6, 7, 'english', 'Rearranging', 'easy', 'apple, fish, ago, mango', 'short_answer', NULL, 'ago, apple, fish, mango', 'Follows the alphabetical order', '2026-04-27 09:37:54'),
(7, 7, 'english', 'True/False', 'easy', 'Fish is the plural of fish\r\n', 'true_false', NULL, 'True', '', '2026-04-27 09:39:16'),
(8, 7, 'english', 'True/False', 'easy', 'Either goes with nor', 'true_false', NULL, 'False', 'Because either goes with or', '2026-04-27 09:40:25'),
(9, 7, 'english', 'Coprehension', 'medium', 'Which of the following are contents of comprehension writing\r\nTitle\r\nConclusion\r\nBody\r\nParagraphs', 'multiple_choice', '[null, null, null, null]', 'Title', '', '2026-04-27 09:43:11'),
(10, 10, 'History', 'Napolion', 'easy', 'When was Napolion born?', 'short_answer', NULL, '1769', '', '2026-04-30 12:45:10'),
(11, 13, 'Fine Art', 'Art', 'medium', 'Blue is not red', 'true_false', NULL, 'True', '', '2026-05-06 12:24:57'),
(12, 13, 'Fine Art', 'Art', 'hard', 'White is Black', 'true_false', NULL, 'True', '', '2026-05-06 12:27:04'),
(13, 16, 'Biology', 'Animal  kingdom', 'easy', 'A monkey is a primate', 'true_false', NULL, 'True', '', '2026-06-03 17:05:57'),
(14, 16, 'Biology', 'Animal  kingdom', 'easy', 'A snake is a bird', 'true_false', NULL, 'False', '', '2026-06-03 17:06:27');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `class_level` varchar(50) NOT NULL,
  `duration_minutes` int NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('draft','pending','approved','live','closed') DEFAULT 'draft',
  `randomize` tinyint(1) DEFAULT '0',
  `max_attempts` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `passmark` int DEFAULT '50',
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `teacher_id`, `title`, `subject`, `class_level`, `duration_minutes`, `start_time`, `end_time`, `status`, `randomize`, `max_attempts`, `created_at`, `passmark`) VALUES
(1, 3, 'Math Midterm Exam', 'Mathematics', 'S4', 60, '2026-04-25 18:48:15', '2026-04-27 18:48:15', 'live', 1, 1, '2026-04-26 15:48:15', 50),
(2, 3, 'Math Quiz 1', 'Mathematics', 'S4', 30, '2026-04-19 18:48:15', '2026-04-20 18:48:15', 'closed', 0, 1, '2026-04-26 15:48:15', 50),
(3, 3, 'math quiz 2', 'Mathematics', 'S4', 15, '2026-04-26 19:13:00', '2026-04-26 19:29:00', 'live', 0, 1, '2026-04-26 16:13:51', 50),
(4, 3, 'q 3', 'Mathematics', 'S4', 60, '2026-04-27 19:58:00', '2026-04-26 20:58:00', 'live', 0, 1, '2026-04-26 16:58:39', 50),
(5, 7, 'English quiz 1', 'english', 'S3', 60, '2026-04-28 12:35:00', '2026-04-28 13:35:00', 'live', 0, 1, '2026-04-27 09:35:49', 50),
(6, 7, 'Quiz 2', 'english', 'S3', 1, '2026-04-27 12:55:00', '2026-04-27 12:56:00', 'live', 0, 1, '2026-04-27 09:54:19', 50),
(7, 7, 'quiz 3', 'english', 'S3', 1, '2026-04-27 13:38:00', '2026-04-27 13:39:00', 'live', 0, 1, '2026-04-27 10:35:22', 50),
(8, 7, 'quiz 4', 'english', 'S4', 2, '2026-04-27 14:15:00', '2026-04-27 14:17:00', 'live', 0, 1, '2026-04-27 11:13:27', 50),
(9, 10, 'Competence in History', 'History', 'S6', 11, '2026-04-30 15:45:00', '2026-04-30 15:56:00', 'pending', 0, 1, '2026-04-30 12:38:25', 71),
(10, 10, 'History', 'History', 'S6', 60, '2026-04-30 16:00:00', '2026-04-30 17:00:00', 'pending', 0, 1, '2026-04-30 12:47:30', 50),
(11, 10, 'quiz 1', 'History', 'S6', 59, '2026-04-30 15:55:00', '2026-04-30 16:54:00', 'pending', 0, 1, '2026-04-30 12:55:39', 50),
(12, 13, 'Fine art quiz 1', 'Fine Art', 'S3', 5, '2026-05-06 15:32:00', '2026-05-06 15:37:00', 'live', 0, 1, '2026-05-06 12:28:18', 50),
(13, 13, 'Fine art quiz 2', 'Fine Art', 'S3', 3, '2026-05-06 16:09:00', '2026-05-06 16:12:00', 'live', 0, 1, '2026-05-06 13:07:52', 50),
(14, 13, 'Fine art quiz 3', 'Fine Art', 'S3', 2, '2026-05-06 16:15:00', '2026-05-06 16:17:00', 'live', 0, 1, '2026-05-06 13:14:19', 50),
(15, 16, 'Biology q1', 'Biology', 'S2', 5, '2026-06-03 20:03:00', '2026-06-03 20:08:00', 'live', 0, 1, '2026-06-03 17:04:23', 50),
(16, 16, 'Bio q2', 'Biology', 'S2', 5, '2026-06-03 20:11:00', '2026-06-03 20:16:00', 'live', 0, 1, '2026-06-03 17:11:16', 50),
(17, 16, 'Bio q3', 'Biology', 'S2', 6, '2026-06-03 20:20:00', '2026-06-03 20:26:00', 'live', 0, 1, '2026-06-03 17:20:18', 50),
(18, 16, 'qq1', 'Biology', 'S2', 4, '2026-06-05 09:49:00', '2026-06-05 09:53:00', 'live', 0, 1, '2026-06-05 06:49:26', 50);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

DROP TABLE IF EXISTS `quiz_attempts`;
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `student_id` int NOT NULL,
  `started_at` datetime NOT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `score` int DEFAULT '0',
  `total_marks` int DEFAULT '0',
  `status` enum('in_progress','submitted','graded') DEFAULT 'in_progress',
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `quiz_id`, `student_id`, `started_at`, `submitted_at`, `score`, `total_marks`, `status`) VALUES
(1, 1, 4, '2026-04-26 16:04:12', '2026-04-26 19:04:12', 10, 20, 'graded'),
(2, 1, 5, '2026-04-26 16:25:36', '2026-04-26 19:25:36', 5, 20, 'graded'),
(3, 4, 4, '2026-04-26 17:05:30', '2026-04-26 20:05:30', 76, 126, 'graded'),
(4, 3, 4, '2026-04-27 09:47:21', '2026-04-27 12:47:21', 0, 0, 'graded'),
(5, 5, 8, '2026-04-27 09:49:39', '2026-04-27 12:49:39', 40, 40, 'graded'),
(6, 6, 8, '2026-04-27 09:57:06', '2026-04-27 12:57:06', 5, 10, 'graded'),
(7, 8, 4, '2026-04-27 14:16:13', '2026-04-27 14:16:13', 0, 100, 'graded'),
(8, 13, 8, '2026-05-06 16:09:16', '2026-05-06 16:12:00', 0, 0, 'graded'),
(9, 14, 8, '2026-05-06 16:15:31', '2026-05-06 16:17:00', 0, 0, 'graded'),
(10, 16, 15, '2026-06-03 20:12:44', '2026-06-03 20:13:31', 5, 10, 'graded'),
(11, 17, 15, '2026-06-03 20:23:28', '2026-06-03 20:26:00', 0, 0, 'graded'),
(12, 18, 19, '2026-06-05 09:52:59', NULL, 0, 0, 'in_progress');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `quiz_id` int NOT NULL,
  `question_id` int NOT NULL,
  `order_index` int NOT NULL,
  `marks` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`quiz_id`,`question_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`quiz_id`, `question_id`, `order_index`, `marks`) VALUES
(1, 1, 1, 5),
(1, 2, 2, 5),
(1, 3, 3, 10),
(2, 1, 1, 5),
(4, 4, 1, 5),
(4, 1, 2, 20),
(4, 2, 3, 16),
(4, 3, 4, 40),
(4, 5, 5, 45),
(5, 6, 1, 20),
(5, 7, 2, 10),
(5, 8, 3, 10),
(6, 6, 1, 5),
(6, 8, 2, 5),
(7, 6, 1, 5),
(7, 8, 2, 5),
(8, 6, 1, 50),
(8, 8, 2, 50),
(10, 10, 1, 5),
(11, 10, 1, 5),
(12, 11, 1, 5),
(12, 12, 2, 5),
(13, 11, 1, 5),
(13, 12, 2, 5),
(14, 11, 1, 5),
(14, 12, 2, 5),
(15, 13, 1, 5),
(15, 14, 2, 5),
(16, 13, 1, 5),
(16, 14, 2, 5),
(17, 14, 1, 5),
(17, 13, 2, 5),
(18, 13, 1, 5),
(18, 14, 2, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','supervisor','teacher','student') NOT NULL,
  `class_level` varchar(50) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `class_level`, `subject`, `created_at`) VALUES
(1, 'OSCAR ARIHIHIKWIZA', 'admin@kokcs.edu.ug', '$2y$10$Uc/lmwsDf.g.tJBjTd3G1OqkgXYcxmeI1hexnWWEHn11/Do8vZSRK', 'admin', NULL, NULL, '2026-04-26 15:48:15'),
(3, 'Teacher User', 'teacher@kokcs.edu.ug', '$2y$10$UBb1MBDBa4JYCO5UQpVZzOb54/Jxng2lMU0TyFziPsvDN3CvFBKi6', 'teacher', NULL, 'Mathematics', '2026-04-26 15:48:15'),
(4, 'Student User', 'student@kokcs.edu.ug', '$2y$10$UBb1MBDBa4JYCO5UQpVZzOb54/Jxng2lMU0TyFziPsvDN3CvFBKi6', 'student', 'S4', NULL, '2026-04-26 15:48:15'),
(5, 'Lameck Opio', 'opio12@gmail.com', '$2y$10$IaolBwOot9KUQUovJ43Mz.Cdcoi03z0L5CRyAlqudsIy4CJ6/dhtG', 'student', 'S4', '', '2026-04-26 16:23:07'),
(7, 'MR DERICK', 'derick@gmail.com', '$2y$10$zsdiRV/yIo5h8gFgoJTbausGGSFW5M0r/8iwnasc3/G/tloi2l06G', 'teacher', 'S3, S4', 'English, Literature', '2026-04-27 09:33:44'),
(6, 'MUJUNI GODWIN', 'goddy@gmail.com', '$2y$10$blk5VFhglfJl5d7g9bfiFuapAA/9MGpKNIiOXXGViaGUM97Dc6Wxi', 'supervisor', 'S3, S4', 'English, Literature', '2026-04-27 09:29:49'),
(8, 'Ari Ossy', 'ariossy275@gmail.com', '$2y$10$FkE0FaxGpky9.oSenkkcdO5GQ6pt8oN.ZhNmPNKrz3EOMz233MLRq', 'student', 'S3', NULL, '2026-04-27 09:48:43'),
(9, 'Harriyo Estate', 'harriyo@gmail.com', '$2y$10$AdYPoM/veaX5juG5.k6cG.hi9oDNicFKC9LffnkOqd7ZU3ff3rYSC', 'student', 'S.6', NULL, '2026-04-30 12:28:04'),
(10, 'Simon', 'simon@gmail.com', '$2y$10$YeXMO1H3z0BQxZIOtombdeyaQlmOn/7WC/pUyYjIrnqa538CovlnW', 'teacher', 'S6', 'History, Religious Education', '2026-04-30 12:35:00'),
(11, 'Okoo', 'okoo@gmail.com', '$2y$10$kZwXr4dsNG7CUwvDw1FwYu11k/1dEJlOVgkJKrwzNWJxRmIfc7MeS', 'supervisor', 'S6', 'History, Religious Education', '2026-04-30 12:40:40'),
(12, 'Opio', 'opio@gmail.com', '$2y$10$OQG4MeS3Fd6D2QEKXxEOeeoDgw2j68JsFp5hgf.KaUr4udHMkHSZa', 'student', 'S.6', NULL, '2026-04-30 12:49:21'),
(13, 'Fame', 'fame@gmail.com', '$2y$10$DYQlMm2NKhQPHktxo4hyjuujGZvvWE25LHSSESoeiHaEWGWkVSjKm', 'teacher', 'S2, S3', 'Fine Art', '2026-05-06 12:19:59'),
(14, 'Came', 'came@gmail.com', '$2y$10$23Pi7q8VcAYbjsGP/Ud6ZOFWhyzwW3/W6712oRKx92sxbEJ2CaIyy', 'supervisor', 'S2, S3', 'Fine Art', '2026-05-06 12:21:21'),
(15, 'Aaron', 'aaron@gmail.com', '$2y$10$1Uat8kdFWzyde8Evb0uxxuI7fBEmso8oA5wbHs2fU7v1Q.qv6ks8m', 'student', 'S2', NULL, '2026-06-03 16:56:36'),
(16, 'AKANKUNDA PATIENCE', 'pati@gmail.com', '$2y$10$OCGicbzIdEEwFdrvNSjAyOCE0DjeFQkoPMMNIEbqqvGsupbze5Suy', 'teacher', 'S2', 'Chemistry, Biology', '2026-06-03 17:00:06'),
(17, 'NSIIMIRE HARRIET', 'harri@gmail.com', '$2y$10$JwQv22D5MfIsdR5ptghIM.7onCgdN.CSCgVqdDYDbXGuAnjQLblNm', 'supervisor', 'S2', 'Chemistry, Biology', '2026-06-03 17:01:42'),
(18, 'MUJUNI GODWIN', 'muji@gmail.com', '$2y$10$xpRDRlOSl0XCO29vjUXTWe1va8DTJFonHjaG1JghKQL2OGB1UznaS', 'teacher', 'S5', 'Mathematics', '2026-06-05 06:47:56'),
(19, 'opioo', 'opioo@gmail.com', '$2y$10$AxfNOhgqhg1jqIF6m/qyIub7VbcD4EIuNqxiyUjzmTEDOv2bkHQUq', 'student', 'S2', 'Chemistry, Biology', '2026-06-05 06:52:18');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
