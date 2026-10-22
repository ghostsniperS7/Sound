<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get all videos with artist and genre information
$videos = fetchAll("SELECT v.*, a.name as artist_name, g.name as genre_name FROM videos v JOIN artists a ON v.artist_id = a.id JOIN genres g ON v.genre_id = g.id ORDER BY v.title");
?>

<div class="container">
    <h1>Manage Videos</h1>

    <a href="add_video.php" class="btn btn-primary">Add New Video</a>

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
            <?php foreach ($videos as $video): ?>
                <tr>
                    <td><?php echo htmlspecialchars($video['title']); ?></td>
                    <td><?php echo htmlspecialchars($video['artist_name']); ?></td>
                    <td><?php echo htmlspecialchars($video['genre_name']); ?></td>
                    <td>
                        <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="btn btn-small">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>