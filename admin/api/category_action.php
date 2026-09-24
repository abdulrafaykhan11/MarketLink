<?php
/**
 * MarketLink Admin API - Product Category Master Data Management
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
$action = trim($_POST['action'] ?? '');

try {
    if ($action === 'toggle_status') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $status = (int)($_POST['is_active'] ?? 1);
        $pdo->prepare("UPDATE product_categories SET is_active = :st WHERE category_id = :cid")
            ->execute([':st' => $status, ':cid' => $catId]);
        echo json_encode(['success' => true, 'message' => 'Category status updated.']);
        exit;
    }

    if ($action === 'save') {
        $catId   = (int)($_POST['category_id'] ?? 0);
        $name    = trim($_POST['category_name'] ?? '');
        $desc    = trim($_POST['description'] ?? '');
        $image   = trim($_POST['category_image'] ?? '');
        $active  = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category name is required.']);
            exit;
        }

        if ($catId > 0) {
            $stmt = $pdo->prepare("UPDATE product_categories SET category_name = :name, description = :desc, category_image = :img, is_active = :act WHERE category_id = :id");
            $stmt->execute([
                ':name' => $name,
                ':desc' => $desc,
                ':img'  => $image,
                ':act'  => $active,
                ':id'   => $catId
            ]);
            echo json_encode(['success' => true, 'message' => 'Category updated successfully!']);
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO product_categories (category_name, description, category_image, is_active, created_at) VALUES (:name, :desc, :img, :act, NOW())");
            $stmt->execute([
                ':name' => $name,
                ':desc' => $desc,
                ':img'  => $image,
                ':act'  => $active
            ]);
            echo json_encode(['success' => true, 'message' => 'New category created successfully!']);
            exit;
        }
    }

    if ($action === 'delete') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $prodCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE category_id = {$catId}")->fetchColumn();
        if ($prodCount > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Cannot delete category: {$prodCount} products are assigned to it. Please deactivate it instead."]);
            exit;
        }
        $pdo->prepare("DELETE FROM product_categories WHERE category_id = :id")->execute([':id' => $catId]);
        echo json_encode(['success' => true, 'message' => 'Category removed.']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Exception $e) {
    error_log("Category error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
