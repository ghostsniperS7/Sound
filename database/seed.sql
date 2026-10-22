-- Insert default admin account
INSERT INTO users (role, status, userid, name, address, phone, email, password_hash, created_at) VALUES
('admin', 'active', 'admin', 'Administrator', 'Admin Address', '1234567890', 'admin@example.com', '$2y$10$ikfKXoh0ci6EdQp9wNbKhO5Fw02ROzvdZYSoDHOxBonkGRJxjTnJu', NOW());

-- Insert languages
INSERT INTO languages (name) VALUES
('English'),
('Urdu'),
('Hindi'),
('Punjabi'),
('Pashto'),
('Sindhi');

-- Insert some genres
INSERT INTO genres (name) VALUES
('Pop'),
('Rock'),
('Jazz'),
('Classical'),
('Hip-Hop'),
('Electronic');

-- Insert site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'SOUND'),
('site_tagline', 'Your Music and Video Streaming Platform'),
('about_text', 'Welcome to SOUND, your ultimate destination for music and video streaming. Discover new and old tracks in both regional and English languages.'),
('contact_email', 'contact@sound.com'),
('contact_phone', '+1234567890');

-- Insert a few demo users
INSERT INTO users (role, status, userid, name, address, phone, email, password_hash, created_at) VALUES
('user', 'active', 'demo1', 'Demo User 1', 'Demo Address 1', '1112223333', 'demo1@example.com', '$2y$10$cEA3N3I1GoC42wM1QnfH/.beuGkswFVh0j8T99hkSZj30WveqMVfa', NOW()),
('user', 'active', 'demo2', 'Demo User 2', 'Demo Address 2', '2223334444', 'demo2@example.com', '$2y$10$/c4PE8FCBDGuO32wsL84EOA4Q0CHMTabT52r3yxUYFB4Pd9oxk23m', NOW());
