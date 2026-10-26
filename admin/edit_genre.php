<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$genre_id = intval($_GET['id'] ?? 0);

if (!$genre_id) {
    setFlash('error', 'Invalid genre ID.');
    header('Location: genres.php');
    exit;
}

$genre = fetchOne("SELECT * FROM genres WHERE id = ?", [$genre_id]);

if (!$genre) {
    setFlash('error', 'Genre not found.');
    header('Location: genres.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $errors[] = 'Genre name is required';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE genres SET name = ? WHERE id = ?");
            $stmt->execute([$name, $genre_id]);

            setFlash('success', 'Genre "' . htmlspecialchars($name) . '" updated successfully.');
            header('Location: genres.php');
            exit;
        }
    }
}

$page_title = 'Edit Genre: ' . $genre['name'];
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Genres', 'url' => '/sound/admin/genres.php'],
    ['title' => 'Edit Genre']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Edit Genre</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Modify genre details.</p>
    </div>
    <div class="header-actions">
        <a href="genres.php" class="btn btn-secondary">
            &larr; Back to Genres
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
    <form method="post" action="edit_genre.php?id=<?php echo $genre_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="name">Genre Name <span class="required-mark">*</span></label>
            <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? $genre['name']); ?>" required>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update Genre</button>
            <a href="genres.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
