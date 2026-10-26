<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$video_id = intval($_GET['id'] ?? 0);

if (!$video_id) {
    setFlash('error', 'Invalid video ID.');
    header('Location: videos.php');
    exit;
}

$video = fetchOne("SELECT v.*, a.name as artist_name, g.name as genre_name, l.name as language_name, y.year 
                   FROM videos v 
                   LEFT JOIN artists a ON v.artist_id = a.id 
                   LEFT JOIN genres g ON v.genre_id = g.id 
                   LEFT JOIN languages l ON v.language_id = l.id 
                   LEFT JOIN years y ON v.year_id = y.id 
                   WHERE v.id = ?", [$video_id]);

if (!$video) {
    setFlash('error', 'Video not found.');
    header('Location: videos.php');
    exit;
}

$artists = fetchAll("SELECT * FROM artists ORDER BY name");
$genres = fetchAll("SELECT * FROM genres ORDER BY name");
$languages = fetchAll("SELECT * FROM languages ORDER BY name");
$years = fetchAll("SELECT DISTINCT year FROM years ORDER BY year DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $artist_id = intval($_POST['artist_id'] ?? 0);
        $genre_id = intval($_POST['genre_id'] ?? 0);
        $language_id = intval($_POST['language_id'] ?? 0);
        $year = intval($_POST['year'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if (empty($title)) $errors[] = 'Title is required';
        if (empty($artist_id)) $errors[] = 'Artist is required';
        if (empty($genre_id)) $errors[] = 'Genre is required';
        if (empty($language_id)) $errors[] = 'Language is required';
        if (empty($year)) $errors[] = 'Year is required';

        $file_path = $video['file_path'];
        $thumbnail_path = $video['thumbnail'];

        // Handle video file replacement
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['video_file']['tmp_name'];
            $file_name = basename($_FILES['video_file']['name']);
            $file_size = $_FILES['video_file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_video_exts = ['mp4', 'webm', 'mov'];
            if (!in_array($file_ext, $allowed_video_exts)) {
                $errors[] = 'Only MP4, WebM, and MOV files are allowed for videos';
            }
            if ($file_size > 500 * 1024 * 1024) {
                $errors[] = 'Video file size must be less than 500MB';
            }

            if (empty($errors)) {
                $file_basename = uniqid('video_') . '.' . $file_ext;
                $dest_dir = UPLOAD_DIR . 'videos';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $dest_path = $dest_dir . DIRECTORY_SEPARATOR . $file_basename;

                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    if (!empty($video['file_path'])) {
                        $old_phys = ROOT_PATH . ltrim($video['file_path'], '/\\');
                        if (file_exists($old_phys)) @unlink($old_phys);
                    }
                    $file_path = 'uploads/videos/' . $file_basename;
                } else {
                    $errors[] = 'Failed to save updated video file';
                }
            }
        }

        // Handle thumbnail replacement
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumb_tmp_path = $_FILES['thumbnail']['tmp_name'];
            $thumb_name = basename($_FILES['thumbnail']['name']);
            $thumb_size = $_FILES['thumbnail']['size'];
            $thumb_ext = strtolower(pathinfo($thumb_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($thumb_ext, $allowed_exts)) {
                $errors[] = 'Only JPEG, PNG, GIF, and WEBP images are allowed for thumbnail';
            }
            if ($thumb_size > 10 * 1024 * 1024) {
                $errors[] = 'Thumbnail size must be less than 10MB';
            }

            if (empty($errors)) {
                $thumb_basename = uniqid('thumb_') . '.' . $thumb_ext;
                $dest_dir = UPLOAD_DIR . 'thumbnails';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $dest_path = $dest_dir . DIRECTORY_SEPARATOR . $thumb_basename;

                if (move_uploaded_file($thumb_tmp_path, $dest_path)) {
                    if (!empty($video['thumbnail'])) {
                        $old_thumb_phys = ROOT_PATH . ltrim($video['thumbnail'], '/\\');
                        if (file_exists($old_thumb_phys)) @unlink($old_thumb_phys);
                    }
                    $thumbnail_path = 'uploads/thumbnails/' . $thumb_basename;
                } else {
                    $errors[] = 'Failed to save updated thumbnail image';
                }
            }
        }

        if (empty($errors)) {
            $year_id = fetchOne("SELECT id FROM years WHERE year = ?", [$year])['id'] ?? null;
            if (!$year_id) {
                $stmt = $pdo->prepare("INSERT INTO years (year) VALUES (?)");
                $stmt->execute([$year]);
                $year_id = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare("UPDATE videos SET artist_id = ?, genre_id = ?, language_id = ?, year_id = ?, title = ?, file_path = ?, thumbnail = ?, description = ? WHERE id = ?");
            $stmt->execute([$artist_id, $genre_id, $language_id, $year_id, $title, $file_path, $thumbnail_path, $description, $video_id]);

            clearOldInput();
            setFlash('success', 'Video "' . htmlspecialchars($title) . '" updated successfully.');
            header('Location: videos.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: edit_video.php?id=' . $video_id);
    exit;
}

$form_title = getOldInput('title', $video['title']);
$form_artist_id = getOldInput('artist_id', $video['artist_id']);
$form_genre_id = getOldInput('genre_id', $video['genre_id']);
$form_language_id = getOldInput('language_id', $video['language_id']);
$form_year = getOldInput('year', $video['year']);
$form_description = getOldInput('description', $video['description']);
clearOldInput();

$page_title = 'Edit Video: ' . $video['title'];
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Videos', 'url' => '/sound/admin/videos.php'],
    ['title' => 'Edit Video']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Edit Video</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Update details or files for "<?php echo htmlspecialchars($video['title']); ?>".</p>
    </div>
    <div class="header-actions">
        <a href="videos.php" class="btn btn-secondary">
            &larr; Back to Videos
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="edit_video.php?id=<?php echo $video_id; ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="title">Video Title <span class="required-mark">*</span></label>
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
                <label for="genre_id">Genre <span class="required-mark">*</span></label>
                <select id="genre_id" name="genre_id" class="admin-form-control" required>
                    <option value="">Select Genre</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo $genre['id']; ?>" <?php echo ($form_genre_id == $genre['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($genre['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="language_id">Language <span class="required-mark">*</span></label>
                <select id="language_id" name="language_id" class="admin-form-control" required>
                    <option value="">Select Language</option>
                    <?php foreach ($languages as $language): ?>
                        <option value="<?php echo $language['id']; ?>" <?php echo ($form_language_id == $language['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($language['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="year">Release Year <span class="required-mark">*</span></label>
                <select id="year" name="year" class="admin-form-control" required>
                    <option value="">Select Year</option>
                    <?php 
                    $current_yr = intval(date('Y'));
                    for ($y = $current_yr + 1; $y >= 1950; $y--): 
                    ?>
                        <option value="<?php echo $y; ?>" <?php echo ($form_year == $y) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="admin-form-group">
            <label for="video_file">Replace Video File (Optional)</label>
            <input type="file" id="video_file" name="video_file" class="admin-form-control" accept=".mp4,.webm,.mov,video/*">
            <?php if (!empty($video['file_path'])): ?>
                <div class="admin-form-hint">Current video: <code><?php echo htmlspecialchars(basename($video['file_path'])); ?></code></div>
            <?php endif; ?>
        </div>

        <div class="admin-form-group">
            <label for="thumbnail">Replace Thumbnail Image (Optional)</label>
            <input type="file" id="thumbnail" name="thumbnail" class="admin-form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
            <?php if (!empty($video['thumbnail'])): ?>
                <div class="admin-media-preview" style="margin-top: 10px; display: flex; align-items: center; gap: 12px;">
                    <?php if (asset_exists($video['thumbnail'])): ?>
                        <img src="<?php echo htmlspecialchars(asset_url($video['thumbnail'])); ?>" alt="Current Thumbnail" style="width: 64px; height: 42px; border-radius: 4px; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 64px; height: 42px; border-radius: 4px; background: #242424; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; color: var(--accent);">MP4</div>
                    <?php endif; ?>
                    <span style="font-size: 13px; color: var(--admin-text-muted);">Current Thumbnail</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="admin-form-control" rows="4"><?php echo htmlspecialchars($form_description); ?></textarea>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update Video</button>
            <a href="videos.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
