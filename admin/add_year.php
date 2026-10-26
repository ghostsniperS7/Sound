<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $year = intval($_POST['year'] ?? 0);

        if ($year < 1900 || $year > intval(date('Y')) + 5) {
            $errors[] = 'Please enter a valid year between 1900 and ' . (intval(date('Y')) + 5);
        } else {
            $existing = fetchOne("SELECT id FROM years WHERE year = ?", [$year]);
            if ($existing) {
                $errors[] = "Year $year already exists in the database.";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO years (year) VALUES (?)");
            $stmt->execute([$year]);

            clearOldInput();
            setFlash('success', "Year $year added successfully.");
            header('Location: years.php');
            exit;
        }
    }

    setFlash('error', implode('<br>', $errors));
    setOldInput($_POST);
    header('Location: add_year.php');
    exit;
}

$form_year = getOldInput('year', date('Y'));
clearOldInput();

$page_title = 'Add New Year';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/sound/admin/dashboard.php'],
    ['title' => 'Years', 'url' => '/sound/admin/years.php'],
    ['title' => 'Add Year']
];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Add New Year</h1>
        <p style="color: var(--admin-text-muted); margin: 4px 0 0 0; font-size: 14px;">Register a new release year.</p>
    </div>
    <div class="header-actions">
        <a href="years.php" class="btn btn-secondary">
            &larr; Back to Years
        </a>
    </div>
</div>

<div class="admin-form-card">
    <form method="post" action="add_year.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="admin-form-group">
            <label for="year">Year <span class="required-mark">*</span></label>
            <input type="number" id="year" name="year" class="admin-form-control" value="<?php echo htmlspecialchars($form_year); ?>" min="1900" max="<?php echo date('Y') + 5; ?>" required placeholder="e.g. 2024">
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Add Year</button>
            <a href="years.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
