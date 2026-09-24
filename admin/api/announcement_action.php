<?php
/**
 * MarketLink Admin API - Platform Announcements & Notification Broadcast
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$pdo = getDBConnection();
$action = trim($_POST['action'] ?? 'create');

try {
    if ($action === 'create') {
        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $targetRole = trim($_POST['target_role'] ?? 'all');
        $broadcast  = isset($_POST['broadcast_notifications']) ? 1 : 0;

        if (empty($title) || empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Title and content cannot be empty.']);
            exit;
        }

        if (!in_array($targetRole, ['all', 'farmer', 'customer'])) {
            $targetRole = 'all';
        }

        $pdo->beginTransaction();

        // 1. Insert into platform_announcements
        $stmt = $pdo->prepare("INSERT INTO platform_announcements (admin_id, title, content, target_role, is_active, created_at)
                               VALUES (:aid, :title, :content, :role, 1, NOW())");
        $stmt->execute([
            ':aid'     => (int)$_SESSION['user_id'],
            ':title'   => $title,
            ':content' => $content,
            ':role'    => $targetRole
        ]);
        $announcementId = $pdo->lastInsertId();

        // 2. Broadcast to users in notifications table if checked
        if ($broadcast) {
            $roleFilter = "";
            if ($targetRole === 'farmer') {
                $roleFilter = "WHERE role = 'farmer'";
            } elseif ($targetRole === 'customer') {
                $roleFilter = "WHERE role = 'customer'";
            }

            $userSql = "SELECT user_id FROM users {$roleFilter}";
            $users = $pdo->query($userSql)->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($users)) {
                $notifInsert = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                                             VALUES (:uid, :title, :msg, 'announcement', 0, NOW())");
                foreach ($users as $uid) {
                    $notifInsert->execute([
                        ':uid'   => $uid,
                        ':title' => '📢 ' . $title,
                        ':msg'   => $content
                    ]);
                }
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Announcement published' . ($broadcast ? ' and broadcasted to user alerts!' : ' successfully!')
        ]);
        exit;
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['announcement_id'] ?? 0);
        $status = (int)($_POST['is_active'] ?? 1);
        $pdo->prepare("UPDATE platform_announcements SET is_active = :st WHERE announcement_id = :id")
            ->execute([':st' => $status, ':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Announcement status toggled.']);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['announcement_id'] ?? 0);
        $pdo->prepare("DELETE FROM platform_announcements WHERE announcement_id = :id")->execute([':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Announcement deleted.']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Announcement error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
