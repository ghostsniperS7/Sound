<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get statistics
$music_count = fetchOne("SELECT COUNT(*) as count FROM music")['count'];
$video_count = fetchOne("SELECT COUNT(*) as count FROM videos")['count'];
$artist_count = fetchOne("SELECT COUNT(*) as count FROM artists")['count'];
$genre_count = fetchOne("SELECT COUNT(*) as count FROM genres")['count'];
$user_count = fetchOne("SELECT COUNT(*) as count FROM users")['count'];

// Get recent activity
$recent_music = fetchAll("SELECT m.*, a.name as artist_name FROM music m JOIN artists a ON m.artist_id = a.id ORDER BY m.created_at DESC LIMIT 5");
$recent_videos = fetchAll("SELECT v.*, a.name as artist_name FROM videos v JOIN artists a ON v.artist_id = a.id ORDER BY v.created_at DESC LIMIT 5");

// Get recent users
$recent_users = fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>

<div class="container">
    <h1>Admin Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Music</h3>
            <p class="stat-number"><?php echo $music_count; ?></p>
            <a href="music.php" class="btn btn-secondary">Manage Music</a>
        </div>
        <div class="stat-card">
            <h3>Videos</h3>
            <p class="stat-number"><?php echo $video_count; ?></p>
            <a href="videos.php" class="btn btn-secondary">Manage Videos</a>
        </div>
        <div class="stat-card">
            <h3>Artists</h3>
            <p class="stat-number"><?php echo $artist_count; ?></p>
            <a href="artists.php" class="btn btn-secondary">Manage Artists</a>
        </div>
        <div class="stat-card">
            <h3>Genres</h3>
            <p class="stat-number"><?php echo $genre_count; ?></p>
            <a href="genres.php" class="btn btn-secondary">Manage Genres</a>
        </div>
        <div class="stat-card">
            <h3>Users</h3>
            <p class="stat-number"><?php echo $user_count; ?></p>
            <a href="users.php" class="btn btn-secondary">Manage Users</a>
        </div>
    </div>

    <div class="recent-activity">
        <h2>Recent Activity</h2>

        <div class="activity-section">
            <h3>Recently Added Music</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_music as $music): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($music['title']); ?></td>
                            <td><?php echo htmlspecialchars($music['artist_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($music['created_at'])); ?></td>
                            <td>
                                <a href="edit_music.php?id=<?php echo $music['id']; ?>" class="btn btn-small">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="activity-section">
            <h3>Recently Added Videos</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_videos as $video): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($video['title']); ?></td>
                            <td><?php echo htmlspecialchars($video['artist_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($video['created_at'])); ?></td>
                            <td>
                                <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="btn btn-small">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="activity-section">
            <h3>Recently Registered Users</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['userid']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['status']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-small">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>