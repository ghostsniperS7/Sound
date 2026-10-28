# SOUND Music & Video Streaming Platform

## Project Overview

**SOUND** is a media streaming web application built with plain PHP, MySQL (PDO), HTML5, CSS3, and JavaScript (Spotify-style dark theme with `#FF5A3C` coral accent). It allows guests and authenticated users to discover, stream, rate, and review music tracks and videos categorized by Artist, Album, Genre, Language, and Release Year.

---

## Folder Structure

```text
D:\xampp\htdocs\sound
├── .htaccess                 -> Root Apache config with custom 404 & 403 ErrorDocuments
├── 403.php                   -> Custom styled 403 Forbidden error page
├── 404.php                   -> Custom styled 404 Not Found error page
├── about.php                 -> Platform information, mission statement, and contact details
├── browse.php                -> Catalog browser by Artist, Album, Genre, Language, and Year
├── favorites.php             -> User liked music tracks and videos
├── liked.php                 -> User liked music and videos list (alias to favorites)
├── index.php                 -> Homepage featuring Latest Music, Latest Videos, and Recently Played
├── login.php                 -> User/admin login with brute-force protection
├── logout.php                -> Session destruction and redirection
├── music.php                 -> Public music catalog and track detail/review page
├── profile.php               -> User profile management, password change, and activity
├── register.php              -> User registration with password hashing
├── search.php                -> Search across titles, artists, albums, and genres
├── videos.php                -> Video library and HTML5 playback detail page
├── admin/                    -> Administrator dashboard and CRUD management
│   ├── add_album.php         -> Add album with artist binding and cover upload
│   ├── add_artist.php        -> Add artist profile
│   ├── add_genre.php         -> Add music genre
│   ├── add_language.php      -> Add language
│   ├── add_music.php         -> Upload MP3 track, cover image, and metadata
│   ├── add_video.php         -> Upload video, thumbnail, and metadata
│   ├── add_year.php          -> Add release year
│   ├── albums.php            -> Master album table
│   ├── artists.php           -> Master artist table
│   ├── dashboard.php         -> Admin dashboard with site metrics
│   ├── edit_album.php        -> Edit album details and cover
│   ├── edit_artist.php       -> Edit artist profile
│   ├── edit_genre.php        -> Edit genre name
│   ├── edit_language.php     -> Edit language name
│   ├── edit_music.php        -> Edit track metadata and media files
│   ├── edit_user.php         -> Manage user account status and role
│   ├── edit_video.php        -> Edit video metadata and media files
│   ├── edit_year.php         -> Edit release year
│   ├── genres.php            -> Master genre table
│   ├── index.php             -> Admin router to dashboard
│   ├── languages.php         -> Master language table
│   ├── music.php             -> Master music catalog
│   ├── settings.php          -> Site settings editor
│   ├── users.php             -> User account management
│   ├── videos.php            -> Master video catalog
│   ├── years.php             -> Master year table
│   └── includes/
│       ├── admin_footer.php  -> Admin footer scripts and copyright
│       └── admin_header.php  -> Admin sidebar and session authorization check
├── assets/
│   ├── css/
│   │   ├── admin.css         -> Administrative dashboard stylesheet
│   │   └── style.css         -> Spotify-style dark theme design system
│   └── js/
│       └── app.js            -> Persistent bottom audio player & favorites AJAX toggler
├── config/
│   ├── config.php            -> Site constants, paths, error logging, and session cookies
│   └── database.php          -> PDO connection, query helpers, and MySQLi compatibility
├── database/
│   ├── schema.sql            -> Complete DDL schema creating all 14 database tables
│   └── seed.sql              -> Seed data for admin, demo users, taxonomies, and settings
├── docs/
│   ├── PROJECT_GUIDE.md      -> Comprehensive architecture, code flow, and viva guide
│   └── README.md             -> System requirements, schema, and setup guide
├── includes/
│   ├── auth.php              -> Authentication, brute-force security, and CSRF protection
│   ├── footer.php            -> Bottom audio player bar and scripts
│   ├── functions.php         -> Ratings, reviews, favorites, history, and cache helpers
│   ├── header.php            -> Navigation sidebar, top search bar, and user menu
│   └── helpers.php           -> Flash messaging API, isNewItem badge helper, and formatting
└── uploads/                  -> Uploaded binary media
    ├── .htaccess             -> Execution protection (php_flag engine off, denies *.php, *.phtml)
    ├── covers/               -> Album and track artwork images
    ├── music/                -> MP3 audio files
    ├── thumbnails/           -> Video thumbnail images
    └── videos/               -> MP4 / WebM / QuickTime video files
```

---

## Database Schema (14 Tables)

```sql
CREATE DATABASE IF NOT EXISTS sound CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sound;

-- 1. Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    userid VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Artists table
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Albums table
CREATE TABLE IF NOT EXISTS albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    year INT,
    cover VARCHAR(255),
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Genres table
CREATE TABLE IF NOT EXISTS genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Languages table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Years table
CREATE TABLE IF NOT EXISTS years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Music table
CREATE TABLE IF NOT EXISTS music (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    album_id INT,
    genre_id INT NOT NULL,
    language_id INT NOT NULL,
    year_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    cover VARCHAR(255),
    description TEXT,
    duration INT,
    play_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Videos table
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    album_id INT,
    genre_id INT NOT NULL,
    language_id INT NOT NULL,
    year_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255),
    description TEXT,
    duration INT,
    play_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('music', 'video') NOT NULL,
    item_id INT NOT NULL,
    review_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Ratings table
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('music', 'video') NOT NULL,
    item_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('music', 'video') NOT NULL,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Recently played table
CREATE TABLE IF NOT EXISTS recently_played (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('music', 'video') NOT NULL,
    item_id INT NOT NULL,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Site settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Login attempts table (Brute-force protection)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid VARCHAR(20),
    ip VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Setup Instructions

1. Install **XAMPP** (with PHP 8.2+ and MySQL).
2. Place the project directory at `D:\xampp\htdocs\sound`.
3. Open MySQL CLI or phpMyAdmin and import the database schema:
   ```bash
   mysql -u root sound < database/schema.sql
   mysql -u root sound < database/seed.sql
   ```
4. Verify database credentials in `config/config.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
5. Ensure `uploads/music/`, `uploads/videos/`, `uploads/covers/`, and `uploads/thumbnails/` are writable.
6. Open your browser and navigate to `http://localhost/sound/`.

---

## Default Login Credentials

- **Administrator**:
  - Username: `admin`
  - Password: `admin`
- **Demo Users**:
  - Username: `demo1` | Password: `demo1`
  - Username: `demo2` | Password: `demo2`

---

## Key Features & Security Protections

1. **Brute-Force Protection**: Tracks failed attempts by username and client IP. Locks the account after 5 failed attempts in 10 minutes without evaluating the password.
2. **Real MIME Type Verification**: Validates file MIME types using PHP `finfo_file` (`audio/mpeg` for audio; `video/mp4`, `video/webm`, `video/quicktime` for video; `image/jpeg|png|gif|webp` for images).
3. **Uploads Execution Disabling**: `uploads/.htaccess` disables PHP execution (`php_flag engine off`) and rejects all requests to `.php` and `.phtml` scripts.
4. **Hardened Session Cookies**: `session_set_cookie_params` enforces `HttpOnly` and `SameSite=Lax`.
5. **CSRF Protection**: All state-changing POST forms require and validate a cryptographic token using `hash_equals()`.
6. **SQL Injection Defense**: All database queries use PDO prepared statements with parameter binding.
7. **Cross-Site Scripting (XSS) Defense**: Output escaping via `htmlspecialchars()` throughout all views.
8. **Persistent Music Player**: Continuous bottom player bar supporting play/pause, seek, volume, and track switching.
9. **Taxonomy & Faceted Search**: Multi-filter catalog browsing across artists, albums, genres, languages, and years.
