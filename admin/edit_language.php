<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$language_id = intval($_GET['id'] ?? 0);

if (!$language_id) {
    setFlash('error', 'Invalid language ID.');
    header('Location: languages.php');
    exit;
}

$lang = fetchOne("SELECT * FROM languages WHERE id = ?", [$language_id]);

if (!$lang) {
    setFlash('error', 'Language not found.');
    header('Location: languages.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $errors[] = 'Language name is required';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE languages SET name = ? WHERE id = ?");
            $stmt->execute([$name, $language_id]);

            clearOldInput();
            setFlash('success', 'Language "' . htmlspecialchars($name) . '" updated successfully.');
            header('Location: languages.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: edit_language.php?id=' . $language_id);
    exit;
}

$form_name = getOldInput('name', $lang['name']);
clearOldInput();

$page_title = 'Edit Language: ' . $lang['name'];
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Languages', 'url' => '/sound/admin/languages.php'],
    ['title' => 'Edit Language']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Edit Language</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Modify language details.</p>
    </div>
    <div class="header-actions">
        <a href="languages.php" class="btn btn-secondary">
            &larr; Back to Languages
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="edit_language.php?id=<?php echo $language_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="name">Language Name <span class="required-mark">*</span></label>
            <input type="text" id="name" name="name" class="admin-form-control" value="<?php echo htmlspecialchars($form_name); ?>" required>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update Language</button>
            <a href="languages.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
