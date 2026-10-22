<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get genre ID from URL
$genre_id = $_GET['id'] ?? null;

if (!$genre_id) {
    header('Location: genres.php');
    exit;
}

// Get genre data
$genre = fetchOne("SELECT * FROM genres WHERE id = ?", [$genre_id]);

if (!$genre) {
    header('Location: genres.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $name = trim($_POST['name'] ?? '');

        // Validate required fields
        if (empty($name)) {
            $errors[] = 'Genre name is required';
        }

        // If no errors, update genre
        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE genres SET name = ? WHERE id = ?");
            $stmt->execute([$name, $genre_id]);

            header('Location: genres.php');
            exit;
        }
    }
}
?>

<div class="container">
    <h1>Edit Genre</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="edit_genre.php?id=<?php echo $genre_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="name">Genre Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($genre['name']); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Genre</button>
        <a href="genres.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>