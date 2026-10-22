<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

// Get site settings from database
$site_settings = [];
if (isset($pdo)) {
    $settings = fetchAll("SELECT setting_key, setting_value FROM site_settings");
    foreach ($settings as $setting) {
        $site_settings[$setting['setting_key']] = $setting['setting_value'];
    }
}

// Set default site settings if not in database
$site_name = $site_settings['site_name'] ?? SITE_NAME;
$site_tagline = $site_settings['site_tagline'] ?? SITE_TAGLINE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?></title>
    <link rel="stylesheet" href="/sound/assets/css/style.css">
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 20C5 20 10 10 20 10C30 10 35 20 35 20" stroke="url(#paint0_linear_1_2)" stroke-width="4" stroke-linecap="round"/>
                    <path d="M5 20C5 20 10 30 20 30C30 30 35 20 35 20" stroke="url(#paint1_linear_1_2)" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <h1><?php echo htmlspecialchars($site_name); ?></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="/sound/">Home</a></li>
                    <li><a href="/sound/music.php">Music</a></li>
                    <li><a href="/sound/videos.php">Videos</a></li>
                    <li><a href="/sound/browse.php">Browse</a></li>
                    <li><a href="/sound/search.php">Search</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="/sound/favorites.php">Liked</a></li>
                    <?php endif; ?>
                    <li><a href="/sound/about.php">About</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="/sound/profile.php">Profile</a></li>
                        <li><a href="/sound/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/sound/login.php">Login</a></li>
                        <li><a href="/sound/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>
        <main class="content">