<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $delete_id = intval($_POST['id'] ?? 0);
        $video = fetchOne("SELECT * FROM videos WHERE id = ?", [$delete_id]);
        if ($video) {
            // Delete video and thumbnail files if exist
            if (!empty($video['file_path'])) {
                $p = ROOT_PATH . ltrim($video['file_path'], '/\\');
                if (file_exists($p)) @unlink($p);
            }
            if (!empty($video['thumbnail'])) {
                $t = ROOT_PATH . ltrim($video['thumbnail'], '/\\');
                if (file_exists($t)) @unlink($t);
            }

            $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->execute([$delete_id]);
            setFlash('success', 'Video "' . htmlspecialchars($video['title']) . '" deleted successfully.');
        } else {
            setFlash('error', 'Video not found.');
        }
    }
    header('Location: videos.php');
    exit;
}

$page_title = 'Manage Videos';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Videos']
];
require_once __DIR__ . '/includes/admin_header.php';

// Search and Pagination
$search = trim($_GET['search'] ?? '');
$params = [];
$where_clauses = [];

if ($search !== '') {
    $where_clauses[] = "(v.title LIKE ? OR a.name LIKE ? OR g.name LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = [$search_param, $search_param, $search_param];
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_sql = "SELECT COUNT(*) as count FROM videos v LEFT JOIN artists a ON v.artist_id = a.id LEFT JOIN genres g ON v.genre_id = g.id $where_sql";
$total_items = fetchOne($count_sql, $params)['count'] ?? 0;

$per_page = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$page = max(1, min($total_pages, intval($_GET['page'] ?? 1)));
$offset = ($page - 1) * $per_page;

$items_sql = "SELECT v.*, a.name as artist_name, g.name as genre_name, l.name as language_name, y.year 
              FROM videos v 
              LEFT JOIN artists a ON v.artist_id = a.id 
              LEFT JOIN genres g ON v.genre_id = g.id 
              LEFT JOIN languages l ON v.language_id = l.id 
              LEFT JOIN years y ON v.year_id = y.id 
              $where_sql 
              ORDER BY v.id DESC LIMIT $per_page OFFSET $offset";
$videos = fetchAll($items_sql, $params);
?>

<div class="admin-page-header">
    <div>
        <h1>Manage Videos</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Browse, search, edit, or remove video files.</p>
    </div>
    <div class="header-actions">
        <a href="add_video.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Video
        </a>
    </div>
</div>

<div class="admin-table-container">
    <div class="admin-table-toolbar">
        <form method="get" action="videos.php" class="admin-search-form">
            <input type="text" name="search" class="admin-search-input" placeholder="Search by title, artist, genre..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            <?php if ($search !== ''): ?>
                <a href="videos.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <div style="font-size: 13px; color: var(--admin-text-muted);">
            Total: <strong><?php echo $total_items; ?></strong> videos
        </div>
    </div>

    <?php if (empty($videos)): ?>
        <div class="admin-empty-state">
            <svg class="admin-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="23 7 16 12 23 17 23 7"></polygon>
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
            </svg>
            <h3>No Videos Found</h3>
            <p><?php echo $search !== '' ? 'No videos matched your search "' . htmlspecialchars($search) . '".' : 'There are no videos uploaded yet.'; ?></p>
            <?php if ($search !== ''): ?>
                <a href="videos.php" class="btn btn-secondary btn-sm">Clear Search</a>
            <?php else: ?>
                <a href="add_video.php" class="btn btn-primary btn-sm">Upload Your First Video</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Thumb</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Genre</th>
                        <th>Language</th>
                        <th>Year</th>
                        <th>Plays</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                        <tr>
                            <td>
                                <?php if (!empty($video['thumbnail']) && asset_exists($video['thumbnail'])): ?>
                                    <img src="<?php echo htmlspecialchars(asset_url($video['thumbnail'])); ?>" alt="Thumb" style="width: 48px; height: 32px; border-radius: 4px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 48px; height: 32px; border-radius: 4px; background: #242424; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; color: var(--accent);">
                                        MP4
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($video['title']); ?></div>
                                <div style="font-size: 12px; color: var(--admin-text-muted);">Added <?php echo date('M j, Y', strtotime($video['created_at'])); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($video['artist_name'] ?? 'Unknown'); ?></td>
                            <td><span class="admin-badge admin-badge-video"><?php echo htmlspecialchars($video['genre_name'] ?? '-'); ?></span></td>
                            <td><?php echo htmlspecialchars($video['language_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($video['year'] ?? '-'); ?></td>
                            <td><?php echo number_format($video['play_count'] ?? 0); ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="post" action="videos.php" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete this video: &quot;<?php echo htmlspecialchars(addslashes($video['title'])); ?>&quot;? This cannot be undone.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="admin-pagination-container">
                <div>
                    Showing <strong><?php echo $offset + 1; ?></strong> to <strong><?php echo min($total_items, $offset + $per_page); ?></strong> of <strong><?php echo $total_items; ?></strong> entries
                </div>
                <div class="admin-pagination">
                    <?php
                    $search_param_str = $search !== '' ? '&search=' . urlencode($search) : '';
                    ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search_param_str; ?>" class="admin-page-link <?php echo $page <= 1 ? 'disabled' : ''; ?>" aria-label="Previous">&laquo;</a>
                    
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == 1 || $p == $total_pages || abs($p - $page) <= 2): ?>
                            <a href="?page=<?php echo $p; ?><?php echo $search_param_str; ?>" class="admin-page-link <?php echo $p === $page ? 'active' : ''; ?>">
                                <?php echo $p; ?>
                            </a>
                        <?php elseif ($p == 2 || $p == $total_pages - 1): ?>
                            <span class="admin-page-link disabled">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <a href="?page=<?php echo $page + 1; ?><?php echo $search_param_str; ?>" class="admin-page-link <?php echo $page >= $total_pages ? 'disabled' : ''; ?>" aria-label="Next">&raquo;</a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
