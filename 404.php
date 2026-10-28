<?php
http_response_code(404);
$page_title = 'Page Not Found (404)';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 680px; margin: 40px auto 60px auto; padding: 0 16px;">
    <div class="card" style="cursor: default; padding: 56px 36px; text-align: center; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
        <div style="width: 88px; height: 88px; border-radius: var(--radius-pill); background: var(--accent-subtle); color: var(--accent); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto;">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>

        <div style="display: inline-block; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); background: var(--accent-subtle); padding: 4px 14px; border-radius: var(--radius-pill); margin-bottom: 12px;">
            Error 404
        </div>

        <h1 style="font-size: 28px; font-weight: 800; color: var(--text-primary); margin-bottom: 12px; letter-spacing: -0.5px;">
            Page Not Found
        </h1>

        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.6; max-width: 480px; margin: 0 auto 32px auto;">
            The page, song, or video you are looking for doesn't exist, has been moved, or the link may be broken.
        </p>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="/sound/" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Back to Home</span>
            </a>
            <a href="/sound/browse.php" class="btn btn-secondary" style="padding: 10px 20px;">
                <span>Browse Catalog</span>
            </a>
            <a href="/sound/search.php" class="btn btn-ghost" style="padding: 10px 18px;">
                <span>Search</span>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
