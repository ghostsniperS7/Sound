<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get all artists for dropdown
$artists = fetchAll("SELECT * FROM artists ORDER BY name");

// Get all genres for dropdown
$genres = fetchAll("SELECT * FROM genres ORDER BY name");

// Get all languages for dropdown
$languages = fetchAll("SELECT * FROM languages ORDER BY name");

// Get all years for dropdown
$years = fetchAll("SELECT DISTINCT year FROM years ORDER BY year DESC");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        // Validate and process form data
        $title = trim($_POST['title'] ?? '');
        $artist_id = $_POST['artist_id'] ?? '';
        $genre_id = $_POST['genre_id'] ?? '';
        $language_id = $_POST['language_id'] ?? '';
        $year = $_POST['year'] ?? '';
        $description = trim($_POST['description'] ?? '');

        // Validate required fields
        if (empty($title)) {
            $errors[] = 'Title is required';
        }
        if (empty($artist_id)) {
            $errors[] = 'Artist is required';
        }
        if (empty($genre_id)) {
            $errors[] = 'Genre is required';
        }
        if (empty($language_id)) {
            $errors[] = 'Language is required';
        }
        if (empty($year)) {
            $errors[] = 'Year is required';
        }

        // Handle file uploads
        $file_path = '';
        $thumbnail_path = '';

        // Handle video file upload
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['video_file']['tmp_name'];
            $file_name = basename($_FILES['video_file']['name']);
            $file_size = $_FILES['video_file']['size'];
            $file_type = $_FILES['video_file']['type'];

            // Validate file type
            $allowed_types = ['video/mp4', 'video/quicktime'];
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'Only MP4 files are allowed for videos';
            }

            // Validate file size (1GB max)
            if ($file_size > 1024 * 1024 * 1024) {
                $errors[] = 'Video file size must be less than 1GB';
            }

            if (empty($errors)) {
                // Generate unique filename
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $file_basename = uniqid() . '.' . $file_ext;
                $file_path = 'uploads/video/' . $file_basename;

                // Create directory if it doesn't exist
                if (!file_exists(__DIR__ . '/../uploads/video')) {
                    mkdir(__DIR__ . '/../uploads/video', 0777, true);
                }

                // Move uploaded file
                if (!move_uploaded_file($file_tmp_path, __DIR__ . '/../' . $file_path)) {
                    $errors[] = 'Failed to upload video file';
                }
            }
        } else {
            $errors[] = 'Video file is required';
        }

        // Handle thumbnail image upload
        if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
            $thumbnail_tmp_path = $_FILES['thumbnail_image']['tmp_name'];
            $thumbnail_name = basename($_FILES['thumbnail_image']['name']);
            $thumbnail_size = $_FILES['thumbnail_image']['size'];
            $thumbnail_type = $_FILES['thumbnail_image']['type'];

            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($thumbnail_type, $allowed_types)) {
                $errors[] = 'Only JPEG, PNG, and GIF images are allowed for thumbnails';
            }

            // Validate file size (10MB max)
            if ($thumbnail_size > 10 * 1024 * 1024) {
                $errors[] = 'Thumbnail image size must be less than 10MB';
            }

            if (empty($errors)) {
                // Generate unique filename
                $thumbnail_ext = pathinfo($thumbnail_name, PATHINFO_EXTENSION);
                $thumbnail_basename = uniqid() . '.' . $thumbnail_ext;
                $thumbnail_path = 'uploads/covers/' . $thumbnail_basename;

                // Create directory if it doesn't exist
                if (!file_exists(__DIR__ . '/../uploads/covers')) {
                    mkdir(__DIR__ . '/../uploads/covers', 0777, true);
                }

                // Move uploaded file
                if (!move_uploaded_file($thumbnail_tmp_path, __DIR__ . '/../' . $thumbnail_path)) {
                    $errors[] = 'Failed to upload thumbnail image';
                }
            }
        }

        // If no errors, create video entry
        if (empty($errors)) {
            // Get or create year
            $year_id = fetchOne("SELECT id FROM years WHERE year = ?", [$year])['id'] ?? null;
            if (!$year_id) {
                $stmt = $pdo->prepare("INSERT INTO years (year) VALUES (?)");
                $stmt->execute([$year]);
                $year_id = $pdo->lastInsertId();
            }

            // Insert video record
            $stmt = $pdo->prepare("INSERT INTO videos (artist_id, genre_id, language_id, year_id, title, file_path, thumbnail, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$artist_id, $genre_id, $language_id, $year_id, $title, $file_path, $thumbnail_path, $description]);

            header('Location: videos.php');
            exit;
        }
    }
}
?>

<div class="container">
    <h1>Add New Video</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="add_video.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="artist_id">Artist</label>
            <select id="artist_id" name="artist_id" required>
                <option value="">Select Artist</option>
                <?php foreach ($artists as $artist): ?>
                    <option value="<?php echo $artist['id']; ?>"><?php echo htmlspecialchars($artist['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="genre_id">Genre</label>
            <select id="genre_id" name="genre_id" required>
                <option value="">Select Genre</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo $genre['id']; ?>"><?php echo htmlspecialchars($genre['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="language_id">Language</label>
            <select id="language_id" name="language_id" required>
                <option value="">Select Language</option>
                <?php foreach ($languages as $language): ?>
                    <option value="<?php echo $language['id']; ?>"><?php echo htmlspecialchars($language['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="year">Year</label>
            <select id="year" name="year" required>
                <option value="">Select Year</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year['year']; ?>"><?php echo htmlspecialchars($year['year']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="video_file">Video File (MP4)</label>
            <input type="file" id="video_file" name="video_file" accept=".mp4,video/mp4" required>
        </div>

        <div class="form-group">
            <label for="thumbnail_image">Thumbnail Image (JPEG, PNG, GIF)</label>
            <input type="file" id="thumbnail_image" name="thumbnail_image" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Add Video</button>
        <a href="videos.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>