<?php
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get recently played items if logged in
$recentlyPlayed = [];
if ($isLoggedIn) {
    $recentlyPlayed = getRecentlyPlayed($_SESSION['user_id']);
}

// Get featured music
$featuredMusic = fetchAll("SELECT m.*, a.name as artist_name FROM music m JOIN artists a ON m.artist_id = a.id ORDER BY m.play_count DESC LIMIT 5");

// Get featured videos
$featuredVideos = fetchAll("SELECT v.*, a.name as artist_name FROM videos v JOIN artists a ON v.artist_id = a.id ORDER BY v.play_count DESC LIMIT 5");

// Include header
include 'includes/header.php';

?>

<!-- HTML content for the homepage -->
<div class="container">
    <h1>Welcome to Sound</h1>

    <?php if ($isLoggedIn && !empty($recentlyPlayed)): ?>
        <div class="recently-played" style="margin-bottom: 32px;">
            <h2>Recently Played</h2>
            <div class="card-grid">
                <?php foreach ($recentlyPlayed as $item): ?>
                    <?php 
                        $isMusic = ($item['item_type'] === 'music');
                        $title = $isMusic ? $item['music_title'] : $item['video_title'];
                        $cover = $isMusic ? $item['music_cover'] : $item['video_thumbnail'];
                        $artist = $item['artist_name'] ?? 'Artist';
                    ?>
                    <div class="card">
                        <div class="card-cover">
                            <?php echo render_cover_tile($title, $cover, $title); ?>
                            <div class="card-play-overlay">
                                <?php if ($isMusic): ?>
                                    <button class="btn-round-play play-btn" data-type="music" data-id="<?php echo $item['item_id']; ?>" data-title="<?php echo htmlspecialchars($title); ?>" data-artist="<?php echo htmlspecialchars($artist); ?>" data-cover="<?php echo htmlspecialchars(asset_url($cover)); ?>" data-url="<?php echo htmlspecialchars(asset_url($item['file_path'])); ?>" title="Play">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5V19L19 12L8 5Z"/>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <a href="videos.php?id=<?php echo $item['item_id']; ?>" class="btn-round-play" title="Watch Video">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5V19L19 12L8 5Z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($title); ?></h3>
                            <p class="card-artist"><?php echo htmlspecialchars($artist); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="featured-content" style="margin-bottom: 32px;">
        <h2>Featured Music</h2>
        <div class="card-grid">
            <?php foreach ($featuredMusic as $music): ?>
                <div class="card">
                    <div class="card-cover">
                        <?php echo render_cover_tile($music['title'], $music['cover'], $music['title']); ?>
                        <div class="card-play-overlay">
                            <button class="btn-round-play play-btn" data-type="music" data-id="<?php echo $music['id']; ?>" data-title="<?php echo htmlspecialchars($music['title']); ?>" data-artist="<?php echo htmlspecialchars($music['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($music['cover'])); ?>" data-url="<?php echo htmlspecialchars(asset_url($music['file_path'])); ?>" title="Play">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5V19L19 12L8 5Z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo htmlspecialchars($music['title']); ?></h3>
                        <p class="card-artist"><?php echo htmlspecialchars($music['artist_name']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="featured-content" style="margin-bottom: 32px;">
        <h2>Featured Videos</h2>
        <div class="card-grid">
            <?php foreach ($featuredVideos as $video): ?>
                <div class="card">
                    <div class="card-cover">
                        <?php echo render_cover_tile($video['title'], $video['thumbnail'], $video['title']); ?>
                        <div class="card-play-overlay">
                            <a href="videos.php?id=<?php echo $video['id']; ?>" class="btn-round-play" title="Watch Video">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5V19L19 12L8 5Z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                        <p class="card-artist"><?php echo htmlspecialchars($video['artist_name']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>