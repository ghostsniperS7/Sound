<?php
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get video details
$videoId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($videoId <= 0) {
    header('Location: index.php');
    exit();
}

// Add to recently played
addToRecentlyPlayed($_SESSION['user_id'], 'video', $videoId);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'])) {
    $reviewText = trim($_POST['review_text']);
    if (!empty($reviewText)) {
        addReview($_SESSION['user_id'], 'video', $videoId, $reviewText);
    }
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    if ($rating >= 1 && $rating <= 5) {
        addRating($_SESSION['user_id'], 'video', $videoId, $rating);
    }
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_toggle'])) {
    if (isInFavorites($_SESSION['user_id'], 'video', $videoId)) {
        removeFromFavorites($_SESSION['user_id'], 'video', $videoId);
    } else {
        addToFavorites($_SESSION['user_id'], 'video', $videoId);
    }
}

// Get video details from database
$sql = "SELECT v.*, a.name as artist_name FROM videos v JOIN artists a ON v.artist_id = a.id WHERE v.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $videoId);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();

if (!$video) {
    header('Location: index.php');
    exit();
}

// Get reviews
$reviews = getReviews('video', $videoId);

// Get average rating
$averageRating = getAverageRating('video', $videoId);

// Check if in favorites
$isFavorite = isInFavorites($_SESSION['user_id'], 'video', $videoId);

// Include header
include 'includes/header.php';

?>

<!-- HTML content for video player and features -->
<div class="container">
    <h1><?php echo htmlspecialchars($video['title']); ?></h1>
    <p>Artist: <?php echo htmlspecialchars($video['artist_name']); ?></p>

    <!-- Video player -->
    <div class="video-player">
        <video controls width="100%">
            <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
            Your browser does not support the video element.
        </video>
    </div>

    <!-- Rating and favorite -->
    <div class="rating-favorite">
        <form method="post">
            <div class="rating">
                <span>Rating: <?php echo number_format($averageRating, 1); ?></span>
                <select name="rating" onchange="this.form.submit()">
                    <option value="">Rate this video</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" name="favorite_toggle" class="favorite-btn">
                <?php echo $isFavorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>
            </button>
        </form>
    </div>

    <!-- Reviews section -->
    <div class="reviews">
        <h2>Reviews</h2>
        <?php if (empty($reviews)): ?>
            <p>No reviews yet.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <p><strong><?php echo htmlspecialchars($review['user_name']); ?></strong> - <?php echo date('M j, Y', strtotime($review['created_at'])); ?></p>
                    <p><?php echo htmlspecialchars($review['review_text']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post">
            <textarea name="review_text" placeholder="Write your review here..." required></textarea>
            <button type="submit">Submit Review</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>