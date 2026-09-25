<?php
/**
 * MarketLink - High Performance Transactional Email Engine
 * Powered by PHPMailer & Gmail SMTP
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Configure and return a new PHPMailer instance
 */
function getPHPMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'arifrafay551@gmail.com';
    $mail->Password   = 'pvlwmxfueobrjalv';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 12;

    $mail->setFrom('arifrafay551@gmail.com', 'MarketLink Platform');
    $mail->addReplyTo('arifrafay551@gmail.com', 'MarketLink Support');

    return $mail;
}

/**
 * Send an email with HTML layout wrapper
 */
function sendMarketLinkEmail(string $toEmail, string $toName, string $subject, string $htmlContent, string $preheader = ''): bool {
    if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    try {
        $mail = getPHPMailer();
        $mail->addAddress($toEmail, $toName ?: 'MarketLink User');
        $mail->isHTML(true);
        $mail->Subject = $subject;

        $template = '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>' . htmlspecialchars($subject) . '</title>
  <style>
    body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased; }
    .email-container { max-width: 600px; margin: 24px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
    .email-header { background: linear-gradient(135deg, #064e3b 0%, #047857 100%); padding: 32px 28px; text-align: center; color: #ffffff; }
    .brand-logo { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
    .brand-logo span { color: #34d399; }
    .brand-tagline { font-size: 13px; color: #a7f3d0; margin-top: 6px; letter-spacing: 0.5px; text-transform: uppercase; font-weight: 600; }
    .email-body { padding: 36px 32px; background: #ffffff; font-size: 15px; line-height: 1.6; color: #334155; }
    .email-footer { background: #f8fafc; padding: 24px 32px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; line-height: 1.5; }
    .btn-action { display: inline-block; padding: 13px 28px; background: #059669; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; margin: 18px 0; text-align: center; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25); }
    .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-info { background: #e0f2fe; color: #0369a1; }
    .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0; }
    .table-details { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 14px; }
    .table-details th { text-align: left; padding: 10px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; }
    .table-details td { padding: 10px; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
    .text-right { text-align: right; }
    .total-row { font-weight: 700; font-size: 15px; color: #065f46; border-top: 2px solid #cbd5e1; }
  </style>
</head>
<body>
  <div style="display:none;font-size:1px;color:#333;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">
    ' . htmlspecialchars($preheader) . '
  </div>
  <div class="email-container">
    <div class="email-header">
      <h1 class="brand-logo">Market<span>Link</span></h1>
      <div class="brand-tagline">Fresh Farm-to-Fork Direct Marketplace</div>
    </div>
    <div class="email-body">
      ' . $htmlContent . '
    </div>
    <div class="email-footer">
      <p style="margin: 0 0 8px 0;">This email was securely dispatched from the <strong>MarketLink Platform</strong>.</p>
      <p style="margin: 0;">&copy; ' . date('Y') . ' MarketLink Pakistan. Empowering Local Farmers & Empowering Clean Nutrition.</p>
    </div>
  </div>
</body>
</html>';

        $mail->Body    = $template;
        $mail->AltBody = strip_tags($htmlContent);

        return $mail->send();
    } catch (Exception $e) {
        error_log("PHPMailer Send Error to {$toEmail}: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * 1. User Registration Welcome Email
 */
function sendWelcomeEmail(string $toEmail, string $username, string $fullName, string $role): bool {
    $subject = "Welcome to MarketLink, " . ($fullName ?: $username) . "! 🌿";
    $preheader = "Your MarketLink account has been successfully created.";
    
    $roleName = ($role === 'farmer') ? 'Verified Farmer / Producer' : 'Valued Customer';
    $badgeClass = ($role === 'farmer') ? 'badge-info' : 'badge-success';

    $content = '
      <h2 style="margin-top:0; color:#0f172a; font-size:22px;">Welcome to the Harvest Community!</h2>
      <p>Hello <strong>' . htmlspecialchars($fullName ?: $username) . '</strong>,</p>
      <p>Thank you for joining <strong>MarketLink</strong> — the premier community platform connecting local growers directly with conscious households.</p>
      
      <div class="info-card">
        <div style="margin-bottom: 8px;"><strong>Account Details:</strong></div>
        <div style="font-size: 14px; margin-bottom: 4px;">• <strong>Username:</strong> ' . htmlspecialchars($username) . '</div>
        <div style="font-size: 14px; margin-bottom: 4px;">• <strong>Registered Email:</strong> ' . htmlspecialchars($toEmail) . '</div>
        <div style="font-size: 14px;">• <strong>Role:</strong> <span class="badge ' . $badgeClass . '">' . htmlspecialchars($roleName) . '</span></div>
      </div>';

    if ($role === 'farmer') {
        $content .= '
          <p>As a <strong>Producer / Farmer</strong>, you can configure your farm stall details, set weekly harvest inventories, assign pickup windows, and directly chat with customers regarding their pre-orders.</p>
          <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 16px; border-radius:6px; font-size:13px; color:#92400e; margin:16px 0;">
            <strong>Verification Note:</strong> Your stall profile is currently in verification. Our admin team reviews all farmer credentials to uphold organic and community standards.
          </div>';
    } else {
        $content .= '
          <p>As a <strong>Customer</strong>, you can browse fresh seasonal produce, save favorite farm stalls, reserve harvest pickup slots, and interact directly with farmers through real-time order messaging.</p>';
    }

    $loginUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/login.php';
    $content .= '
      <div style="text-align: center; margin: 28px 0;">
        <a href="' . $loginUrl . '" class="btn-action">Sign In to Your Dashboard &rarr;</a>
      </div>
      <p style="font-size:13px; color:#64748b;">If you have any questions or need assistance, reply directly to this email or visit our Help Center.</p>';

    return sendMarketLinkEmail($toEmail, $fullName ?: $username, $subject, $content, $preheader);
}

/**
 * 2. User Login Alert Email
 */
function sendLoginAlertEmail(string $toEmail, string $username, string $fullName, ?string $ip = null, ?string $userAgent = null): bool {
    $subject = "Security Notice: Successful Sign-In to MarketLink 🔐";
    $preheader = "New login detected on your MarketLink account.";

    $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    $time = date('d M Y, h:i A') . ' (PKT)';
    $agent = $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'Web Browser');

    $content = '
      <h2 style="margin-top:0; color:#0f172a; font-size:20px;">Successful Sign-In Notice</h2>
      <p>Hello <strong>' . htmlspecialchars($fullName ?: $username) . '</strong>,</p>
      <p>This is a confirmation that your <strong>MarketLink</strong> account was just accessed successfully.</p>
      
      <div class="info-card">
        <div style="font-size:14px; margin-bottom:6px;"><strong>Login Timestamp:</strong> ' . $time . '</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>IP Address:</strong> <code>' . htmlspecialchars($ip) . '</code></div>
        <div style="font-size:13px; color:#64748b; word-break:break-all;"><strong>Device / Browser:</strong> ' . htmlspecialchars(substr($agent, 0, 120)) . '</div>
      </div>

      <p style="font-size:14px; color:#475569;">If this was you, you can safely ignore this notice. If you did not perform this login, please secure your credentials immediately or notify platform support.</p>
      
      <div style="text-align: center; margin: 24px 0;">
        <a href="http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/login.php" class="btn-action">Access Security Dashboard</a>
      </div>';

    return sendMarketLinkEmail($toEmail, $fullName ?: $username, $subject, $content, $preheader);
}

/**
 * Send a short-lived password-reset verification code.
 */
function sendPasswordResetOtpEmail(string $toEmail, string $username, string $otp): bool {
    $subject = 'Your MarketLink password reset code';
    $preheader = 'Use this code to verify your password reset request.';
    $content = '
      <h2 style="margin-top:0; color:#0f172a; font-size:22px;">Reset your password</h2>
      <p>Hello <strong>' . htmlspecialchars($username) . '</strong>,</p>
      <p>We received a request to reset your MarketLink password. Enter this verification code on the password reset page:</p>
      <div style="margin:24px 0; padding:18px; border-radius:12px; background:#ecfdf5; border:1px solid #a7f3d0; text-align:center; font-family:monospace; font-size:28px; font-weight:800; letter-spacing:7px; color:#065f46;">' . htmlspecialchars($otp) . '</div>
      <p>This code expires in <strong>10 minutes</strong> and can be used only once.</p>
      <p style="font-size:14px; color:#475569;">If you did not request a password reset, you can safely ignore this email. Your password will not be changed.</p>';

    return sendMarketLinkEmail($toEmail, $username, $subject, $content, $preheader);
}

/**
 * 3. Order Placed Notification (Customer Receipt & Farmer Notification)
 */
function sendOrderConfirmationEmails(int $orderId): bool {
    try {
        $pdo = getDBConnection();

        // Fetch complete order details
        $stmt = $pdo->prepare("SELECT o.*, 
                                      u_cust.email as customer_email, u_cust.username as customer_username,
                                      cp.full_name as customer_name, u_cust.phone_number as customer_phone,
                                      u_farm.email as farmer_email, u_farm.username as farmer_username,
                                      fp.stall_name, fp.contact_person, fp.business_phone, fp.business_email,
                                      m.market_name, m.address as market_address,
                                      ps.start_time, ps.end_time
                               FROM orders o
                               JOIN users u_cust ON o.customer_id = u_cust.user_id
                               LEFT JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                               JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                               JOIN users u_farm ON fp.farmer_id = u_farm.user_id
                               JOIN markets m ON o.market_id = m.market_id
                               JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                               WHERE o.order_id = :oid LIMIT 1");
        $stmt->execute([':oid' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return false;
        }

        // Fetch order items
        $iStmt = $pdo->prepare("SELECT oi.*, p.product_name, p.unit 
                                FROM order_items oi
                                JOIN products p ON oi.product_id = p.product_id
                                WHERE oi.order_id = :oid");
        $iStmt->execute([':oid' => $orderId]);
        $items = $iStmt->fetchAll();

        // Build items table HTML
        $itemsHtml = '';
        foreach ($items as $it) {
            $itemsHtml .= '
              <tr>
                <td><strong>' . htmlspecialchars($it['product_name']) . '</strong></td>
                <td>' . (float)$it['quantity'] . ' ' . htmlspecialchars($it['unit']) . '</td>
                <td>Rs. ' . number_format($it['unit_price'], 2) . '</td>
                <td class="text-right">Rs. ' . number_format($it['subtotal'], 2) . '</td>
              </tr>';
        }

        $pickupSlotFormatted = date('h:i A', strtotime($order['start_time'])) . ' - ' . date('h:i A', strtotime($order['end_time']));
        $pickupDateFormatted = date('D, d M Y', strtotime($order['pickup_date']));
        $totalFormatted = 'Rs. ' . number_format($order['total_amount'], 2);

        // A. Send confirmation to CUSTOMER
        $custSubject = "Order Confirmation: Pre-Order #{$order['order_number']} 🥦";
        $custPreheader = "Your pre-order with {$order['stall_name']} has been submitted.";
        
        $chatUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/customer/chat.php?order_id=' . $order['order_id'];

        $custContent = '
          <h2 style="margin-top:0; color:#0f172a; font-size:22px;">Thank You for Your Harvest Pre-Order!</h2>
          <p>Hello <strong>' . htmlspecialchars($order['customer_name'] ?: $order['customer_username']) . '</strong>,</p>
          <p>Your pre-order has been placed with <strong>' . htmlspecialchars($order['stall_name']) . '</strong>. The grower will harvest and pack your items for collection.</p>
          
          <div class="info-card">
            <div style="font-size:16px; font-weight:700; color:#065f46; margin-bottom:10px;">Order Summary: #' . htmlspecialchars($order['order_number']) . '</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Pickup Date:</strong> ' . $pickupDateFormatted . '</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Pickup Time Slot:</strong> ' . $pickupSlotFormatted . '</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Market Destination:</strong> ' . htmlspecialchars($order['market_name']) . '</div>
            <div style="font-size:13px; color:#64748b;"><strong>Stall Address:</strong> ' . htmlspecialchars($order['market_address']) . '</div>
          </div>

          <table class="table-details">
            <thead>
              <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Rate</th>
                <th class="text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              ' . $itemsHtml . '
              <tr class="total-row">
                <td colspan="3"><strong>Total Payable at Pickup:</strong></td>
                <td class="text-right"><strong>' . $totalFormatted . '</strong></td>
              </tr>
            </tbody>
          </table>

          <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:16px; margin:24px 0;">
            <div style="font-weight:700; color:#065f46; margin-bottom:4px;">💬 Direct Order Chat Enabled</div>
            <div style="font-size:13px; color:#047857;">You can now exchange messages, photos, and harvest updates directly with the farmer until your pickup is completed.</div>
            <div style="margin-top:12px;">
              <a href="' . $chatUrl . '" class="btn-action" style="margin:0; padding:10px 20px; font-size:13px;">Open Order Chat with Seller &rarr;</a>
            </div>
          </div>';

        sendMarketLinkEmail($order['customer_email'], $order['customer_name'] ?: $order['customer_username'], $custSubject, $custContent, $custPreheader);

        // B. Send notification to FARMER
        $farmSubject = "New Pre-Order Received: #{$order['order_number']} 📦";
        $farmPreheader = "Customer {$order['customer_name']} placed a new pre-order of {$totalFormatted}.";
        
        $farmerChatUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/farmer/chat.php?order_id=' . $order['order_id'];

        $farmContent = '
          <h2 style="margin-top:0; color:#0f172a; font-size:22px;">New Pre-Order Received!</h2>
          <p>Hello <strong>' . htmlspecialchars($order['contact_person'] ?: $order['farmer_username']) . '</strong>,</p>
          <p>Great news! A new customer pre-order has arrived for your stall <strong>' . htmlspecialchars($order['stall_name']) . '</strong>.</p>
          
          <div class="info-card">
            <div style="font-size:16px; font-weight:700; color:#065f46; margin-bottom:10px;">Order Details: #' . htmlspecialchars($order['order_number']) . '</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Customer:</strong> ' . htmlspecialchars($order['customer_name'] ?: $order['customer_username']) . '</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Customer Contact:</strong> ' . htmlspecialchars($order['customer_phone'] ?: 'N/A') . ' (' . htmlspecialchars($order['customer_email']) . ')</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Scheduled Pickup:</strong> ' . $pickupDateFormatted . ' (' . $pickupSlotFormatted . ')</div>
            <div style="font-size:14px;"><strong>Market:</strong> ' . htmlspecialchars($order['market_name']) . '</div>
          </div>

          <table class="table-details">
            <thead>
              <tr>
                <th>Product to Harvest</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th class="text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              ' . $itemsHtml . '
              <tr class="total-row">
                <td colspan="3"><strong>Total Order Value:</strong></td>
                <td class="text-right"><strong>' . $totalFormatted . '</strong></td>
              </tr>
            </tbody>
          </table>

          <div style="text-align:center; margin:28px 0;">
            <a href="' . $farmerChatUrl . '" class="btn-action">Chat with Customer &amp; Manage Order &rarr;</a>
          </div>';

        $farmerEmail = $order['business_email'] ?: $order['farmer_email'];
        sendMarketLinkEmail($farmerEmail, $order['contact_person'] ?: $order['farmer_username'], $farmSubject, $farmContent, $farmPreheader);

        return true;
    } catch (Exception $e) {
        error_log("Order confirmation email error: " . $e->getMessage());
        return false;
    }
}

/**
 * 4. Order Status Update / Cancellation Email
 */
function sendOrderStatusEmail(int $orderId, string $newStatus, string $reason = ''): bool {
    try {
        $pdo = getDBConnection();

        $stmt = $pdo->prepare("SELECT o.*, 
                                      u_cust.email as customer_email, u_cust.username as customer_username,
                                      cp.full_name as customer_name,
                                      fp.stall_name, fp.contact_person, fp.business_phone,
                                      m.market_name, m.address as market_address,
                                      ps.start_time, ps.end_time
                               FROM orders o
                               JOIN users u_cust ON o.customer_id = u_cust.user_id
                               LEFT JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                               JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                               JOIN markets m ON o.market_id = m.market_id
                               JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                               WHERE o.order_id = :oid LIMIT 1");
        $stmt->execute([':oid' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return false;
        }

        $pickupSlotFormatted = date('h:i A', strtotime($order['start_time'])) . ' - ' . date('h:i A', strtotime($order['end_time']));
        $pickupDateFormatted = date('D, d M Y', strtotime($order['pickup_date']));

        $statusTitle = '';
        $statusMsg = '';
        $subject = '';

        switch ($newStatus) {
            case 'accepted':
                $subject = "Order Accepted: #{$order['order_number']} by {$order['stall_name']} 🥕";
                $statusTitle = "Your Harvest Pre-Order Has Been Accepted!";
                $statusMsg = "The farmer <strong>" . htmlspecialchars($order['stall_name']) . "</strong> has accepted your order and added it to their harvest schedule for <strong>" . $pickupDateFormatted . "</strong>.";
                break;

            case 'ready_for_pickup':
                $subject = "Ready for Collection: Pre-Order #{$order['order_number']} 🥬";
                $statusTitle = "Your Order is Packed & Ready for Pickup!";
                $statusMsg = "Your harvest box has been sorted and packed. Please proceed to <strong>" . htmlspecialchars($order['stall_name']) . "</strong> at <strong>" . htmlspecialchars($order['market_name']) . "</strong> during your pickup window (<strong>" . $pickupSlotFormatted . "</strong>).";
                break;

            case 'completed':
                $subject = "Order Completed: Pre-Order #{$order['order_number']} 🎉";
                $statusTitle = "Pickup Completed — Enjoy Your Fresh Produce!";
                $statusMsg = "Your order with <strong>" . htmlspecialchars($order['stall_name']) . "</strong> has been fulfilled. Thank you for supporting sustainable, local agriculture! Please leave a review to share your feedback.";
                break;

            case 'cancelled':
            case 'declined':
                $subject = "Order Notice: Pre-Order #{$order['order_number']} Cancelled";
                $statusTitle = "Your Pre-Order Has Been Cancelled";
                $statusMsg = "Your order #<strong>" . htmlspecialchars($order['order_number']) . "</strong> has been cancelled." . ($reason ? "<br><br><strong>Reason:</strong> " . htmlspecialchars($reason) : "");
                break;

            default:
                return false;
        }

        $content = '
          <h2 style="margin-top:0; color:#0f172a; font-size:22px;">' . $statusTitle . '</h2>
          <p>Hello <strong>' . htmlspecialchars($order['customer_name'] ?: $order['customer_username']) . '</strong>,</p>
          <p>' . $statusMsg . '</p>
          
          <div class="info-card">
            <div style="font-size:15px; font-weight:700; color:#065f46; margin-bottom:8px;">Order Details</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Order Number:</strong> #' . htmlspecialchars($order['order_number']) . '</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Stall:</strong> ' . htmlspecialchars($order['stall_name']) . '</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Pickup Date:</strong> ' . $pickupDateFormatted . ' (' . $pickupSlotFormatted . ')</div>
            <div style="font-size:14px; margin-bottom:4px;"><strong>Market Destination:</strong> ' . htmlspecialchars($order['market_name']) . '</div>
            <div style="font-size:14px;"><strong>Order Total:</strong> Rs. ' . number_format($order['total_amount'], 2) . '</div>
          </div>

          <div style="text-align:center; margin:24px 0;">
            <a href="http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/customer/orders.php" class="btn-action">View Order &amp; Details &rarr;</a>
          </div>';

        return sendMarketLinkEmail($order['customer_email'], $order['customer_name'] ?: $order['customer_username'], $subject, $content, $statusTitle);
    } catch (Exception $e) {
        error_log("Order status email error: " . $e->getMessage());
        return false;
    }
}

/**
 * 5. Contact Form User Confirmation Email
 */
function sendContactConfirmationEmail(string $name, string $email, string $subject, string $message, int $inquiryId): bool {
    $ticketNum = 'INQ-' . str_pad((string)$inquiryId, 5, '0', STR_PAD_LEFT);
    $emailSubject = "We Received Your Message [{$ticketNum}] • MarketLink Support 📬";
    $preheader = "Thank you for reaching out to MarketLink. Our administration is reviewing your message.";

    $body = '
      <h2 style="margin-top:0; color:#0f172a; font-size:22px;">Message Received!</h2>
      <p>Hello <strong>' . htmlspecialchars($name) . '</strong>,</p>
      <p>Thank you for getting in touch with <strong>MarketLink</strong>. We have successfully logged your inquiry in our system under ticket reference <strong>#' . $ticketNum . '</strong>.</p>
      
      <div class="info-card">
        <div style="font-size:15px; font-weight:700; color:#065f46; margin-bottom:10px;">Inquiry Summary</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Reference:</strong> #' . $ticketNum . '</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Date &amp; Time:</strong> ' . date('d M Y, h:i A') . ' (PKT)</div>
        <div style="font-size:14px; margin-top:10px; padding:12px; background:#ffffff; border-radius:8px; border:1px solid #e2e8f0; color:#334155; white-space:pre-wrap;">' . nl2br(htmlspecialchars($message)) . '</div>
      </div>

      <p style="font-size:14px; color:#475569;">Our administrative team actively reviews all incoming communications. You will receive an official response directly to this email address within <strong>24 business hours</strong>.</p>
      
      <div style="text-align:center; margin:28px 0;">
        <a href="http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/index.php" class="btn-action">Return to MarketLink &rarr;</a>
      </div>';

    return sendMarketLinkEmail($email, $name, $emailSubject, $body, $preheader);
}

/**
 * 6. Contact Form Admin Notification Alert
 */
function sendAdminContactAlertEmail(string $name, string $email, string $phone, string $subject, string $message, int $inquiryId): bool {
    $ticketNum = 'INQ-' . str_pad((string)$inquiryId, 5, '0', STR_PAD_LEFT);
    $adminSubject = "🚨 [New Inquiry #{$ticketNum}] {$subject} — from {$name}";
    $preheader = "New contact form message submitted on MarketLink platform.";

    $adminUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/admin/inquiries.php?id=' . $inquiryId;

    $body = '
      <h2 style="margin-top:0; color:#991b1b; font-size:22px;">New Contact Inquiry Received</h2>
      <p>A new visitor or customer message has just been submitted via the <strong>MarketLink Contact Form</strong>.</p>
      
      <div class="info-card">
        <div style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:10px;">Customer &amp; Message Details</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Ticket Number:</strong> #' . $ticketNum . '</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Sender Name:</strong> ' . htmlspecialchars($name) . '</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Email Address:</strong> <a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Phone Number:</strong> ' . htmlspecialchars($phone ?: 'Not provided') . '</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</div>
        <div style="font-size:14px; margin-bottom:6px;"><strong>Timestamp:</strong> ' . date('d M Y, h:i A') . ' (PKT)</div>
        <div style="font-size:14px; margin-top:12px; padding:14px; background:#ffffff; border-radius:8px; border:1px solid #cbd5e1; color:#0f172a; line-height:1.6; white-space:pre-wrap;">' . nl2br(htmlspecialchars($message)) . '</div>
      </div>

      <div style="text-align:center; margin:28px 0;">
        <a href="' . $adminUrl . '" class="btn-action" style="background:#0f766e;">Open &amp; Reply in Admin Portal &rarr;</a>
      </div>
      <p style="font-size:13px; color:#64748b; text-align:center;">You can also respond directly from the MarketLink Admin Dashboard.</p>';

    // Send to admin email and platform support mailbox
    sendMarketLinkEmail('admin@marketlink.com', 'MarketLink Admin', $adminSubject, $body, $preheader);
    return sendMarketLinkEmail('arifrafay551@gmail.com', 'MarketLink Operations', $adminSubject, $body, $preheader);
}

/**
 * 7. Admin Official Reply Email to Customer
 */
function sendAdminReplyEmail(string $customerName, string $customerEmail, string $originalSubject, string $originalMessage, string $adminReply, string $adminName = 'MarketLink Support Team'): bool {
    $emailSubject = "Response to Your Inquiry: {$originalSubject} • MarketLink 🌿";
    $preheader = "MarketLink Administration has responded to your message.";

    $body = '
      <h2 style="margin-top:0; color:#047857; font-size:22px;">Response from MarketLink Support</h2>
      <p>Hello <strong>' . htmlspecialchars($customerName) . '</strong>,</p>
      <p>Our administration team has reviewed your inquiry regarding <strong>' . htmlspecialchars($originalSubject) . '</strong>. Please find our official response below:</p>
      
      <!-- Official Admin Reply Highlight Box -->
      <div style="background:#ecfdf5; border:1.5px solid #10b981; border-radius:12px; padding:20px; margin:22px 0;">
        <div style="font-size:13px; font-weight:700; color:#065f46; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
          💬 Official Reply from ' . htmlspecialchars($adminName) . '
        </div>
        <div style="font-size:15px; color:#064e3b; line-height:1.7; white-space:pre-wrap;">' . nl2br(htmlspecialchars($adminReply)) . '</div>
        <div style="font-size:12px; color:#047857; margin-top:12px; border-top:1px dashed #6ee7b7; padding-top:8px;">
          Dispatched on ' . date('d M Y, h:i A') . ' (PKT)
        </div>
      </div>

      <!-- Original Inquiry Reference -->
      <div class="info-card">
        <div style="font-size:13px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px;">Your Original Message:</div>
        <div style="font-size:13px; color:#475569; line-height:1.5; font-style:italic; white-space:pre-wrap;">' . nl2br(htmlspecialchars($originalMessage)) . '</div>
      </div>

      <p style="font-size:14px; color:#475569;">If you have any further questions or require further assistance, simply reply directly to this email or visit our community portal.</p>
      
      <div style="text-align:center; margin:28px 0;">
        <a href="http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/index.php" class="btn-action">Visit MarketLink &rarr;</a>
      </div>';

    return sendMarketLinkEmail($customerEmail, $customerName, $emailSubject, $body, $preheader);
}
