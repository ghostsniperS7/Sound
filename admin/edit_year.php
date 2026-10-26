<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$year_id = intval($_GET['id'] ?? 0);

if (!$year_id) {
    setFlash('error', 'Invalid year ID.');
    header('Location: years.php');
    exit;
}

$yr = fetchOne("SELECT * FROM years WHERE id = ?", [$year_id]);

if (!$yr) {
    setFlash('error', 'Year not found.');
    header('Location: years.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $year = intval($_POST['year'] ?? 0);

        if ($year < 1900 || $year > intval(date('Y')) + 5) {
            $errors[] = 'Please enter a valid year between 1900 and ' . (intval(date('Y')) + 5);
        } else {
            // Check unique
            $existing = fetchOne("SELECT id FROM years WHERE year = ? AND id != ?", [$year, $year_id]);
            if ($existing) {
                $errors[] = "Year $year already exists in the database.";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE years SET year = ? WHERE id = ?");
            $stmt->execute([$year, $year_id]);

            setFlash('success', "Year updated to $year successfully.");
            header('Location: years.php');
            exit;
        }
    }
}

$page_title = 'Edit Year: ' . $yr['year'];
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Years', 'url' => '/sound/admin/years.php'],
    ['title' => 'Edit Year']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Edit Year</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Modify release year.</p>
    </div>
    <div class="header-actions">
        <a href="years.php" class="btn btn-secondary">
            &larr; Back to Years
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
    <form method="post" action="edit_year.php?id=<?php echo $year_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="year">Year <span class="required-mark">*</span></label>
            <input type="number" id="year" name="year" class="admin-form-control" value="<?php echo htmlspecialchars($_POST['year'] ?? $yr['year']); ?>" required min="1900" max="<?php echo date('Y') + 5; ?>">
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Update Year</button>
            <a href="years.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
