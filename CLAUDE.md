# SOUND Project Configuration

## Project Summary
SOUND is a music and video streaming platform built with plain PHP and MySQL.

## Paths
- XAMPP: D:\xampp
- PHP: D:\xampp\php\php.exe
- MySQL: D:\xampp\mysql\bin\mysql.exe -u root
- Database: sound

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

## Workflow Rules
1. Edit files directly, one small task at a time
2. Never re-read the whole project
3. Never put $ characters inside shell commands that write password hashes
4. Use a PHP script instead of shell commands for password hashes

## Original Requirements
1. Guest access to music pages without login
2. Global UI polish with solid accent color
3. Spotify-style layout and cards
4. Audio icon favicon

Co-Authored-By: Claude Code <noreply@anthropic.com>