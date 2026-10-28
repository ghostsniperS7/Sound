<?php
$page_title = 'About Us';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

// Site settings are already loaded in header.php into $site_settings, but ensure defaults
$site_name_val = $site_settings['site_name'] ?? (defined('SITE_NAME') ? SITE_NAME : 'SOUND');
$site_tagline_val = $site_settings['site_tagline'] ?? (defined('SITE_TAGLINE') ? SITE_TAGLINE : 'Music & Video Streaming');
$about_text_val = $site_settings['about_text'] ?? 'Welcome to SOUND, your ultimate destination for music and video streaming.';
$contact_email_val = $site_settings['contact_email'] ?? 'contact@sound.com';
$contact_phone_val = $site_settings['contact_phone'] ?? '+1 234 567 890';
?>

<div class="container" style="max-width: 1000px; margin: 0 auto; padding-bottom: 40px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 28px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -0.5px;">About <?php echo htmlspecialchars($site_name_val); ?></h1>
        <p style="color: var(--text-muted); font-size: 15px; margin: 0;"><?php echo htmlspecialchars($site_tagline_val); ?></p>
    </div>

    <!-- Main About Hero Card -->
    <div class="card" style="cursor: default; padding: 32px; margin-bottom: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
        <div style="display: flex; align-items: flex-start; gap: 20px;">
            <div style="width: 52px; height: 52px; border-radius: var(--radius-card); background: var(--accent-subtle); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--accent);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            </div>
            <div style="flex: 1;">
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">Our Mission</h2>
                <p style="color: var(--text-muted); font-size: 15px; line-height: 1.7; margin: 0; white-space: pre-line;">
                    <?php echo htmlspecialchars($about_text_val); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Feature Highlights Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <div class="card" style="cursor: default; padding: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
            <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(255, 90, 60, 0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--accent);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18V5l12-2v13"></path>
                    <circle cx="6" cy="18" r="3"></circle>
                    <circle cx="18" cy="16" r="3"></circle>
                </svg>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">Rich Music Library</h3>
            <p style="color: var(--text-muted); font-size: 13.5px; line-height: 1.6; margin: 0;">
                Stream thousands of tracks across diverse genres and languages, with lossless audio clarity.
            </p>
        </div>

        <div class="card" style="cursor: default; padding: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
            <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(255, 90, 60, 0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--accent);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="23 7 16 12 23 17 23 7"></polygon>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                </svg>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">Music Videos</h3>
            <p style="color: var(--text-muted); font-size: 13.5px; line-height: 1.6; margin: 0;">
                Watch official music videos and live performances in high definition directly in the player.
            </p>
        </div>

        <div class="card" style="cursor: default; padding: 24px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
            <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(255, 90, 60, 0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--accent);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                </svg>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">Persistent Audio</h3>
            <p style="color: var(--text-muted); font-size: 13.5px; line-height: 1.6; margin: 0;">
                Your music keeps playing in the background as you browse different sections of the platform.
            </p>
        </div>
    </div>

    <!-- Contact & Info Card -->
    <div class="card" style="cursor: default; padding: 28px 32px; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-card);">
        <h2 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 18px;">Contact & Support</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 38px; height: 38px; border-radius: var(--radius-md); background: var(--bg-elevated); display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-subtle); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Email Support</div>
                    <a href="mailto:<?php echo htmlspecialchars($contact_email_val); ?>" style="color: var(--text-primary); font-size: 14px; font-weight: 600;">
                        <?php echo htmlspecialchars($contact_email_val); ?>
                    </a>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 38px; height: 38px; border-radius: var(--radius-md); background: var(--bg-elevated); display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-subtle); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Phone Support</div>
                    <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $contact_phone_val)); ?>" style="color: var(--text-primary); font-size: 14px; font-weight: 600;">
                        <?php echo htmlspecialchars($contact_phone_val); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
