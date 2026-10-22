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
        $cover_path = '';

        // Handle music file upload
        if (isset($_FILES['music_file']) && $_FILES['music_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['music_file']['tmp_name'];
            $file_name = basename($_FILES['music_file']['name']);
            $file_size = $_FILES['music_file']['size'];
            $file_type = $_FILES['music_file']['type'];

            // Validate file type
            $allowed_types = ['audio/mpeg', 'audio/mp3'];
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'Only MP3 files are allowed for music';
            }

            // Validate file size (100MB max)
            if ($file_size > UPLOAD_MAX_SIZE) {
                $errors[] = 'Music file size must be less than 100MB';
            }

            if (empty($errors)) {
                // Generate unique filename
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $file_basename = uniqid() . '.' . $file_ext;
                $file_path = 'uploads/music/' . $file_basename;

                // Create directory if it doesn't exist
                if (!file_exists(__DIR__ . '/../uploads/music')) {
                    mkdir(__DIR__ . '/../uploads/music', 0777, true);
                }

                // Move uploaded file
                if (!move_uploaded_file($file_tmp_path, __DIR__ . '/../' . $file_path)) {
                    $errors[] = 'Failed to upload music file';
                }
            }
        } else {
            $errors[] = 'Music file is required';
        }

        // Handle cover image upload
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_tmp_path = $_FILES['cover_image']['tmp_name'];
            $cover_name = basename($_FILES['cover_image']['name']);
            $cover_size = $_FILES['cover_image']['size'];
            $cover_type = $_FILES['cover_image']['type'];

            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($cover_type, $allowed_types)) {
                $errors[] = 'Only JPEG, PNG, and GIF images are allowed for cover';
            }

            // Validate file size (10MB max)
            if ($cover_size > 10 * 1024 * 1024) {
                $errors[] = 'Cover image size must be less than 10MB';
            }

            if (empty($errors)) {
                // Generate unique filename
                $cover_ext = pathinfo($cover_name, PATHINFO_EXTENSION);
                $cover_basename = uniqid() . '.' . $cover_ext;
                $cover_path = 'uploads/covers/' . $cover_basename;

                // Create directory if it doesn't exist
                if (!file_exists(__DIR__ . '/../uploads/covers')) {
                    mkdir(__DIR__ . '/../uploads/covers', 0777, true);
                }

                // Move uploaded file
                if (!move_uploaded_file($cover_tmp_path, __DIR__ . '/../' . $cover_path)) {
                    $errors[] = 'Failed to upload cover image';
                }
            }
        }

        // If no errors, create music entry
        if (empty($errors)) {
            // Get or create year
            $year_id = fetchOne("SELECT id FROM years WHERE year = ?", [$year])['id'] ?? null;
            if (!$year_id) {
                $stmt = $pdo->prepare("INSERT INTO years (year) VALUES (?)");
                $stmt->execute([$year]);
                $year_id = $pdo->lastInsertId();
            }

            // Insert music record
            $stmt = $pdo->prepare("INSERT INTO music (artist_id, genre_id, language_id, year_id, title, file_path, cover, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$artist_id, $genre_id, $language_id, $year_id, $title, $file_path, $cover_path, $description]);

            header('Location: music.php');
            exit;
        }
    }
}
?>

<div class="container">
    <h1>Add New Music</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="add_music.php" enctype="multipart/form-data">
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
            <label for="music_file">Music File (MP3)</label>
            <input type="file" id="music_file" name="music_file" accept=".mp3,audio/mpeg" required>
        </div>

        <div class="form-group">
            <label for="cover_image">Cover Image (JPEG, PNG, GIF)</label>
            <input type="file" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Add Music</button>
        <a href="music.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>