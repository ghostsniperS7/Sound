<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$artists = fetchAll("SELECT * FROM artists ORDER BY name");
$genres = fetchAll("SELECT * FROM genres ORDER BY name");
$languages = fetchAll("SELECT * FROM languages ORDER BY name");
$years = fetchAll("SELECT DISTINCT year FROM years ORDER BY year DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Validate CSRF token
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

        $file_path = '';
        $cover_path = '';

        // Handle music file upload
        if (isset($_FILES['music_file']) && $_FILES['music_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['music_file']['tmp_name'];
            $file_name = basename($_FILES['music_file']['name']);
            $file_size = $_FILES['music_file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_ext !== 'mp3') {
                $errors[] = 'Only MP3 files are allowed for music';
            }
            if ($file_size > UPLOAD_MAX_SIZE) {
                $errors[] = 'Music file size must be less than 100MB';
            }

            if (empty($errors)) {
                $file_basename = uniqid('track_') . '.' . $file_ext;
                $dest_dir = UPLOAD_DIR . 'music';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $target_disk_path = $dest_dir . DIRECTORY_SEPARATOR . $file_basename;

                if (!move_uploaded_file($file_tmp_path, $target_disk_path)) {
                    $errors[] = 'Failed to move uploaded music file to destination directory.';
                } else {
                    $file_path = 'uploads/music/' . $file_basename;
                }
            }
        } else {
            $errors[] = 'Music audio file is required (.mp3 format)';
        }

        // Handle cover image upload
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_tmp_path = $_FILES['cover_image']['tmp_name'];
            $cover_name = basename($_FILES['cover_image']['name']);
            $cover_size = $_FILES['cover_image']['size'];
            $cover_ext = strtolower(pathinfo($cover_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($cover_ext, $allowed_exts)) {
                $errors[] = 'Only JPEG, PNG, GIF, and WEBP images are allowed for cover';
            }
            if ($cover_size > 10 * 1024 * 1024) {
                $errors[] = 'Cover image size must be less than 10MB';
            }

            if (empty($errors)) {
                $cover_basename = uniqid('cover_') . '.' . $cover_ext;
                $dest_dir = UPLOAD_DIR . 'covers';
                if (!is_dir($dest_dir)) {
                    @mkdir($dest_dir, 0777, true);
                }
                $target_disk_path = $dest_dir . DIRECTORY_SEPARATOR . $cover_basename;

                if (!move_uploaded_file($cover_tmp_path, $target_disk_path)) {
                    $errors[] = 'Failed to move uploaded cover image to destination directory.';
                } else {
                    $cover_path = 'uploads/covers/' . $cover_basename;
                }
            }
        }

        if (empty($errors)) {
            // Get or create year
            $year_id = fetchOne("SELECT id FROM years WHERE year = ?", [$year])['id'] ?? null;
            if (!$year_id) {
                $stmt = $pdo->prepare("INSERT INTO years (year) VALUES (?)");
                $stmt->execute([$year]);
                $year_id = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare("INSERT INTO music (artist_id, genre_id, language_id, year_id, title, file_path, cover, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$artist_id, $genre_id, $language_id, $year_id, $title, $file_path, $cover_path, $description]);

            clearOldInput();
            setFlash('success', 'Music track "' . htmlspecialchars($title) . '" uploaded successfully.');
            header('Location: music.php');
            exit;
        }
    }

    // Post/Redirect/Get on validation error
    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: add_music.php');
    exit;
}

// Retrieve old input if returning from failed POST
$form_title = getOldInput('title', '');
$form_artist_id = getOldInput('artist_id', 0);
$form_genre_id = getOldInput('genre_id', 0);
$form_language_id = getOldInput('language_id', 0);
$form_year = getOldInput('year', date('Y'));
$form_description = getOldInput('description', '');
clearOldInput();

$page_title = 'Add New Music';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Music', 'url' => '/sound/admin/music.php'],
    ['title' => 'Add Music']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Add New Music</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Upload an MP3 audio track and metadata.</p>
    </div>
    <div class="header-actions">
        <a href="music.php" class="btn btn-secondary">
            &larr; Back to Music
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="add_music.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="title">Track Title <span class="required-mark">*</span></label>
            <input type="text" id="title" name="title" class="admin-form-control" value="<?php echo htmlspecialchars($form_title); ?>" required placeholder="e.g. Bohemian Rhapsody">
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
            <label for="music_file">Music Audio File (MP3) <span class="required-mark">*</span></label>
            <input type="file" id="music_file" name="music_file" class="admin-form-control" accept=".mp3,audio/mpeg" required>
            <div class="admin-form-hint">Accepted format: .mp3 (Max size: 100MB)</div>
        </div>

        <div class="admin-form-group">
            <label for="cover_image">Cover Image</label>
            <input type="file" id="cover_image" name="cover_image" class="admin-form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
            <div class="admin-form-hint">Accepted formats: JPG, PNG, GIF, WEBP (Max size: 10MB)</div>
        </div>

        <div class="admin-form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="admin-form-control" rows="4" placeholder="Optional notes or lyrics..."><?php echo htmlspecialchars($form_description); ?></textarea>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Add Music</button>
            <a href="music.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
