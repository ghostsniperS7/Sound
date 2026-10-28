<?php
$page_title = 'My Profile';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If not logged in, show friendly guest prompt or redirect
$isLoggedIn = isLoggedIn();
if (!$isLoggedIn) {
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container" style="max-width: 580px; margin: 40px auto; padding-bottom: 40px;">
        <div class="card" style="cursor: default; padding: 48px 36px; text-align: center; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-pill); background: var(--accent-subtle); color: var(--accent); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">Login to view your profile</h2>
            <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 28px;">
                Manage your account credentials, view listening history, and manage liked songs.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <a href="/sound/login.php" class="btn btn-primary" style="padding: 10px 28px;">Login</a>
                <a href="/sound/register.php" class="btn btn-secondary" style="padding: 10px 24px;">Create an account</a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$userId = intval($_SESSION['user_id']);
$user = fetchOne("SELECT id, userid, name, email, role, phone, address, created_at FROM users WHERE id = ?", [$userId]);

if (!$user) {
    setFlash('error', 'User record not found.');
    header('Location: /sound/');
    exit;
}

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token. Please try again.');
        header('Location: /sound/profile.php');
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $errors = [];
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    // Check if email taken by another user
    if (!empty($email)) {
        $existing = fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
        if ($existing) {
            $errors[] = 'This email is already in use by another account.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $userId]);
        $_SESSION['user_name'] = $name;
        setFlash('success', 'Profile updated successfully.');
        header('Location: /sound/profile.php');
        exit;
    } else {
        setFlash('error', implode('<br>', $errors));
        header('Location: /sound/profile.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
$initial = strtoupper(substr($user['name'] ?: 'U', 0, 1));
?>

<div class="container" style="max-width: 900px; margin: 0 auto; padding-bottom: 40px;">
    <!-- Page Header -->
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 28px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">User Profile</h1>
        <p style="color: var(--text-muted); font-size: 15px; margin: 0;">Manage your personal account details and preferences.</p>
    </div>

    <!-- User Identity Hero Card -->
    <div class="card" style="cursor: default; padding: 28px 32px; margin-bottom: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-pill); background: var(--accent); color: #FFFFFF; font-size: 24px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 90, 60, 0.35);">
                <?php echo htmlspecialchars($initial); ?>
            </div>
            <div>
                <h2 style="font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;"><?php echo htmlspecialchars($user['name']); ?></h2>
                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 13.5px;">
                    <span>@<?php echo htmlspecialchars($user['userid']); ?></span>
                    <span>&bull;</span>
                    <span style="background: var(--bg-elevated); padding: 2px 10px; border-radius: var(--radius-pill); text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--accent);">
                        <?php echo htmlspecialchars($user['role']); ?>
                    </span>
                    <span>&bull;</span>
                    <span>Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <?php if (isAdmin()): ?>
                <a href="/sound/admin/dashboard.php" class="btn btn-secondary btn-sm" style="color: var(--accent);">Admin Dashboard</a>
            <?php endif; ?>
            <a href="/sound/liked.php" class="btn btn-secondary btn-sm">My Liked Items</a>
        </div>
    </div>

    <!-- Edit Profile Form Card -->
    <div class="card" style="cursor: default; padding: 32px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
        <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px;">Account Information</h3>

        <form method="post" action="/sound/profile.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 18px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="userid">User ID (Permanent)</label>
                    <input type="text" id="userid" value="<?php echo htmlspecialchars($user['userid']); ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 18px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 28px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
