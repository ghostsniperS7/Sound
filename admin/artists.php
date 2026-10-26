<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $delete_id = intval($_POST['id'] ?? 0);
        $artist = fetchOne("SELECT * FROM artists WHERE id = ?", [$delete_id]);
        if ($artist) {
            $stmt = $pdo->prepare("DELETE FROM artists WHERE id = ?");
            $stmt->execute([$delete_id]);
            setFlash('success', 'Artist "' . htmlspecialchars($artist['name']) . '" deleted successfully.');
        } else {
            setFlash('error', 'Artist not found.');
        }
    }
    header('Location: artists.php');
    exit;
}

$page_title = 'Manage Artists';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Artists']
];
require_once __DIR__ . '/includes/admin_header.php';

// Search and Pagination
$search = trim($_GET['search'] ?? '');
$params = [];
$where_clauses = [];

if ($search !== '') {
    $where_clauses[] = "(name LIKE ? OR description LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = [$search_param, $search_param];
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_sql = "SELECT COUNT(*) as count FROM artists $where_sql";
$total_items = fetchOne($count_sql, $params)['count'] ?? 0;

$per_page = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$page = max(1, min($total_pages, intval($_GET['page'] ?? 1)));
$offset = ($page - 1) * $per_page;

$items_sql = "SELECT a.*, 
              (SELECT COUNT(*) FROM music WHERE artist_id = a.id) as music_count,
              (SELECT COUNT(*) FROM videos WHERE artist_id = a.id) as video_count
              FROM artists a 
              $where_sql 
              ORDER BY a.name ASC LIMIT $per_page OFFSET $offset";
$artists = fetchAll($items_sql, $params);
?>

<div class="admin-page-header">
    <div>
        <h1>Manage Artists</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Browse, search, edit, or remove musical artists.</p>
    </div>
    <div class="header-actions">
        <a href="add_artist.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Artist
        </a>
    </div>
</div>

<div class="admin-table-container">
    <div class="admin-table-toolbar">
        <form method="get" action="artists.php" class="admin-search-form">
            <input type="text" name="search" class="admin-search-input" placeholder="Search by artist name or bio..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            <?php if ($search !== ''): ?>
                <a href="artists.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <div style="font-size: 13px; color: var(--admin-text-muted);">
            Total: <strong><?php echo $total_items; ?></strong> artists
        </div>
    </div>

    <?php if (empty($artists)): ?>
        <div class="admin-empty-state">
            <svg class="admin-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <h3>No Artists Found</h3>
            <p><?php echo $search !== '' ? 'No artists matched your search "' . htmlspecialchars($search) . '".' : 'No artists have been added yet.'; ?></p>
            <?php if ($search !== ''): ?>
                <a href="artists.php" class="btn btn-secondary btn-sm">Clear Search</a>
            <?php else: ?>
                <a href="add_artist.php" class="btn btn-primary btn-sm">Add New Artist</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Music Tracks</th>
                        <th>Videos</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artists as $artist): ?>
                        <tr>
                            <td style="font-weight: 600; color: #fff;">
                                <?php echo htmlspecialchars($artist['name']); ?>
                            </td>
                            <td style="color: var(--admin-text-muted); max-width: 320px;">
                                <?php 
                                $desc = $artist['description'] ?? '';
                                echo htmlspecialchars(strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : ($desc ?: '-')); 
                                ?>
                            </td>
                            <td><span class="admin-badge admin-badge-music"><?php echo $artist['music_count']; ?> tracks</span></td>
                            <td><span class="admin-badge admin-badge-video"><?php echo $artist['video_count']; ?> videos</span></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="edit_artist.php?id=<?php echo $artist['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="post" action="artists.php" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete artist: &quot;<?php echo htmlspecialchars(addslashes($artist['name'])); ?>&quot;? All associated music and videos may also be removed.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $artist['id']; ?>">
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
