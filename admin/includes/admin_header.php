<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Guarantee admin authentication
requireAdmin();

// Flash message helpers
if (!function_exists('setFlash')) {
    function setFlash($type, $message) {
        $_SESSION['flash_' . $type] = $message;
    }
}

if (!function_exists('getFlash')) {
    function getFlash($type) {
        if (isset($_SESSION['flash_' . $type])) {
            $msg = $_SESSION['flash_' . $type];
            unset($_SESSION['flash_' . $type]);
            return $msg;
        }
        return null;
    }
}

// Determine active section and current script
$current_script = basename($_SERVER['PHP_SELF']);
$section_map = [
    'dashboard'  => ['dashboard.php', 'index.php'],
    'music'      => ['music.php', 'add_music.php', 'edit_music.php'],
    'videos'     => ['videos.php', 'add_video.php', 'edit_video.php'],
    'artists'    => ['artists.php', 'add_artist.php', 'edit_artist.php'],
    'albums'     => ['albums.php', 'add_album.php', 'edit_album.php'],
    'genres'     => ['genres.php', 'add_genre.php', 'edit_genre.php'],
    'languages'  => ['languages.php', 'add_language.php', 'edit_language.php'],
    'years'      => ['years.php', 'add_year.php', 'edit_year.php'],
    'users'      => ['users.php', 'edit_user.php'],
    'settings'   => ['settings.php']
];

$active_section = 'dashboard';
foreach ($section_map as $sec => $files) {
    if (in_array($current_script, $files)) {
        $active_section = $sec;
        break;
    }
}

// Auto-build breadcrumbs if not explicitly set
if (!isset($breadcrumbs) || !is_array($breadcrumbs)) {
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php']
    ];
    if ($active_section !== 'dashboard') {
        $section_name = ucfirst($active_section);
        $section_url = "/sound/admin/{$active_section}.php";
        
        if (str_starts_with($current_script, 'add_')) {
            $breadcrumbs[] = ['title' => $section_name, 'url' => $section_url];
            $breadcrumbs[] = ['title' => 'Add ' . substr($section_name, 0, -1)];
        } elseif (str_starts_with($current_script, 'edit_')) {
            $breadcrumbs[] = ['title' => $section_name, 'url' => $section_url];
            $breadcrumbs[] = ['title' => 'Edit ' . substr($section_name, 0, -1)];
        } else {
            $breadcrumbs[] = ['title' => $section_name];
        }
    }
}

$page_title = $page_title ?? 'Admin Dashboard';
$site_name = defined('SITE_NAME') ? SITE_NAME : 'SOUND';
$user_name = $_SESSION['user_name'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SOUND Admin Dashboard and Management Panel">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars($site_name); ?> Admin</title>
    <link rel="icon" type="image/svg+xml" href="/sound/favicon.svg">
    <link rel="alternate icon" href="/sound/favicon.ico">
    <link rel="stylesheet" href="/sound/assets/css/style.css">
    <link rel="stylesheet" href="/sound/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="#FF5A3C" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="12" width="4" height="14" rx="2" fill="#FF5A3C"/>
                    <rect x="9" y="6" width="4" height="20" rx="2" fill="#FF5A3C"/>
                    <rect x="16" y="10" width="4" height="16" rx="2" fill="#FF5A3C"/>
                    <rect x="23" y="4" width="4" height="22" rx="2" fill="#FF5A3C"/>
                </svg>
                <div>
                    <div class="brand-title"><?php echo htmlspecialchars($site_name); ?></div>
                    <span class="brand-badge">Admin Panel</span>
                </div>
            </div>

            <nav class="admin-nav">
                <div class="nav-section-title">Overview</div>
                <a href="/sound/admin/dashboard.php" class="admin-nav-item <?php echo $active_section === 'dashboard' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <div class="nav-section-title">Media Management</div>
                <a href="/sound/admin/music.php" class="admin-nav-item <?php echo $active_section === 'music' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"></path>
                        <circle cx="6" cy="18" r="3"></circle>
                        <circle cx="18" cy="16" r="3"></circle>
                    </svg>
                    <span>Music</span>
                </a>

                <a href="/sound/admin/videos.php" class="admin-nav-item <?php echo $active_section === 'videos' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="23 7 16 12 23 17 23 7"></polygon>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                    <span>Videos</span>
                </a>

                <a href="/sound/admin/artists.php" class="admin-nav-item <?php echo $active_section === 'artists' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Artists</span>
                </a>

                <a href="/sound/admin/albums.php" class="admin-nav-item <?php echo $active_section === 'albums' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Albums</span>
                </a>

                <div class="nav-section-title">Taxonomy</div>
                <a href="/sound/admin/genres.php" class="admin-nav-item <?php echo $active_section === 'genres' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="9" x2="20" y2="9"></line>
                        <line x1="4" y1="15" x2="20" y2="15"></line>
                        <line x1="10" y1="3" x2="8" y2="21"></line>
                        <line x1="16" y1="3" x2="14" y2="21"></line>
                    </svg>
                    <span>Genres</span>
                </a>

                <a href="/sound/admin/languages.php" class="admin-nav-item <?php echo $active_section === 'languages' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <span>Languages</span>
                </a>

                <a href="/sound/admin/years.php" class="admin-nav-item <?php echo $active_section === 'years' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Years</span>
                </a>

                <div class="nav-section-title">System</div>
                <a href="/sound/admin/users.php" class="admin-nav-item <?php echo $active_section === 'users' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>Users</span>
                </a>

                <a href="/sound/admin/settings.php" class="admin-nav-item <?php echo $active_section === 'settings' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Site Settings</span>
                </a>

                <div class="admin-nav-divider"></div>

                <a href="/sound/" target="_blank" class="admin-nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span>View Website</span>
                </a>

                <a href="/sound/logout.php" class="admin-nav-item" style="color: #f87171;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Wrapper -->
        <div class="admin-main">
            <!-- Topbar with Breadcrumbs & Profile -->
            <header class="admin-topbar">
                <nav class="admin-breadcrumbs" aria-label="Breadcrumb">
                    <?php 
                    $last_idx = count($breadcrumbs) - 1;
                    foreach ($breadcrumbs as $i => $crumb): 
                    ?>
                        <?php if ($i > 0): ?>
                            <span class="separator">/</span>
                        <?php endif; ?>

                        <?php if ($i < $last_idx && !empty($crumb['url'])): ?>
                            <a href="<?php echo htmlspecialchars($crumb['url']); ?>"><?php echo htmlspecialchars($crumb['title']); ?></a>
                        <?php else: ?>
                            <span class="current"><?php echo htmlspecialchars($crumb['title']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>

                <div class="admin-user-info">
                    <span class="admin-user-badge">Admin: <?php echo htmlspecialchars($user_name); ?></span>
                </div>
            </header>

            <!-- Admin Content Container -->
            <main class="admin-content">
                <?php 
                $flash_success = getFlash('success');
                $flash_error = getFlash('error');
                ?>
                <?php if ($flash_success): ?>
                    <div class="admin-alert admin-alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <div><?php echo htmlspecialchars($flash_success); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($flash_error): ?>
                    <div class="admin-alert admin-alert-error">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <div><?php echo htmlspecialchars($flash_error); ?></div>
                    </div>
                <?php endif; ?>
