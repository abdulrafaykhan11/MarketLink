<?php
/**
 * MarketLink - High Speed Order Chat API
 * Handles: Fetch Conversations, Fetch Messages, Send Text / Photos / Documents
 * Enforces: Order closure rule (Completed/Cancelled orders are read-only)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$pdo = getDBConnection();
$currentUserId = (int)$_SESSION['user_id'];
$currentUserRole = $_SESSION['role'];

$action = $_REQUEST['action'] ?? '';

// Helper to format bytes
function formatBytes($bytes, $precision = 1) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow = floor(log($bytes) / log(1024));
    return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
}

try {
    // -------------------------------------------------------------
    // 1. FETCH CONVERSATIONS LIST
    // -------------------------------------------------------------
    if ($action === 'fetch_conversations') {
        if ($currentUserRole === 'customer') {
            $sql = "SELECT o.order_id, o.order_number, o.order_status, o.pickup_date, o.farmer_id as other_user_id,
                           fp.stall_name as other_name, fp.contact_person as other_subname, u.profile_image,
                           m.market_name,
                           (SELECT message_text FROM order_messages om WHERE om.order_id = o.order_id ORDER BY om.message_id DESC LIMIT 1) as last_message,
                           (SELECT attachment_type FROM order_messages om WHERE om.order_id = o.order_id ORDER BY om.message_id DESC LIMIT 1) as last_attachment_type,
                           (SELECT created_at FROM order_messages om WHERE om.order_id = o.order_id ORDER BY om.message_id DESC LIMIT 1) as last_message_time,
                           (SELECT COUNT(*) FROM order_messages om WHERE om.order_id = o.order_id AND om.receiver_id = :uid1 AND om.is_read = 0) as unread_count
                    FROM orders o
                    JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                    JOIN users u ON fp.farmer_id = u.user_id
                    JOIN markets m ON o.market_id = m.market_id
                    WHERE o.customer_id = :uid2
                    ORDER BY CASE WHEN o.order_status IN ('placed', 'accepted', 'ready_for_pickup') THEN 1 ELSE 2 END,
                             COALESCE((SELECT MAX(created_at) FROM order_messages om WHERE om.order_id = o.order_id), o.created_at) DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':uid1' => $currentUserId, ':uid2' => $currentUserId]);
        } else {
            // Farmer / Admin
            $sql = "SELECT o.order_id, o.order_number, o.order_status, o.pickup_date, o.customer_id as other_user_id,
                           COALESCE(cp.full_name, u.username) as other_name, u.phone_number as other_subname, u.profile_image,
                           m.market_name,
                           (SELECT message_text FROM order_messages om WHERE om.order_id = o.order_id ORDER BY om.message_id DESC LIMIT 1) as last_message,
                           (SELECT attachment_type FROM order_messages om WHERE om.order_id = o.order_id ORDER BY om.message_id DESC LIMIT 1) as last_attachment_type,
                           (SELECT created_at FROM order_messages om WHERE om.order_id = o.order_id ORDER BY om.message_id DESC LIMIT 1) as last_message_time,
                           (SELECT COUNT(*) FROM order_messages om WHERE om.order_id = o.order_id AND om.receiver_id = :uid1 AND om.is_read = 0) as unread_count
                    FROM orders o
                    JOIN users u ON o.customer_id = u.user_id
                    LEFT JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                    JOIN markets m ON o.market_id = m.market_id
                    WHERE o.farmer_id = :uid2 OR :is_admin = 'admin'
                    ORDER BY CASE WHEN o.order_status IN ('placed', 'accepted', 'ready_for_pickup') THEN 1 ELSE 2 END,
                             COALESCE((SELECT MAX(created_at) FROM order_messages om WHERE om.order_id = o.order_id), o.created_at) DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':uid1' => $currentUserId, ':uid2' => $currentUserId, ':is_admin' => $currentUserRole]);
        }

        $conversations = $stmt->fetchAll();

        foreach ($conversations as &$c) {
            $c['is_closed'] = in_array($c['order_status'], ['completed', 'cancelled', 'declined']);
            $c['formatted_date'] = date('M d', strtotime($c['pickup_date']));
            if (!empty($c['last_message_time'])) {
                $c['last_time_human'] = date('h:i A', strtotime($c['last_message_time']));
            } else {
                $c['last_time_human'] = '';
            }

            if (empty($c['last_message']) && !empty($c['last_attachment_type']) && $c['last_attachment_type'] !== 'none') {
                $c['last_message'] = ($c['last_attachment_type'] === 'image') ? '📷 Photo' : '📎 Document';
            }
        }

        echo json_encode(['status' => 'success', 'conversations' => $conversations]);
        exit;
    }

    // -------------------------------------------------------------
    // 2. FETCH MESSAGES FOR A GIVEN ORDER
    // -------------------------------------------------------------
    if ($action === 'fetch_messages') {
        $orderId = (int)($_GET['order_id'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
            exit;
        }

        // Verify order membership
        $ordStmt = $pdo->prepare("SELECT o.*, 
                                         fp.stall_name, fp.contact_person, fp.business_phone,
                                         u_cust.username as customer_username, cp.full_name as customer_name,
                                         m.market_name
                                  FROM orders o
                                  JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                                  JOIN users u_cust ON o.customer_id = u_cust.user_id
                                  LEFT JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                                  JOIN markets m ON o.market_id = m.market_id
                                  WHERE o.order_id = :oid AND (o.customer_id = :uid1 OR o.farmer_id = :uid2 OR :is_admin = 'admin')
                                  LIMIT 1");
        $ordStmt->execute([
            ':oid' => $orderId,
            ':uid1' => $currentUserId,
            ':uid2' => $currentUserId,
            ':is_admin' => $currentUserRole
        ]);
        $order = $ordStmt->fetch();

        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found or permission denied']);
            exit;
        }

        // Mark incoming messages as read
        $updStmt = $pdo->prepare("UPDATE order_messages SET is_read = 1 WHERE order_id = :oid AND receiver_id = :uid AND is_read = 0");
        $updStmt->execute([':oid' => $orderId, ':uid' => $currentUserId]);

        // Fetch messages
        $msgStmt = $pdo->prepare("SELECT om.*, u.username as sender_username, u.profile_image as sender_avatar
                                  FROM order_messages om
                                  JOIN users u ON om.sender_id = u.user_id
                                  WHERE om.order_id = :oid
                                  ORDER BY om.message_id ASC");
        $msgStmt->execute([':oid' => $orderId]);
        $messages = $msgStmt->fetchAll();

        foreach ($messages as &$m) {
            $m['is_mine'] = ((int)$m['sender_id'] === $currentUserId);
            $m['time_formatted'] = date('h:i A', strtotime($m['created_at']));
            $m['date_formatted'] = date('M d, Y', strtotime($m['created_at']));
            $m['file_size_formatted'] = formatBytes((int)($m['file_size'] ?? 0));
        }

        $isClosed = in_array($order['order_status'], ['completed', 'cancelled', 'declined']);

        $otherPartyName = ($currentUserRole === 'customer') 
            ? $order['stall_name'] 
            : ($order['customer_name'] ?: $order['customer_username']);

        $otherPartySub = ($currentUserRole === 'customer')
            ? ('Producer: ' . $order['contact_person'])
            : ('Market Pickup: ' . $order['market_name']);

        echo json_encode([
            'status' => 'success',
            'order' => [
                'order_id' => (int)$order['order_id'],
                'order_number' => $order['order_number'],
                'order_status' => $order['order_status'],
                'pickup_date' => date('D, M d, Y', strtotime($order['pickup_date'])),
                'is_closed' => $isClosed,
                'other_party_name' => $otherPartyName,
                'other_party_sub' => $otherPartySub,
                'farmer_id' => (int)$order['farmer_id'],
                'customer_id' => (int)$order['customer_id']
            ],
            'messages' => $messages
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // 3. SEND MESSAGE (TEXT, PHOTO, DOCUMENT)
    // -------------------------------------------------------------
    if ($action === 'send_message') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'POST method required']);
            exit;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $messageText = trim($_POST['message_text'] ?? '');

        if ($orderId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid order parameter']);
            exit;
        }

        // Fetch and verify order
        $ordStmt = $pdo->prepare("SELECT o.*, fp.stall_name, u_cust.username as customer_username, cp.full_name as customer_name
                                  FROM orders o
                                  JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                                  JOIN users u_cust ON o.customer_id = u_cust.user_id
                                  LEFT JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                                  WHERE o.order_id = :oid AND (o.customer_id = :uid1 OR o.farmer_id = :uid2 OR :is_admin = 'admin')
                                  LIMIT 1");
        $ordStmt->execute([
            ':oid' => $orderId,
            ':uid1' => $currentUserId,
            ':uid2' => $currentUserId,
            ':is_admin' => $currentUserRole
        ]);
        $order = $ordStmt->fetch();

        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found or permission denied']);
            exit;
        }

        // CRITICAL REQUIREMENT: "jaise hi order khatam hoa chat close ho jaegi"
        if (in_array($order['order_status'], ['completed', 'cancelled', 'declined'])) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'This order has ended (' . ucfirst($order['order_status']) . '). The chat is now closed and read-only.'
            ]);
            exit;
        }

        // Determine recipient
        $receiverId = ($currentUserId === (int)$order['customer_id']) 
            ? (int)$order['farmer_id'] 
            : (int)$order['customer_id'];

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = 'none';
        $fileSize = 0;

        // Process Attachment if present
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $origName = basename($file['name']);
            $fileExt = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $fileSize = (int)$file['size'];

            $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $docExts   = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'zip'];

            if (in_array($fileExt, $imageExts, true)) {
                $attachmentType = 'image';
            } elseif (in_array($fileExt, $docExts, true)) {
                $attachmentType = 'document';
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Unsupported file format. Please upload photos (JPG, PNG, WEBP) or documents (PDF, DOCX, TXT).']);
                exit;
            }

            if ($fileSize > (15 * 1024 * 1024)) { // 15MB limit
                echo json_encode(['status' => 'error', 'message' => 'Attachment exceeds maximum 15MB limit.']);
                exit;
            }

            $uploadDir = __DIR__ . '/../uploads/chat/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uniqueName = 'chat_' . $orderId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $destination = $uploadDir . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $attachmentPath = 'uploads/chat/' . $uniqueName;
                $attachmentName = $origName;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save attachment file.']);
                exit;
            }
        }

        if (empty($messageText) && empty($attachmentPath)) {
            echo json_encode(['status' => 'error', 'message' => 'Please type a message or select an attachment.']);
            exit;
        }

        // Insert message
        $insStmt = $pdo->prepare("INSERT INTO order_messages 
                                  (order_id, sender_id, receiver_id, message_text, attachment_path, attachment_name, attachment_type, file_size, is_read, created_at)
                                  VALUES (:oid, :sid, :rid, :txt, :apath, :aname, :atype, :fsize, 0, NOW())");
        $insStmt->execute([
            ':oid' => $orderId,
            ':sid' => $currentUserId,
            ':rid' => $receiverId,
            ':txt' => $messageText ?: null,
            ':apath' => $attachmentPath,
            ':aname' => $attachmentName,
            ':atype' => $attachmentType,
            ':fsize' => $fileSize
        ]);
        $newMsgId = $pdo->lastInsertId();

        // In-app Notification for receiver
        $senderName = ($currentUserRole === 'customer') 
            ? ($order['customer_name'] ?: $order['customer_username']) 
            : $order['stall_name'];

        $notifSnippet = !empty($messageText) ? substr($messageText, 0, 80) : ($attachmentType === 'image' ? 'Sent a photo' : 'Sent a document');
        
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                       VALUES (:uid, :title, :msg, 'system', 0, NOW())")
            ->execute([
                ':uid' => $receiverId,
                ':title' => "New Message from {$senderName}",
                ':msg' => "Order #{$order['order_number']}: {$notifSnippet}"
            ]);

        echo json_encode([
            'status' => 'success',
            'message_id' => $newMsgId,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'attachment_name' => $attachmentName,
            'created_at' => date('h:i A')
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action parameter']);
    exit;

} catch (Exception $e) {
    error_log("Chat API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Internal server error: ' . $e->getMessage()]);
    exit;
}
