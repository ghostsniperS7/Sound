<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: /sound/');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $userid = trim($_POST['userid'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($userid)) {
            $errors[] = 'User ID is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }

        if (empty($errors)) {
            if (login($userid, $password)) {
                header('Location: /sound/');
                exit;
            } else {
                $errors[] = 'Invalid user ID or password';
            }
        }
    }
}

// Include header
require_once 'includes/header.php';
?>

<div class="container">
    <h1>Login</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="userid">User ID</label>
            <input type="text" id="userid" name="userid" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>