<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $delete_id = intval($_POST['id'] ?? 0);
        $genre = fetchOne("SELECT * FROM genres WHERE id = ?", [$delete_id]);
        if ($genre) {
            $stmt = $pdo->prepare("DELETE FROM genres WHERE id = ?");
            $stmt->execute([$delete_id]);
            setFlash('success', 'Genre "' . htmlspecialchars($genre['name']) . '" deleted successfully.');
        } else {
            setFlash('error', 'Genre not found.');
        }
    }
    header('Location: genres.php');
    exit;
}

$page_title = 'Manage Genres';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Genres']
];
require_once __DIR__ . '/includes/admin_header.php';

// Search and Pagination
$search = trim($_GET['search'] ?? '');
$params = [];
$where_clauses = [];

if ($search !== '') {
    $where_clauses[] = "name LIKE ?";
    $params = ['%' . $search . '%'];
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_sql = "SELECT COUNT(*) as count FROM genres $where_sql";
$total_items = fetchOne($count_sql, $params)['count'] ?? 0;

$per_page = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$page = max(1, min($total_pages, intval($_GET['page'] ?? 1)));
$offset = ($page - 1) * $per_page;

$items_sql = "SELECT g.*, 
              (SELECT COUNT(*) FROM music WHERE genre_id = g.id) as music_count,
              (SELECT COUNT(*) FROM videos WHERE genre_id = g.id) as video_count
              FROM genres g 
              $where_sql 
              ORDER BY g.name ASC LIMIT $per_page OFFSET $offset";
$genres = fetchAll($items_sql, $params);
?>

<div class="admin-page-header">
    <div>
        <h1>Manage Genres</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Browse, search, edit, or remove music and video genres.</p>
    </div>
    <div class="header-actions">
        <a href="add_genre.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Genre
        </a>
    </div>
</div>

<div class="admin-table-container">
    <div class="admin-table-toolbar">
        <form method="get" action="genres.php" class="admin-search-form">
            <input type="text" name="search" class="admin-search-input" placeholder="Search genres by name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            <?php if ($search !== ''): ?>
                <a href="genres.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <div style="font-size: 13px; color: var(--admin-text-muted);">
            Total: <strong><?php echo $total_items; ?></strong> genres
        </div>
    </div>

    <?php if (empty($genres)): ?>
        <div class="admin-empty-state">
            <svg class="admin-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="9" x2="20" y2="9"></line>
                <line x1="4" y1="15" x2="20" y2="15"></line>
                <line x1="10" y1="3" x2="8" y2="21"></line>
                <line x1="16" y1="3" x2="14" y2="21"></line>
            </svg>
            <h3>No Genres Found</h3>
            <p><?php echo $search !== '' ? 'No genres matched your search "' . htmlspecialchars($search) . '".' : 'No genres have been added yet.'; ?></p>
            <?php if ($search !== ''): ?>
                <a href="genres.php" class="btn btn-secondary btn-sm">Clear Search</a>
            <?php else: ?>
                <a href="add_genre.php" class="btn btn-primary btn-sm">Add New Genre</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Genre Name</th>
                        <th>Music Tracks</th>
                        <th>Videos</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($genres as $genre): ?>
                        <tr>
                            <td style="font-weight: 600; color: #fff;">
                                <?php echo htmlspecialchars($genre['name']); ?>
                            </td>
                            <td><span class="admin-badge admin-badge-music"><?php echo $genre['music_count']; ?> tracks</span></td>
                            <td><span class="admin-badge admin-badge-video"><?php echo $genre['video_count']; ?> videos</span></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="edit_genre.php?id=<?php echo $genre['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="post" action="genres.php" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete genre: &quot;<?php echo htmlspecialchars(addslashes($genre['name'])); ?>&quot;?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $genre['id']; ?>">
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
