# SOUND Music Streaming Platform

## Project Overview

SOUND is a comprehensive music and video streaming platform that allows users to discover, play, and review regional and English language tracks.

## Folder Structure

```
D:\xampp\htdocs\sound
├── admin/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   └── README.md
├── includes/
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── config/
│   └── config.php
├── index.php
├── music.php
├── videos.php
├── browse.php
├── search.php
├── favorites.php
├── about.php
├── login.php
├── register.php
└── profile.php
```

## Database Schema

```
CREATE DATABASE IF NOT EXISTS sound CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sound;

-- Users table
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
);

-- Artists table
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Albums table
CREATE TABLE IF NOT EXISTS albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    year INT,
    cover VARCHAR(255),
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
);

-- Genres table
CREATE TABLE IF NOT EXISTS genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- Languages table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- Music table
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
);

-- Videos table
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
);
```

## Setup Instructions

1. Install XAMPP with PHP 8.x and MySQL
2. Clone the repository to `D:\xampp\htdocs\sound`
3. Import the database schema using `database/schema.sql`
4. Seed the database with sample data using `database/seed.sql`
5. Configure the database connection in `config/config.php`
6. Start Apache and MySQL servers in XAMPP
7. Access the application at `http://localhost/sound`

## Login Credentials

- **Admin**:
  - Username: admin
  - Password: admin

- **Demo Users**:
  - Username: demo1
  - Password: demo1
  - Username: demo2
  - Password: demo2

## Features

1. User Authentication (Login/Register)
2. Music and Video Streaming
3. Search and Browse Functionality
4. User Profiles
5. Favorites System
6. Reviews and Ratings
7. Admin Dashboard

## Requirements to Features Mapping

| Requirement | Feature | File | Status |
|-------------|---------|------|--------|
| Guest Access | Music Playback | music.php | Complete |
| Global UI | Spotify-like Design | includes/header.php | Complete |
| Layout Style | Spotify-like Layout | index.php | Complete |
| Favicon | Audio Icon Favicon | includes/header.php | Complete |

## ER Diagram Breakdown

```
[Users] --< [Reviews]
[Users] --< [Ratings]
[Users] --< [Favorites]
[Users] --< [Recently Played]
[Artists] --< [Albums]
[Artists] --< [Music]
[Artists] --< [Videos]
[Genres] --< [Music]
[Genres] --< [Videos]
[Languages] --< [Music]
[Languages] --< [Videos]
[Years] --< [Music]
[Years] --< [Videos]
```
