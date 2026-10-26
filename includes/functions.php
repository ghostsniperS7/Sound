<?php
// SOUND - Core Helper Functions (Reviews, Ratings, Favorites, History, and Caching)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * Add a user review for a music or video item
 */
function addReview($userId, $itemType, $itemId, $reviewText) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, item_type, item_id, review_text) VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$userId, $itemType, $itemId, $reviewText]);

    if ($success) {
        clearCache("reviews_{$itemType}_{$itemId}");
        clearCache("average_rating_{$itemType}_{$itemId}");
    }

    return $success;
}

/**
 * Add or update a 1-5 star rating
 */
function addRating($userId, $itemType, $itemId, $rating) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO ratings (user_id, item_type, item_id, rating) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
    $success = $stmt->execute([$userId, $itemType, $itemId, $rating]);

    if ($success) {
        clearCache("average_rating_{$itemType}_{$itemId}");
    }

    return $success;
}

/**
 * Add an item to user favorites
 */
function addToFavorites($userId, $itemType, $itemId) {
    global $pdo;
    // Check if already in favorites to prevent duplicate errors
    if (isInFavorites($userId, $itemType, $itemId)) {
        return true;
    }
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, item_type, item_id) VALUES (?, ?, ?)");
    $success = $stmt->execute([$userId, $itemType, $itemId]);

    if ($success) {
        clearCache("favorites_{$userId}");
    }

    return $success;
}

/**
 * Remove an item from user favorites
 */
function removeFromFavorites($userId, $itemType, $itemId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?");
    $success = $stmt->execute([$userId, $itemType, $itemId]);

    if ($success) {
        clearCache("favorites_{$userId}");
    }

    return $success;
}

/**
 * Add an item to user listening history
 */
function addToRecentlyPlayed($userId, $itemType, $itemId) {
    if (empty($userId) || $userId <= 0) {
        return false;
    }
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO recently_played (user_id, item_type, item_id) VALUES (?, ?, ?)");
    $success = $stmt->execute([$userId, $itemType, $itemId]);

    if ($success) {
        clearCache("recently_played_{$userId}_10");
    }

    return $success;
}

/**
 * Get all reviews for an item
 */
function getReviews($itemType, $itemId) {
    global $pdo;
    $cacheKey = "reviews_{$itemType}_{$itemId}";

    return getCachedData($cacheKey, function() use ($pdo, $itemType, $itemId) {
        $stmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.item_type = ? AND r.item_id = ? ORDER BY r.created_at DESC");
        $stmt->execute([$itemType, $itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    });
}

/**
 * Get average star rating and total vote count
 */
function getAverageRating($itemType, $itemId) {
    global $pdo;
    $cacheKey = "average_rating_{$itemType}_{$itemId}";

    return getCachedData($cacheKey, function() use ($pdo, $itemType, $itemId) {
        $stmt = $pdo->prepare("SELECT AVG(rating) as average_rating, COUNT(*) as rating_count FROM ratings WHERE item_type = ? AND item_id = ?");
        $stmt->execute([$itemType, $itemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'average_rating' => $row['average_rating'] !== null ? (float)$row['average_rating'] : 0.0,
            'rating_count'   => (int)($row['rating_count'] ?? 0)
        ];
    });
}

/**
 * Check if an item is favorited by the user
 */
function isInFavorites($userId, $itemType, $itemId) {
    if (empty($userId) || $userId <= 0) {
        return false;
    }
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?");
    $stmt->execute([$userId, $itemType, $itemId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return !empty($row) && $row['count'] > 0;
}

/**
 * Cache helper
 */
function getCachedData($key, $callback, $ttl = 3600) {
    $cacheDir = __DIR__ . '/../cache/';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
    $cacheFile = $cacheDir . md5($key) . '.cache';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        $content = @file_get_contents($cacheFile);
        if ($content !== false) {
            $data = @unserialize($content);
            if ($data !== false) {
                return $data;
            }
        }
    }

    $data = call_user_func($callback);
    @file_put_contents($cacheFile, serialize($data));
    return $data;
}

function clearCache($key) {
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
    }
}

function clearAllCache() {
    $cacheDir = __DIR__ . '/../cache/';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '*.cache');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}

/**
 * Get recently played items
 */
function getRecentlyPlayed($userId, $limit = 10) {
    if (empty($userId) || $userId <= 0) {
        return [];
    }
    global $pdo;
    $limit = (int)$limit;
    $cacheKey = "recently_played_{$userId}_{$limit}";

    return getCachedData($cacheKey, function() use ($pdo, $userId, $limit) {
        $sql = "SELECT rp.*,
                  CASE WHEN rp.item_type = 'music' THEN m.title ELSE v.title END as title,
                  CASE WHEN rp.item_type = 'music' THEN m.cover ELSE v.thumbnail END as cover,
                  a.name as artist_name
           FROM recently_played rp
           LEFT JOIN music m ON rp.item_type = 'music' AND rp.item_id = m.id
           LEFT JOIN videos v ON rp.item_type = 'video' AND rp.item_id = v.id
           LEFT JOIN artists a ON (rp.item_type = 'music' AND m.artist_id = a.id) OR (rp.item_type = 'video' AND v.artist_id = a.id)
           WHERE rp.user_id = ? ORDER BY rp.played_at DESC LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    });
}