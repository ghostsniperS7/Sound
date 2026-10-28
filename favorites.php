<?php
// SOUND - Favorites Endpoint (AJAX API & Navigation Alias)
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If GET request, redirect to liked.php to maintain clean URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sound/liked.php', true, 301);
    exit;
}

// Ensure response is always JSON
header('Content-Type: application/json; charset=utf-8');

// Parse request data (supporting both form-data/x-www-form-urlencoded and JSON payload)
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

$csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$itemType  = trim($_POST['item_type'] ?? '');
$itemId    = intval($_POST['item_id'] ?? 0);

if (is_array($jsonData)) {
    if (isset($jsonData['csrf_token'])) $csrfToken = $jsonData['csrf_token'];
    if (isset($jsonData['item_type']))  $itemType  = trim($jsonData['item_type']);
    if (isset($jsonData['item_id']))    $itemId    = intval($jsonData['item_id']);
}

// 1. Guest Check (Requirement 1b: small message "Login to like" with login link, no error/redirect loop)
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success'   => false,
        'logged_in' => false,
        'message'   => 'Login to like',
        'login_url' => '/sound/login.php'
    ]);
    exit;
}

// 2. CSRF Token Validation
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'success'   => false,
        'logged_in' => true,
        'message'   => 'Invalid security token. Please refresh the page.'
    ]);
    exit;
}

// 3. Input Validation
if (!in_array($itemType, ['music', 'video'], true) || $itemId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success'   => false,
        'logged_in' => true,
        'message'   => 'Invalid media item specified.'
    ]);
    exit;
}

$userId = intval($_SESSION['user_id']);

// 4. Toggle Like / Unlike
if (isInFavorites($userId, $itemType, $itemId)) {
    removeFromFavorites($userId, $itemType, $itemId);
    $liked = false;
    $message = 'Removed from favorites';
} else {
    addToFavorites($userId, $itemType, $itemId);
    $liked = true;
    $message = 'Added to favorites';
}

echo json_encode([
    'success'   => true,
    'logged_in' => true,
    'liked'     => $liked,
    'item_type' => $itemType,
    'item_id'   => $itemId,
    'message'   => $message
]);
exit;
