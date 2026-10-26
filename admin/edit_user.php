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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

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

        $email_check = fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
        if ($email_check) {
            $errors[] = 'Email is already in use by another account';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, address = ?, phone = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $address, $phone, $email, $role, $status, $user_id]);

            if ($user_id === intval($_SESSION['user_id'])) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
            }

            clearOldInput();
            setFlash('success', 'User @' . htmlspecialchars($user['userid']) . ' updated successfully.');
            header('Location: users.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: edit_user.php?id=' . $user_id);
    exit;
}

$form_name = getOldInput('name', $user['name']);
$form_email = getOldInput('email', $user['email']);
$form_phone = getOldInput('phone', $user['phone']);
$form_address = getOldInput('address', $user['address']);
$form_role = getOldInput('role', $user['role']);
$form_status = getOldInput('status', $user['status']);
clearOldInput();

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
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Modify permissions and status for @<?php echo htmlspecialchars($user['userid']); ?>.</p>
    </div>
    <div class="header-actions">
        <a href="users.php" class="btn btn-secondary">
            &larr; Back to Users
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="edit_user.php?id=<?php echo $user_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="userid">User ID / Username</label>
                <input type="text" id="userid" class="admin-form-control" value="<?php echo htmlspecialchars($user['userid']); ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
                <div class="admin-form-hint">Username cannot be changed.</div>
            </div>

            <div class="admin-form-group">
                <label for="name">Full Name <span class="required-mark">*</span></label>
                <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($form_name); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="email">Email Address <span class="required-mark">*</span></label>
                <input type="email" id="email" name="email" class="admin-form-control" value="<?php echo htmlspecialchars($form_email); ?>" required>
            </div>

            <div class="admin-form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="admin-form-control" value="<?php echo htmlspecialchars($form_phone); ?>">
            </div>
        </div>

        <div class="admin-form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" class="admin-form-control" rows="3"><?php echo htmlspecialchars($form_address); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="role">User Role <span class="required-mark">*</span></label>
                <select id="role" name="role" class="admin-form-control" required>
                    <option value="user" <?php echo ($form_role === 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($form_role === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="status">Account Status <span class="required-mark">*</span></label>
                <select id="status" name="status" class="admin-form-control" required>
                    <option value="active" <?php echo ($form_status === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="disabled" <?php echo ($form_status === 'disabled') ? 'selected' : ''; ?>>Disabled</option>
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
