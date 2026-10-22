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
            $errors[] = 'User ID is required';
        }
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }

        // Validate userid format
        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $userid)) {
            $errors[] = 'User ID must be 4-20 characters and can only contain letters, numbers, and underscores';
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        // Validate phone format
        if (!preg_match('/^[+0-9\s-]{7,15}$/', $phone)) {
            $errors[] = 'Invalid phone number format';
        }

        // Validate password strength
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must be at least 8 characters and contain at least one letter and one number';
        }

        // Check if userid is unique
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE userid = ?");
            $stmt->execute([$userid]);
            if ($stmt->fetch()) {
                $errors[] = 'User ID is already taken';
            }
        }

        // If no errors, create user
        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (userid, name, address, phone, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userid, $name, $address, $phone, $email, $password_hash]);

            // Log the user in
            if (login($userid, $password)) {
                header('Location: /sound/');
                exit;
            } else {
                $errors[] = 'Registration successful but login failed. Please try logging in.';
            }
        }
    }
}

// Include header
require_once 'includes/header.php';
?>

<div class="container">
    <h1>Register</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="userid">User ID</label>
            <input type="text" id="userid" name="userid" required minlength="4" maxlength="20" pattern="[a-zA-Z0-9_]+">
        </div>

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address"></textarea>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>