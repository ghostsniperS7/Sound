-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 05:49 PM
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
-- Database: `sound`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

CREATE TABLE `artists` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `artists`
--

INSERT INTO `artists` (`id`, `name`, `description`) VALUES
(2, 'Asim Azhar', ''),
(3, 'Murtaza Qizilbash', ''),
(4, 'Navjot Ahuja', ''),
(5, 'Abdul Hannan', ''),
(6, 'Anuv Jain', ''),
(7, 'Kushagra', ''),
(9, 'Farak', ''),
(10, 'Paresh Pahuja', ''),
(11, 'Dilip Joshi', '');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('music','video') NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `item_type`, `item_id`, `created_at`) VALUES
(11, 2, 'music', 1, '2026-10-27 12:18:39'),
(12, 1, 'music', 8, '2026-10-27 12:37:46');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`) VALUES
(1, 'Pop'),
(2, 'Rock'),
(3, 'Jazz'),
(4, 'Classical'),
(5, 'Hip-Hop'),
(6, 'Electronic'),
(7, 'Comedy');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`) VALUES
(1, 'English'),
(2, 'Urdu'),
(3, 'Hindi'),
(4, 'Punjabi'),
(5, 'Pashto'),
(6, 'Sindhi');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `userid` varchar(20) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `music`
--

CREATE TABLE `music` (
  `id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `genre_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `year_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `play_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `music`
--

INSERT INTO `music` (`id`, `artist_id`, `album_id`, `genre_id`, `language_id`, `year_id`, `title`, `file_path`, `cover`, `description`, `duration`, `play_count`, `created_at`) VALUES
(1, 2, NULL, 1, 2, 1, 'Aarzu', 'uploads/music/track_6adfa054218e3.mp3', 'uploads/covers/cover_6adfa0542241a.jpg', 'A viral, soulful indie-pop romantic track that captures feelings of love, longing, and emotional attachment. Features the famous line \"Phir se bolo\" and an emotionally resonant poetic outro.', NULL, 0, '2026-10-26 18:47:48'),
(2, 3, NULL, 1, 4, 1, 'Hum', 'uploads/music/track_6adfa9dd275f1.mp3', 'uploads/covers/cover_6adfa9dd278a6.jpg', 'A soulful and melancholic indie pop ballad about love and longing. It beautifully blends atmospheric lo-fi textures with acoustic guitars and emotional Punjabi poetry', NULL, 0, '2026-10-26 19:28:29'),
(3, 4, NULL, 1, 3, 1, 'Khat', 'uploads/music/track_6adfab39aeb3f.mp3', 'uploads/covers/cover_6adfab39aee16.jpg', 'A beautifully crafted indie pop track layered with themes of love, nostalgia, and emotional connection, reminiscing about the old-school charm of letters (khat).', NULL, 0, '2026-10-26 19:34:17'),
(4, 5, NULL, 1, 2, 2, 'Khasara', 'uploads/music/track_6adfaba65b7e0.mp3', 'uploads/covers/cover_6adfaba65bbda.jpg', 'A soulful and deeply emotional indie-pop track focusing on theme of heartbreak, betrayal, and profound grief. Features a highly melancholic chorus with the standout lines \"Kaise ye hua khasara, na main sambhla dobara\".', NULL, 0, '2026-10-26 19:36:06'),
(5, 6, NULL, 1, 3, 1, 'Arz Kiya Hai', 'uploads/music/track_6adfac370f242.mp3', 'uploads/covers/cover_6adfac370f64e.jpg', 'An intimate, heartfelt acoustic indie-pop masterpiece featured on Coke Studio Bharat Season 3. It masterfully reflects on poetic love and features lyrical nods to legendary Urdu poets like Faiz and Ghalib over a warm harmonium and guitar arrangement.', NULL, 0, '2026-10-26 19:38:31'),
(6, 7, NULL, 1, 1, 1, 'Finding Her', 'uploads/music/track_6adface32ff53.mp3', 'uploads/covers/cover_6adface33025f.jpg', 'A soothing and melancholic Hindi indie-pop track that explores themes of distance, deep memories, and the emotional struggle of longing for someone who is no longer around. Features the famous catchy hook line \"Jaana, tu aata nahin, sapno se jaata nahin\".', NULL, 0, '2026-10-26 19:41:23'),
(7, 9, NULL, 1, 2, 2, 'Taare', 'uploads/music/track_6adfb67ec09f1.mp3', 'uploads/covers/cover_6adfb67ec1409.jpg', 'A deeply soothing and atmospheric indie-pop track characterized by soulful melancholic melodies, warm acoustic elements, and raw vocals. Features the viral and emotionally resonant hook line \"Haatho se khali\".', NULL, 0, '2026-10-26 20:22:22'),
(8, 10, NULL, 1, 4, 3, 'Dooron Dooron', 'uploads/music/track_6adfb70597445.mp3', 'uploads/covers/cover_6adfb70597782.jpg', 'A soulful and heartfelt indie love anthem that beautifully captures the feeling of emotional distance, unrequited love, and looking at someone fondly from afar. The live/unplugged version beautifully integrates traditional poetry by Bulleh Shah in the middle.', NULL, 0, '2026-10-26 20:24:37');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('music','video') NOT NULL,
  `item_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `item_type`, `item_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 2, 'music', 1, 5, '2026-10-27 10:57:48', '2026-10-27 10:57:48'),
(2, 2, 'video', 1, 4, '2026-10-27 10:57:49', '2026-10-27 10:57:49'),
(7, 1, 'video', 1, 3, '2026-10-27 11:03:40', '2026-10-27 11:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `recently_played`
--

CREATE TABLE `recently_played` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('music','video') NOT NULL,
  `item_id` int(11) NOT NULL,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recently_played`
--

INSERT INTO `recently_played` (`id`, `user_id`, `item_type`, `item_id`, `played_at`) VALUES
(1, 2, 'music', 1, '2026-10-26 21:20:46'),
(2, 1, 'video', 1, '2026-10-27 09:58:47'),
(3, 2, 'music', 1, '2026-10-27 10:57:48'),
(4, 2, 'music', 1, '2026-10-27 10:57:49'),
(5, 2, 'video', 1, '2026-10-27 10:57:49'),
(6, 2, 'video', 1, '2026-10-27 10:57:49'),
(7, 2, 'music', 1, '2026-10-27 10:57:49'),
(8, 2, 'music', 1, '2026-10-27 10:57:49'),
(9, 2, 'music', 1, '2026-10-27 10:57:49'),
(10, 2, 'music', 1, '2026-10-27 11:00:01'),
(11, 2, 'music', 1, '2026-10-27 11:00:01'),
(12, 2, 'video', 1, '2026-10-27 11:00:01'),
(13, 2, 'video', 1, '2026-10-27 11:00:01'),
(14, 2, 'music', 1, '2026-10-27 11:00:01'),
(15, 2, 'music', 1, '2026-10-27 11:00:01'),
(16, 2, 'music', 1, '2026-10-27 11:00:01'),
(17, 2, 'music', 1, '2026-10-27 11:00:42'),
(18, 2, 'music', 1, '2026-10-27 11:00:42'),
(19, 2, 'video', 1, '2026-10-27 11:00:42'),
(20, 2, 'video', 1, '2026-10-27 11:00:42'),
(21, 2, 'music', 1, '2026-10-27 11:00:42'),
(22, 2, 'music', 1, '2026-10-27 11:00:42'),
(23, 2, 'music', 1, '2026-10-27 11:00:43'),
(24, 2, 'video', 1, '2026-10-27 11:00:43'),
(25, 2, 'video', 1, '2026-10-27 11:00:43'),
(26, 2, 'video', 1, '2026-10-27 11:00:43'),
(27, 1, 'video', 1, '2026-10-27 11:03:31'),
(28, 1, 'video', 1, '2026-10-27 11:03:40'),
(29, 1, 'video', 1, '2026-10-27 12:35:49');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('music','video') NOT NULL,
  `item_id` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'site_name', 'SOUND'),
(2, 'site_tagline', 'Your Music and Video Streaming Platform'),
(3, 'about_text', 'Welcome to SOUND, your ultimate destination for music and video streaming in both regional and English languages.'),
(4, 'contact_email', 'contact@sound.com'),
(5, 'contact_phone', '+1234567890');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `userid` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `status`, `userid`, `name`, `address`, `phone`, `email`, `password_hash`, `created_at`) VALUES
(1, 'admin', 'active', 'admin', 'Administrator', 'Admin Address', '1234567890', 'admin@example.com', '$2y$10$GoApBtGDnUHjij8fc229f.wm6vrI2LagR3UzHSdCOxGtCbanaXBkq', '2026-10-14 18:30:54'),
(2, 'user', 'active', 'demo1', 'Demo User 1', 'Demo Address 1', '1112223333', 'demo1@example.com', '$2y$10$wzRLKCw43olGBjtwBWQAgOBVMdzOXNKsmx007QgKHRNiWA9DnBQdu', '2026-10-14 18:30:55'),
(3, 'user', 'active', 'demo2', 'Demo User 2', 'Demo Address 2', '2223334444', 'demo2@example.com', '$2y$10$VePCu.Tiid0K6niRDmMGJeGD.1iQPwpAAoryRZLG1N3rXhBAQRgbO', '2026-10-14 18:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `genre_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `year_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `play_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `artist_id`, `album_id`, `genre_id`, `language_id`, `year_id`, `title`, `file_path`, `thumbnail`, `description`, `duration`, `play_count`, `created_at`) VALUES
(1, 11, NULL, 7, 3, 1, 'Guess The Episode! | Pakadi gayi Chori! | Taarak Mehta Ka Ooltah Chashmah', 'uploads/videos/video_6ae075c087424.mp4', 'uploads/thumbnails/thumb_6ae075c08779f.jpg', 'A hilarious short mystery clip from Taarak Mehta Ka Ooltah Chashmah\'s \"Guess The Episode\" challenge series. The video features a classic comedy situation in the Gokuldham society where a funny plan or secret theft is exposed.', NULL, 0, '2026-10-27 09:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `years`
--

CREATE TABLE `years` (
  `id` int(11) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `years`
--

INSERT INTO `years` (`id`, `year`) VALUES
(3, 2024),
(1, 2025),
(2, 2026);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artist_id` (`artist_id`);

--
-- Indexes for table `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`item_type`,`item_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `music`
--
ALTER TABLE `music`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artist_id` (`artist_id`),
  ADD KEY `album_id` (`album_id`),
  ADD KEY `genre_id` (`genre_id`),
  ADD KEY `language_id` (`language_id`),
  ADD KEY `year_id` (`year_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`item_type`,`item_id`);

--
-- Indexes for table `recently_played`
--
ALTER TABLE `recently_played`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`item_type`,`item_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userid` (`userid`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artist_id` (`artist_id`),
  ADD KEY `album_id` (`album_id`),
  ADD KEY `genre_id` (`genre_id`),
  ADD KEY `language_id` (`language_id`),
  ADD KEY `year_id` (`year_id`);

--
-- Indexes for table `years`
--
ALTER TABLE `years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `artists`
--
ALTER TABLE `artists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `music`
--
ALTER TABLE `music`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `recently_played`
--
ALTER TABLE `recently_played`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `years`
--
ALTER TABLE `years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `albums`
--
ALTER TABLE `albums`
  ADD CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `music`
--
ALTER TABLE `music`
  ADD CONSTRAINT `music_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `music_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `music_ibfk_3` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `music_ibfk_4` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `music_ibfk_5` FOREIGN KEY (`year_id`) REFERENCES `years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recently_played`
--
ALTER TABLE `recently_played`
  ADD CONSTRAINT `recently_played_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `videos_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `videos_ibfk_3` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `videos_ibfk_4` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `videos_ibfk_5` FOREIGN KEY (`year_id`) REFERENCES `years` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
