<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get all artists
$artists = fetchAll("SELECT * FROM artists ORDER BY name");
?>

<div class="container">
    <h1>Manage Artists</h1>

    <a href="add_artist.php" class="btn btn-primary">Add New Artist</a>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($artists as $artist): ?>
                <tr>
                    <td><?php echo htmlspecialchars($artist['name']); ?></td>
                    <td><?php echo htmlspecialchars($artist['description']); ?></td>
                    <td>
                        <a href="edit_artist.php?id=<?php echo $artist['id']; ?>" class="btn btn-small">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>