<?php
require_once 'includes/functions.php';

// Check if user is logged in
// Allow guest access to music page


// Get music details
$musicId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($musicId <= 0) {
    header('Location: index.php');
    exit();
}

// Add to recently played
addToRecentlyPlayed($_SESSION['user_id'], 'music', $musicId);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    $reviewText = trim($_POST['review_text']);
    if (!empty($reviewText)) {
        addReview($_SESSION['user_id'], 'music', $musicId, $reviewText);
    }
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    $rating = intval($_POST['rating']);
    if ($rating >= 1 && $rating <= 5) {
        addRating($_SESSION['user_id'], 'music', $musicId, $rating);
    }
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_toggle'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    if (isInFavorites($_SESSION['user_id'], 'music', $musicId)) {
        removeFromFavorites($_SESSION['user_id'], 'music', $musicId);
    } else {
        addToFavorites($_SESSION['user_id'], 'music', $musicId);
    }
}

// Get music details from database
$sql = "SELECT m.*, a.name as artist_name FROM music m JOIN artists a ON m.artist_id = a.id WHERE m.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $musicId);
$stmt->execute();
$music = $stmt->get_result()->fetch_assoc();

if (!$music) {
    header('Location: index.php');
    exit();
}

// Get reviews
$reviews = getReviews('music', $musicId);

// Get average rating
$averageRating = getAverageRating('music', $musicId);

// Check if in favorites
$isFavorite = isInFavorites($_SESSION['user_id'], 'music', $musicId);

// Include header
include 'includes/header.php';

?>

<!-- HTML content for music player and features -->
<div class="container">
    <h1><?php echo htmlspecialchars($music['title']); ?></h1>
    <p>Artist: <?php echo htmlspecialchars($music['artist_name']); ?></p>

    <!-- Music player -->
    <div class="music-player">
        <audio controls>
            <source src="<?php echo htmlspecialchars($music['file_path']); ?>" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
    </div>

    <!-- Rating and favorite -->
    <div class="rating-favorite">
        <form method="post">
            <div class="rating">
                <span>Rating: <?php echo number_format($averageRating, 1); ?></span>
                <select name="rating" onchange="this.form.submit()">
                    <option value="">Rate this song</option>
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