<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$album_id = intval($_GET['id'] ?? 0);

if (!$album_id) {
    setFlash('error', 'Invalid album ID.');
    header('Location: albums.php');
    exit;
}

$album = fetchOne("SELECT * FROM albums WHERE id = ?", [$album_id]);

if (!$album) {
    setFlash('error', 'Album not found.');
    header('Location: albums.php');
    exit;
}

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

        $cover_path = $album['cover'];

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

            // Real MIME check with finfo_file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $cover_mime = $finfo ? finfo_file($finfo, $cover_tmp_path) : '';
            if ($finfo) finfo_close($finfo);

            $allowed_image_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($cover_mime, $allowed_image_mimes, true)) {
                $errors[] = 'Invalid cover image: MIME type must be image/jpeg, image/png, image/gif, or image/webp (detected: ' . htmlspecialchars($cover_mime ?: 'unknown') . ')';
            }

            if (empty($errors)) {
                $cover_basename = uniqid('album_') . '.' . $cover_ext;
                $dest_dir = UPLOAD_DIR . 'covers';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $dest_path = $dest_dir . DIRECTORY_SEPARATOR . $cover_basename;

                if (move_uploaded_file($cover_tmp_path, $dest_path)) {
                    if (!empty($album['cover'])) {
                        $old_phys = ROOT_PATH . ltrim($album['cover'], '/\\');
                        if (file_exists($old_phys)) @unlink($old_phys);
                    }
                    $cover_path = 'uploads/covers/' . $cover_basename;
                } else {
                    $errors[] = 'Failed to save updated cover image';
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE albums SET artist_id = ?, title = ?, year = ?, cover = ? WHERE id = ?");
            $stmt->execute([$artist_id, $title, $year ?: null, $cover_path, $album_id]);

            clearOldInput();
            setFlash('success', 'Album "' . htmlspecialchars($title) . '" updated successfully.');
            header('Location: albums.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: edit_album.php?id=' . $album_id);
    exit;
}

$form_title = getOldInput('title', $album['title']);
$form_artist_id = getOldInput('artist_id', $album['artist_id']);
$form_year = getOldInput('year', $album['year']);
clearOldInput();

$page_title = 'Edit Album: ' . $album['title'];
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Albums', 'url' => '/sound/admin/albums.php'],
    ['title' => 'Edit Album']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Edit Album</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Modify album details.</p>
    </div>
    <div class="header-actions">
        <a href="albums.php" class="btn btn-secondary">
            &larr; Back to Albums
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="edit_album.php?id=<?php echo $album_id; ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="title">Album Title <span class="required-mark">*</span></label>
            <input type="text" id="title" name="title" class="admin-form-control" value="<?php echo htmlspecialchars($form_title); ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
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
                <input type="number" id="year" name="year" class="admin-form-control" value="<?php echo htmlspecialchars($form_year); ?>" min="1900" max="<?php echo date('Y') + 1; ?>">
            </div>
        </div>

        <div class="admin-form-group">
            <label for="cover">Replace Album Cover Image (Optional)</label>
            <input type="file" id="cover" name="cover" class="admin-form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
            <?php if (!empty($album['cover'])): ?>
                <div class="admin-media-preview" style="margin-top: 10px; display: flex; align-items: center; gap: 12px;">
                    <?php echo render_cover_tile($album['title'], $album['cover'], 'Current Cover', '', 'style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover;"'); ?>
                    <span style="font-size: 13px; color: var(--admin-text-muted);">Current Cover</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update Album</button>
            <a href="albums.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
