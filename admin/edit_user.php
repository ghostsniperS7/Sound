<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    setFlash('error', 'Invalid user ID.');
    header('Location: users.php');
    exit;
}

$user = fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);

if (!$user) {
    setFlash('error', 'User not found.');
    header('Location: users.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        // Check if email already used by another user
        $email_check = fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
        if ($email_check) {
            $errors[] = 'Email is already in use by another account';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, address = ?, phone = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $address, $phone, $email, $role, $status, $user_id]);

            // If the admin edited their own record, update session name
            if ($user_id === intval($_SESSION['user_id'])) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
            }

            setFlash('success', 'User @' . htmlspecialchars($user['userid']) . ' updated successfully.');
            header('Location: users.php');
            exit;
        }
    }
}

$page_title = 'Edit User: ' . $user['name'];
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Users', 'url' => '/sound/admin/users.php'],
    ['title' => 'Edit User']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Edit User Profile</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Manage account details, permissions, and status.</p>
    </div>
    <div class="header-actions">
        <a href="users.php" class="btn btn-secondary">
            &larr; Back to Users
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert admin-alert-error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="admin-form-card">
    <form method="post" action="edit_user.php?id=<?php echo $user_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="userid">User ID / Username</label>
                <input type="text" id="userid" name="userid" class="admin-form-control" value="<?php echo htmlspecialchars($user['userid']); ?>" readonly>
                <div class="admin-form-hint">Username cannot be changed.</div>
            </div>

            <div class="admin-form-group">
                <label for="name">Full Name <span class="required-mark">*</span></label>
                <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? $user['name']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="email">Email Address <span class="required-mark">*</span></label>
                <input type="email" id="email" name="email" class="admin-form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>" required>
            </div>

            <div class="admin-form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="admin-form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone']); ?>">
            </div>
        </div>

        <div class="admin-form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" class="admin-form-control" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? $user['address']); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="role">User Role <span class="required-mark">*</span></label>
                <select id="role" name="role" class="admin-form-control" required>
                    <option value="user" <?php echo (($user['role'] === 'user')) ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo (($user['role'] === 'admin')) ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="status">Account Status <span class="required-mark">*</span></label>
                <select id="status" name="status" class="admin-form-control" required>
                    <option value="active" <?php echo (($user['status'] === 'active')) ? 'selected' : ''; ?>>Active</option>
                    <option value="disabled" <?php echo (($user['status'] === 'disabled')) ? 'selected' : ''; ?>>Disabled</option>
                </select>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
