<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$artist_id = intval($_GET['id'] ?? 0);

if (!$artist_id) {
    setFlash('error', 'Invalid artist ID.');
    header('Location: artists.php');
    exit;
}

$artist = fetchOne("SELECT * FROM artists WHERE id = ?", [$artist_id]);

if (!$artist) {
    setFlash('error', 'Artist not found.');
    header('Location: artists.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $errors[] = 'Artist name is required';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE artists SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $artist_id]);

            setFlash('success', 'Artist "' . htmlspecialchars($name) . '" updated successfully.');
            header('Location: artists.php');
            exit;
        }
    }
}

$page_title = 'Edit Artist: ' . $artist['name'];
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Artists', 'url' => '/sound/admin/artists.php'],
    ['title' => 'Edit Artist']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Edit Artist</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Modify artist information.</p>
    </div>
    <div class="header-actions">
        <a href="artists.php" class="btn btn-secondary">
            &larr; Back to Artists
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert admin-alert-error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="admin-form-card">
    <form method="post" action="edit_artist.php?id=<?php echo $artist_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="name">Artist Name <span class="required-mark">*</span></label>
            <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? $artist['name']); ?>" required>
        </div>

        <div class="admin-form-group">
            <label for="description">Artist Biography / Description</label>
            <textarea id="description" name="description" class="admin-form-control" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? $artist['description']); ?></textarea>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update Artist</button>
            <a href="artists.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
