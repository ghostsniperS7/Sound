<?php
// SOUND - Global Application Configuration

// Application Debug Mode (Set true for development, false for production)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'sound');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Site configuration
if (!defined('SITE_NAME')) define('SITE_NAME', 'SOUND');
if (!defined('SITE_TAGLINE')) define('SITE_TAGLINE', 'Your Music and Video Streaming Platform');

// Base Paths & URLs
if (!defined('BASE_URL')) define('BASE_URL', '/sound/');
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);

// File upload configuration
if (!defined('UPLOAD_MAX_SIZE')) define('UPLOAD_MAX_SIZE', 100 * 1024 * 1024); // 100MB

// Error Handling Configuration (Task 4)
$logDir = ROOT_PATH . 'logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
$logFile = $logDir . DIRECTORY_SEPARATOR . 'php_errors.log';

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $logFile);
    error_reporting(E_ALL);

    // Friendly error page for unhandled exceptions
    set_exception_handler(function (\Throwable $e) use ($logFile) {
        $msg = sprintf("[%s] Uncaught Exception: %s in %s:%d\nStack Trace:\n%s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        @error_log($msg, 3, $logFile);

        if (!headers_sent()) {
            http_response_code(500);
        }
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - ' . htmlspecialchars(SITE_NAME) . '</title>
    <link rel="stylesheet" href="/sound/assets/css/style.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #121212; color: #FFFFFF; font-family: "Segoe UI", sans-serif; padding: 20px; }
        .error-card { max-width: 480px; width: 100%; background: #181818; border: 1px solid #282828; border-radius: 8px; padding: 36px 32px; text-align: center; }
        .error-icon { width: 48px; height: 48px; color: #FF5A3C; margin: 0 auto 16px auto; }
        h1 { font-size: 22px; margin-bottom: 12px; color: #FFFFFF; }
        p { color: #B3B3B3; font-size: 14px; line-height: 1.6; margin-bottom: 24px; }
        .btn-home { display: inline-block; background: #FF5A3C; color: #FFFFFF; padding: 10px 24px; border-radius: 9999px; font-size: 14px; font-weight: 600; text-decoration: none; }
        .btn-home:hover { background: #ff7157; }
    </style>
</head>
<body>
    <div class="error-card">
        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <h1>Something went wrong</h1>
        <p>An unexpected error occurred while processing your request. Our team has logged the issue and is working on a fix.</p>
        <a href="/sound/" class="btn-home">Return to Home</a>
    </div>
</body>
</html>';
        exit;
    });
}