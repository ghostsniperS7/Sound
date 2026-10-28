<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$artists = fetchAll("SELECT * FROM artists ORDER BY name");
$genres = fetchAll("SELECT * FROM genres ORDER BY name");
$languages = fetchAll("SELECT * FROM languages ORDER BY name");
$years = fetchAll("SELECT DISTINCT year FROM years ORDER BY year DESC");
$albums = fetchAll("SELECT al.*, ar.name as artist_name FROM albums al JOIN artists ar ON al.artist_id = ar.id ORDER BY ar.name ASC, al.title ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $artist_id = intval($_POST['artist_id'] ?? 0);
        $album_id = !empty($_POST['album_id']) ? intval($_POST['album_id']) : null;
        $genre_id = intval($_POST['genre_id'] ?? 0);
        $language_id = intval($_POST['language_id'] ?? 0);
        $year = intval($_POST['year'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if (empty($title)) $errors[] = 'Title is required';
        if (empty($artist_id)) $errors[] = 'Artist is required';
        if (empty($genre_id)) $errors[] = 'Genre is required';
        if (empty($language_id)) $errors[] = 'Language is required';
        if (empty($year)) $errors[] = 'Year is required';

        $file_path = '';
        $thumbnail_path = '';

        // Handle video file upload
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

            // Real MIME check with finfo_file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $video_mime = $finfo ? finfo_file($finfo, $file_tmp_path) : '';
            if ($finfo) finfo_close($finfo);

            $allowed_video_mimes = ['video/mp4', 'video/webm', 'video/quicktime'];
            if (!in_array($video_mime, $allowed_video_mimes, true)) {
                $errors[] = 'Invalid video file: MIME type must be video/mp4, video/webm, or video/quicktime (detected: ' . htmlspecialchars($video_mime ?: 'unknown') . ')';
            }

            if (empty($errors)) {
                $file_basename = uniqid('video_') . '.' . $file_ext;
                $dest_dir = UPLOAD_DIR . 'videos';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $dest_path = $dest_dir . DIRECTORY_SEPARATOR . $file_basename;

                if (!move_uploaded_file($file_tmp_path, $dest_path)) {
                    $errors[] = 'Failed to save uploaded video file';
                } else {
                    $file_path = 'uploads/videos/' . $file_basename;
                }
            }
        } else {
            $errors[] = 'Video file is required';
        }

        // Handle thumbnail upload
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

            // Real MIME check with finfo_file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $thumb_mime = $finfo ? finfo_file($finfo, $thumb_tmp_path) : '';
            if ($finfo) finfo_close($finfo);

            $allowed_image_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($thumb_mime, $allowed_image_mimes, true)) {
                $errors[] = 'Invalid thumbnail image: MIME type must be image/jpeg, image/png, image/gif, or image/webp (detected: ' . htmlspecialchars($thumb_mime ?: 'unknown') . ')';
            }

            if (empty($errors)) {
                $thumb_basename = uniqid('thumb_') . '.' . $thumb_ext;
                $dest_dir = UPLOAD_DIR . 'thumbnails';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $dest_path = $dest_dir . DIRECTORY_SEPARATOR . $thumb_basename;

                if (!move_uploaded_file($thumb_tmp_path, $dest_path)) {
                    $errors[] = 'Failed to save uploaded thumbnail image';
                } else {
                    $thumbnail_path = 'uploads/thumbnails/' . $thumb_basename;
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

            $stmt = $pdo->prepare("INSERT INTO videos (artist_id, album_id, genre_id, language_id, year_id, title, file_path, thumbnail, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$artist_id, $album_id, $genre_id, $language_id, $year_id, $title, $file_path, $thumbnail_path, $description]);

            clearOldInput();
            setFlash('success', 'Video "' . htmlspecialchars($title) . '" uploaded successfully.');
            header('Location: videos.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: add_video.php');
    exit;
}

$form_title = getOldInput('title', '');
$form_artist_id = getOldInput('artist_id', 0);
$form_album_id = getOldInput('album_id', 0);
$form_genre_id = getOldInput('genre_id', 0);
$form_language_id = getOldInput('language_id', 0);
$form_year = getOldInput('year', date('Y'));
$form_description = getOldInput('description', '');
clearOldInput();

$page_title = 'Add New Video';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Videos', 'url' => '/sound/admin/videos.php'],
    ['title' => 'Add Video']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Add New Video</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Upload a video and optional thumbnail.</p>
    </div>
    <div class="header-actions">
        <a href="videos.php" class="btn btn-secondary">
            &larr; Back to Videos
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="add_video.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="title">Video Title <span class="required-mark">*</span></label>
            <input type="text" id="title" name="title" class="admin-form-control" value="<?php echo htmlspecialchars($form_title); ?>" required placeholder="e.g. Official Music Video">
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
                <label for="album_id">Album <span style="color: var(--admin-text-muted); font-size: 12px; font-weight: normal;">(Optional)</span></label>
                <select id="album_id" name="album_id" class="admin-form-control">
                    <option value="">No Album (Single / Standalone)</option>
                    <?php foreach ($albums as $album): ?>
                        <option value="<?php echo $album['id']; ?>" data-artist-id="<?php echo $album['artist_id']; ?>" <?php echo ($form_album_id == $album['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($album['title'] . (!empty($album['artist_name']) ? ' — ' . $album['artist_name'] : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="admin-form-hint">Filters to selected artist's albums automatically.</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
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
            <label for="video_file">Video File (MP4, WebM, MOV) <span class="required-mark">*</span></label>
            <input type="file" id="video_file" name="video_file" class="admin-form-control" accept=".mp4,.webm,.mov,video/*" required>
            <div class="admin-form-hint">Accepted formats: MP4, WebM, MOV (Max size: 500MB)</div>
        </div>

        <div class="admin-form-group">
            <label for="thumbnail">Thumbnail Image</label>
            <input type="file" id="thumbnail" name="thumbnail" class="admin-form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
            <div class="admin-form-hint">Accepted formats: JPG, PNG, GIF, WEBP (Max size: 10MB)</div>
        </div>

        <div class="admin-form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="admin-form-control" rows="4" placeholder="Optional video description..."><?php echo htmlspecialchars($form_description); ?></textarea>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Add Video</button>
            <a href="videos.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var artistSelect = document.getElementById('artist_id');
    var albumSelect = document.getElementById('album_id');
    if (!artistSelect || !albumSelect) return;

    function filterAlbums() {
        var selectedArtist = artistSelect.value;
        var currentSelectedAlbum = albumSelect.value;
        var options = albumSelect.querySelectorAll('option');

        options.forEach(function(opt) {
            if (!opt.value) {
                opt.hidden = false;
                opt.disabled = false;
                return;
            }
            var optArtist = opt.getAttribute('data-artist-id');
            if (!selectedArtist || optArtist === selectedArtist) {
                opt.hidden = false;
                opt.disabled = false;
            } else {
                opt.hidden = true;
                opt.disabled = true;
                if (opt.value === currentSelectedAlbum) {
                    albumSelect.value = '';
                }
            }
        });
    }

    artistSelect.addEventListener('change', filterAlbums);
    filterAlbums();
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
