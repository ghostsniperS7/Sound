<?php
// Database connection
require_once __DIR__ . '/../config/database.php';

// Function to add a review
function addReview($userId, $itemType, $itemId, $reviewText) {
    global $conn;
    $sql = "INSERT INTO reviews (user_id, item_type, item_id, review_text) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isis", $userId, $itemType, $itemId, $reviewText);
    $success = $stmt->execute();

    // Clear cache for reviews and average rating
    if ($success) {
        clearCache("reviews_{$itemType}_{$itemId}");
        clearCache("average_rating_{$itemType}_{$itemId}");
    }

    return $success;
}

// Function to add a rating
function addRating($userId, $itemType, $itemId, $rating) {
    global $conn;
    $sql = "INSERT INTO ratings (user_id, item_type, item_id, rating) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $userId, $itemType, $itemId, $rating);
    $success = $stmt->execute();

    // Clear cache for average rating
    if ($success) {
        clearCache("average_rating_{$itemType}_{$itemId}");
    }

    return $success;
}

// Function to add to favorites
function addToFavorites($userId, $itemType, $itemId) {
    global $conn;
    $sql = "INSERT INTO favorites (user_id, item_type, item_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $userId, $itemType, $itemId);
    $success = $stmt->execute();

    // Clear cache for favorites
    if ($success) {
        clearCache("favorites_{$userId}");
    }

    return $success;
}

// Function to remove from favorites
function removeFromFavorites($userId, $itemType, $itemId) {
    global $conn;
    $sql = "DELETE FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $userId, $itemType, $itemId);
    $success = $stmt->execute();

    // Clear cache for favorites
    if ($success) {
        clearCache("favorites_{$userId}");
    }

    return $success;
}

// Function to add to recently played
function addToRecentlyPlayed($userId, $itemType, $itemId) {
    global $conn;
    $sql = "INSERT INTO recently_played (user_id, item_type, item_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $userId, $itemType, $itemId);
    $success = $stmt->execute();

    // Clear cache for recently played
    if ($success) {
        clearCache("recently_played_{$userId}_10");
    }

    return $success;
}

// Function to get reviews for an item
function getReviews($itemType, $itemId) {
    global $conn;
    $cacheKey = "reviews_{$itemType}_{$itemId}";

    return getCachedData($cacheKey, function() use ($conn, $itemType, $itemId) {
        $sql = "SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.item_type = ? AND r.item_id = ? ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $itemType, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    });
}

// Function to get average rating for an item
function getAverageRating($itemType, $itemId) {
    global $conn;
    $cacheKey = "average_rating_{$itemType}_{$itemId}";

    return getCachedData($cacheKey, function() use ($conn, $itemType, $itemId) {
        $sql = "SELECT AVG(rating) as average_rating, COUNT(*) as rating_count FROM ratings WHERE item_type = ? AND item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $itemType, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return [
            'average_rating' => $row['average_rating'],
            'rating_count' => $row['rating_count']
        ];
    });
}

// Function to check if an item is in favorites
function isInFavorites($userId, $itemType, $itemId) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $userId, $itemType, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

// Simple caching function
function getCachedData($key, $callback, $ttl = 3600) {
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';

    // Check if cache file exists and is not expired
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        return unserialize(file_get_contents($cacheFile));
    }

    // Get fresh data and cache it
    $data = call_user_func($callback);
    file_put_contents($cacheFile, serialize($data));
    return $data;
}

// Clear cache for a specific key
function clearCache($key) {
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

// Clear all cache
function clearAllCache() {
    $cacheDir = __DIR__ . '/../cache/';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// Function to get recently played items for a user
function getRecentlyPlayed($userId, $limit = 10) {
    global $conn;
    $cacheKey = "recently_played_{$userId}_{$limit}";

    return getCachedData($cacheKey, function() use ($conn, $userId, $limit) {
        $sql = "SELECT rp.*,
                  CASE WHEN rp.item_type = 'music' THEN m.title ELSE v.title END as title,
                  CASE WHEN rp.item_type = 'music' THEN m.cover ELSE v.thumbnail END as cover,
                  a.name as artist_name
           FROM recently_played rp
           LEFT JOIN music m ON rp.item_type = 'music' AND rp.item_id = m.id
           LEFT JOIN videos v ON rp.item_type = 'video' AND rp.item_id = v.id
           LEFT JOIN artists a ON (rp.item_type = 'music' AND m.artist_id = a.id) OR (rp.item_type = 'video' AND v.artist_id = a.id)
           WHERE rp.user_id = ? ORDER BY rp.played_at DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    });
}
?>