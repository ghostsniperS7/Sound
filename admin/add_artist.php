<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

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
            $stmt = $pdo->prepare("INSERT INTO artists (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);

            setFlash('success', 'Artist "' . htmlspecialchars($name) . '" added successfully.');
            header('Location: artists.php');
            exit;
        }
    }
}

$page_title = 'Add New Artist';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Artists', 'url' => '/sound/admin/artists.php'],
    ['title' => 'Add Artist']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Add New Artist</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Create a new artist profile.</p>
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
    <form method="post" action="add_artist.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="name">Artist Name <span class="required-mark">*</span></label>
            <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required placeholder="e.g. Queen, Atif Aslam">
        </div>

        <div class="admin-form-group">
            <label for="description">Artist Biography / Description</label>
            <textarea id="description" name="description" class="admin-form-control" rows="5" placeholder="Optional background information about the artist..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Add Artist</button>
            <a href="artists.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
