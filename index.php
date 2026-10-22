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
        <div class="recently-played">
            <h2>Recently Played</h2>
            <div class="card-grid">
                <?php foreach ($recentlyPlayed as $item): ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($item['item_type'] === 'music'): ?>
                                <?php if ($item['music_cover']): ?>
                                    <img src="<?php echo htmlspecialchars($item['music_cover']); ?>" alt="<?php echo htmlspecialchars($item['music_title']); ?>">
                                <?php else: ?>
                                    <?php
                                        $initials = strtoupper(substr($item['music_title'], 0, 2));
                                        $hue = (ord($initials[0]) * 30) % 360;
                                    ?>
                                    <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                        <span><?php echo htmlspecialchars($initials); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($item['video_thumbnail']): ?>
                                    <img src="<?php echo htmlspecialchars($item['video_thumbnail']); ?>" alt="<?php echo htmlspecialchars($item['video_title']); ?>">
                                <?php else: ?>
                                    <?php
                                        $initials = strtoupper(substr($item['video_title'], 0, 2));
                                        $hue = (ord($initials[0]) * 30) % 360;
                                    ?>
                                    <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                        <span><?php echo htmlspecialchars($initials); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="play-overlay">
                                <button class="play-btn" data-type="<?php echo $item['item_type']; ?>" data-id="<?php echo $item['item_id']; ?>" data-title="<?php echo htmlspecialchars($item['item_type'] === 'music' ? $item['music_title'] : $item['video_title']); ?>" data-artist="<?php echo htmlspecialchars($item['item_type'] === 'music' ? $item['artist_name'] : $item['artist_name']); ?>" data-cover="<?php echo htmlspecialchars($item['item_type'] === 'music' ? $item['music_cover'] : $item['video_thumbnail']); ?>" data-url="<?php echo htmlspecialchars($item['item_type'] === 'music' ? $item['file_path'] : $item['file_path']); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($item['item_type'] === 'music' ? $item['music_title'] : $item['video_title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['artist_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="featured-content">
        <h2>Featured Music</h2>
        <div class="card-grid">
            <?php foreach ($featuredMusic as $music): ?>
                <div class="card">
                    <div class="card-image">
                        <?php if ($music['cover']): ?>
                            <img src="<?php echo htmlspecialchars($music['cover']); ?>" alt="<?php echo htmlspecialchars($music['title']); ?>">
                        <?php else: ?>
                            <?php
                                $initials = strtoupper(substr($music['title'], 0, 2));
                                $hue = (ord($initials[0]) * 30) % 360;
                            ?>
                            <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                <span><?php echo htmlspecialchars($initials); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="play-overlay">
                            <button class="play-btn" data-type="music" data-id="<?php echo $music['id']; ?>" data-title="<?php echo htmlspecialchars($music['title']); ?>" data-artist="<?php echo htmlspecialchars($music['artist_name']); ?>" data-cover="<?php echo htmlspecialchars($music['cover'] ?? ''); ?>" data-url="<?php echo htmlspecialchars($music['file_path']); ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($music['title']); ?></h3>
                        <p><?php echo htmlspecialchars($music['artist_name']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="featured-content">
        <h2>Featured Videos</h2>
        <div class="card-grid">
            <?php foreach ($featuredVideos as $video): ?>
                <div class="card">
                    <div class="card-image">
                        <?php if ($video['thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                        <?php else: ?>
                            <?php
                                $initials = strtoupper(substr($video['title'], 0, 2));
                                $hue = (ord($initials[0]) * 30) % 360;
                            ?>
                            <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                <span><?php echo htmlspecialchars($initials); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="play-overlay">
                            <a href="video_detail.php?id=<?php echo $video['id']; ?>" class="play-btn">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                        <p><?php echo htmlspecialchars($video['artist_name']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // Add event listeners for play buttons
    document.addEventListener('DOMContentLoaded', function() {
        const playButtons = document.querySelectorAll('.play-btn[data-type="music"]');
        const trackList = [];

        playButtons.forEach(button => {
            button.addEventListener('click', function() {
                const track = {
                    id: this.dataset.id,
                    title: this.dataset.title,
                    artist: this.dataset.artist,
                    cover: this.dataset.cover,
                    url: this.dataset.url
                };

                // Check if track is already in the list
                const existingIndex = trackList.findIndex(t => t.id === track.id);
                if (existingIndex === -1) {
                    trackList.push(track);
                }

                // Update the player
                updateTrackList(trackList);
                const playPauseBtn = document.getElementById('play-pause-btn');
                playPauseBtn.click();
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>