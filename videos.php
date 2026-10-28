<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$isLoggedIn = isLoggedIn();
$userId = $isLoggedIn ? intval($_SESSION['user_id']) : null;
$videoId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// =========================================================================
// DETAIL VIEW (When ?id= is passed)
// =========================================================================
if ($videoId > 0) {
    // Handle review submission / edit / delete (PRG with CSRF protection)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['review_text']) || isset($_POST['action']))) {
        $action = $_POST['action'] ?? 'add_review';

        if (in_array($action, ['add_review', 'edit_review', 'delete_review'], true)) {
            if (!$isLoggedIn) {
                setFlash('error', 'Please login to manage reviews.');
                header('Location: /sound/login.php');
                exit();
            }
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlash('error', 'Invalid security token. Please try again.');
                header("Location: /sound/videos.php?id={$videoId}");
                exit();
            }

            if ($action === 'add_review') {
                $reviewText = trim($_POST['review_text'] ?? '');
                if (!empty($reviewText)) {
                    addReview($userId, 'video', $videoId, $reviewText);
                    setFlash('success', 'Your review has been submitted.');
                } else {
                    setFlash('error', 'Review text cannot be empty.');
                }
            } elseif ($action === 'edit_review') {
                $reviewId = intval($_POST['review_id'] ?? 0);
                $reviewText = trim($_POST['review_text'] ?? '');
                if ($reviewId > 0 && !empty($reviewText)) {
                    if (updateReview($reviewId, $userId, $reviewText)) {
                        setFlash('success', 'Your review has been updated.');
                    } else {
                        setFlash('error', 'Unable to update review.');
                    }
                } else {
                    setFlash('error', 'Review text cannot be empty.');
                }
            } elseif ($action === 'delete_review') {
                $reviewId = intval($_POST['review_id'] ?? 0);
                if ($reviewId > 0) {
                    if (deleteReview($reviewId, $userId)) {
                        setFlash('success', 'Your review has been deleted.');
                    } else {
                        setFlash('error', 'Unable to delete review.');
                    }
                }
            }

            header("Location: /sound/videos.php?id={$videoId}");
            exit();
        }
    }

    // Handle rating submission (PRG with CSRF protection)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
        if (!$isLoggedIn) {
            setFlash('error', 'Please login to rate this video.');
            header('Location: /sound/login.php');
            exit();
        }
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token.');
            header("Location: /sound/videos.php?id={$videoId}");
            exit();
        }
        $rating = intval($_POST['rating']);
        if ($rating >= 1 && $rating <= 5) {
            addRating($userId, 'video', $videoId, $rating);
            setFlash('success', "You rated this video {$rating}/5 stars.");
        }
        header("Location: /sound/videos.php?id={$videoId}");
        exit();
    }

    // Handle favorite toggle (PRG with CSRF protection)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_toggle'])) {
        if (!$isLoggedIn) {
            setFlash('error', 'Please login to save favorites.');
            header('Location: /sound/login.php');
            exit();
        }
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token.');
            header("Location: /sound/videos.php?id={$videoId}");
            exit();
        }
        if (isInFavorites($userId, 'video', $videoId)) {
            removeFromFavorites($userId, 'video', $videoId);
            setFlash('info', 'Removed from your liked videos.');
        } else {
            addToFavorites($userId, 'video', $videoId);
            setFlash('success', 'Added to your liked videos.');
        }
        header("Location: /sound/videos.php?id={$videoId}");
        exit();
    }

    // Fetch video details
    $video = fetchOne("
        SELECT v.*, a.name as artist_name, g.name as genre_name, l.name as language_name, y.year as release_year, al.title as album_title
        FROM videos v
        JOIN artists a ON v.artist_id = a.id
        LEFT JOIN genres g ON v.genre_id = g.id
        LEFT JOIN languages l ON v.language_id = l.id
        LEFT JOIN years y ON v.year_id = y.id
        LEFT JOIN albums al ON v.album_id = al.id
        WHERE v.id = ?
    ", [$videoId]);

    if (!$video) {
        setFlash('error', 'Video not found.');
        header('Location: /sound/videos.php');
        exit();
    }

    // Add to listening history for logged-in users
    if ($isLoggedIn) {
        addToRecentlyPlayed($userId, 'video', $videoId);
    }

    $reviews = getReviews('video', $videoId);
    $ratingData = getAverageRating('video', $videoId);
    $averageRating = isset($ratingData['average_rating']) ? (float)$ratingData['average_rating'] : 0.0;
    $ratingCount = isset($ratingData['rating_count']) ? intval($ratingData['rating_count']) : 0;
    $isFavorite = $isLoggedIn ? isInFavorites($userId, 'video', $videoId) : false;
    $userRating = $isLoggedIn ? getUserRating($userId, 'video', $videoId) : 0;

    $page_title = $video['title'] . ' - ' . $video['artist_name'];
    require_once __DIR__ . '/includes/header.php';
    ?>

    <div class="container" style="max-width: 1000px; margin: 0 auto; padding-bottom: 50px;">
        <!-- Navigation Back Link -->
        <div style="margin-bottom: 20px;">
            <a href="/sound/videos.php" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to Video Library</span>
            </a>
        </div>

        <!-- Video Player Card -->
        <div class="card" style="cursor: default; padding: 0; margin-bottom: 24px; border: 1px solid var(--border-color); background: #000; border-radius: var(--radius-card); overflow: hidden; box-shadow: var(--shadow-card);">
            <video controls style="width: 100%; max-height: 540px; display: block;" <?php if (!empty($video['thumbnail']) && asset_exists($video['thumbnail'])): ?>poster="<?php echo htmlspecialchars(asset_url($video['thumbnail'])); ?>"<?php endif; ?>>
                <source src="<?php echo htmlspecialchars(asset_url($video['file_path'])); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <!-- Video Header Info Card -->
        <div class="card" style="cursor: default; padding: 24px 28px; margin-bottom: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div style="flex: 1; min-width: 260px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: var(--accent); font-weight: 700; background: var(--accent-subtle); padding: 2px 8px; border-radius: var(--radius-pill);">
                        Music Video
                    </span>
                    <?php if (!empty($video['genre_name'])): ?>
                        <span style="font-size: 12px; color: var(--text-muted); background: var(--bg-elevated); padding: 2px 10px; border-radius: var(--radius-pill); font-weight: 600;">
                            <?php echo htmlspecialchars($video['genre_name']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 style="font-size: 28px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">
                    <?php echo htmlspecialchars($video['title']); ?>
                </h1>

                <p style="font-size: 15px; color: var(--text-muted); margin: 0;">
                    By <a href="/sound/browse.php?artist=<?php echo $video['artist_id']; ?>" style="color: var(--text-primary); font-weight: 700; text-decoration: underline;"><?php echo htmlspecialchars($video['artist_name']); ?></a>
                    <?php if (!empty($video['release_year'])): ?>
                        &bull; <span><?php echo htmlspecialchars($video['release_year']); ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted); background: var(--bg-elevated); padding: 6px 14px; border-radius: var(--radius-pill);">
                    <span style="color: #FFB800; font-weight: 700;">★ <?php echo number_format($averageRating, 1); ?></span>
                    <span>(<?php echo $ratingCount; ?> <?php echo $ratingCount === 1 ? 'vote' : 'votes'; ?>)</span>
                </div>

                <form method="post" action="/sound/videos.php?id=<?php echo $videoId; ?>" style="display: inline-block;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="favorite_toggle" value="1">
                    <button type="button" class="btn <?php echo $isFavorite ? 'btn-secondary' : 'btn-ghost'; ?> like-btn hero-like-btn<?php echo $isFavorite ? ' liked' : ''; ?>" data-action="toggle-like" data-item-type="video" data-item-id="<?php echo $videoId; ?>" aria-label="<?php echo $isFavorite ? 'Remove from favorites' : 'Add to favorites'; ?>" title="<?php echo $isFavorite ? 'Unlike' : 'Like'; ?>" style="gap: 8px;">
                        <svg class="icon-heart-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <svg class="icon-heart-filled" width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)" stroke="var(--accent)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span><?php echo $isFavorite ? 'Favorited' : 'Add to Favorites'; ?></span>
                    </button>
                </form>
            </div>
        </div>

        <?php if (!empty($video['description'])): ?>
            <!-- Description Card -->
            <div class="card" style="cursor: default; padding: 22px 28px; margin-bottom: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">About this Video</h3>
                <p style="margin: 0; color: var(--text-primary); font-size: 14.5px; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Rate Video Card -->
        <div class="card" style="cursor: default; padding: 24px 28px; margin-bottom: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Rate this Video</h3>
                <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">
                    <?php if ($userRating > 0): ?>
                        You rated this <span style="color: #FFB800; font-weight: 700;">★ <?php echo $userRating; ?>/5</span>. Change your rating anytime:
                    <?php else: ?>
                        Share your star rating with fellow viewers (1 - 5 stars)
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($isLoggedIn): ?>
                <form method="post" action="/sound/videos.php?id=<?php echo $videoId; ?>" style="display: flex; align-items: center; gap: 10px;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <select name="rating" onchange="this.form.submit()" style="min-width: 200px; padding: 8px 12px; font-size: 13.5px;">
                        <option value="">Select rating (1 - 5 stars)</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($userRating === $i) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('★', $i) . " ({$i} star" . ($i > 1 ? 's' : '') . ")"; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </form>
            <?php else: ?>
                <div>
                    <a href="/sound/login.php" class="btn btn-secondary btn-sm">Login to rate</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reviews & Discussion Section -->
        <div class="card" style="cursor: default; padding: 28px 32px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0;">Reviews & Discussion</h2>
                <span class="browse-count"><?php echo count($reviews); ?></span>
            </div>

            <form method="post" action="/sound/videos.php?id=<?php echo $videoId; ?>" style="margin-bottom: 30px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action" value="add_review">
                <div class="form-group" style="margin-bottom: 14px;">
                    <textarea name="review_text" placeholder="<?php echo $isLoggedIn ? 'Write your thoughts about this video...' : 'Please login to leave a review...'; ?>" <?php echo $isLoggedIn ? 'required' : 'disabled'; ?> style="min-height: 90px;"></textarea>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <button type="submit" class="btn btn-primary" <?php echo $isLoggedIn ? '' : 'disabled'; ?>>
                        Submit Review
                    </button>
                    <?php if (!$isLoggedIn): ?>
                        <span style="font-size: 13.5px; color: var(--text-muted);">
                            <a href="/sound/login.php" style="color: var(--accent); font-weight: 600; text-decoration: underline;">Login</a> to post a review
                        </span>
                    <?php endif; ?>
                </div>
            </form>

            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <p style="color: var(--text-subtle); font-size: 14px; text-align: center; padding: 24px 0; margin: 0;">
                        No reviews yet. Be the first to share your thoughts on this video!
                    </p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($reviews as $rev): 
                            $isOwnReview = $isLoggedIn && ($rev['user_id'] == $userId);
                        ?>
                            <div class="review-item" id="review-<?php echo $rev['id']; ?>" style="padding: 16px 20px; background: var(--bg-elevated); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <strong style="color: var(--text-primary); font-size: 14px;"><?php echo htmlspecialchars($rev['user_name']); ?></strong>
                                        <?php if ($isOwnReview): ?>
                                            <span style="font-size: 10px; background: var(--accent-subtle); color: var(--accent); padding: 1px 6px; border-radius: var(--radius-pill); font-weight: 700;">YOU</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 12px; color: var(--text-subtle);"><?php echo date('M j, Y', strtotime($rev['created_at'])); ?></span>
                                        <?php if ($isOwnReview): ?>
                                            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEditReview(<?php echo $rev['id']; ?>)" style="padding: 2px 8px; font-size: 11px;">Edit</button>
                                            <form method="post" action="/sound/videos.php?id=<?php echo $videoId; ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete your review?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <input type="hidden" name="action" value="delete_review">
                                                <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                                <button type="submit" class="btn btn-ghost btn-sm" style="padding: 2px 8px; font-size: 11px; color: var(--danger);">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div id="review-text-<?php echo $rev['id']; ?>">
                                    <p style="margin: 0; color: var(--text-muted); font-size: 14px; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($rev['review_text'])); ?></p>
                                </div>
                                <?php if ($isOwnReview): ?>
                                    <form id="review-edit-form-<?php echo $rev['id']; ?>" method="post" action="/sound/videos.php?id=<?php echo $videoId; ?>" style="display: none; margin-top: 10px;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="edit_review">
                                        <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                        <textarea name="review_text" required style="min-height: 70px; margin-bottom: 8px; font-size: 13.5px;"><?php echo htmlspecialchars($rev['review_text']); ?></textarea>
                                        <div style="display: flex; gap: 8px;">
                                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditReview(<?php echo $rev['id']; ?>)">Cancel</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function toggleEditReview(id) {
        var textDiv = document.getElementById('review-text-' + id);
        var form = document.getElementById('review-edit-form-' + id);
        if (!textDiv || !form) return;
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            textDiv.style.display = 'none';
        } else {
            form.style.display = 'none';
            textDiv.style.display = 'block';
        }
    }
    </script>

    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// =========================================================================
// CATALOG / LIBRARY VIEW (When no ?id= is passed)
// =========================================================================
$page_title = 'Video Library';

$allVideos = fetchAll("
    SELECT v.*, a.name as artist_name, g.name as genre_name, y.year as release_year
    FROM videos v
    JOIN artists a ON v.artist_id = a.id
    LEFT JOIN genres g ON v.genre_id = g.id
    LEFT JOIN years y ON v.year_id = y.id
    ORDER BY v.title ASC
");

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding-bottom: 50px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px; display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">Video Library</h1>
            <p style="color: var(--text-muted); font-size: 15px; margin: 0;">Watch high definition music videos and performances.</p>
        </div>
        <div>
            <a href="/sound/browse.php" class="btn btn-secondary btn-sm">Browse by Category</a>
        </div>
    </div>

    <!-- Video Grid Section -->
    <section class="browse-section">
        <div class="browse-section-header">
            <h2 class="browse-section-title">
                <span>All Videos</span>
                <span class="browse-count"><?php echo count($allVideos); ?></span>
            </h2>
        </div>

        <?php if (empty($allVideos)): ?>
            <div class="empty-state-card" style="padding: 48px 24px;">
                <div style="width: 56px; height: 56px; border-radius: var(--radius-pill); background: var(--bg-elevated); color: var(--text-subtle); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="23 7 16 12 23 17 23 7"></polygon>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                </div>
                <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">No music videos uploaded yet</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0 auto 20px auto; max-width: 400px;">
                    Check back soon or explore our extensive music catalog in the meantime.
                </p>
                <a href="/sound/music.php" class="btn btn-primary" style="padding: 10px 24px;">Explore Music Library</a>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($allVideos as $video): 
                    $isLiked = !empty($userLikedIds['video'][$video['id']]);
                ?>
                    <div class="card">
                        <div class="card-cover">
                            <?php echo render_cover_tile($video['title'], $video['thumbnail'] ?? '', $video['title']); ?>
                            <?php if (isNewItem($video['created_at'] ?? '')): ?>
                                <span class="badge-new">NEW</span>
                            <?php endif; ?>
                            <?php echo render_like_button('video', $video['id'], $isLiked, 'card-like-btn'); ?>
                            <div class="card-play-overlay">
                                <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" class="btn-round-play" title="Watch Video">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5V19L19 12L8 5Z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" data-action="view-details"><?php echo htmlspecialchars($video['title']); ?></a>
                            </h3>
                            <p class="card-artist">
                                <a href="/sound/browse.php?artist=<?php echo $video['artist_id']; ?>"><?php echo htmlspecialchars($video['artist_name']); ?></a>
                            </p>
                            <?php if (!empty($video['genre_name'])): ?>
                                <p class="card-subtitle" style="font-size: 12px; margin-top: 3px; color: var(--text-subtle);"><?php echo htmlspecialchars($video['genre_name']); ?></p>
                            <?php endif; ?>
                            <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>