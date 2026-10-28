<?php
// SOUND - Global Helper Functions & Flash API

if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    @session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    @session_start();
}

/**
 * Set a flash message in the user's session
 * @param string $type 'success', 'error', 'info', or 'warning'
 * @param string $message The message text
 */
function setFlash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Retrieve and clear a flash message from the session
 * @param string $type 'success', 'error', 'info', or 'warning'
 * @return string|null The flash message if present, or null
 */
function getFlash($type) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

/**
 * Check if a flash message exists without clearing it
 * @param string $type
 * @return bool
 */
function hasFlash($type) {
    return isset($_SESSION['flash_' . $type]);
}

/**
 * Render all pending flash alerts to HTML and clear them
 * @return string HTML alert string
 */
function showFlash() {
    $types = [
        'success' => 'alert-success',
        'error'   => 'alert-error',
        'danger'  => 'alert-error',
        'warning' => 'alert-warning',
        'info'    => 'alert-info'
    ];

    $html = '';
    foreach ($types as $type => $cssClass) {
        $msg = getFlash($type);
        if ($msg) {
            $html .= '<div class="alert ' . $cssClass . '">' . htmlspecialchars($msg) . '</div>';
        }
    }
    return $html;
}

/**
 * Build a consistent root-relative asset URL for uploaded files, images, and audio/video
 * Works seamlessly whether called from the public root or inside /admin/
 * @param string|null $path Relative path stored in database, e.g. "uploads/music/track_123.mp3"
 * @return string Proper root-relative URL, e.g. "/sound/uploads/music/track_123.mp3"
 */
function asset_url($path) {
    if (empty($path)) {
        return '';
    }
    $path = trim($path);
    // External URL
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    // Already formatted with /sound/
    if (str_starts_with($path, '/sound/')) {
        return $path;
    }
    if (str_starts_with($path, 'sound/')) {
        return '/' . $path;
    }
    // Clean relative path
    $clean = ltrim($path, '/\\');
    return '/sound/' . str_replace('\\', '/', $clean);
}

/**
 * Check if an uploaded asset file actually exists on disk under sound/
 * @param string|null $path
 * @return bool
 */
function asset_exists($path) {
    if (empty($path)) {
        return false;
    }
    if (!defined('ROOT_PATH')) {
        return false;
    }
    $clean = ltrim(str_replace(['/sound/', '\\'], ['', '/'], $path), '/');
    return file_exists(ROOT_PATH . $clean);
}

/**
 * Render a cover image or solid fallback initials placeholder tile
 * Guarantees NO broken images are ever displayed to the user
 * @param string $title Item title for initials
 * @param string|null $coverPath Relative path or URL
 * @param string $alt Alt attribute
 * @param string $extraClasses Custom CSS classes
 * @param string $attr Additional HTML attributes
 * @return string HTML markup
 */
function render_cover_tile($title, $coverPath, $alt = '', $extraClasses = '', $attr = '') {
    $altText = !empty($alt) ? $alt : $title;
    if (!empty($coverPath) && asset_exists($coverPath)) {
        $url = htmlspecialchars(asset_url($coverPath));
        $altSafe = htmlspecialchars($altText);
        $classSafe = htmlspecialchars($extraClasses);
        return "<img src=\"{$url}\" alt=\"{$altSafe}\" class=\"{$classSafe}\" {$attr}>";
    }

    // Fallback: Solid dark tile with coral initials
    $initials = strtoupper(substr(trim($title ?: 'SO'), 0, 2));
    $classSafe = htmlspecialchars('placeholder ' . $extraClasses);
    return "<div class=\"{$classSafe}\" {$attr}><span style=\"color: #FF5A3C; font-weight: 800; font-size: 14px;\">" . htmlspecialchars($initials) . "</span></div>";
}

/**
 * Preserve old form input during Post/Redirect/Get flow
 * @param array $data Typically $_POST
 */
function setOldInput($data) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['old_input'] = $data;
}

/**
 * Retrieve preserved old form input
 * @param string $key Field name
 * @param mixed $default Default value
 * @return mixed
 */
function getOldInput($key, $default = '') {
    return $_SESSION['old_input'][$key] ?? $default;
}

/**
 * Clear preserved old form input
 */
function clearOldInput() {
    unset($_SESSION['old_input']);
}

/**
 * Check if an item is newly created (within the last 7 days)
 * @param string|null $created_at Timestamp string from database
 * @return bool True if created within the last 7 days, false otherwise
 */
function isNewItem($created_at) {
    if (empty($created_at)) {
        return false;
    }
    $timestamp = strtotime($created_at);
    if ($timestamp === false) {
        return false;
    }
    return $timestamp >= (time() - (7 * 86400));
}
