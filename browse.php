<?php
$page_title = 'Browse Library';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Detect active filter parameter
$isFiltered = false;
$filterHeading = '';
$filterSubheading = '';
$matchingMusic = [];
$matchingVideos = [];

if (isset($_GET['artist'])) {
    $isFiltered = true;
    $artistId = intval($_GET['artist']);
    $artist = fetchOne("SELECT * FROM artists WHERE id = ?", [$artistId]);
    $filterName = $artist['name'] ?? 'Unknown Artist';
    $filterHeading = "Music by " . $filterName;
    $page_title = $filterHeading;

    $matchingMusic = fetchAll("
        SELECT m.*, a.name as artist_name, g.name as genre_name
        FROM music m
        JOIN artists a ON m.artist_id = a.id
        LEFT JOIN genres g ON m.genre_id = g.id
        WHERE m.artist_id = ?
        ORDER BY m.title ASC
    ", [$artistId]);

    $matchingVideos = fetchAll("
        SELECT v.*, a.name as artist_name, g.name as genre_name
        FROM videos v
        JOIN artists a ON v.artist_id = a.id
        LEFT JOIN genres g ON v.genre_id = g.id
        WHERE v.artist_id = ?
        ORDER BY v.title ASC
    ", [$artistId]);

} elseif (isset($_GET['album'])) {
    $isFiltered = true;
    $albumId = intval($_GET['album']);
    $album = fetchOne("
        SELECT al.*, ar.name as artist_name
        FROM albums al
        JOIN artists ar ON al.artist_id = ar.id
        WHERE al.id = ?
    ", [$albumId]);
    $filterName = $album['title'] ?? 'Unknown Album';
    $filterHeading = "Music by " . $filterName;
    $page_title = $filterHeading;

    $matchingMusic = fetchAll("
        SELECT m.*, a.name as artist_name, g.name as genre_name
        FROM music m
        JOIN artists a ON m.artist_id = a.id
        LEFT JOIN genres g ON m.genre_id = g.id
        WHERE m.album_id = ?
        ORDER BY m.title ASC
    ", [$albumId]);

    $matchingVideos = fetchAll("
        SELECT v.*, a.name as artist_name, g.name as genre_name
        FROM videos v
        JOIN artists a ON v.artist_id = a.id
        LEFT JOIN genres g ON v.genre_id = g.id
        WHERE v.album_id = ?
        ORDER BY v.title ASC
    ", [$albumId]);

} elseif (isset($_GET['genre'])) {
    $isFiltered = true;
    $genreId = intval($_GET['genre']);
    $genre = fetchOne("SELECT * FROM genres WHERE id = ?", [$genreId]);
    $filterName = $genre['name'] ?? 'Unknown Genre';
    $filterHeading = "Music by " . $filterName;
    $page_title = $filterHeading;

    $matchingMusic = fetchAll("
        SELECT m.*, a.name as artist_name, g.name as genre_name
        FROM music m
        JOIN artists a ON m.artist_id = a.id
        LEFT JOIN genres g ON m.genre_id = g.id
        WHERE m.genre_id = ?
        ORDER BY m.title ASC
    ", [$genreId]);

    $matchingVideos = fetchAll("
        SELECT v.*, a.name as artist_name, g.name as genre_name
        FROM videos v
        JOIN artists a ON v.artist_id = a.id
        LEFT JOIN genres g ON v.genre_id = g.id
        WHERE v.genre_id = ?
        ORDER BY v.title ASC
    ", [$genreId]);

} elseif (isset($_GET['language'])) {
    $isFiltered = true;
    $languageId = intval($_GET['language']);
    $language = fetchOne("SELECT * FROM languages WHERE id = ?", [$languageId]);
    $filterName = $language['name'] ?? 'Unknown Language';
    $filterHeading = "Music by " . $filterName;
    $page_title = $filterHeading;

    $matchingMusic = fetchAll("
        SELECT m.*, a.name as artist_name, g.name as genre_name
        FROM music m
        JOIN artists a ON m.artist_id = a.id
        LEFT JOIN genres g ON m.genre_id = g.id
        WHERE m.language_id = ?
        ORDER BY m.title ASC
    ", [$languageId]);

    $matchingVideos = fetchAll("
        SELECT v.*, a.name as artist_name, g.name as genre_name
        FROM videos v
        JOIN artists a ON v.artist_id = a.id
        LEFT JOIN genres g ON v.genre_id = g.id
        WHERE v.language_id = ?
        ORDER BY v.title ASC
    ", [$languageId]);

} elseif (isset($_GET['year'])) {
    $isFiltered = true;
    $yearParam = trim($_GET['year']);
    // Support both year ID or the numeric year
    $yearRecord = fetchOne("SELECT * FROM years WHERE id = ? OR year = ?", [intval($yearParam), intval($yearParam)]);
    $filterName = $yearRecord['year'] ?? $yearParam;
    $yearId = $yearRecord['id'] ?? intval($yearParam);
    $filterHeading = "Music by " . $filterName;
    $page_title = $filterHeading;

    $matchingMusic = fetchAll("
        SELECT m.*, a.name as artist_name, g.name as genre_name
        FROM music m
        JOIN artists a ON m.artist_id = a.id
        LEFT JOIN genres g ON m.genre_id = g.id
        JOIN years y ON m.year_id = y.id
        WHERE y.id = ? OR y.year = ?
        ORDER BY m.title ASC
    ", [$yearId, intval($yearParam)]);

    $matchingVideos = fetchAll("
        SELECT v.*, a.name as artist_name, g.name as genre_name
        FROM videos v
        JOIN artists a ON v.artist_id = a.id
        LEFT JOIN genres g ON v.genre_id = g.id
        JOIN years y ON v.year_id = y.id
        WHERE y.id = ? OR y.year = ?
        ORDER BY v.title ASC
    ", [$yearId, intval($yearParam)]);
}

// Fixed curated palette of solid muted colors (NO gradients)
$solidMutedPalette = [
    '#253545', // muted slate navy
    '#382d3b', // muted dark plum
    '#283c34', // muted dark pine
    '#423226', // muted dark bronze
    '#3a2c2c', // muted terracotta coal
    '#293b42', // muted steel teal
    '#33283d', // muted dark violet
    '#2e3b28'  // muted deep moss
];

// If main browse page (unfiltered), load catalog data with counts
if (!$isFiltered) {
    // 1. Artists with song counts
    $artists = fetchAll("
        SELECT a.*, COUNT(m.id) as song_count
        FROM artists a
        LEFT JOIN music m ON a.id = m.artist_id
        GROUP BY a.id, a.name, a.description
        ORDER BY a.name ASC
    ");

    // 2. Albums with artist names
    $albums = fetchAll("
        SELECT al.*, ar.name as artist_name, COUNT(m.id) as song_count
        FROM albums al
        JOIN artists ar ON al.artist_id = ar.id
        LEFT JOIN music m ON al.id = m.album_id
        GROUP BY al.id, al.title, al.year, al.cover, ar.name
        ORDER BY al.title ASC
    ");

    // 3. Genres with total item counts (music + videos)
    $genres = fetchAll("
        SELECT g.*,
            (SELECT COUNT(*) FROM music m WHERE m.genre_id = g.id) +
            (SELECT COUNT(*) FROM videos v WHERE v.genre_id = g.id) as item_count
        FROM genres g
        ORDER BY g.name ASC
    ");

    // 4. Languages with total item counts (music + videos)
    $languages = fetchAll("
        SELECT l.*,
            (SELECT COUNT(*) FROM music m WHERE m.language_id = l.id) +
            (SELECT COUNT(*) FROM videos v WHERE v.language_id = l.id) as item_count
        FROM languages l
        ORDER BY l.name ASC
    ");

    // 5. Years (newest first) with total item counts
    $years = fetchAll("
        SELECT y.*,
            (SELECT COUNT(*) FROM music m WHERE m.year_id = y.id) +
            (SELECT COUNT(*) FROM videos v WHERE v.year_id = y.id) as item_count
        FROM years y
        ORDER BY y.year DESC
    ");
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding-bottom: 50px;">

    <?php if ($isFiltered): ?>
        <!-- ===============================================================
             FILTERED RESULTS VIEW
             =============================================================== -->
        <div style="margin-bottom: 24px;">
            <a href="/sound/browse.php" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to Browse</span>
            </a>

            <h1 style="font-size: 30px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">
                <?php echo htmlspecialchars($filterHeading); ?>
            </h1>
            <p style="color: var(--text-muted); font-size: 14.5px; margin: 0;">
                Filtered collection of music and videos matching this category.
            </p>
        </div>

        <?php 
        $hasMusic = !empty($matchingMusic);
        $hasVideos = !empty($matchingVideos);
        ?>

        <?php if (!$hasMusic && !$hasVideos): ?>
            <!-- Empty Filter State Card -->
            <div class="empty-state-card" style="margin-top: 32px; padding: 48px 32px;">
                <div style="width: 56px; height: 56px; border-radius: var(--radius-pill); background: var(--bg-elevated); color: var(--text-subtle); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </div>
                <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">No music or videos found</h3>
                <p style="color: var(--text-muted); font-size: 14px; max-width: 440px; margin: 0 auto 24px auto;">
                    There are currently no music tracks or videos categorized under this filter.
                </p>
                <a href="/sound/browse.php" class="btn btn-primary" style="padding: 10px 24px;">Browse Other Categories</a>
            </div>

        <?php else: ?>

            <?php if ($hasMusic): ?>
                <!-- Matching Music Section -->
                <div class="browse-section">
                    <div class="browse-section-header">
                        <h2 class="browse-section-title">
                            <span>Matching Music</span>
                            <span class="browse-count"><?php echo count($matchingMusic); ?></span>
                        </h2>
                    </div>

                    <div class="card-grid">
                        <?php foreach ($matchingMusic as $item): 
                            $isLiked = !empty($userLikedIds['music'][$item['id']]);
                        ?>
                            <div class="card" data-track-id="<?php echo $item['id']; ?>" data-track-url="<?php echo htmlspecialchars(asset_url($item['file_path'])); ?>" data-track-title="<?php echo htmlspecialchars($item['title']); ?>" data-track-artist="<?php echo htmlspecialchars($item['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($item['cover'] ?? '')); ?>">
                                <div class="card-cover">
                                    <?php echo render_cover_tile($item['title'], $item['cover'] ?? '', $item['title']); ?>
                                    <?php if (isNewItem($item['created_at'] ?? '')): ?>
                                        <span class="badge-new">NEW</span>
                                    <?php endif; ?>
                                    <?php echo render_like_button('music', $item['id'], $isLiked, 'card-like-btn'); ?>
                                    <div class="card-play-overlay">
                                        <button class="btn-round-play play-btn" data-type="music" data-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-artist="<?php echo htmlspecialchars($item['artist_name']); ?>" data-cover="<?php echo htmlspecialchars(asset_url($item['cover'] ?? '')); ?>" data-url="<?php echo htmlspecialchars(asset_url($item['file_path'])); ?>" title="Play Track">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M8 5V19L19 12L8 5Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">
                                        <a href="/sound/music.php?id=<?php echo $item['id']; ?>" data-action="view-details"><?php echo htmlspecialchars($item['title']); ?></a>
                                    </h3>
                                    <p class="card-artist"><?php echo htmlspecialchars($item['artist_name']); ?></p>
                                    <a href="/sound/music.php?id=<?php echo $item['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($hasVideos): ?>
                <!-- Matching Videos Section -->
                <div class="browse-section">
                    <div class="browse-section-header">
                        <h2 class="browse-section-title">
                            <span>Matching Videos</span>
                            <span class="browse-count"><?php echo count($matchingVideos); ?></span>
                        </h2>
                    </div>

                    <div class="card-grid">
                        <?php foreach ($matchingVideos as $video): 
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
                                    <a href="/sound/videos.php?id=<?php echo $video['id']; ?>" class="card-details-action" data-action="view-details">Details & Reviews &rarr;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    <?php else: ?>
        <!-- ===============================================================
             MAIN BROWSE DIRECTORY VIEW
             =============================================================== -->
        <div style="margin-bottom: 32px;">
            <h1 style="font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">Browse Library</h1>
            <p style="color: var(--text-muted); font-size: 15px; margin: 0;">Explore artists, albums, genres, languages, and release years.</p>
        </div>

        <!-- 1. Artists Section -->
        <section class="browse-section">
            <div class="browse-section-header">
                <h2 class="browse-section-title">
                    <span>Artists</span>
                    <span class="browse-count"><?php echo count($artists); ?></span>
                </h2>
            </div>

            <div class="card-grid">
                <?php foreach ($artists as $index => $artist): ?>
                    <?php
                    $initials = strtoupper(substr(trim($artist['name']), 0, 2));
                    $avatarBg = $solidMutedPalette[$index % count($solidMutedPalette)];
                    ?>
                    <a href="/sound/browse.php?artist=<?php echo $artist['id']; ?>" class="card artist-card" style="text-decoration: none;">
                        <div class="artist-avatar-wrapper">
                            <div class="artist-avatar-circle" style="background-color: <?php echo $avatarBg; ?>;">
                                <span><?php echo htmlspecialchars($initials); ?></span>
                            </div>
                        </div>
                        <h3 class="card-title" style="text-align: center; width: 100%;"><?php echo htmlspecialchars($artist['name']); ?></h3>
                        <p class="card-subtitle" style="text-align: center; width: 100%;">
                            <?php echo (int)$artist['song_count']; ?> <?php echo ((int)$artist['song_count'] === 1) ? 'Song' : 'Songs'; ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 2. Albums Section -->
        <section class="browse-section">
            <div class="browse-section-header">
                <h2 class="browse-section-title">
                    <span>Albums</span>
                    <span class="browse-count"><?php echo count($albums); ?></span>
                </h2>
            </div>

            <?php if (empty($albums)): ?>
                <div class="empty-state-card">
                    <p style="margin: 0; font-size: 15px; font-weight: 600; color: var(--text-muted);">No albums yet</p>
                </div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($albums as $album): ?>
                        <a href="/sound/browse.php?album=<?php echo $album['id']; ?>" class="card" style="text-decoration: none;">
                            <div class="card-cover">
                                <?php echo render_cover_tile($album['title'], $album['cover'] ?? '', $album['title']); ?>
                            </div>
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($album['title']); ?></h3>
                                <p class="card-artist"><?php echo htmlspecialchars($album['artist_name']); ?></p>
                                <?php if (!empty($album['year'])): ?>
                                    <p class="card-subtitle" style="font-size: 12px; margin-top: 2px;"><?php echo htmlspecialchars($album['year']); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- 3. Genres Section (Solid Muted Color Tiles, No Gradients) -->
        <section class="browse-section">
            <div class="browse-section-header">
                <h2 class="browse-section-title">
                    <span>Genres</span>
                    <span class="browse-count"><?php echo count($genres); ?></span>
                </h2>
            </div>

            <div class="category-tile-grid">
                <?php foreach ($genres as $index => $genre): ?>
                    <?php 
                    $tileBg = $solidMutedPalette[$index % count($solidMutedPalette)];
                    ?>
                    <a href="/sound/browse.php?genre=<?php echo $genre['id']; ?>" class="category-tile" style="background-color: <?php echo $tileBg; ?>; text-decoration: none;">
                        <div class="category-tile-name"><?php echo htmlspecialchars($genre['name']); ?></div>
                        <div class="category-tile-count">
                            <?php echo (int)$genre['item_count']; ?> <?php echo ((int)$genre['item_count'] === 1) ? 'Item' : 'Items'; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 4. Languages Section (Solid Muted Color Tiles, No Gradients) -->
        <section class="browse-section">
            <div class="browse-section-header">
                <h2 class="browse-section-title">
                    <span>Languages</span>
                    <span class="browse-count"><?php echo count($languages); ?></span>
                </h2>
            </div>

            <div class="category-tile-grid">
                <?php foreach ($languages as $index => $language): ?>
                    <?php 
                    // Offset index for different colors between genres and languages
                    $tileBg = $solidMutedPalette[($index + 3) % count($solidMutedPalette)];
                    ?>
                    <a href="/sound/browse.php?language=<?php echo $language['id']; ?>" class="category-tile" style="background-color: <?php echo $tileBg; ?>; text-decoration: none;">
                        <div class="category-tile-name"><?php echo htmlspecialchars($language['name']); ?></div>
                        <div class="category-tile-count">
                            <?php echo (int)$language['item_count']; ?> <?php echo ((int)$language['item_count'] === 1) ? 'Item' : 'Items'; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 5. Years Section (Row of Chip Buttons, Newest First) -->
        <section class="browse-section">
            <div class="browse-section-header">
                <h2 class="browse-section-title">
                    <span>Release Years</span>
                    <span class="browse-count"><?php echo count($years); ?></span>
                </h2>
            </div>

            <div class="year-chips-row">
                <?php foreach ($years as $year): ?>
                    <a href="/sound/browse.php?year=<?php echo $year['year']; ?>" class="year-chip" style="text-decoration: none;">
                        <span><?php echo htmlspecialchars($year['year']); ?></span>
                        <span class="chip-count"><?php echo (int)$year['item_count']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>