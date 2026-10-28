-- SOUND Initial Seed Data
-- User bcrypt hashes read directly from the live database `sound`

USE sound;

-- 1. Users (admin and demo accounts with valid bcrypt hashes from live DB)
INSERT INTO users (id, role, status, userid, name, address, phone, email, password_hash, created_at) VALUES
(1, 'admin', 'active', 'admin', 'Administrator', 'Admin Address', '1234567890', 'admin@example.com', '$2y$10$GoApBtGDnUHjij8fc229f.wm6vrI2LagR3UzHSdCOxGtCbanaXBkq', '2026-10-14 23:30:54'),
(2, 'user', 'active', 'demo1', 'Demo User 1', 'Demo Address 1', '1112223333', 'demo1@example.com', '$2y$10$wzRLKCw43olGBjtwBWQAgOBVMdzOXNKsmx007QgKHRNiWA9DnBQdu', '2026-10-14 23:30:55'),
(3, 'user', 'active', 'demo2', 'Demo User 2', 'Demo Address 2', '2223334444', 'demo2@example.com', '$2y$10$VePCu.Tiid0K6niRDmMGJeGD.1iQPwpAAoryRZLG1N3rXhBAQRgbO', '2026-10-14 23:30:55')
ON DUPLICATE KEY UPDATE name=VALUES(name), password_hash=VALUES(password_hash);

-- 2. Languages
INSERT INTO languages (id, name) VALUES
(1, 'English'),
(2, 'Urdu'),
(3, 'Hindi'),
(4, 'Punjabi'),
(5, 'Pashto'),
(6, 'Sindhi')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 3. Genres
INSERT INTO genres (id, name) VALUES
(1, 'Pop'),
(2, 'Rock'),
(3, 'Jazz'),
(4, 'Classical'),
(5, 'Hip-Hop'),
(6, 'Electronic'),
(7, 'Comedy')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 4. Years
INSERT INTO years (id, year) VALUES
(1, 2025),
(2, 2026),
(3, 2024)
ON DUPLICATE KEY UPDATE year=VALUES(year);

-- 5. Artists
INSERT INTO artists (id, name, description) VALUES
(2, 'Asim Azhar', 'Pakistani singer, songwriter and actor.'),
(3, 'Murtaza Qizilbash', 'Vocalist and performer.'),
(4, 'Navjot Ahuja', 'Contemporary singer and composer.'),
(5, 'Abdul Hannan', 'Pakistani singer-songwriter known for indie pop.'),
(6, 'Anuv Jain', 'Indian singer, songwriter and acoustic musician.'),
(7, 'Kushagra', 'Singer and music producer.'),
(9, 'Farak', 'Indie alternative music producer.'),
(10, 'Paresh Pahuja', 'Singer and actor.'),
(11, 'Dilip Joshi', 'Artist and performer.')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 6. Site Settings
INSERT INTO site_settings (id, setting_key, setting_value) VALUES
(1, 'site_name', 'SOUND'),
(2, 'site_tagline', 'Your Music and Video Streaming Platform'),
(3, 'about_text', 'Welcome to SOUND, your ultimate destination for music and video streaming in both regional and English languages.'),
(4, 'contact_email', 'contact@sound.com'),
(5, 'contact_phone', '+1234567890')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
