<?php
/**
 * MarketLink - Newsletter Subscription API
 * Saves subscriber to DB and sends a welcome email via PHPMailer
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mailer.php';

/* ── Only accept POST ─────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

/* ── Parse body (JSON or form-encoded) ───────────────────────────────────── */
$rawBody = file_get_contents('php://input');
$data    = json_decode($rawBody, true);
$email   = trim($data['email'] ?? ($_POST['email'] ?? ''));

/* ── Validate email ──────────────────────────────────────────────────────── */
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Please provide a valid email address.']);
    exit;
}

$email = strtolower($email);

try {
    $pdo = getDBConnection();

    /* ── Create table if it doesn't exist ─────────────────────────────────── */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email       VARCHAR(255) NOT NULL UNIQUE,
            subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ip_address  VARCHAR(45),
            is_active   TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    /* ── Check if already subscribed ─────────────────────────────────────── */
    $check = $pdo->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);
    $existing = $check->fetch();

    if ($existing) {
        if ($existing['is_active']) {
            echo json_encode([
                'success' => true,
                'message' => 'You\'re already on our harvest list! 🌿 Check your inbox every Thursday.'
            ]);
            exit;
        } else {
            /* Re-activate unsubscribed user */
            $reactivate = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 1, subscribed_at = NOW() WHERE email = :email");
            $reactivate->execute([':email' => $email]);
        }
    } else {
        /* Insert new subscriber */
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? null;

        $insert = $pdo->prepare("
            INSERT INTO newsletter_subscribers (email, ip_address)
            VALUES (:email, :ip)
        ");
        $insert->execute([':email' => $email, ':ip' => $ip]);
    }

    /* ── Send confirmation email to subscriber ───────────────────────────── */
    sendNewsletterWelcomeEmail($email);

    echo json_encode([
        'success' => true,
        'message' => 'Subscribed! 🌾 Check your inbox — a welcome email is on its way.'
    ]);

} catch (\PDOException $e) {
    error_log('Newsletter subscribe DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A server error occurred. Please try again shortly.']);
}

/* ── Mailer helper ───────────────────────────────────────────────────────── */
function sendNewsletterWelcomeEmail(string $toEmail): void
{
    $subject   = '🌿 You\'re on the MarketLink Harvest List!';
    $preheader = 'Every Thursday\'s dawn harvest & weekend stall schedules straight to your inbox.';

    $homeUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/index.php';

    $content = '
      <h2 style="margin-top:0; color:#0f172a; font-size:22px;">Welcome to the Harvest Community! 🌱</h2>
      <p>Hello there,</p>
      <p>
        Thank you for subscribing to <strong>MarketLink\'s Fresh Harvest Alert</strong>!
        You\'ll now receive our curated weekly newsletter packed with:
      </p>
      <ul style="padding-left:1.3rem; color:#334155; line-height:1.8; font-size:15px;">
        <li>🥦 <strong>Thursday dawn harvest lists</strong> — know what\'s fresh before the weekend</li>
        <li>📅 <strong>Weekend stall pickup schedules</strong> — plan your market visits in advance</li>
        <li>🌾 <strong>New farmer stall announcements</strong> — discover local producers near you</li>
        <li>💡 <strong>Seasonal produce tips</strong> — make the most of every harvest</li>
      </ul>

      <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:12px; padding:20px; margin:24px 0;">
        <div style="font-weight:700; color:#065f46; margin-bottom:6px; font-size:15px;">📬 What to expect</div>
        <div style="font-size:14px; color:#047857; line-height:1.6;">
          We send one email per week — every <strong>Thursday morning</strong>. No spam, no clutter.
          You can unsubscribe at any time by replying to any of our emails.
        </div>
      </div>

      <div style="text-align:center; margin:28px 0;">
        <a href="' . $homeUrl . '" class="btn-action">Explore the Marketplace &rarr;</a>
      </div>

      <p style="font-size:13px; color:#64748b;">
        Your subscription email: <strong>' . htmlspecialchars($toEmail) . '</strong><br>
        If you didn\'t subscribe to MarketLink, you can safely ignore this email.
      </p>';

    // Fire-and-forget — don't block the response on mail errors
    try {
        sendMarketLinkEmail($toEmail, '', $subject, $content, $preheader);
    } catch (\Throwable $e) {
        error_log('Newsletter welcome email failed for ' . $toEmail . ': ' . $e->getMessage());
    }
}
