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
            $stmt = $pdo->prepare("UPDATE genres SET name = ? WHERE id = ?");
            $stmt->execute([$name, $genre_id]);

            clearOldInput();
            setFlash('success', 'Genre "' . htmlspecialchars($name) . '" updated successfully.');
            header('Location: genres.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: edit_genre.php?id=' . $genre_id);
    exit;
}

$form_name = getOldInput('name', $genre['name']);
clearOldInput();

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

<div class="admin-form-card">
    <form method="post" action="edit_genre.php?id=<?php echo $genre_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="name">Genre Name <span class="required-mark">*</span></label>
            <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($form_name); ?>" required>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update Genre</button>
            <a href="genres.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
