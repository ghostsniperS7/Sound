<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /sound/');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        // Validate input
        $userid = trim($_POST['userid'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate required fields
        if (empty($userid)) {
            $errors[] = 'User ID is required.';
        }
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($email)) {
            $errors[] = 'Email is required.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }

        // Validate userid format
        if (!empty($userid) && !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $userid)) {
            $errors[] = 'User ID must be 4-20 characters (letters, numbers, underscores only).';
        }

        // Validate email format
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address format.';
        }

        // Validate phone format
        if (!empty($phone) && !preg_match('/^[+0-9\s-]{7,15}$/', $phone)) {
            $errors[] = 'Invalid phone number format.';
        }

        // Validate password strength
        if (!empty($password) && (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password))) {
            $errors[] = 'Password must be at least 8 characters and contain at least one letter and one number.';
        }

        // Check if userid is unique
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE userid = ?");
            $stmt->execute([$userid]);
            if ($stmt->fetch()) {
                $errors[] = 'User ID is already taken. Please choose another.';
            }
        }

        // Check if email is unique
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            }
        }

        // If no errors, create user
        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (userid, name, address, phone, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userid, $name, $address, $phone, $email, $password_hash]);

            // Log the user in
            if (login($userid, $password)) {
                setFlash('success', 'Account created successfully! Welcome to SOUND.');
                header('Location: /sound/');
                exit;
            } else {
                $errors[] = 'Registration successful but auto-login failed. Please sign in manually.';
            }
        }
    }
}

$page_title = 'Create an Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 600px; margin: 30px auto; padding-bottom: 50px;">
    <!-- Auth Card -->
    <div class="card" style="cursor: default; padding: 40px 36px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 52px; height: 52px; border-radius: var(--radius-pill); background: var(--accent-subtle); color: var(--accent); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </div>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">Create Your Account</h1>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Join SOUND to stream and save your favorite music & videos.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="margin-bottom: 24px;">
                <div style="width: 100%;">
                    <?php foreach ($errors as $error): ?>
                        <p style="margin: 2px 0; font-size: 13.5px;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="/sound/register.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required placeholder="John Doe">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="userid">User ID <span class="required">*</span></label>
                    <input type="text" id="userid" name="userid" value="<?php echo htmlspecialchars($_POST['userid'] ?? ''); ?>" required minlength="4" maxlength="20" pattern="[a-zA-Z0-9_]+" placeholder="e.g. musiclover99">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="you@example.com">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+1 234 567 8900">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="address">Address (Optional)</label>
                <textarea id="address" name="address" rows="2" placeholder="Your street address or city"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required minlength="8" placeholder="Min. 8 characters with numbers">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-size: 15px; font-weight: 700;">
                Create Account
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); text-align: center;">
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Already have an account? <a href="/sound/login.php" style="color: var(--accent); font-weight: 600; text-decoration: underline;">Sign In here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>