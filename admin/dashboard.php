<?php
$page_title = 'Dashboard';
$breadcrumbs = [
    ['title' => 'Dashboard']
];
require_once __DIR__ . '/includes/admin_header.php';

// Get statistics
$music_count = fetchOne("SELECT COUNT(*) as count FROM music")['count'] ?? 0;
$video_count = fetchOne("SELECT COUNT(*) as count FROM videos")['count'] ?? 0;
$user_count = fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
$artist_count = fetchOne("SELECT COUNT(*) as count FROM artists")['count'] ?? 0;

$music_plays = fetchOne("SELECT COALESCE(SUM(play_count), 0) as total FROM music")['total'] ?? 0;
$video_plays = fetchOne("SELECT COALESCE(SUM(play_count), 0) as total FROM videos")['total'] ?? 0;
$total_plays = $music_plays + $video_plays;

// Get latest uploads (music + videos combined)
$latest_music = fetchAll("SELECT m.id, m.title, 'Music' as media_type, a.name as artist_name, m.created_at FROM music m LEFT JOIN artists a ON m.artist_id = a.id ORDER BY m.created_at DESC LIMIT 5");
$latest_videos = fetchAll("SELECT v.id, v.title, 'Video' as media_type, a.name as artist_name, v.created_at FROM videos v LEFT JOIN artists a ON v.artist_id = a.id ORDER BY v.created_at DESC LIMIT 5");

$latest_uploads = array_merge($latest_music, $latest_videos);
usort($latest_uploads, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
$latest_uploads = array_slice($latest_uploads, 0, 5);

// Get latest registered users
$recent_users = fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>

<div class="admin-page-header">
    <div>
        <h1>Dashboard Overview</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Welcome back, <?php echo htmlspecialchars($user_name); ?>. Here's a snapshot of your platform.</p>
    </div>
    <div class="header-actions">
        <a href="add_music.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Music
        </a>
        <a href="add_video.php" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
            Add Video
        </a>
    </div>
</div>

<!-- 5 Stat Cards: Music, Videos, Users, Artists, Total Plays -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18V5l12-2v13"></path>
                <circle cx="6" cy="18" r="3"></circle>
                <circle cx="18" cy="16" r="3"></circle>
            </svg>
        </div>
        <div class="admin-stat-details">
            <div class="stat-value"><?php echo number_format($music_count); ?></div>
            <div class="stat-label">Total Music</div>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon pink">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="23 7 16 12 23 17 23 7"></polygon>
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
            </svg>
        </div>
        <div class="admin-stat-details">
            <div class="stat-value"><?php echo number_format($video_count); ?></div>
            <div class="stat-label">Total Videos</div>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="admin-stat-details">
            <div class="stat-value"><?php echo number_format($user_count); ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div class="admin-stat-details">
            <div class="stat-value"><?php echo number_format($artist_count); ?></div>
            <div class="stat-label">Total Artists</div>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon yellow">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="10 8 16 12 10 16 10 8"></polygon>
            </svg>
        </div>
        <div class="admin-stat-details">
            <div class="stat-value"><?php echo number_format($total_plays); ?></div>
            <div class="stat-label">Total Plays</div>
        </div>
    </div>
</div>

<!-- Two Small Tables: Latest Uploads & Latest Registered Users -->
<div class="admin-dashboard-grid">
    <!-- Latest Uploads Panel -->
    <div class="admin-panel">
        <div class="admin-panel-header">
            <h3>Latest Uploads</h3>
            <a href="music.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Date</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($latest_uploads)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--admin-text-muted); padding: 24px;">No uploads found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($latest_uploads as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['media_type'] === 'Music'): ?>
                                        <span class="admin-badge admin-badge-music">Music</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-video">Video</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($item['artist_name'] ?? 'Unknown'); ?></td>
                                <td style="color: var(--admin-text-muted); font-size: 13px;"><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                <td style="text-align: right;">
                                    <?php if ($item['media_type'] === 'Music'): ?>
                                        <a href="edit_music.php?id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <?php else: ?>
                                        <a href="edit_video.php?id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Latest Registered Users Panel -->
    <div class="admin-panel">
        <div class="admin-panel-header">
            <h3>Latest Registered Users</h3>
            <a href="users.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_users)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--admin-text-muted); padding: 24px;">No registered users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_users as $u): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($u['name']); ?></div>
                                    <div style="font-size: 12px; color: var(--admin-text-muted);">@<?php echo htmlspecialchars($u['userid']); ?></div>
                                </td>
                                <td style="color: var(--admin-text-muted); font-size: 13px;"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="admin-badge admin-badge-<?php echo htmlspecialchars($u['role']); ?>">
                                        <?php echo htmlspecialchars($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-badge admin-badge-<?php echo htmlspecialchars($u['status']); ?>">
                                        <?php echo htmlspecialchars($u['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
