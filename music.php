<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$isLoggedIn = isLoggedIn();
$userId = $isLoggedIn ? intval($_SESSION['user_id']) : null;
$musicId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// =========================================================================
// DETAIL VIEW (When ?id= is passed)
// =========================================================================
if ($musicId > 0) {
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
                header("Location: /sound/music.php?id={$musicId}");
                exit();
            }

            if ($action === 'add_review') {
                $reviewText = trim($_POST['review_text'] ?? '');
                if (!empty($reviewText)) {
                    addReview($userId, 'music', $musicId, $reviewText);
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

            header("Location: /sound/music.php?id={$musicId}");
            exit();
        }
    }

    // Handle rating submission (PRG with CSRF protection)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
        if (!$isLoggedIn) {
            setFlash('error', 'Please login to rate this song.');
            header('Location: /sound/login.php');
            exit();
        }
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token.');
            header("Location: /sound/music.php?id={$musicId}");
            exit();
        }
        $rating = intval($_POST['rating']);
        if ($rating >= 1 && $rating <= 5) {
            addRating($userId, 'music', $musicId, $rating);
            setFlash('success', "You rated this song {$rating}/5 stars.");
        }
        header("Location: /sound/music.php?id={$musicId}");
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
            header("Location: /sound/music.php?id={$musicId}");
            exit();
        }
        if (isInFavorites($userId, 'music', $musicId)) {
            removeFromFavorites($userId, 'music', $musicId);
            setFlash('info', 'Removed from your liked songs.');
        } else {
            addToFavorites($userId, 'music', $musicId);
            setFlash('success', 'Added to your liked songs.');
        }
        header("Location: /sound/music.php?id={$musicId}");
        exit();
    }

    // Fetch song details
    $music = fetchOne("
        SELECT m.*, a.name as artist_name, g.name as genre_name, l.name as language_name, y.year as release_year, al.title as album_title
        FROM music m
        JOIN artists a ON m.artist_id = a.id
        LEFT JOIN genres g ON m.genre_id = g.id
        LEFT JOIN languages l ON m.language_id = l.id
        LEFT JOIN years y ON m.year_id = y.id
        LEFT JOIN albums al ON m.album_id = al.id
        WHERE m.id = ?
    ", [$musicId]);

    if (!$music) {
        setFlash('error', 'Song not found.');
        header('Location: /sound/music.php');
        exit();
    }

    // Add to listening history for logged-in users
    if ($isLoggedIn) {
        addToRecentlyPlayed($userId, 'music', $musicId);
    }

    $reviews = getReviews('music', $musicId);
    $ratingData = getAverageRating('music', $musicId);
    $averageRating = isset($ratingData['average_rating']) ? (float)$ratingData['average_rating'] : 0.0;
    $ratingCount = isset($ratingData['rating_count']) ? intval($ratingData['rating_count']) : 0;
    $isFavorite = $isLoggedIn ? isInFavorites($userId, 'music', $musicId) : false;
    $userRating = $isLoggedIn ? getUserRating($userId, 'music', $musicId) : 0;

    $page_title = $music['title'] . ' - ' . $music['artist_name'];
    require_once __DIR__ . '/includes/header.php';
    ?>

    <div class="container" style="max-width: 1000px; margin: 0 auto; padding-bottom: 50px;">
        <!-- Navigation Back Link -->
        <div style="margin-bottom: 20px;">
            <a href="/sound/music.php" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to Music Library</span>
            </a>
        </div>

        <!-- Track Hero Card -->
        <div class="card" style="cursor: default; padding: 32px; margin-bottom: 28px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
            <div style="display: flex; gap: 32px; align-items: flex-start; flex-wrap: wrap;">
                <div style="width: 220px; height: 220px; flex-shrink: 0; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-card); background: var(--bg-elevated);">
                    <?php echo render_cover_tile($music['title'], $music['cover'] ?? '', $music['title'], '', 'style="width: 100%; height: 100%; object-fit: cover;"'); ?>
                </div>

                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: var(--accent); font-weight: 700; background: var(--accent-subtle); padding: 2px 8px; border-radius: var(--radius-pill);">
                            Track
                        </span>
                        <?php if (!empty($music['genre_name'])): ?>
                            <span style="font-size: 12px; color: var(--text-muted); background: var(--bg-elevated); padding: 2px 10px; border-radius: var(--radius-pill); font-weight: 600;">
                                <?php echo htmlspecialchars($music['genre_name']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 style="font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">
                        <?php echo htmlspecialchars($music['title']); ?>
                    </h1>

                    <p style="font-size: 16px; color: var(--text-muted); margin-bottom: 16px;">
                        By <a href="/sound/browse.php?artist=<?php echo $music['artist_id']; ?>" style="color: var(--text-primary); font-weight: 700; text-decoration: underline;"><?php echo htmlspecialchars($music['artist_name']); ?></a>
                        <?php if (!empty($music['album_title'])): ?>
                            &bull; Album: <span style="color: var(--text-primary);"><?php echo htmlspecialchars($music['album_title']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($music['release_year'])): ?>
                            &bull; <span><?php echo htmlspecialchars($music['release_year']); ?></span>
                        <?php endif; ?>
                    </p>

                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 22px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted); background: var(--bg-elevated); padding: 6px 14px; border-radius: var(--radius-pill);">
                            <span style="color: #FFB800; font-weight: 700;">★ <?php echo number_format($averageRating, 1); ?></span>
                            <span>(<?php echo $ratingCount; ?> <?php echo $ratingCount === 1 ? 'vote' : 'votes'; ?>)</span>
                        </div>

                        <button class="btn btn-primary play-btn" data-type="music" data-id="<?php echo $music['id']; ?>" data-title="<?php echo htmlspecialchars($music['title']); ?>" data-artist="<?php echo htmlspecialchars($music['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($music['cover'])); ?>" data-url="<?php echo htmlspecialchars(asset_url($music['file_path'])); ?>" style="padding: 10px 24px; font-weight: 700;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 5V19L19 12L8 5Z"/>
                            </svg>
                            <span>Play Now</span>
                        </button>

                        <form method="post" action="/sound/music.php?id=<?php echo $musicId; ?>" style="display: inline-block;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="favorite_toggle" value="1">
                            <button type="button" class="btn <?php echo $isFavorite ? 'btn-secondary' : 'btn-ghost'; ?> like-btn hero-like-btn<?php echo $isFavorite ? ' liked' : ''; ?>" data-action="toggle-like" data-item-type="music" data-item-id="<?php echo $musicId; ?>" aria-label="<?php echo $isFavorite ? 'Remove from favorites' : 'Add to favorites'; ?>" title="<?php echo $isFavorite ? 'Unlike' : 'Like'; ?>" style="gap: 8px;">
                                <svg class="icon-heart-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                <svg class="icon-heart-filled" width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)" stroke="var(--accent)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                <span><?php echo $isFavorite ? 'Favorited' : 'Add to Favorites'; ?></span>
                            </button>
                        </form>
                    </div>

                    <!-- Native Audio Player Preview -->
                    <div style="background: var(--bg-elevated); padding: 14px 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Preview Player</div>
                        <audio controls style="width: 100%;">
                            <source src="<?php echo htmlspecialchars(asset_url($music['file_path'])); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Card -->
        <div class="card" style="cursor: default; padding: 24px 28px; margin-bottom: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Rate this Track</h3>
                <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">
                    <?php if ($userRating > 0): ?>
                        You rated this <span style="color: #FFB800; font-weight: 700;">★ <?php echo $userRating; ?>/5</span>. Change your rating anytime:
                    <?php else: ?>
                        Share your rating with the community (1 - 5 stars)
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($isLoggedIn): ?>
                <form method="post" action="/sound/music.php?id=<?php echo $musicId; ?>" style="display: flex; align-items: center; gap: 10px;">
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

            <form method="post" action="/sound/music.php?id=<?php echo $musicId; ?>" style="margin-bottom: 30px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action" value="add_review">
                <div class="form-group" style="margin-bottom: 14px;">
                    <textarea name="review_text" placeholder="<?php echo $isLoggedIn ? 'Write your review about this song...' : 'Please login to leave a review...'; ?>" <?php echo $isLoggedIn ? 'required' : 'disabled'; ?> style="min-height: 90px;"></textarea>
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
                        No reviews yet. Be the first to share your thoughts on this track!
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
                                            <form method="post" action="/sound/music.php?id=<?php echo $musicId; ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete your review?');">
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
                                    <form id="review-edit-form-<?php echo $rev['id']; ?>" method="post" action="/sound/music.php?id=<?php echo $musicId; ?>" style="display: none; margin-top: 10px;">
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
$page_title = 'Music Library';

$allMusic = fetchAll("
    SELECT m.*, a.name as artist_name, g.name as genre_name, y.year as release_year
    FROM music m
    JOIN artists a ON m.artist_id = a.id
    LEFT JOIN genres g ON m.genre_id = g.id
    LEFT JOIN years y ON m.year_id = y.id
    ORDER BY m.title ASC
");

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding-bottom: 50px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px; display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">Music Library</h1>
            <p style="color: var(--text-muted); font-size: 15px; margin: 0;">Explore and stream our complete collection of songs.</p>
        </div>
        <div>
            <a href="/sound/browse.php" class="btn btn-secondary btn-sm">Browse by Category</a>
        </div>
    </div>

    <!-- Music Grid Section -->
    <section class="browse-section">
        <div class="browse-section-header">
            <h2 class="browse-section-title">
                <span>All Tracks</span>
                <span class="browse-count"><?php echo count($allMusic); ?></span>
            </h2>
        </div>

        <?php if (empty($allMusic)): ?>
            <div class="empty-state-card">
                <p style="margin: 0; font-size: 15px; font-weight: 600;">No music tracks uploaded yet.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($allMusic as $track): 
                    $isLiked = !empty($userLikedIds['music'][$track['id']]);
                ?>
                    <div class="card" data-track-id="<?php echo $track['id']; ?>" data-track-url="<?php echo htmlspecialchars(asset_url($track['file_path'])); ?>" data-track-title="<?php echo htmlspecialchars($track['title']); ?>" data-track-artist="<?php echo htmlspecialchars($track['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($track['cover'] ?? '')); ?>">
                        <div class="card-cover">
                            <?php echo render_cover_tile($track['title'], $track['cover'] ?? '', $track['title']); ?>
                            <?php if (isNewItem($track['created_at'] ?? '')): ?>
                                <span class="badge-new">NEW</span>
                            <?php endif; ?>
                            <?php echo render_like_button('music', $track['id'], $isLiked, 'card-like-btn'); ?>
                            <div class="card-play-overlay">
                                <button class="btn-round-play play-btn" data-type="music" data-id="<?php echo $track['id']; ?>" data-title="<?php echo htmlspecialchars($track['title']); ?>" data-artist="<?php echo htmlspecialchars($track['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($track['cover'] ?? '')); ?>" data-url="<?php echo htmlspecialchars(asset_url($track['file_path'])); ?>" title="Play Track">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5V19L19 12L8 5Z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="/sound/music.php?id=<?php echo $track['id']; ?>" data-action="view-details"><?php echo htmlspecialchars($track['title']); ?></a>
                            </h3>
                            <p class="card-artist">
                                <a href="/sound/browse.php?artist=<?php echo $track['artist_id']; ?>"><?php echo htmlspecialchars($track['artist_name']); ?></a>
                            </p>
                            <?php if (!empty($track['genre_name'])): ?>
                                <p class="card-subtitle" style="font-size: 12px; margin-top: 3px; color: var(--text-subtle);"><?php echo htmlspecialchars($track['genre_name']); ?></p>
                            <?php endif; ?>
                            <a href="/sound/music.php?id=<?php echo $track['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>