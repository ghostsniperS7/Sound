<?php
$page_title = 'Liked Songs & Videos';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$isLoggedIn = isLoggedIn();
$userId = $isLoggedIn ? intval($_SESSION['user_id']) : null;

// Handle Unlike POST request (PRG pattern with CSRF protection)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unlike') {
    if (!$isLoggedIn) {
        setFlash('error', 'Please login to manage your liked items.');
        header('Location: /sound/login.php');
        exit;
    }

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token. Please refresh and try again.');
        header('Location: /sound/liked.php');
        exit;
    }

    $itemType = trim($_POST['item_type'] ?? '');
    $itemId = intval($_POST['item_id'] ?? 0);

    if (in_array($itemType, ['music', 'video'], true) && $itemId > 0) {
        removeFromFavorites($userId, $itemType, $itemId);
        setFlash('success', 'Removed from your liked items.');
    }

    header('Location: /sound/liked.php');
    exit;
}

// Fetch user's liked items if logged in
$likedMusic = [];
$likedVideos = [];

if ($isLoggedIn) {
    $likedMusic = fetchAll("
        SELECT m.*, m.created_at as item_created_at, a.name as artist_name, f.created_at as liked_at
        FROM favorites f
        JOIN music m ON f.item_id = m.id
        JOIN artists a ON m.artist_id = a.id
        WHERE f.user_id = ? AND f.item_type = 'music'
        ORDER BY f.created_at DESC
    ", [$userId]);

    $likedVideos = fetchAll("
        SELECT v.*, v.created_at as item_created_at, a.name as artist_name, f.created_at as liked_at
        FROM favorites f
        JOIN videos v ON f.item_id = v.id
        JOIN artists a ON v.artist_id = a.id
        WHERE f.user_id = ? AND f.item_type = 'video'
        ORDER BY f.created_at DESC
    ", [$userId]);
}

$hasLikes = (!empty($likedMusic) || !empty($likedVideos));

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding-bottom: 40px;">
    <!-- Page Header -->
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 28px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">Liked Collection</h1>
        <p style="color: var(--text-muted); font-size: 15px; margin: 0;">Your personal collection of favorite music tracks and videos.</p>
    </div>

    <?php if (!$isLoggedIn): ?>
        <!-- Friendly Guest State Card -->
        <div class="card" style="cursor: default; max-width: 580px; margin: 40px auto; padding: 48px 36px; text-align: center; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-pill); background: var(--accent-subtle); color: var(--accent); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">Login to see your liked songs</h2>
            <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 28px; max-width: 440px; margin-left: auto; margin-right: auto;">
                Save tracks and videos to your private collection and access them anytime across all your devices.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="/sound/login.php" class="btn btn-primary" style="padding: 10px 28px; font-weight: 700;">Login</a>
                <a href="/sound/register.php" class="btn btn-secondary" style="padding: 10px 24px;">Create an account</a>
            </div>
        </div>

    <?php else: ?>
        <!-- Logged-in Empty State Card (Requirement 1c: Shown when list is or becomes empty) -->
        <div id="liked-empty-state" class="card" style="cursor: default; max-width: 580px; margin: 40px auto; padding: 48px 36px; text-align: center; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); <?php echo $hasLikes ? 'display: none;' : ''; ?>">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-pill); background: var(--bg-elevated); color: var(--text-subtle); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <h2 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">You have not liked anything yet</h2>
            <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 24px; max-width: 420px; margin-left: auto; margin-right: auto;">
                Explore music tracks and videos, then click the heart icon on any song or video to build your favorites list.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <a href="/sound/browse.php" class="btn btn-primary" style="padding: 10px 24px;">Browse Music & Videos</a>
            </div>
        </div>

        <!-- Logged-in Populated State Container -->
        <div id="liked-populated-state" style="<?php echo $hasLikes ? '' : 'display: none;'; ?>">
        <!-- Logged-in Populated State -->
        <?php if (!empty($likedMusic)): ?>
            <div id="liked-music-section" class="liked-section" style="margin-bottom: 36px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                        <span>Liked Music</span>
                        <span style="font-size: 12px; background: var(--bg-elevated); color: var(--text-muted); padding: 2px 8px; border-radius: var(--radius-pill); font-weight: 600;"><?php echo count($likedMusic); ?></span>
                    </h2>
                </div>

                <div class="card-grid">
                    <?php foreach ($likedMusic as $track): ?>
                        <div class="card" data-track-id="<?php echo $track['id']; ?>" data-track-url="<?php echo htmlspecialchars(asset_url($track['file_path'])); ?>" data-track-title="<?php echo htmlspecialchars($track['title']); ?>" data-track-artist="<?php echo htmlspecialchars($track['artist_name']); ?>" data-track-cover="<?php echo htmlspecialchars(asset_url($track['cover'] ?? '')); ?>">
                            <div class="card-cover">
                                <?php echo render_cover_tile($track['title'], $track['cover'] ?? '', $track['title']); ?>
                                <?php if (isNewItem($track['item_created_at'] ?? $track['created_at'] ?? '')): ?>
                                    <span class="badge-new">NEW</span>
                                <?php endif; ?>
                                <?php echo render_like_button('music', $track['id'], true, 'card-like-btn'); ?>
                                <div class="card-play-overlay">
                                    <button class="btn-round-play play-btn" data-type="music" data-id="<?php echo $track['id']; ?>" data-title="<?php echo htmlspecialchars($track['title']); ?>" data-artist="<?php echo htmlspecialchars($track['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($track['cover'] ?? '')); ?>" data-url="<?php echo htmlspecialchars(asset_url($track['file_path'])); ?>" title="Play">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5V19L19 12L8 5Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="card-content" style="flex: 1;">
                                <h3 class="card-title">
                                    <a href="/sound/music.php?id=<?php echo $track['id']; ?>" data-action="view-details"><?php echo htmlspecialchars($track['title']); ?></a>
                                </h3>
                                <p class="card-artist"><?php echo htmlspecialchars($track['artist_name']); ?></p>
                                <a href="/sound/music.php?id=<?php echo $track['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                            </div>

                            <!-- Unlike Action Form -->
                            <form method="post" action="/sound/liked.php" style="margin-top: 10px;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="action" value="unlike">
                                <input type="hidden" name="item_type" value="music">
                                <input type="hidden" name="item_id" value="<?php echo $track['id']; ?>">
                                <button type="button" class="btn btn-secondary btn-sm" data-action="toggle-like" data-item-type="music" data-item-id="<?php echo $track['id']; ?>" style="width: 100%; font-size: 12px; padding: 6px 10px; display: flex; align-items: center; justify-content: center; gap: 6px;" title="Remove from Liked">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--accent)" stroke="var(--accent)" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                    <span>Unlike</span>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($likedVideos)): ?>
            <div id="liked-video-section" class="liked-section" style="margin-bottom: 36px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                        <span>Liked Videos</span>
                        <span style="font-size: 12px; background: var(--bg-elevated); color: var(--text-muted); padding: 2px 8px; border-radius: var(--radius-pill); font-weight: 600;"><?php echo count($likedVideos); ?></span>
                    </h2>
                </div>

                <div class="card-grid">
                    <?php foreach ($likedVideos as $video): ?>
                        <div class="card">
                            <div class="card-cover">
                                <?php echo render_cover_tile($video['title'], $video['thumbnail'] ?? '', $video['title']); ?>
                                <?php if (isNewItem($video['item_created_at'] ?? $video['created_at'] ?? '')): ?>
                                    <span class="badge-new">NEW</span>
                                <?php endif; ?>
                                <?php echo render_like_button('video', $video['id'], true, 'card-like-btn'); ?>
                                <div class="card-play-overlay">
                                    <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" class="btn-round-play" title="Watch Video">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5V19L19 12L8 5Z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="card-content" style="flex: 1;">
                                <h3 class="card-title">
                                    <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" data-action="view-details"><?php echo htmlspecialchars($video['title']); ?></a>
                                </h3>
                                <p class="card-artist"><?php echo htmlspecialchars($video['artist_name']); ?></p>
                                <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                            </div>

                            <!-- Unlike Action Form -->
                            <form method="post" action="/sound/liked.php" style="margin-top: 10px;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="action" value="unlike">
                                <input type="hidden" name="item_type" value="video">
                                <input type="hidden" name="item_id" value="<?php echo $video['id']; ?>">
                                <button type="button" class="btn btn-secondary btn-sm" data-action="toggle-like" data-item-type="video" data-item-id="<?php echo $video['id']; ?>" style="width: 100%; font-size: 12px; padding: 6px 10px; display: flex; align-items: center; justify-content: center; gap: 6px;" title="Remove from Liked">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--accent)" stroke="var(--accent)" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                    <span>Unlike</span>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        </div><!-- /#liked-populated-state -->
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
