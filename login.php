<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /sound/admin/dashboard.php');
    } else {
        header('Location: /sound/index.php');
    }
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $userid = trim($_POST['userid'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($userid)) {
            $errors[] = 'User ID is required.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }

        if (empty($errors)) {
            $client_ip = getClientIP();

            // Check brute-force protection (5 failed attempts in 10 minutes)
            if (isLoginLocked($userid, $client_ip)) {
                $errors[] = 'Too many attempts, try again in 10 minutes';
            } else {
                if (login($userid, $password)) {
                    clearLoginAttempts($userid, $client_ip);
                    if (isAdmin()) {
                        header('Location: /sound/admin/dashboard.php');
                    } else {
                        header('Location: /sound/index.php');
                    }
                    exit;
                } else {
                    recordLoginAttempt($userid, $client_ip);
                    $errors[] = 'Invalid user ID or password.';
                }
            }
        }
    }
}

$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container auth-container auth-container-sm">
    <!-- Auth Card -->
    <div class="card auth-card">
        <div class="auth-header">
            <div class="auth-icon-badge">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
            </div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to your account to access your music library.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error auth-alert">
                <div class="auth-alert-body">
                    <?php foreach ($errors as $error): ?>
                        <p class="auth-alert-text"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="/sound/login.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group auth-form-group">
                <label for="userid">User ID</label>
                <input type="text" id="userid" name="userid" value="<?php echo htmlspecialchars($_POST['userid'] ?? ''); ?>" required autofocus placeholder="Enter your username">
            </div>

            <div class="form-group auth-form-group-lg">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary auth-submit-btn">
                Sign In
            </button>
        </form>

        <div class="auth-footer">
            <p class="auth-footer-text">
                Don't have an account? <a href="/sound/register.php" class="auth-link">Create an account</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>