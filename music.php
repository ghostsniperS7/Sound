<?php
require_once 'includes/functions.php';

// Music ID validation
$musicId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($musicId <= 0) {
    header('Location: index.php');
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? intval($_SESSION['user_id']) : null;

// Handle review submission (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'])) {
    if (!$isLoggedIn) {
        setFlash('error', 'Please login to write a review.');
        header('Location: login.php');
        exit();
    }
    $reviewText = trim($_POST['review_text']);
    if (!empty($reviewText)) {
        addReview($userId, 'music', $musicId, $reviewText);
        setFlash('success', 'Your review has been submitted.');
    } else {
        setFlash('error', 'Review text cannot be empty.');
    }
    header("Location: music.php?id={$musicId}");
    exit();
}

// Handle rating submission (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if (!$isLoggedIn) {
        setFlash('error', 'Please login to rate this song.');
        header('Location: login.php');
        exit();
    }
    $rating = intval($_POST['rating']);
    if ($rating >= 1 && $rating <= 5) {
        addRating($userId, 'music', $musicId, $rating);
        setFlash('success', "You rated this song {$rating}/5 stars.");
    }
    header("Location: music.php?id={$musicId}");
    exit();
}

// Handle favorite toggle (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_toggle'])) {
    if (!$isLoggedIn) {
        setFlash('error', 'Please login to save favorites.');
        header('Location: login.php');
        exit();
    }
    if (isInFavorites($userId, 'music', $musicId)) {
        removeFromFavorites($userId, 'music', $musicId);
        setFlash('info', 'Removed from your favorites.');
    } else {
        addToFavorites($userId, 'music', $musicId);
        setFlash('success', 'Added to your favorites.');
    }
    header("Location: music.php?id={$musicId}");
    exit();
}

// Get music details from database via PDO
$music = fetchOne("SELECT m.*, a.name as artist_name FROM music m JOIN artists a ON m.artist_id = a.id WHERE m.id = ?", [$musicId]);

if (!$music) {
    setFlash('error', 'Song not found.');
    header('Location: index.php');
    exit();
}

// Add to recently played for logged-in users
if ($isLoggedIn) {
    addToRecentlyPlayed($userId, 'music', $musicId);
}

// Get reviews and average rating
$reviews = getReviews('music', $musicId);
$ratingData = getAverageRating('music', $musicId);
$averageRating = isset($ratingData['average_rating']) ? (float)$ratingData['average_rating'] : 0.0;
$ratingCount = isset($ratingData['rating_count']) ? intval($ratingData['rating_count']) : 0;

// Check if in favorites
$isFavorite = $isLoggedIn ? isInFavorites($userId, 'music', $musicId) : false;

// Include header
include 'includes/header.php';
?>

<div class="container" style="max-width: 900px; padding: 24px 0;">
    <div style="display: flex; gap: 32px; align-items: flex-start; margin-bottom: 32px; flex-wrap: wrap;">
        <div style="width: 220px; height: 220px; flex-shrink: 0; border-radius: 8px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.5);">
            <?php echo render_cover_tile($music['title'], $music['cover'], $music['title'], '', 'style="width: 100%; height: 100%; object-fit: cover;"'); ?>
        </div>
        
        <div style="flex: 1; min-width: 280px;">
            <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: var(--accent); font-weight: 700; margin-bottom: 6px;">Track</div>
            <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 8px;"><?php echo htmlspecialchars($music['title']); ?></h1>
            <p style="font-size: 16px; color: var(--text-muted); margin-bottom: 16px;">
                By <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($music['artist_name']); ?></strong>
            </p>

            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted);">
                    <span style="color: #FFB800; font-weight: 700;">★ <?php echo number_format($averageRating, 1); ?></span>
                    <span>(<?php echo $ratingCount; ?> <?php echo $ratingCount === 1 ? 'vote' : 'votes'; ?>)</span>
                </div>
                
                <button class="btn btn-primary play-btn" data-type="music" data-id="<?php echo $music['id']; ?>" data-title="<?php echo htmlspecialchars($music['title']); ?>" data-artist="<?php echo htmlspecialchars($music['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($music['cover'])); ?>" data-url="<?php echo htmlspecialchars(asset_url($music['file_path'])); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5V19L19 12L8 5Z"/>
                    </svg>
                    Play Now
                </button>

                <form method="post" style="display: inline-block;">
                    <button type="submit" name="favorite_toggle" class="btn <?php echo $isFavorite ? 'btn-secondary' : 'btn-ghost'; ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $isFavorite ? 'var(--accent)' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <?php echo $isFavorite ? 'Favorited' : 'Add to Favorites'; ?>
                    </button>
                </form>
            </div>

            <!-- Native Audio Player Fallback -->
            <div style="background: var(--bg-surface); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                <audio controls style="width: 100%;">
                    <source src="<?php echo htmlspecialchars(asset_url($music['file_path'])); ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
        </div>
    </div>

    <!-- Rating Section -->
    <div style="background: var(--bg-surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 24px;">
        <h3 style="margin-bottom: 12px; font-size: 16px;">Rate this Track</h3>
        <form method="post" style="display: flex; align-items: center; gap: 12px; max-width: 320px;">
            <select name="rating" onchange="this.form.submit()" style="flex: 1;">
                <option value="">Select a rating (1 - 5 stars)</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?php echo $i; ?>"><?php echo str_repeat('★', $i) . " ({$i} star" . ($i > 1 ? 's' : '') . ")"; ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <!-- Reviews section -->
    <div style="background: var(--bg-surface); padding: 24px; border-radius: 8px; border: 1px solid var(--border-color);">
        <h2 style="font-size: 20px; margin-bottom: 16px;">Reviews & Discussion</h2>
        
        <form method="post" style="margin-bottom: 28px;">
            <div class="form-group">
                <textarea name="review_text" placeholder="<?php echo $isLoggedIn ? 'Write an honest review about this track...' : 'Please login to leave a review...'; ?>" <?php echo $isLoggedIn ? 'required' : 'disabled'; ?> style="min-height: 80px;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" <?php echo $isLoggedIn ? '' : 'disabled'; ?>>
                Submit Review
            </button>
            <?php if (!$isLoggedIn): ?>
                <span style="font-size: 13px; color: var(--text-muted); margin-left: 12px;">
                    <a href="login.php" style="color: var(--accent); text-decoration: underline;">Login</a> to post a review
                </span>
            <?php endif; ?>
        </form>

        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <p style="color: var(--text-subtle);">No reviews yet. Be the first to share your thoughts!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div style="padding: 14px; background: var(--bg-elevated); border-radius: 6px; margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <strong style="color: var(--text-primary); font-size: 14px;"><?php echo htmlspecialchars($review['user_name']); ?></strong>
                            <span style="font-size: 12px; color: var(--text-subtle);"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <p style="margin: 0; color: var(--text-muted); font-size: 14px; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>