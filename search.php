<?php
$page_title = 'Search Catalog';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize search parameters
$search_query = trim($_GET['q'] ?? '');
$search_type = in_array(($_GET['type'] ?? ''), ['all', 'music', 'videos'], true) ? $_GET['type'] : 'all';
$genre_id = !empty($_GET['genre']) ? intval($_GET['genre']) : '';
$language_id = !empty($_GET['language']) ? intval($_GET['language']) : '';
$year = !empty($_GET['year']) ? trim($_GET['year']) : '';

// Has any search parameter been submitted?
$hasSearched = (!empty($search_query) || !empty($genre_id) || !empty($language_id) || !empty($year));

$music_results = [];
$video_results = [];

if ($hasSearched) {
    // 1. Search Music (if type is 'all' or 'music')
    if ($search_type === 'all' || $search_type === 'music') {
        $music_sql = "
            SELECT m.*, a.name as artist_name, g.name as genre_name, l.name as language_name, y.year as release_year
            FROM music m
            JOIN artists a ON m.artist_id = a.id
            LEFT JOIN genres g ON m.genre_id = g.id
            LEFT JOIN languages l ON m.language_id = l.id
            LEFT JOIN years y ON m.year_id = y.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search_query)) {
            $music_sql .= " AND (m.title LIKE ? OR a.name LIKE ?)";
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
        }

        if (!empty($genre_id)) {
            $music_sql .= " AND m.genre_id = ?";
            $params[] = $genre_id;
        }

        if (!empty($language_id)) {
            $music_sql .= " AND m.language_id = ?";
            $params[] = $language_id;
        }

        if (!empty($year)) {
            $music_sql .= " AND (y.year = ? OR y.id = ?)";
            $params[] = intval($year);
            $params[] = intval($year);
        }

        $music_sql .= " ORDER BY m.title ASC";
        $music_results = fetchAll($music_sql, $params);
    }

    // 2. Search Videos (if type is 'all' or 'videos')
    if ($search_type === 'all' || $search_type === 'videos') {
        $video_sql = "
            SELECT v.*, a.name as artist_name, g.name as genre_name, l.name as language_name, y.year as release_year
            FROM videos v
            JOIN artists a ON v.artist_id = a.id
            LEFT JOIN genres g ON v.genre_id = g.id
            LEFT JOIN languages l ON v.language_id = l.id
            LEFT JOIN years y ON v.year_id = y.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search_query)) {
            $video_sql .= " AND (v.title LIKE ? OR a.name LIKE ?)";
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
        }

        if (!empty($genre_id)) {
            $video_sql .= " AND v.genre_id = ?";
            $params[] = $genre_id;
        }

        if (!empty($language_id)) {
            $video_sql .= " AND v.language_id = ?";
            $params[] = $language_id;
        }

        if (!empty($year)) {
            $video_sql .= " AND (y.year = ? OR y.id = ?)";
            $params[] = intval($year);
            $params[] = intval($year);
        }

        $video_sql .= " ORDER BY v.title ASC";
        $video_results = fetchAll($video_sql, $params);
    }
}

// Get filter options from database
$genres = fetchAll("SELECT * FROM genres ORDER BY name ASC");
$languages = fetchAll("SELECT * FROM languages ORDER BY name ASC");
$years = fetchAll("SELECT DISTINCT year FROM years ORDER BY year DESC");

$totalResults = count($music_results) + count($video_results);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding-bottom: 50px;">
    <!-- Page Header -->
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">Search Catalog</h1>
        <p style="color: var(--text-muted); font-size: 15px; margin: 0;">Find tracks, artists, and music videos across our entire collection.</p>
    </div>

    <!-- Search & Filter Card -->
    <div class="card" style="cursor: default; padding: 24px 28px; margin-bottom: 36px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
        <form method="get" action="/sound/search.php" id="catalog-search-form">
            <!-- Search Query Input & Type Toggle -->
            <div style="display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px;">
                <div style="flex: 1; min-width: 260px; position: relative;">
                    <div class="search-input-wrapper">
                        <span class="search-icon-wrapper" aria-hidden="true">
                            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="search" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search by song title or artist name..." style="padding-left: 44px; height: 42px; background: var(--bg-elevated);" autocomplete="off">
                    </div>
                </div>

                <!-- Type Toggle: All / Music / Videos -->
                <div>
                    <div class="search-type-toggle" role="radiogroup" aria-label="Content type">
                        <label class="toggle-pill <?php echo ($search_type === 'all') ? 'active' : ''; ?>">
                            <input type="radio" name="type" value="all" <?php echo ($search_type === 'all') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span>All</span>
                        </label>
                        <label class="toggle-pill <?php echo ($search_type === 'music') ? 'active' : ''; ?>">
                            <input type="radio" name="type" value="music" <?php echo ($search_type === 'music') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span>Music</span>
                        </label>
                        <label class="toggle-pill <?php echo ($search_type === 'videos') ? 'active' : ''; ?>">
                            <input type="radio" name="type" value="videos" <?php echo ($search_type === 'videos') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span>Videos</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Styled Filter Dropdowns Row -->
            <div class="search-filter-grid">
                <div class="form-group">
                    <label for="filter-genre">Genre</label>
                    <select id="filter-genre" name="genre">
                        <option value="">All Genres</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo $genre['id']; ?>" <?php echo ($genre_id == $genre['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="filter-language">Language</label>
                    <select id="filter-language" name="language">
                        <option value="">All Languages</option>
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?php echo $lang['id']; ?>" <?php echo ($language_id == $lang['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lang['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="filter-year">Release Year</label>
                    <select id="filter-year" name="year">
                        <option value="">All Years</option>
                        <?php foreach ($years as $yr): ?>
                            <option value="<?php echo $yr['year']; ?>" <?php echo ($year == $yr['year']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($yr['year']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 10px 20px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Filter</span>
                    </button>
                    <?php if ($hasSearched): ?>
                        <a href="/sound/search.php" class="btn btn-secondary" style="padding: 10px 14px;" title="Reset Search">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Search Results Content Area -->
    <?php if (!$hasSearched): ?>
        <!-- Idle Search Prompt -->
        <div class="empty-state-card" style="padding: 48px 24px;">
            <div style="width: 56px; height: 56px; border-radius: var(--radius-pill); background: var(--bg-elevated); color: var(--accent); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">Search for songs, artists, or videos</h3>
            <p style="color: var(--text-muted); font-size: 14px; max-width: 440px; margin: 0 auto 20px auto;">
                Use the search input above or select specific genres, languages, and years to discover media.
            </p>
            <a href="/sound/browse.php" class="btn btn-secondary btn-sm">Explore Browse Categories</a>
        </div>

    <?php elseif ($totalResults === 0): ?>
        <!-- No Results Found State -->
        <div class="empty-state-card" style="padding: 48px 24px;">
            <div style="width: 56px; height: 56px; border-radius: var(--radius-pill); background: var(--bg-elevated); color: var(--text-subtle); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                No matches found<?php echo !empty($search_query) ? ' for "' . htmlspecialchars($search_query) . '"' : ''; ?>
            </h3>
            <p style="color: var(--text-muted); font-size: 14px; max-width: 440px; margin: 0 auto 20px auto;">
                Try checking for typos, using broader keywords, or clearing selected filters to find what you want to play.
            </p>
            <a href="/sound/search.php" class="btn btn-primary" style="padding: 10px 24px;">Clear All Filters</a>
        </div>

    <?php else: ?>
        <!-- Results Summary Bar -->
        <div style="margin-bottom: 24px; color: var(--text-muted); font-size: 14px;">
            Found <strong style="color: var(--text-primary);"><?php echo $totalResults; ?></strong> <?php echo ($totalResults === 1) ? 'result' : 'results'; ?><?php echo !empty($search_query) ? ' for "<span style=\"color: var(--accent);\">' . htmlspecialchars($search_query) . '</span>"' : ''; ?>
        </div>

        <!-- Music Results Section -->
        <?php if (!empty($music_results)): ?>
            <div class="browse-section">
                <div class="browse-section-header">
                    <h2 class="browse-section-title">
                        <span>Music Tracks</span>
                        <span class="browse-count"><?php echo count($music_results); ?></span>
                    </h2>
                </div>

                <div class="card-grid">
                    <?php foreach ($music_results as $track): 
                        $isLiked = !empty($userLikedIds['music'][$track['id']]);
                    ?>
                        <div class="card" data-track-id="<?php echo $track['id']; ?>" data-track-url="<?php echo htmlspecialchars(asset_url($track['file_path'])); ?>" data-track-title="<?php echo htmlspecialchars($track['title']); ?>" data-track-artist="<?php echo htmlspecialchars($track['artist_name']); ?>" data-track-cover="<?php echo htmlspecialchars(asset_url($track['cover'] ?? '')); ?>">
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
                                <p class="card-artist"><?php echo htmlspecialchars($track['artist_name']); ?></p>
                                <?php if (!empty($track['genre_name'])): ?>
                                    <p class="card-subtitle" style="font-size: 12px; margin-top: 3px; color: var(--text-subtle);"><?php echo htmlspecialchars($track['genre_name']); ?></p>
                                <?php endif; ?>
                                <a href="/sound/music.php?id=<?php echo $track['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Video Results Section -->
        <?php if (!empty($video_results)): ?>
            <div class="browse-section">
                <div class="browse-section-header">
                    <h2 class="browse-section-title">
                        <span>Videos</span>
                        <span class="browse-count"><?php echo count($video_results); ?></span>
                    </h2>
                </div>

                <div class="card-grid">
                    <?php foreach ($video_results as $video): 
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
                                <p class="card-artist"><?php echo htmlspecialchars($video['artist_name']); ?></p>
                                <?php if (!empty($video['genre_name'])): ?>
                                    <p class="card-subtitle" style="font-size: 12px; margin-top: 3px; color: var(--text-subtle);"><?php echo htmlspecialchars($video['genre_name']); ?></p>
                                <?php endif; ?>
                                <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>