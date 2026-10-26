<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}

$settings = fetchAll("SELECT * FROM site_settings");
$site_settings = [];
foreach ($settings as $setting) {
    $site_settings[$setting['setting_key']] = $setting['setting_value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $site_name = trim($_POST['site_name'] ?? '');
        $site_tagline = trim($_POST['site_tagline'] ?? '');
        $about_text = trim($_POST['about_text'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');

        if (empty($site_name)) {
            $errors[] = 'Site Name is required';
        }

        if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid contact email format';
        }

        if (empty($errors)) {
            updateSetting('site_name', $site_name);
            updateSetting('site_tagline', $site_tagline);
            updateSetting('about_text', $about_text);
            updateSetting('contact_email', $contact_email);
            updateSetting('contact_phone', $contact_phone);

            clearOldInput();
            setFlash('success', 'Site settings updated successfully.');
            header('Location: settings.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: settings.php');
    exit;
}

$form_site_name = getOldInput('site_name', $site_settings['site_name'] ?? 'SOUND');
$form_site_tagline = getOldInput('site_tagline', $site_settings['site_tagline'] ?? 'Music & Video Streaming');
$form_about_text = getOldInput('about_text', $site_settings['about_text'] ?? '');
$form_contact_email = getOldInput('contact_email', $site_settings['contact_email'] ?? '');
$form_contact_phone = getOldInput('contact_phone', $site_settings['contact_phone'] ?? '');
clearOldInput();

$page_title = 'Site Settings';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Site Settings']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Site Settings</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Manage global site configuration and information.</p>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="settings.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="site_name">Site Name <span class="required-mark">*</span></label>
            <input type="text" id="site_name" name="site_name" class="admin-form-control" value="<?php echo htmlspecialchars($form_site_name); ?>" required>
        </div>

        <div class="admin-form-group">
            <label for="site_tagline">Site Tagline</label>
            <input type="text" id="site_tagline" name="site_tagline" class="admin-form-control" value="<?php echo htmlspecialchars($form_site_tagline); ?>">
        </div>

        <div class="admin-form-group">
            <label for="about_text">About SOUND Website</label>
            <textarea id="about_text" name="about_text" class="admin-form-control" rows="5"><?php echo htmlspecialchars($form_about_text); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="admin-form-group">
                <label for="contact_email">Support / Contact Email</label>
                <input type="email" id="contact_email" name="contact_email" class="admin-form-control" value="<?php echo htmlspecialchars($form_contact_email); ?>">
            </div>

            <div class="admin-form-group">
                <label for="contact_phone">Contact Phone</label>
                <input type="tel" id="contact_phone" name="contact_phone" class="admin-form-control" value="<?php echo htmlspecialchars($form_contact_phone); ?>">
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
