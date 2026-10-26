<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $errors[] = 'Genre name is required';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
            $stmt->execute([$name]);

            clearOldInput();
            setFlash('success', 'Genre "' . htmlspecialchars($name) . '" added successfully.');
            header('Location: genres.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: add_genre.php');
    exit;
}

$form_name = getOldInput('name', '');
clearOldInput();

$page_title = 'Add New Genre';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Genres', 'url' => '/sound/admin/genres.php'],
    ['title' => 'Add Genre']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Add New Genre</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Define a new musical or video category.</p>
    </div>
    <div class="header-actions">
        <a href="genres.php" class="btn btn-secondary">
            &larr; Back to Genres
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="add_genre.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="name">Genre Name <span class="required-mark">*</span></label>
            <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($form_name); ?>" required placeholder="e.g. Rock, Pop, Classical">
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Add Genre</button>
            <a href="genres.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
