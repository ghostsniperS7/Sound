<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $delete_id = intval($_POST['id'] ?? 0);
        $album = fetchOne("SELECT * FROM albums WHERE id = ?", [$delete_id]);
        if ($album) {
            if (!empty($album['cover']) && file_exists(__DIR__ . '/../../' . $album['cover'])) {
                @unlink(__DIR__ . '/../../' . $album['cover']);
            }
            $stmt = $pdo->prepare("DELETE FROM albums WHERE id = ?");
            $stmt->execute([$delete_id]);
            setFlash('success', 'Album "' . htmlspecialchars($album['title']) . '" deleted successfully.');
        } else {
            setFlash('error', 'Album not found.');
        }
    }
    header('Location: albums.php');
    exit;
}

$page_title = 'Manage Albums';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Albums']
];
require_once __DIR__ . '/includes/admin_header.php';

// Search and Pagination
$search = trim($_GET['search'] ?? '');
$params = [];
$where_clauses = [];

if ($search !== '') {
    $where_clauses[] = "(al.title LIKE ? OR ar.name LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = [$search_param, $search_param];
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_sql = "SELECT COUNT(*) as count FROM albums al LEFT JOIN artists ar ON al.artist_id = ar.id $where_sql";
$total_items = fetchOne($count_sql, $params)['count'] ?? 0;

$per_page = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$page = max(1, min($total_pages, intval($_GET['page'] ?? 1)));
$offset = ($page - 1) * $per_page;

$items_sql = "SELECT al.*, ar.name as artist_name,
              (SELECT COUNT(*) FROM music WHERE album_id = al.id) as track_count
              FROM albums al 
              LEFT JOIN artists ar ON al.artist_id = ar.id 
              $where_sql 
              ORDER BY al.title ASC LIMIT $per_page OFFSET $offset";
$albums = fetchAll($items_sql, $params);
?>

<div class="admin-page-header">
    <div>
        <h1>Manage Albums</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Browse, search, edit, or remove music albums.</p>
    </div>
    <div class="header-actions">
        <a href="add_album.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Album
        </a>
    </div>
</div>

<div class="admin-table-container">
    <div class="admin-table-toolbar">
        <form method="get" action="albums.php" class="admin-search-form">
            <input type="text" name="search" class="admin-search-input" placeholder="Search by album title, artist..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            <?php if ($search !== ''): ?>
                <a href="albums.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <div style="font-size: 13px; color: var(--admin-text-muted);">
            Total: <strong><?php echo $total_items; ?></strong> albums
        </div>
    </div>

    <?php if (empty($albums)): ?>
        <div class="admin-empty-state">
            <svg class="admin-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <h3>No Albums Found</h3>
            <p><?php echo $search !== '' ? 'No albums matched your search "' . htmlspecialchars($search) . '".' : 'No albums have been added yet.'; ?></p>
            <?php if ($search !== ''): ?>
                <a href="albums.php" class="btn btn-secondary btn-sm">Clear Search</a>
            <?php else: ?>
                <a href="add_album.php" class="btn btn-primary btn-sm">Add New Album</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Cover</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Year</th>
                        <th>Tracks</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($albums as $album): ?>
                        <tr>
                            <td>
                                <?php if (!empty($album['cover'])): ?>
                                    <img src="/sound/<?php echo htmlspecialchars($album['cover']); ?>" alt="Cover" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; border-radius: 6px; background: rgba(124, 92, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; color: var(--admin-primary);">
                                        ALBUM
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 600; color: #fff;">
                                <?php echo htmlspecialchars($album['title']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($album['artist_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($album['year'] ?? '-'); ?></td>
                            <td><span class="admin-badge admin-badge-music"><?php echo $album['track_count']; ?> tracks</span></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="edit_album.php?id=<?php echo $album['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="post" action="albums.php" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete album: &quot;<?php echo htmlspecialchars(addslashes($album['title'])); ?>&quot;?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $album['id']; ?>">
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
