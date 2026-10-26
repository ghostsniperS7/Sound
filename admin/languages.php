<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        $delete_id = intval($_POST['id'] ?? 0);
        $lang = fetchOne("SELECT * FROM languages WHERE id = ?", [$delete_id]);
        if ($lang) {
            $stmt = $pdo->prepare("DELETE FROM languages WHERE id = ?");
            $stmt->execute([$delete_id]);
            setFlash('success', 'Language "' . htmlspecialchars($lang['name']) . '" deleted successfully.');
        } else {
            setFlash('error', 'Language not found.');
        }
    }
    header('Location: languages.php');
    exit;
}

$page_title = 'Manage Languages';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Languages']
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

$count_sql = "SELECT COUNT(*) as count FROM languages $where_sql";
$total_items = fetchOne($count_sql, $params)['count'] ?? 0;

$per_page = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$page = max(1, min($total_pages, intval($_GET['page'] ?? 1)));
$offset = ($page - 1) * $per_page;

$items_sql = "SELECT l.*, 
              (SELECT COUNT(*) FROM music WHERE language_id = l.id) as music_count,
              (SELECT COUNT(*) FROM videos WHERE language_id = l.id) as video_count
              FROM languages l 
              $where_sql 
              ORDER BY l.name ASC LIMIT $per_page OFFSET $offset";
$languages = fetchAll($items_sql, $params);
?>

<div class="admin-page-header">
    <div>
        <h1>Manage Languages</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Browse, search, edit, or remove media languages.</p>
    </div>
    <div class="header-actions">
        <a href="add_language.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Language
        </a>
    </div>
</div>

<div class="admin-table-container">
    <div class="admin-table-toolbar">
        <form method="get" action="languages.php" class="admin-search-form">
            <input type="text" name="search" class="admin-search-input" placeholder="Search languages..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            <?php if ($search !== ''): ?>
                <a href="languages.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <div style="font-size: 13px; color: var(--admin-text-muted);">
            Total: <strong><?php echo $total_items; ?></strong> languages
        </div>
    </div>

    <?php if (empty($languages)): ?>
        <div class="admin-empty-state">
            <svg class="admin-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <h3>No Languages Found</h3>
            <p><?php echo $search !== '' ? 'No languages matched your search "' . htmlspecialchars($search) . '".' : 'No languages have been added yet.'; ?></p>
            <?php if ($search !== ''): ?>
                <a href="languages.php" class="btn btn-secondary btn-sm">Clear Search</a>
            <?php else: ?>
                <a href="add_language.php" class="btn btn-primary btn-sm">Add New Language</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Language Name</th>
                        <th>Music Tracks</th>
                        <th>Videos</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($languages as $lang): ?>
                        <tr>
                            <td style="font-weight: 600; color: #fff;">
                                <?php echo htmlspecialchars($lang['name']); ?>
                            </td>
                            <td><span class="admin-badge admin-badge-music"><?php echo $lang['music_count']; ?> tracks</span></td>
                            <td><span class="admin-badge admin-badge-video"><?php echo $lang['video_count']; ?> videos</span></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <a href="edit_language.php?id=<?php echo $lang['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="post" action="languages.php" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete language: &quot;<?php echo htmlspecialchars(addslashes($lang['name'])); ?>&quot;?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $lang['id']; ?>">
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
