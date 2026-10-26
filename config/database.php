<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        die("Could not connect to the database " . DB_NAME . ": " . $e->getMessage());
    } else {
        error_log("Database connection error: " . $e->getMessage());
        http_response_code(500);
        die("<!DOCTYPE html><html><head><title>Service Unavailable</title><link rel='stylesheet' href='/sound/assets/css/style.css'></head><body style='display:flex;align-items:center;justify-content:center;min-height:100vh;background:#121212;color:#fff;font-family:\"Segoe UI\", sans-serif;'><div style='text-align:center;'><h1>Service Temporarily Unavailable</h1><p style='color:#b3b3b3;'>We are experiencing database connectivity issues. Please try again in a few moments.</p></div></body></html>");
    }
}

// MySQLi connection for backward compatibility with existing functions.php
global $conn;
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn && !$conn->connect_error) {
    $conn->set_charset("utf8mb4");
}

function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
}

function fetchOne($sql, $params = []) {
    return query($sql, $params)->fetch(PDO::FETCH_ASSOC);
}

// Automatically load helpers and flash API everywhere database.php is loaded
require_once __DIR__ . '/../includes/helpers.php';