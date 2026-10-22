<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get artist ID from URL
$artist_id = $_GET['id'] ?? null;

if (!$artist_id) {
    header('Location: artists.php');
    exit;
}

// Get artist data
$artist = fetchOne("SELECT * FROM artists WHERE id = ?", [$artist_id]);

if (!$artist) {
    header('Location: artists.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validate required fields
        if (empty($name)) {
            $errors[] = 'Artist name is required';
        }

        // If no errors, update artist
        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE artists SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $artist_id]);

            header('Location: artists.php');
            exit;
        }
    }
}
?>

<div class="container">
    <h1>Edit Artist</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="edit_artist.php?id=<?php echo $artist_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="name">Artist Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($artist['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($artist['description']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Artist</button>
        <a href="artists.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>