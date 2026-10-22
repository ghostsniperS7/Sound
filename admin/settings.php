<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is admin
requireAdmin();

// Get all site settings
$settings = fetchAll("SELECT * FROM site_settings");

// Convert settings array to associative array for easier access
$site_settings = [];
foreach ($settings as $setting) {
    $site_settings[$setting['setting_key']] = $setting['setting_value'];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        // Update site name
        $site_name = trim($_POST['site_name'] ?? '');
        if (empty($site_name)) {
            $errors[] = 'Site name is required';
        } else {
            updateSetting('site_name', $site_name);
        }

        // Update site tagline
        $site_tagline = trim($_POST['site_tagline'] ?? '');
        updateSetting('site_tagline', $site_tagline);

        // Update about text
        $about_text = trim($_POST['about_text'] ?? '');
        updateSetting('about_text', $about_text);

        // Update contact email
        $contact_email = trim($_POST['contact_email'] ?? '');
        if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid contact email format';
        } else {
            updateSetting('contact_email', $contact_email);
        }

        // Update contact phone
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        updateSetting('contact_phone', $contact_phone);

        if (empty($errors)) {
            header('Location: settings.php');
            exit;
        }
    }
}

function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}
?>

<div class="container">
    <h1>Site Settings</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="settings.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="site_name">Site Name</label>
            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_settings['site_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="site_tagline">Site Tagline</label>
            <input type="text" id="site_tagline" name="site_tagline" value="<?php echo htmlspecialchars($site_settings['site_tagline'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="about_text">About Text</label>
            <textarea id="about_text" name="about_text" rows="5"><?php echo htmlspecialchars($site_settings['about_text'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="contact_email">Contact Email</label>
            <input type="email" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($site_settings['contact_email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="contact_phone">Contact Phone</label>
            <input type="tel" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($site_settings['contact_phone'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>