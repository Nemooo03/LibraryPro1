-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 05:46 AM
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
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(150) NOT NULL,
  `publication` varchar(150) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `available_quantity` int(11) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `year_published` year(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `publication`, `category_id`, `isbn`, `description`, `quantity`, `available_quantity`, `image`, `year_published`, `created_at`, `updated_at`) VALUES
(1, 'Noli Me Tangere', 'Jose Rizal', 'Philippine Publishing House', 5, '978-971-123-001', 'A classic Philippine novel exposing colonial injustice and social ills.', 5, 5, 'book_1773626132_69b76314e37be.png', '1981', '2026-03-16 01:47:54', '2026-03-16 02:47:23'),
(2, 'El Filibusterismo', 'Jose Rizal', 'Philippine Publishing House', 1, '978-971-123-002', 'The sequel to Noli Me Tangere, depicting the Filipino struggle for freedom.', 4, 4, 'book_1773626165_69b7633530693.jpg', '2005', '2026-03-16 01:47:54', '2026-03-16 01:56:05'),
(3, 'Florante at Laura', 'Francisco Balagtas', 'Adarna House', 1, '978-971-123-003', 'An epic poem in Filipino literature set in medieval times.', 3, 2, 'book_1773626180_69b76344316cd.jpg', '2002', '2026-03-16 01:47:54', '2026-03-16 03:02:57'),
(4, 'Computer Programming 1', 'Andrew Tanenbaum', 'Prentice Hall', 3, '978-0-13-123456-7', 'Comprehensive introduction to programming concepts and algorithms.', 8, 6, 'book_1773626244_69b76384d2220.jpg', '2019', '2026-03-16 01:47:54', '2026-03-16 01:57:24'),
(5, 'Calculus: Early Transcendentals', 'James Stewart', 'Cengage Learning', 3, '978-1-285-74062-1', 'The standard calculus textbook used in engineering and science courses.', 5, 0, 'book_1773626258_69b7639268363.jpg', '2020', '2026-03-16 01:47:54', '2026-03-16 03:16:41'),
(6, 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 'Harper', 5, '978-0-06-231609-7', 'A sweeping history of the human species from the Stone Age to modern times.', 6, 6, 'book_1773626270_69b7639ec8bb1.jpg', '2015', '2026-03-16 01:47:54', '2026-03-16 01:57:50'),
(7, 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar Straus Giroux', 6, '978-0-374-27563-1', 'Explores the two systems that drive the way we think.', 7, 7, 'book_1773626283_69b763ab7e353.png', '2011', '2026-03-16 01:47:54', '2026-03-16 03:30:05'),
(8, 'The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Free Press', 7, '978-0-7432-6951-3', 'Timeless principles for personal and professional effectiveness.', 5, 5, 'book_1773626296_69b763b8d319c.jpg', '1989', '2026-03-16 01:47:54', '2026-03-16 01:58:16'),
(9, 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 4, '978-0-553-38016-3', 'An exploration of the universe for the general reader.', 4, 0, 'book_1773626307_69b763c3b699f.jpg', '1988', '2026-03-16 01:47:54', '2026-03-16 03:16:24'),
(10, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'Scholastic', 1, '978-0-439-70818-8', 'The first book in the beloved Harry Potter series.', 5, 5, 'book_1773626322_69b763d21ec3b.jpg', '1997', '2026-03-16 01:47:54', '2026-03-16 02:01:26'),
(11, 'The Contemporary World', 'Claudio & Abinales', 'different houses', 3, '978-971-981-8328', 'the present-day era characterized by rapid technological advancement, intense globalization, and increasing interdependence among nations.', 2, 2, 'book_1773626465_69b76461d2dae.jpg', '2018', '2026-03-16 02:01:05', '2026-03-16 02:01:05'),
(13, 'A Little History of Philosophy', 'Nigel Warburton', 'philosopher, author, and podcaster', 3, '030-001-877-93', 'an accessible, 40-chapter introduction to Western philosophy, surveying major thinkers from Socrates to modern philosophers', 1, 1, 'book_1773626963_69b7665313a35.jpg', '2003', '2026-03-16 02:09:23', '2026-03-16 03:12:04'),
(14, 'Introduction To Information Technology Fundamentals', 'Jerelyn S. Besueña and Jake R. Pomperada', 'Intramuros, Manila', 4, '978-621-427-0583', 'covers the essential concepts of computing, networking, and data management, focusing on how hardware and software are used to process, store, and secure information', 4, 4, 'book_1773627439_69b7682f5748f.jpg', '2019', '2026-03-16 02:17:19', '2026-03-16 03:12:00'),
(15, 'The Joy of Php', 'Alan Forbes', 'independently published.', 4, '101-494-267-357', 'a beginner-friendly guide designed to teach PHP and MySQL through an engaging, hands-on approach rather than dry theory', 2, 2, 'book_1773627653_69b76905ad1c1.jpg', '2015', '2026-03-16 02:20:53', '2026-03-16 02:20:53'),
(16, 'Mathematics In The Modern World', 'Romeo M. Daligdig', 'tertiary textbook in the Philippines,', 3, '978-621-427-151-1', 'a general education course exploring math beyond formulas, focusing on its practical, intellectual, and aesthetic roles', 3, 3, 'book_1773627889_69b769f18ab7e.jpg', '2018', '2026-03-16 02:24:49', '2026-03-16 02:24:49'),
(19, 'Frankenstein', 'Mary Shelley', 'Lackington, Hughes, Harding, Mavor, & Jones', 8, '080-075-928-211', 'It follows Victor Frankenstein, a Swiss scientist who creates a sentient, grotesque creature in a botched experiment', 2, 2, 'book_1773628940_69b76e0c6b6ae.jpg', '1951', '2026-03-16 02:42:20', '2026-03-16 02:42:42'),
(20, 'The Haunting of Hill House', 'shirley jackson', 'Penguin Classics', 8, '978-0143129370', 'explores themes of isolation, psychological trauma, and the supernatural', 3, 3, 'book_1773629105_69b76eb15a8a5.jpg', '1959', '2026-03-16 02:45:05', '2026-03-16 03:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `book_requests`
--

CREATE TABLE `book_requests` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_requests`
--

INSERT INTO `book_requests` (`id`, `book_id`, `user_id`, `request_date`, `status`, `admin_note`, `processed_by`, `processed_at`) VALUES
(6, 4, 7, '2026-03-16 03:01:31', 'rejected', '', 6, '2026-03-16 03:04:30'),
(7, 3, 7, '2026-03-16 03:01:36', 'approved', '', 6, '2026-03-16 03:02:57'),
(8, 13, 7, '2026-03-16 03:08:56', 'approved', 'Quality', 6, '2026-03-16 03:09:37');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_books`
--

CREATE TABLE `borrowed_books` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `penalty` decimal(10,2) DEFAULT 0.00,
  `penalty_per_day` decimal(10,2) DEFAULT 5.00,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `notes` text DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowed_books`
--

INSERT INTO `borrowed_books` (`id`, `book_id`, `user_id`, `request_id`, `borrow_date`, `due_date`, `return_date`, `penalty`, `penalty_per_day`, `status`, `notes`, `issued_by`, `created_at`, `updated_at`) VALUES
(5, 3, 7, 7, '2026-03-16', '2026-03-30', NULL, 85.00, 5.00, 'overdue', NULL, 6, '2026-03-16 03:02:57', '2026-04-16 03:03:22'),
(6, 13, 7, 8, '2026-03-16', '2026-03-30', '2026-03-16', 0.00, 5.00, 'returned', NULL, 6, '2026-03-16 03:09:37', '2026-03-16 03:10:12');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Fiction', 'Novels, short stories, and imaginative literature', '2026-03-16 01:47:54'),
(3, 'Academic', 'Textbooks and scholarly publications', '2026-03-16 01:47:54'),
(4, 'Science & Technology', 'Books on science, engineering, and technology', '2026-03-16 01:47:54'),
(5, 'History', 'Historical accounts and biographies', '2026-03-16 01:47:54'),
(6, 'Philosophy', 'Works on ethics, logic, and metaphysics', '2026-03-16 01:47:54'),
(7, 'Self-Help', 'Personal development and motivational books', '2026-03-16 01:47:54'),
(8, 'Horror', 'is a film genre that seeks to elicit physical or psychological fear in its viewers.', '2026-03-16 01:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(5, 6, 'New Borrow Request', 'James Serilla has requested to borrow a book. Please review.', 'info', 1, '2026-03-16 03:01:31'),
(6, 6, 'New Borrow Request', 'James Serilla has requested to borrow a book. Please review.', 'info', 1, '2026-03-16 03:01:36'),
(7, 7, 'Borrow Request Approved', 'Your request for \"Florante at Laura\" has been approved. Due date: 2026-03-30.', 'success', 1, '2026-03-16 03:02:57'),
(8, 7, 'Borrow Request Rejected', 'Your request for \"Computer Programming 1\" was rejected. ', 'danger', 1, '2026-03-16 03:04:30'),
(9, 6, 'New Borrow Request', 'James Serilla has requested to borrow a book. Please review.', 'info', 1, '2026-03-16 03:08:56'),
(10, 7, 'Borrow Request Approved', 'Your request for \"A Little History of Philosophy\" has been approved. Due date: 2026-03-30.', 'success', 1, '2026-03-16 03:09:37'),
(11, 7, 'Book Returned', 'You have returned \"A Little History of Philosophy\".', 'success', 1, '2026-03-16 03:10:12');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `label` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `label`, `updated_at`) VALUES
('loan_period_days', '14', 'Default Loan Period (days)', '2026-03-16 01:52:01'),
('max_borrows', '3', 'Max Books per Borrower at Once', '2026-03-16 01:52:01'),
('penalty_grace', '0', 'Grace Period After Due Date (days, 0 = none)', '2026-03-16 01:52:01'),
('penalty_per_day', '5', 'Penalty per Overdue Day (₱)', '2026-03-16 01:52:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','borrower') NOT NULL DEFAULT 'borrower',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `status`, `avatar`, `created_at`, `updated_at`) VALUES
(6, 'Admin', 'Admin@gmail.com', '$2y$12$Z1jynblFcB8s8fKG55JSleFbiQasB5dW6NWJW7jAXV8JBc8TJc8J6', 'admin', '0947396864', NULL, 'active', NULL, '2026-03-16 01:50:10', '2026-03-16 01:50:33'),
(7, 'James Serilla', 'James@gmail.com', '$2y$12$rLufOZV6iAxJ3d48veIltuSAa44ZXMGwBjjf2cc5OxKBs8on5Vkna', 'borrower', '0947382109', NULL, 'active', NULL, '2026-03-16 02:48:08', '2026-03-16 04:05:38'),
(8, 'Alagdon, Ian Dhyniel Caubat', 'Alagdon@gmail.com', '$2y$12$0UsAKJwnOs0f2zt2q.3C/.2nU//t6CXKj.YQs1ER3R8W0GJJZzejS', 'borrower', '09240106364', '', 'active', NULL, '2026-03-16 03:41:39', '2026-03-16 04:23:26'),
(9, 'Amado, Rubina Deog', 'Amado@gmail.com', '$2y$12$kdK2RnRli3021nweo9IhWueG3aIXTazEVLyR/DfrGx5mVklJcvVyy', 'borrower', '09240102470', '', 'active', NULL, '2026-03-16 03:42:09', '2026-03-16 04:23:17'),
(10, 'Ambawas, Angela', 'Ambawas@gmail.com', '$2y$12$5MUMSHC18vGVfv2vk2Hb5uecfDEFH8OvBhJAPVLYMoV3K2QQ1iWL.', 'borrower', '09230114339', NULL, 'active', NULL, '2026-03-16 03:42:59', '2026-03-16 03:42:59'),
(11, 'Antonio, Wendhil Lacsa', 'Antonio@gmail.com', '$2y$12$Hh.WzqIJMyNc6QJUw2eHiOnCNGSVx84hMgeBk0LyvtGhpV4TmZcm6', 'borrower', '09240104768', NULL, 'active', NULL, '2026-03-16 03:43:40', '2026-03-16 03:43:40'),
(12, 'Aradaza, Yhesha Mae', 'Aradaza@gmail.com', '$2y$12$k4SiyuMAQaxlFvzmZABh5eY4.23v0CnjfH5L1CGJZzQde4aH4HZcW', 'borrower', '09240104994', NULL, 'active', NULL, '2026-03-16 03:44:33', '2026-03-16 03:44:33'),
(13, 'Aragonis, Mark Lester', 'Aragonis@gmail.com', '$2y$12$0h/QOwAuQ9Q6W0fhVohnt.E2yyiL2CggBiyUU3IQItrM71hO7Vfya', 'borrower', '09240100723', NULL, 'active', NULL, '2026-03-16 03:45:08', '2026-03-16 03:45:08'),
(14, 'Bao, Micha Tanilon', 'Bao@gmail.com', '$2y$12$/Stjx7ZNpxWZU07bpdo1qePCSfBp70gKJ1YIb9iKW7lFmXqSUreVu', 'borrower', '09240105028', NULL, 'active', NULL, '2026-03-16 03:48:48', '2026-03-16 03:48:48'),
(15, 'Baroro, Juvy Montilla', 'Baroro@gmail.com', '$2y$12$3g2joFnLXYq6oDbw1xhf2e9vjWO4.tIjuDpQscSUcCCe6GMVw.GeO', 'borrower', '09240100707', NULL, 'active', NULL, '2026-03-16 03:49:20', '2026-03-16 03:49:20'),
(16, 'Batarao, Laurence Monzon', 'Batarao@gmail.com', '$2y$12$2Aj3wTiHxexuJfmoQC3QRO/bvShLiEY7s13F2Fu8docO6jBk7W66e', 'borrower', '09240105778', NULL, 'active', NULL, '2026-03-16 03:50:00', '2026-03-16 03:50:00'),
(17, 'Boton, Aron Dave Cante', 'Boton@gmail.com', '$2y$12$dHkTd763.pUPdM30ENFeX.MncowP8wEu9xB6zjqKUSW1FajOfo2MW', 'borrower', '09240112834', NULL, 'active', NULL, '2026-03-16 03:50:35', '2026-03-16 03:50:35'),
(18, 'Buenaventura, Carlo Chinchilla', 'Buenaventura@gmail.com', '$2y$12$pP37oTkFZINY.eyAx3aJcuT5BbYpdlcl6lvqhri9BRtIfg2tS3lXK', 'borrower', '09240104126', NULL, 'active', NULL, '2026-03-16 03:51:08', '2026-03-16 03:51:08'),
(20, 'Catsisa, Sid Philip Villareal', 'Catsisa@gmail.com', '$2y$12$FusKlsZwef/DkRd6FDlEsuPTo9oOZVfrJyuZMZWqnSU5ZgdUVVzuW', 'borrower', '09240104168', NULL, 'active', NULL, '2026-03-16 03:56:07', '2026-03-16 03:56:07'),
(21, 'Ching, Mark Lorence Magugat', 'Ching@gmail.com', '$2y$12$zyx8Grr6RADZuWy2lRuyL.kAP1ApjDGijk8lHYfomDDv8z2C89hxS', 'borrower', '09240106849', NULL, 'active', NULL, '2026-03-16 03:56:40', '2026-03-16 03:56:40'),
(22, 'Delavictoria, Syrel Ramiso', 'Delavictoria@gmail.com', '$2y$12$qhY0QvMm072hTmPP7tq4X.73l3HKI5LC0yA4eerGwZ8.YAdCP5X9K', 'borrower', '240102944', NULL, 'active', NULL, '2026-03-16 03:57:18', '2026-03-16 03:57:18'),
(24, 'Fellizar, Jhon Eric Sollestre', 'Fellizar@gmail.com', '$2y$12$cuQ4lLGdMADgLwlrjNogduDkgi0rxab0V4sBLVWrvIAeS6Biy5NAa', 'borrower', '09240101482', NULL, 'active', NULL, '2026-03-16 04:00:55', '2026-03-16 04:00:55'),
(25, 'Gabrang, Alexa Angela Diciembre', 'Gabrang@gmail.com', '$2y$12$B9ozToq.y8yONyS7NF13bOZ1ZtlNVYnmS5Z.IuRhKa9erOftsu4pO', 'borrower', '09240104279', NULL, 'active', NULL, '2026-03-16 04:07:08', '2026-03-16 04:07:08'),
(26, 'Gonzales, Joren Acabal', 'Gonzales@gmail.com240103219', '$2y$12$iIMndw6.mtY/dBPuqdR2P.ZA4ySNY8Ob8R1631aeVBJXvzJpDFGl2', 'borrower', '09240103219', NULL, 'active', NULL, '2026-03-16 04:07:40', '2026-03-16 04:07:40'),
(27, 'Guzman, Angeline Oranza', 'Guzman@gmail.com', '$2y$12$KhbajGw/.i3toP5zm3/ixOv7rahs4VPdukji7.bNt5coobfWJUY4y', 'borrower', '09240115034', NULL, 'active', NULL, '2026-03-16 04:08:20', '2026-03-16 04:08:20'),
(28, 'Hinampas, Baby Jane Verginesa', 'Hinampas@gmail.com', '$2y$12$tW8eeTwCB1P2qK1NmcxypeRVuS0j86w7iBJHxJ.V.ZZ1XBq1AwQAO', 'borrower', '09240112782', NULL, 'active', NULL, '2026-03-16 04:09:10', '2026-03-16 04:09:10'),
(29, 'Ibatuan, John Owen Mupada', 'Ibatuan@gmail.com', '$2y$12$Ku5LKWmfPlbDsR7M1r9bdOLMgTFtJNRzWUO2PiBOI3MhChSIrZyay', 'borrower', '09240113405', NULL, 'active', NULL, '2026-03-16 04:09:48', '2026-03-16 04:09:48'),
(30, 'Ida, Jenny Joy Samsona', 'Ida@gmail.com', '$2y$12$WaZMAwiDQEVW7cRujcYrLeTK9aGBPJzif4sCKeKZgOBaHjamPFFt2', 'borrower', '240100330', NULL, 'active', NULL, '2026-03-16 04:10:23', '2026-03-16 04:10:23'),
(31, 'Larano, Jomari Dela Cruz', 'Larano@gmail.com', '$2y$12$p5sAHdxfRnzS3GiPNH0/9.MfajJtug7jR8.cUKOhRPlv2vtxNtVgO', 'borrower', '09240108760', NULL, 'active', NULL, '2026-03-16 04:11:28', '2026-03-16 04:11:28'),
(32, 'Lazona, Angel Joy Feliciados', 'Lazona@gmail.com', '$2y$12$z7D0qoQjbXlAD4EuUAMQq.P9Uh0zDUX48chW4asZ/OHPCrBQaVhFe', 'borrower', '09240115139', NULL, 'active', NULL, '2026-03-16 04:12:04', '2026-03-16 04:12:04'),
(33, 'Mabotot, Charles L', 'Mabotot@gmail.com', '$2y$12$eqEAwMhmyLWvn7tHVKNMGOUPhq5scW2KzKXIkfo1bHNe3ZegrWXgO', 'borrower', '09240113009', NULL, 'active', NULL, '2026-03-16 04:12:42', '2026-03-16 04:12:42'),
(34, 'Malayas, Czarina Joyce Tumacas', 'Malayas@gmail.com', '$2y$12$C9AoYnJThwMcFgtYzIz1Zeff.aSmkSnlHgIvjtJRciu1Jw5JxF.c6', 'borrower', '09240102743', NULL, 'active', NULL, '2026-03-16 04:13:15', '2026-03-16 04:13:15'),
(35, 'Malayo, Jhonrey Christian Mejica', 'Malayo@gmail.com', '$2y$12$NFS3xKw0KWbdzZ.C6lGpnuRxfkvOqHCH55MONC3kpJp23RaoJ3eZK', 'borrower', '09240102321', NULL, 'active', NULL, '2026-03-16 04:14:13', '2026-03-16 04:14:13'),
(36, 'Mindajao, Joshua Mai', 'Mindajao@gmail.com', '$2y$12$bJmHgI/BZbAZyLa46348N.LzGmhGcGQ4atqvYqYA2Stbs3XrNxUzG', 'borrower', '09240108868', NULL, 'active', NULL, '2026-03-16 04:14:47', '2026-03-16 04:14:47'),
(37, 'Montejo, Christopher Sabugao', 'Montejo@gmail.com', '$2y$12$HmQbNh3uAFMoVhsb5XBVRecce/c6gVrbL.lSRjKrjDGF.3.bM10ei', 'borrower', '09240100287', NULL, 'active', NULL, '2026-03-16 04:15:34', '2026-03-16 04:15:34'),
(38, 'Muñoz, John Raye Allen Andalis', 'John@gmail.com', '$2y$12$nzgb.PadtzV3tz65Ro3VLeFuK6IWEZ6XzXGO6qenT86i7ouf5T06.', 'borrower', '09240107051', NULL, 'active', NULL, '2026-03-16 04:19:34', '2026-03-16 04:19:34'),
(39, 'Pagaragan, Michael King', 'Pagaragan@gmail.com', '$2y$12$JfbIOyCkI0yoeOan1MYtvuRQSRtkUpsPLOiDAQlLtkfYAUsuEXn0i', 'borrower', '09240100681', '', 'active', NULL, '2026-03-16 04:19:57', '2026-03-16 04:23:36'),
(40, 'Pelonia, Jolo Alcantara', 'Pelonia@gmail.com', '$2y$12$cqChbn0zPb4ijtre22E25uU92EgFMcvvWIEXPHo3JDK7gR8mTVikC', 'borrower', '09240103904', NULL, 'active', NULL, '2026-03-16 04:20:26', '2026-03-16 04:20:26'),
(41, 'Posadas, Menvy Posadas', 'Posadas@gmail.com', '$2y$12$DVE3EHi4fCNvE6v1n3P0CeQGXMWlK23dzD8QJi0ZpEyKuizC3USaO', 'borrower', '09240103846', NULL, 'active', NULL, '2026-03-16 04:21:03', '2026-03-16 04:21:03'),
(42, 'Quiabang, Marines Angaoan', 'Quiabang@gmail.com', '$2y$12$q9OfM5xM3PC0nLxlXjSCju94qrpJyR8zgm4XDG.oiVHodtqvD/8ma', 'borrower', '09240100244', NULL, 'active', NULL, '2026-03-16 04:21:24', '2026-03-16 04:21:24'),
(43, 'Rabe, Danella Bacang', 'Rabe@gmail.com', '$2y$12$8nSqOg9jxaa0RMss.hDM4OJbsCpbLSBzISaz74YZsJ6eHJADCZ1BO', 'borrower', '09240104917', NULL, 'active', NULL, '2026-03-16 04:21:53', '2026-03-16 04:21:53'),
(44, 'Ramos, Chris Wyne DelapeÑa', 'Ramos@gmail.com', '$2y$12$.o9tSj1966JkbjrX0jdx/uNtuNhtHJSyqHRtpcq9m6D4ypGiz.PjC', 'borrower', '09240103934', NULL, 'active', NULL, '2026-03-16 04:22:30', '2026-03-16 04:22:30'),
(45, 'Sales, Nica Ella Cadornigara', 'Sales@gmail.com', '$2y$12$nsd8i5RLRWkatg4tSE6Ep.EbfBYvKh2MZQl7YvIkZ3jLdGhFonKxq', 'borrower', '09240106037', NULL, 'active', NULL, '2026-03-16 04:24:40', '2026-03-16 04:24:40'),
(46, 'Santelices, Rhio Jay Bañas', 'Santelices@gmail.com', '$2y$12$DFZIvuPTZ0hcdZWfZz2tmeqK4qUUHTPs3ArEM.Tp4d7rNNxp2qsea', 'borrower', '09240104630', NULL, 'active', NULL, '2026-03-16 04:25:14', '2026-03-16 04:25:14'),
(47, 'Tan, Andrew Jefferson Limpio', 'Tan@gmail.com', '$2y$12$0B.CMg2ux97H5ROt/fm2jeEfh4RBoaMmvozt969leLgo8FHTx7WxS', 'borrower', '09240113401', NULL, 'active', NULL, '2026-03-16 04:25:42', '2026-03-16 04:25:42'),
(48, 'Tan, Edward Barcena', 'Edward@gmail.com', '$2y$12$P7gLVytqO3G7YBB3yXtN5eanzngEsM6nJMVqWQ0PZ6ZbnQjZKLAk6', 'borrower', '09240112804', NULL, 'active', NULL, '2026-03-16 04:26:36', '2026-03-16 04:26:36'),
(49, 'Tanlogon, Gian Jeremy Logro', 'Tanlogon@gmail.com', '$2y$12$cKq06fhW0.nWyFL7jz1Vru1B6vlk5qnW1PULscfZAg8vs60kOKpZu', 'borrower', '09240101445', NULL, 'active', NULL, '2026-03-16 04:27:15', '2026-03-16 04:27:15'),
(51, 'Villanueva, Ace Cabana', 'Villanueva@gmail.com', '$2y$12$vHOHvhAMZ/i8uvK8C4vqvea.iwZuqI9/NBTHmWQEQZSCy7Wv5goYO', 'borrower', '09240104870', NULL, 'active', NULL, '2026-03-16 04:29:09', '2026-03-16 04:29:09'),
(52, 'Villarmia, Dennis Gonzaga', 'Villarmia@gmail.com', '$2y$12$Irr2p47trpKoG3Q5FN0dK.20waVx92kSZa63/8x7UD02GcNNbz2fW', 'borrower', '09240102928', NULL, 'active', NULL, '2026-03-16 04:29:34', '2026-03-16 04:29:34'),
(53, 'Balucos, Ranidel Malasan', 'Balucos@gmail.com', '$2y$12$977I0E532WjXFQm0DixZnuMqKDuxyDtzKzJuy8o.W8qosSEtqprCW', 'borrower', '09240106825', '', 'active', NULL, '2026-03-16 04:36:27', '2026-03-16 04:36:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `fk_books_category` (`category_id`);

--
-- Indexes for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_req_book` (`book_id`),
  ADD KEY `fk_req_user` (`user_id`),
  ADD KEY `fk_req_admin` (`processed_by`);

--
-- Indexes for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bor_book` (`book_id`),
  ADD KEY `fk_bor_user` (`user_id`),
  ADD KEY `fk_bor_request` (`request_id`),
  ADD KEY `fk_bor_issued` (`issued_by`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notif_user` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `book_requests`
--
ALTER TABLE `book_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `book_requests`
--
ALTER TABLE `book_requests`
  ADD CONSTRAINT `fk_req_admin` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_req_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_req_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD CONSTRAINT `fk_bor_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bor_issued` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bor_request` FOREIGN KEY (`request_id`) REFERENCES `book_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
