<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Get all artists
$artists = fetchAll("SELECT * FROM artists ORDER BY name");

// Get all albums with artist information
$albums = fetchAll("SELECT a.*, ar.name as artist_name FROM albums a JOIN artists ar ON a.artist_id = ar.id ORDER BY a.title");

// Get all genres
$genres = fetchAll("SELECT * FROM genres ORDER BY name");

// Get all languages
$languages = fetchAll("SELECT * FROM languages ORDER BY name");

// Get all years
$years = fetchAll("SELECT DISTINCT year FROM years ORDER BY year DESC");
?>

<div class="container">
    <h1>Browse</h1>

    <div class="browse-section">
        <h2>Artists</h2>
        <div class="browse-grid">
            <?php foreach ($artists as $artist): ?>
                <a href="browse.php?artist=<?php echo $artist['id']; ?>" class="browse-item">
                    <?php echo htmlspecialchars($artist['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="browse-section">
        <h2>Albums</h2>
        <div class="browse-grid">
            <?php foreach ($albums as $album): ?>
                <a href="browse.php?album=<?php echo $album['id']; ?>" class="browse-item">
                    <?php echo htmlspecialchars($album['title']); ?>
                    <span class="artist-name"><?php echo htmlspecialchars($album['artist_name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="browse-section">
        <h2>Genres</h2>
        <div class="browse-grid">
            <?php foreach ($genres as $genre): ?>
                <a href="browse.php?genre=<?php echo $genre['id']; ?>" class="browse-item">
                    <?php echo htmlspecialchars($genre['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="browse-section">
        <h2>Languages</h2>
        <div class="browse-grid">
            <?php foreach ($languages as $language): ?>
                <a href="browse.php?language=<?php echo $language['id']; ?>" class="browse-item">
                    <?php echo htmlspecialchars($language['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="browse-section">
        <h2>Years</h2>
        <div class="browse-grid">
            <?php foreach ($years as $year): ?>
                <a href="browse.php?year=<?php echo $year['year']; ?>" class="browse-item">
                    <?php echo htmlspecialchars($year['year']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (isset($_GET['artist'])): ?>
        <?php
            $artist_id = $_GET['artist'];
            $artist = fetchOne("SELECT * FROM artists WHERE id = ?", [$artist_id]);
            $artist_music = fetchAll("SELECT m.*, g.name as genre_name FROM music m JOIN genres g ON m.genre_id = g.id WHERE m.artist_id = ? ORDER BY m.title", [$artist_id]);
        ?>
        <div class="section">
            <h2>Music by <?php echo htmlspecialchars($artist['name']); ?></h2>
            <div class="card-grid">
                <?php foreach ($artist_music as $item): ?>
                    <?php $is_new = (strtotime($item['created_at']) > strtotime('-7 days')); ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($item['cover']): ?>
                                <img src="<?php echo htmlspecialchars($item['cover']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <?php
                                    $initials = strtoupper(substr($item['title'], 0, 2));
                                    $hue = (ord($initials[0]) * 30) % 360;
                                ?>
                                <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                    <span><?php echo htmlspecialchars($initials); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
                            <?php endif; ?>
                            <div class="play-overlay">
                                <button class="play-btn" data-type="music" data-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-artist="<?php echo htmlspecialchars($artist['name']); ?>" data-cover="<?php echo htmlspecialchars($item['cover'] ?? ''); ?>" data-url="<?php echo htmlspecialchars($item['file_path']); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="genre"><?php echo htmlspecialchars($item['genre_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['album'])): ?>
        <?php
            $album_id = $_GET['album'];
            $album = fetchOne("SELECT a.*, ar.name as artist_name FROM albums a JOIN artists ar ON a.artist_id = ar.id WHERE a.id = ?", [$album_id]);
            $album_music = fetchAll("SELECT m.*, g.name as genre_name FROM music m JOIN genres g ON m.genre_id = g.id WHERE m.album_id = ? ORDER BY m.title", [$album_id]);
        ?>
        <div class="section">
            <h2><?php echo htmlspecialchars($album['title']); ?> by <?php echo htmlspecialchars($album['artist_name']); ?></h2>
            <div class="card-grid">
                <?php foreach ($album_music as $item): ?>
                    <?php $is_new = (strtotime($item['created_at']) > strtotime('-7 days')); ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($item['cover']): ?>
                                <img src="<?php echo htmlspecialchars($item['cover']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <?php
                                    $initials = strtoupper(substr($item['title'], 0, 2));
                                    $hue = (ord($initials[0]) * 30) % 360;
                                ?>
                                <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                    <span><?php echo htmlspecialchars($initials); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
                            <?php endif; ?>
                            <div class="play-overlay">
                                <button class="play-btn" data-type="music" data-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-artist="<?php echo htmlspecialchars($album['artist_name']); ?>" data-cover="<?php echo htmlspecialchars($item['cover'] ?? ''); ?>" data-url="<?php echo htmlspecialchars($item['file_path']); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="genre"><?php echo htmlspecialchars($item['genre_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['genre'])): ?>
        <?php
            $genre_id = $_GET['genre'];
            $genre = fetchOne("SELECT * FROM genres WHERE id = ?", [$genre_id]);
            $genre_music = fetchAll("SELECT m.*, a.name as artist_name FROM music m JOIN artists a ON m.artist_id = a.id WHERE m.genre_id = ? ORDER BY m.title", [$genre_id]);
            $genre_videos = fetchAll("SELECT v.*, a.name as artist_name FROM videos v JOIN artists a ON v.artist_id = a.id WHERE v.genre_id = ? ORDER BY v.title", [$genre_id]);
        ?>
        <div class="section">
            <h2><?php echo htmlspecialchars($genre['name']); ?> Music</h2>
            <div class="card-grid">
                <?php foreach ($genre_music as $item): ?>
                    <?php $is_new = (strtotime($item['created_at']) > strtotime('-7 days')); ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($item['cover']): ?>
                                <img src="<?php echo htmlspecialchars($item['cover']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <?php
                                    $initials = strtoupper(substr($item['title'], 0, 2));
                                    $hue = (ord($initials[0]) * 30) % 360;
                                ?>
                                <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                    <span><?php echo htmlspecialchars($initials); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
                            <?php endif; ?>
                            <div class="play-overlay">
                                <button class="play-btn" data-type="music" data-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-artist="<?php echo htmlspecialchars($item['artist_name']); ?>" data-cover="<?php echo htmlspecialchars($item['cover'] ?? ''); ?>" data-url="<?php echo htmlspecialchars($item['file_path']); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['artist_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <h2><?php echo htmlspecialchars($genre['name']); ?> Videos</h2>
            <div class="card-grid">
                <?php foreach ($genre_videos as $video): ?>
                    <?php $is_new = (strtotime($video['created_at']) > strtotime('-7 days')); ?>
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
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
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
    <?php endif; ?>

    <?php if (isset($_GET['language'])): ?>
        <?php
            $language_id = $_GET['language'];
            $language = fetchOne("SELECT * FROM languages WHERE id = ?", [$language_id]);
            $language_music = fetchAll("SELECT m.*, a.name as artist_name, g.name as genre_name FROM music m JOIN artists a ON m.artist_id = a.id JOIN genres g ON m.genre_id = g.id WHERE m.language_id = ? ORDER BY m.title", [$language_id]);
            $language_videos = fetchAll("SELECT v.*, a.name as artist_name, g.name as genre_name FROM videos v JOIN artists a ON v.artist_id = a.id JOIN genres g ON v.genre_id = g.id WHERE v.language_id = ? ORDER BY v.title", [$language_id]);
        ?>
        <div class="section">
            <h2><?php echo htmlspecialchars($language['name']); ?> Music</h2>
            <div class="card-grid">
                <?php foreach ($language_music as $item): ?>
                    <?php $is_new = (strtotime($item['created_at']) > strtotime('-7 days')); ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($item['cover']): ?>
                                <img src="<?php echo htmlspecialchars($item['cover']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <?php
                                    $initials = strtoupper(substr($item['title'], 0, 2));
                                    $hue = (ord($initials[0]) * 30) % 360;
                                ?>
                                <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                    <span><?php echo htmlspecialchars($initials); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
                            <?php endif; ?>
                            <div class="play-overlay">
                                <button class="play-btn" data-type="music" data-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-artist="<?php echo htmlspecialchars($item['artist_name']); ?>" data-cover="<?php echo htmlspecialchars($item['cover'] ?? ''); ?>" data-url="<?php echo htmlspecialchars($item['file_path']); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['artist_name']); ?></p>
                            <p class="genre"><?php echo htmlspecialchars($item['genre_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <h2><?php echo htmlspecialchars($language['name']); ?> Videos</h2>
            <div class="card-grid">
                <?php foreach ($language_videos as $video): ?>
                    <?php $is_new = (strtotime($video['created_at']) > strtotime('-7 days')); ?>
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
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
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
                            <p class="genre"><?php echo htmlspecialchars($video['genre_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['year'])): ?>
        <?php
            $year = $_GET['year'];
            $year_music = fetchAll("SELECT m.*, a.name as artist_name, g.name as genre_name FROM music m JOIN artists a ON m.artist_id = a.id JOIN genres g ON m.genre_id = g.id JOIN years y ON m.year_id = y.id WHERE y.year = ? ORDER BY m.title", [$year]);
            $year_videos = fetchAll("SELECT v.*, a.name as artist_name, g.name as genre_name FROM videos v JOIN artists a ON v.artist_id = a.id JOIN genres g ON v.genre_id = g.id JOIN years y ON v.year_id = y.id WHERE y.year = ? ORDER BY v.title", [$year]);
        ?>
        <div class="section">
            <h2><?php echo htmlspecialchars($year); ?> Music</h2>
            <div class="card-grid">
                <?php foreach ($year_music as $item): ?>
                    <?php $is_new = (strtotime($item['created_at']) > strtotime('-7 days')); ?>
                    <div class="card">
                        <div class="card-image">
                            <?php if ($item['cover']): ?>
                                <img src="<?php echo htmlspecialchars($item['cover']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <?php
                                    $initials = strtoupper(substr($item['title'], 0, 2));
                                    $hue = (ord($initials[0]) * 30) % 360;
                                ?>
                                <div class="placeholder" style="background: linear-gradient(135deg, hsl(<?php echo $hue; ?>, 70%, 60%), hsl(<?php echo $hue; ?>, 70%, 40%));">
                                    <span><?php echo htmlspecialchars($initials); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
                            <?php endif; ?>
                            <div class="play-overlay">
                                <button class="play-btn" data-type="music" data-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-artist="<?php echo htmlspecialchars($item['artist_name']); ?>" data-cover="<?php echo htmlspecialchars($item['cover'] ?? ''); ?>" data-url="<?php echo htmlspecialchars($item['file_path']); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['artist_name']); ?></p>
                            <p class="genre"><?php echo htmlspecialchars($item['genre_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <h2><?php echo htmlspecialchars($year); ?> Videos</h2>
            <div class="card-grid">
                <?php foreach ($year_videos as $video): ?>
                    <?php $is_new = (strtotime($video['created_at']) > strtotime('-7 days')); ?>
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
                            <?php if ($is_new): ?>
                                <span class="new-badge">NEW</span>
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
                            <p class="genre"><?php echo htmlspecialchars($video['genre_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
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

<?php require_once 'includes/footer.php'; ?>