<?php
/**
 * MarketLink - Farmer API: Add or Update Produce Listing
 * Updated: Handles product photo upload (required per SRS)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

requireRole(['farmer', 'admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$pdo = getDBConnection();
$userId = (int)$_SESSION['user_id'];

$productId    = (int)($_POST['product_id'] ?? 0);
$productName  = trim($_POST['product_name'] ?? '');
$categoryId   = (int)($_POST['category_id'] ?? 1);
$price        = (float)($_POST['price'] ?? 0.00);
$unit         = trim($_POST['unit'] ?? 'kg');
$description  = trim($_POST['description'] ?? '');
$isRecurring  = isset($_POST['is_recurring_template']) ? 1 : 0;
$stockQuantity = (float)($_POST['stock_quantity'] ?? 50.00);
$existingImage = trim($_POST['existing_image'] ?? ''); // keep old image if no new upload

if (empty($productName)) {
    echo json_encode(['status' => 'error', 'message' => 'Produce item name is required.']);
    exit;
}

if ($price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Price must be greater than zero.']);
    exit;
}

// --- Handle photo upload ---
$imageUrl = $existingImage;

if (!empty($_FILES['product_photo']) && $_FILES['product_photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['product_photo'];
    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimes)) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG or WEBP product images accepted.']);
        exit;
    }

    if ($file['size'] > 8 * 1024 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'Product image must be under 8MB.']);
        exit;
    }

    $ext = match($mimeType) {
        'image/jpeg', 'image/jpg' => 'jpg',
        'image/png'               => 'png',
        'image/webp'              => 'webp',
        default                   => 'jpg'
    };

    $uploadDir = __DIR__ . '/../../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Remove old product image if exists and is from uploads dir
    if (!empty($existingImage) && strpos($existingImage, 'uploads/') === 0 && file_exists(__DIR__ . '/../../' . $existingImage)) {
        @unlink(__DIR__ . '/../../' . $existingImage);
    }

    $filename = 'product_' . $userId . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $imageUrl = 'uploads/products/' . $filename;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save product image. Check server permissions.']);
        exit;
    }
} elseif (empty($imageUrl) && $productId === 0) {
    // New product with no image — require it
    echo json_encode(['status' => 'error', 'message' => 'A product photo is required. Customers need to see what they are ordering.']);
    exit;
}

// Fallback image for categories if still empty (edge case)
if (empty($imageUrl)) {
    $categoryName = $pdo->prepare("SELECT category_name FROM product_categories WHERE category_id = :cid");
    $categoryName->execute([':cid' => $categoryId]);
    $cat = $categoryName->fetchColumn();

    if (stripos($cat, 'Fruit') !== false) {
        $imageUrl = 'assets/images/products/apples.svg';
    } elseif (stripos($cat, 'Dairy') !== false || stripos($cat, 'Egg') !== false) {
        $imageUrl = 'assets/images/products/eggs.svg';
    } elseif (stripos($cat, 'Bakery') !== false) {
        $imageUrl = 'assets/images/products/sourdough.svg';
    } elseif (stripos($cat, 'Honey') !== false) {
        $imageUrl = 'assets/images/products/honey.svg';
    } elseif (stripos($cat, 'Herb') !== false) {
        $imageUrl = 'assets/images/products/herbs.svg';
    } else {
        $imageUrl = 'assets/images/products/tomatoes.jpg';
    }
}

try {
    if ($productId > 0) {
        // Edit existing product
        $stmt = $pdo->prepare("UPDATE products SET 
                                category_id = :cid, 
                                product_name = :pname, 
                                description = :pdesc, 
                                unit = :unit, 
                                is_recurring_template = :recur, 
                                image_url = :img, 
                                updated_at = NOW() 
                                WHERE product_id = :pid AND (farmer_id = :fid OR :is_admin = 'admin')");
        $stmt->execute([
            ':cid'      => $categoryId,
            ':pname'    => $productName,
            ':pdesc'    => $description,
            ':unit'     => $unit,
            ':recur'    => $isRecurring,
            ':img'      => $imageUrl,
            ':pid'      => $productId,
            ':fid'      => $userId,
            ':is_admin' => $_SESSION['role'] ?? ''
        ]);

        // Update weekly inventory prices and quantities
        $invStmt = $pdo->prepare("UPDATE weekly_inventory SET price = :price, stock_quantity = :qty WHERE product_id = :pid");
        $invStmt->execute([
            ':price' => $price,
            ':qty'   => $stockQuantity,
            ':pid'   => $productId
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Produce listing updated successfully!']);
    } else {
        // Create new product
        $stmt = $pdo->prepare("INSERT INTO products (farmer_id, category_id, product_name, description, unit, is_recurring_template, image_url, created_at)
                               VALUES (:fid, :cid, :pname, :pdesc, :unit, :recur, :img, NOW())");
        $stmt->execute([
            ':fid'   => $userId,
            ':cid'   => $categoryId,
            ':pname' => $productName,
            ':pdesc' => $description,
            ':unit'  => $unit,
            ':recur' => $isRecurring,
            ':img'   => $imageUrl
        ]);
        $newProdId = (int)$pdo->lastInsertId();

        // Retrieve farmer's stalls
        $stallStmt = $pdo->prepare("SELECT stall_id FROM farmer_market_stalls WHERE farmer_id = :fid");
        $stallStmt->execute([':fid' => $userId]);
        $stalls = $stallStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($stalls)) {
            // Check if there is any stall or create default
            $marketId = (int)$pdo->query("SELECT market_id FROM markets LIMIT 1")->fetchColumn();
            if ($marketId) {
                $pStall = $pdo->prepare("INSERT INTO farmer_market_stalls (farmer_id, market_id, stall_number_location, operating_days, status, assigned_at)
                                         VALUES (:fid, :mid, 'Stall 1', 'Saturday, Sunday', 'active', NOW())");
                $pStall->execute([':fid' => $userId, ':mid' => $marketId]);
                $stalls = [(int)$pdo->lastInsertId()];
            }
        }

        // Seed weekly inventory days
        $invStmt = $pdo->prepare("INSERT INTO weekly_inventory (product_id, stall_id, day_of_week, stock_quantity, price, is_available)
                                 VALUES (:pid, :stid, :day, :qty, :price, 1)
                                 ON DUPLICATE KEY UPDATE stock_quantity = VALUES(stock_quantity), price = VALUES(price)");

        foreach ($stalls as $stallId) {
            foreach (['Saturday', 'Sunday', 'Friday', 'Wednesday'] as $day) {
                $invStmt->execute([
                    ':pid'   => $newProdId,
                    ':stid'  => $stallId,
                    ':day'   => $day,
                    ':qty'   => $stockQuantity,
                    ':price' => $price
                ]);
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'New produce listed successfully!']);
    }
} catch (Exception $e) {
    error_log("Save Product Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while saving product: ' . $e->getMessage()]);
}
