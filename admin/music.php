<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get all music with artist and genre information
$music_items = fetchAll("SELECT m.*, a.name as artist_name, g.name as genre_name FROM music m JOIN artists a ON m.artist_id = a.id JOIN genres g ON m.genre_id = g.id ORDER BY m.title");
?>

<div class="container">
    <h1>Manage Music</h1>

    <a href="add_music.php" class="btn btn-primary">Add New Music</a>

    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Artist</th>
                <th>Genre</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($music_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['artist_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['genre_name']); ?></td>
                    <td>
                        <a href="edit_music.php?id=<?php echo $item['id']; ?>" class="btn btn-small">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>