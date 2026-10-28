<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Single pre-fetch query for user's liked IDs across music and videos (Requirement 1d)
$userLikedIds = isLoggedIn() ? getUserLikedIds(intval($_SESSION['user_id'])) : ['music' => [], 'video' => []];

// Get site settings from database
$site_settings = [];
if (isset($pdo)) {
    $settings = fetchAll("SELECT setting_key, setting_value FROM site_settings");
    foreach ($settings as $setting) {
        $site_settings[$setting['setting_key']] = $setting['setting_value'];
    }
}

// Set default site settings if not in database
$site_name = $site_settings['site_name'] ?? (defined('SITE_NAME') ? SITE_NAME : 'SOUND');
$site_tagline = $site_settings['site_tagline'] ?? (defined('SITE_TAGLINE') ? SITE_TAGLINE : 'Music & Video Streaming');
$current_page = basename($_SERVER['PHP_SELF']);
$page_meta_desc = htmlspecialchars($site_tagline);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $page_meta_desc; ?>">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo htmlspecialchars($site_name); ?></title>
    <link rel="icon" type="image/svg+xml" href="/sound/favicon.svg">
    <link rel="alternate icon" href="/sound/favicon.ico">
    <link rel="stylesheet" href="/sound/assets/css/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <script>
        window.SOUND_IS_LOGGED_IN = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        window.SOUND_CSRF_TOKEN = <?php echo json_encode($csrf_token); ?>;
        window.SOUND_USER_LIKED = <?php echo json_encode($userLikedIds); ?>;
    </script>
</head>
<body>
    <div class="app">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <a href="/sound/" class="logo" aria-label="<?php echo htmlspecialchars($site_name); ?> Home">
                <svg class="logo-icon" viewBox="0 0 28 28" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="12" width="4" height="14" rx="2" fill="currentColor"/>
                    <rect x="9" y="6" width="4" height="20" rx="2" fill="currentColor"/>
                    <rect x="16" y="10" width="4" height="16" rx="2" fill="currentColor"/>
                    <rect x="23" y="4" width="4" height="22" rx="2" fill="currentColor"/>
                </svg>
                <span class="logo-text"><?php echo htmlspecialchars($site_name); ?></span>
            </a>

            <nav>
                <ul>
                    <li class="<?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">
                        <a href="/sound/">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page === 'music.php') ? 'active' : ''; ?>">
                        <a href="/sound/music.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18V5l12-2v13"></path>
                                <circle cx="6" cy="18" r="3"></circle>
                                <circle cx="18" cy="16" r="3"></circle>
                            </svg>
                            <span>Music</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page === 'videos.php') ? 'active' : ''; ?>">
                        <a href="/sound/videos.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                            </svg>
                            <span>Videos</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page === 'browse.php') ? 'active' : ''; ?>">
                        <a href="/sound/browse.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                            </svg>
                            <span>Browse</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page === 'search.php') ? 'active' : ''; ?>">
                        <a href="/sound/search.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <span>Search</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page === 'liked.php' || $current_page === 'favorites.php') ? 'active' : ''; ?>">
                        <a href="/sound/liked.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <span>Liked</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page === 'about.php') ? 'active' : ''; ?>">
                        <a href="/sound/about.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>About</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Bottom of Sidebar: User / Auth Card -->
            <div class="sidebar-user-block">
                <?php if (isLoggedIn()): ?>
                    <div class="sidebar-user-info">
                        <?php 
                        $u_name = $_SESSION['user_name'] ?? 'User';
                        $initial = strtoupper(substr($u_name, 0, 1));
                        ?>
                        <div class="sidebar-avatar"><?php echo htmlspecialchars($initial); ?></div>
                        <div class="sidebar-user-details">
                            <div class="sidebar-user-name"><?php echo htmlspecialchars($u_name); ?></div>
                            <div class="sidebar-user-role"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'user'); ?></div>
                        </div>
                    </div>
                    <ul style="list-style: none; margin-top: 8px; display: flex; flex-direction: column; gap: 2px;">
                        <?php if (isAdmin()): ?>
                            <li>
                                <a href="/sound/admin/dashboard.php" style="display: flex; align-items: center; gap: 10px; padding: 6px 10px; font-size: 13px; color: var(--accent); font-weight: 600;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                    <span>Admin Panel</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="/sound/profile.php" style="display: flex; align-items: center; gap: 10px; padding: 6px 10px; font-size: 13px; color: var(--text-muted);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="/sound/logout.php" style="display: flex; align-items: center; gap: 10px; padding: 6px 10px; font-size: 13px; color: var(--danger);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="/sound/login.php" class="btn btn-secondary btn-sm" style="width: 100%;">Login</a>
                        <a href="/sound/register.php" class="btn btn-primary btn-sm" style="width: 100%;">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Wrapper with Top Bar and Content Area -->
        <div class="main-wrapper">
            <!-- Spotify-Style Top Bar -->
            <header class="top-bar">
                <div class="top-bar-search">
                    <form action="/sound/search.php" method="get" class="top-bar-search-form" id="top-search-form" role="search">
                        <div class="search-input-wrapper">
                            <span class="search-icon-wrapper" aria-hidden="true">
                                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                            <input type="search" id="top-search-input" name="q" placeholder="What do you want to play?" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" aria-label="Search" autocomplete="off">
                            <button type="button" class="search-clear-btn" id="top-search-clear" aria-label="Clear search" title="Clear search" style="<?php echo !empty($_GET['q']) ? 'display: flex;' : 'display: none;'; ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="top-bar-user">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="/sound/admin/dashboard.php" class="btn btn-ghost btn-sm" style="color: var(--accent); border-color: rgba(255, 90, 60, 0.4);">Admin Panel</a>
                        <?php endif; ?>
                        <a href="/sound/profile.php" class="user-badge" title="View Profile">
                            <span class="sidebar-avatar" style="width: 24px; height: 24px; font-size: 11px;"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1))); ?></span>
                            <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                        </a>
                        <a href="/sound/logout.php" class="btn btn-ghost btn-sm" title="Log out">Logout</a>
                    <?php else: ?>
                        <a href="/sound/login.php" class="btn btn-ghost btn-sm">Log in</a>
                        <a href="/sound/register.php" class="btn btn-primary btn-sm btn-pill">Sign up</a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Page Content (swapped by SPA pjax on public pages) -->
            <main class="content">
                <?php if (function_exists('showFlash')): ?>
                    <?php echo showFlash(); ?>
                <?php endif; ?>