<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Initialize variables
$search_query = $_GET['q'] ?? '';
$search_type = $_GET['type'] ?? 'all';
$genre_id = $_GET['genre'] ?? '';
$language_id = $_GET['language'] ?? '';
$year = $_GET['year'] ?? '';

// Build the search query
$music_results = [];
$video_results = [];

if (!empty($search_query)) {
    // Search music
    $music_sql = "SELECT m.*, a.name as artist_name, g.name as genre_name FROM music m JOIN artists a ON m.artist_id = a.id JOIN genres g ON m.genre_id = g.id WHERE m.title LIKE ?";
    $params = ['%' . $search_query . '%'];

    if (!empty($genre_id)) {
        $music_sql .= " AND m.genre_id = ?";
        $params[] = $genre_id;
    }

    if (!empty($language_id)) {
        $music_sql .= " AND m.language_id = ?";
        $params[] = $language_id;
    }

    if (!empty($year)) {
        $music_sql .= " AND y.year = ?";
        $params[] = $year;
    }

    $music_sql .= " ORDER BY m.title";
    $music_results = fetchAll($music_sql, $params);

    // Search videos
    $video_sql = "SELECT v.*, a.name as artist_name, g.name as genre_name FROM videos v JOIN artists a ON v.artist_id = a.id JOIN genres g ON v.genre_id = g.id WHERE v.title LIKE ?";
    $params = ['%' . $search_query . '%'];

    if (!empty($genre_id)) {
        $video_sql .= " AND v.genre_id = ?";
        $params[] = $genre_id;
    }

    if (!empty($language_id)) {
        $video_sql .= " AND v.language_id = ?";
        $params[] = $language_id;
    }

    if (!empty($year)) {
        $video_sql .= " AND y.year = ?";
        $params[] = $year;
    }

    $video_sql .= " ORDER BY v.title";
    $video_results = fetchAll($video_sql, $params);
}

// Get all genres for filtering
$genres = fetchAll("SELECT * FROM genres");

// Get all languages for filtering
$languages = fetchAll("SELECT * FROM languages");

// Get all years for filtering
$years = fetchAll("SELECT DISTINCT year FROM years ORDER BY year DESC");
?>

<div class="container">
    <h1>Search</h1>

    <div class="search-form">
        <form method="get" action="search.php">
            <div class="form-group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search for music or videos..." required>
            </div>

            <div class="form-group">
                <select name="type">
                    <option value="all" <?php echo ($search_type === 'all') ? 'selected' : ''; ?>>All</option>
                    <option value="music" <?php echo ($search_type === 'music') ? 'selected' : ''; ?>>Music</option>
                    <option value="videos" <?php echo ($search_type === 'videos') ? 'selected' : ''; ?>>Videos</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="genre">Genre:</label>
                <select id="genre" name="genre">
                    <option value="">All Genres</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo $genre['id']; ?>" <?php echo ($genre_id == $genre['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($genre['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="language">Language:</label>
                <select id="language" name="language">
                    <option value="">All Languages</option>
                    <?php foreach ($languages as $language): ?>
                        <option value="<?php echo $language['id']; ?>" <?php echo ($language_id == $language['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($language['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="year">Year:</label>
                <select id="year" name="year">
                    <option value="">All Years</option>
                    <?php foreach ($years as $year_item): ?>
                        <option value="<?php echo $year_item['year']; ?>" <?php echo ($year == $year_item['year']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($year_item['year']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <?php if (!empty($search_query)): ?>
        <?php if ($search_type === 'all' || $search_type === 'music'): ?>
            <div class="section">
                <h2>Music Results</h2>
                <?php if (empty($music_results)): ?>
                    <p>No music found matching your search.</p>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($music_results as $item): ?>
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
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($search_type === 'all' || $search_type === 'videos'): ?>
            <div class="section">
                <h2>Video Results</h2>
                <?php if (empty($video_results)): ?>
                    <p>No videos found matching your search.</p>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($video_results as $video): ?>
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
                <?php endif; ?>
            </div>
        <?php endif; ?>
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