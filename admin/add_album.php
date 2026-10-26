<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$artists = fetchAll("SELECT * FROM artists ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $artist_id = intval($_POST['artist_id'] ?? 0);
        $year = intval($_POST['year'] ?? 0);

        if (empty($title)) $errors[] = 'Album title is required';
        if (empty($artist_id)) $errors[] = 'Artist is required';

        $cover_path = '';
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $cover_tmp_path = $_FILES['cover']['tmp_name'];
            $cover_name = basename($_FILES['cover']['name']);
            $cover_size = $_FILES['cover']['size'];
            $cover_ext = strtolower(pathinfo($cover_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($cover_ext, $allowed_exts)) {
                $errors[] = 'Only JPEG, PNG, GIF, and WEBP images are allowed for album cover';
            }
            if ($cover_size > 10 * 1024 * 1024) {
                $errors[] = 'Cover image size must be less than 10MB';
            }

            if (empty($errors)) {
                $cover_basename = uniqid('album_') . '.' . $cover_ext;
                $dest_dir = UPLOAD_DIR . 'covers';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $dest_path = $dest_dir . DIRECTORY_SEPARATOR . $cover_basename;

                if (!move_uploaded_file($cover_tmp_path, $dest_path)) {
                    $errors[] = 'Failed to save uploaded cover image';
                } else {
                    $cover_path = 'uploads/covers/' . $cover_basename;
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO albums (artist_id, title, year, cover) VALUES (?, ?, ?, ?)");
            $stmt->execute([$artist_id, $title, $year ?: null, $cover_path]);

            clearOldInput();
            setFlash('success', 'Album "' . htmlspecialchars($title) . '" created successfully.');
            header('Location: albums.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: add_album.php');
    exit;
}

$form_title = getOldInput('title', '');
$form_artist_id = getOldInput('artist_id', 0);
$form_year = getOldInput('year', '');
clearOldInput();

$page_title = 'Add New Album';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Albums', 'url' => '/sound/admin/albums.php'],
    ['title' => 'Add Album']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Add New Album</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Create an album entry.</p>
    </div>
    <div class="header-actions">
        <a href="albums.php" class="btn btn-secondary">
            &larr; Back to Albums
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="add_album.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="title">Album Title <span class="required-mark">*</span></label>
            <input type="text" id="title" name="title" class="admin-form-control" value="<?php echo htmlspecialchars($form_title); ?>" required placeholder="e.g. Abbey Road">
        </div>

        <div class="admin-form-group">
            <label for="artist_id">Artist <span class="required-mark">*</span></label>
            <select id="artist_id" name="artist_id" class="admin-form-control" required>
                <option value="">Select Artist</option>
                <?php foreach ($artists as $artist): ?>
                    <option value="<?php echo $artist['id']; ?>" <?php echo ($form_artist_id == $artist['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($artist['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-form-group">
            <label for="year">Release Year</label>
            <input type="number" id="year" name="year" class="admin-form-control" value="<?php echo htmlspecialchars($form_year); ?>" min="1900" max="<?php echo date('Y') + 1; ?>" placeholder="e.g. 1969">
        </div>

        <div class="admin-form-group">
            <label for="cover">Album Cover Image</label>
            <input type="file" id="cover" name="cover" class="admin-form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
            <div class="admin-form-hint">Accepted formats: JPG, PNG, GIF, WEBP (Max size: 10MB)</div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Create Album</button>
            <a href="albums.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
