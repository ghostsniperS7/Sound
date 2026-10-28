<?php
$page_title = 'Home';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
$isLoggedIn = isLoggedIn();

// Get recently played items if logged in
$recentlyPlayed = [];
if ($isLoggedIn) {
    $recentlyPlayed = getRecentlyPlayed($_SESSION['user_id']);
}

// Get latest music (ORDER BY created_at DESC, LIMIT 5)
$latestMusic = fetchAll("
    SELECT m.*, a.name as artist_name, g.name as genre_name
    FROM music m
    JOIN artists a ON m.artist_id = a.id
    LEFT JOIN genres g ON m.genre_id = g.id
    ORDER BY m.created_at DESC
    LIMIT 5
");

// Get latest videos (ORDER BY created_at DESC, LIMIT 5)
$latestVideos = fetchAll("
    SELECT v.*, a.name as artist_name, g.name as genre_name
    FROM videos v
    JOIN artists a ON v.artist_id = a.id
    LEFT JOIN genres g ON v.genre_id = g.id
    ORDER BY v.created_at DESC
    LIMIT 5
");

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding-bottom: 50px;">
    <!-- Homepage Page Header -->
    <div style="margin-bottom: 36px; display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 34px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">
                Welcome to <?php echo htmlspecialchars($site_name); ?>
            </h1>
            <p style="color: var(--text-muted); font-size: 15px; margin: 0;">
                <?php echo htmlspecialchars($site_tagline); ?>
            </p>
        </div>
        <div>
            <a href="/sound/browse.php" class="btn btn-primary btn-sm" style="padding: 8px 18px; font-weight: 700;">
                Browse All Categories
            </a>
        </div>
    </div>

    <?php if ($isLoggedIn && !empty($recentlyPlayed)): ?>
        <!-- Recently Played Section -->
        <section class="browse-section">
            <div class="browse-section-header">
                <h2 class="browse-section-title">
                    <span>Recently Played</span>
                    <span class="browse-count"><?php echo count($recentlyPlayed); ?></span>
                </h2>
            </div>

            <div class="card-grid">
                <?php foreach ($recentlyPlayed as $item): ?>
                    <?php 
                        $isMusic = ($item['item_type'] === 'music');
                        $title = $item['title'] ?? 'Title';
                        $cover = $item['cover'] ?? '';
                        $artist = $item['artist_name'] ?? 'Artist';
                        $itemType = $isMusic ? 'music' : 'video';
                        $itemId = (int)$item['item_id'];
                        $isLiked = !empty($userLikedIds[$itemType][$itemId]);
                        $detailUrl = $isMusic ? "/sound/music.php?id={$itemId}" : "/sound/videos.php?id={$itemId}";
                    ?>
                    <div class="card" <?php if ($isMusic && !empty($item['file_path'])): ?>data-track-id="<?php echo $itemId; ?>" data-track-url="<?php echo htmlspecialchars(asset_url($item['file_path'])); ?>" data-track-title="<?php echo htmlspecialchars($title); ?>" data-track-artist="<?php echo htmlspecialchars($artist); ?>" data-cover="<?php echo htmlspecialchars(asset_url($cover)); ?>"<?php endif; ?>>
                        <div class="card-cover">
                            <?php echo render_cover_tile($title, $cover, $title); ?>
                            <?php if (isNewItem($item['item_created_at'] ?? '')): ?>
                                <span class="badge-new">NEW</span>
                            <?php endif; ?>
                            <?php echo render_like_button($itemType, $itemId, $isLiked, 'card-like-btn'); ?>
                            <div class="card-play-overlay">
                                <?php if ($isMusic && !empty($item['file_path'])): ?>
                                    <button class="btn-round-play play-btn" data-type="music" data-id="<?php echo $itemId; ?>" data-title="<?php echo htmlspecialchars($title); ?>" data-artist="<?php echo htmlspecialchars($artist); ?>" data-cover="<?php echo htmlspecialchars(asset_url($cover)); ?>" data-url="<?php echo htmlspecialchars(asset_url($item['file_path'])); ?>" title="Play Track">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5V19L19 12L8 5Z"/>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo $detailUrl; ?>" class="btn-round-play" title="Watch Video">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5V19L19 12L8 5Z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="<?php echo $detailUrl; ?>" data-action="view-details"><?php echo htmlspecialchars($title); ?></a>
                            </h3>
                            <p class="card-artist"><?php echo htmlspecialchars($artist); ?></p>
                            <a href="<?php echo $detailUrl; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Latest Music Section -->
    <section class="browse-section">
        <div class="browse-section-header">
            <h2 class="browse-section-title">
                <span>Latest Music</span>
                <span class="browse-count"><?php echo count($latestMusic); ?></span>
            </h2>
            <a href="/sound/music.php" style="font-size: 13px; font-weight: 600; color: var(--accent);">See all music &rarr;</a>
        </div>

        <?php if (empty($latestMusic)): ?>
            <div class="empty-state-card">
                <p style="margin: 0; font-size: 14px;">No music tracks uploaded yet.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($latestMusic as $music): 
                    $isLiked = !empty($userLikedIds['music'][$music['id']]);
                ?>
                    <div class="card" data-track-id="<?php echo $music['id']; ?>" data-track-url="<?php echo htmlspecialchars(asset_url($music['file_path'])); ?>" data-track-title="<?php echo htmlspecialchars($music['title']); ?>" data-track-artist="<?php echo htmlspecialchars($music['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($music['cover'] ?? '')); ?>">
                        <div class="card-cover">
                            <?php echo render_cover_tile($music['title'], $music['cover'] ?? '', $music['title']); ?>
                            <?php if (isNewItem($music['created_at'] ?? '')): ?>
                                <span class="badge-new">NEW</span>
                            <?php endif; ?>
                            <?php echo render_like_button('music', $music['id'], $isLiked, 'card-like-btn'); ?>
                            <div class="card-play-overlay">
                                <button class="btn-round-play play-btn" data-type="music" data-id="<?php echo $music['id']; ?>" data-title="<?php echo htmlspecialchars($music['title']); ?>" data-artist="<?php echo htmlspecialchars($music['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($music['cover'] ?? '')); ?>" data-url="<?php echo htmlspecialchars(asset_url($music['file_path'])); ?>" title="Play Track">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5V19L19 12L8 5Z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="/sound/music.php?id=<?php echo $music['id']; ?>" data-action="view-details"><?php echo htmlspecialchars($music['title']); ?></a>
                            </h3>
                            <p class="card-artist">
                                <a href="/sound/browse.php?artist=<?php echo $music['artist_id']; ?>"><?php echo htmlspecialchars($music['artist_name']); ?></a>
                            </p>
                            <?php if (!empty($music['genre_name'])): ?>
                                <p class="card-subtitle" style="font-size: 12px; margin-top: 3px; color: var(--text-subtle);"><?php echo htmlspecialchars($music['genre_name']); ?></p>
                            <?php endif; ?>
                            <a href="/sound/music.php?id=<?php echo $music['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Latest Videos Section -->
    <section class="browse-section">
        <div class="browse-section-header">
            <h2 class="browse-section-title">
                <span>Latest Videos</span>
                <span class="browse-count"><?php echo count($latestVideos); ?></span>
            </h2>
            <a href="/sound/videos.php" style="font-size: 13px; font-weight: 600; color: var(--accent);">See all videos &rarr;</a>
        </div>

        <?php if (empty($latestVideos)): ?>
            <div class="empty-state-card" style="padding: 32px 20px;">
                <p style="margin: 0; font-size: 14px;">No music videos featured currently.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($latestVideos as $video): 
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
                            <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>