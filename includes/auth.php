<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && (($_SESSION['user_role'] ?? '') === 'admin');
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /sound/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: /sound/login.php');
        exit;
    }
    if (!isAdmin()) {
        header('Location: /sound/index.php');
        exit;
    }
}

/**
 * Get client IP address supporting forwarded headers if available
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Check if login is locked due to brute-force attempts (5 failed in 10 minutes)
 */
function isLoginLocked($userid, $ip = null) {
    global $pdo;
    if ($ip === null) {
        $ip = getClientIP();
    }

    $userCount = 0;
    $ipCount = 0;

    if (!empty($userid)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE userid = ? AND attempted_at >= NOW() - INTERVAL 10 MINUTE");
        $stmt->execute([$userid]);
        $userCount = (int)$stmt->fetchColumn();
    }

    if (!empty($ip)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at >= NOW() - INTERVAL 10 MINUTE");
        $stmt->execute([$ip]);
        $ipCount = (int)$stmt->fetchColumn();
    }

    $combinedCount = 0;
    if (!empty($userid) && !empty($ip)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE (userid = ? OR ip = ?) AND attempted_at >= NOW() - INTERVAL 10 MINUTE");
        $stmt->execute([$userid, $ip]);
        $combinedCount = (int)$stmt->fetchColumn();
    }

    return ($userCount >= 5 || $ipCount >= 5 || $combinedCount >= 5);
}

/**
 * Record a failed login attempt
 */
function recordLoginAttempt($userid, $ip = null) {
    global $pdo;
    if ($ip === null) {
        $ip = getClientIP();
    }
    $stmt = $pdo->prepare("INSERT INTO login_attempts (userid, ip, attempted_at) VALUES (?, ?, NOW())");
    $stmt->execute([$userid, $ip]);
}

/**
 * Clear login attempts for a userid or IP
 */
function clearLoginAttempts($userid, $ip = null) {
    global $pdo;
    if ($ip === null) {
        $ip = getClientIP();
    }
    $conditions = [];
    $params = [];
    if (!empty($userid)) {
        $conditions[] = "userid = ?";
        $params[] = $userid;
    }
    if (!empty($ip)) {
        $conditions[] = "ip = ?";
        $params[] = $ip;
    }
    if (empty($conditions)) {
        return;
    }
    $where = implode(' OR ', $conditions);
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE {$where}");
    $stmt->execute($params);
}

function login($userid, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE userid = ? AND status = 'active'");
    $stmt->execute([$userid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        clearLoginAttempts($userid, getClientIP());
        return true;
    }

    return false;
}

function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    @session_destroy();
    header('Location: /sound/');
    exit;
}

// Ensure session is started with secure cookie parameters
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

// CSRF protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Initialize CSRF token for forms
$csrf_token = generateCSRFToken();