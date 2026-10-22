<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get all genres
$genres = fetchAll("SELECT * FROM genres ORDER BY name");
?>

<div class="container">
    <h1>Manage Genres</h1>

    <a href="add_genre.php" class="btn btn-primary">Add New Genre</a>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($genres as $genre): ?>
                <tr>
                    <td><?php echo htmlspecialchars($genre['name']); ?></td>
                    <td>
                        <a href="edit_genre.php?id=<?php echo $genre['id']; ?>" class="btn btn-small">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>