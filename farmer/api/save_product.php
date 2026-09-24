<?php
/**
 * MarketLink - Farmer API: Add or Update Produce Listing
 * Multi-Photo Support: 1 Compulsory Cover Photo + up to 4 Optional Gallery Photos
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

/**
 * Upload Helper for Product Photos
 */
function uploadProductImageFile(array $file, int $userId): ?string {
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimes)) {
        return null;
    }

    if ($file['size'] > 8 * 1024 * 1024) {
        return null;
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

    $filename = 'product_' . $userId . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'uploads/products/' . $filename;
    }

    return null;
}

// 1. Handle Primary Compulsory Photo
$primaryImageUrl = $existingImage;

if (!empty($_FILES['product_photo']) && $_FILES['product_photo']['error'] === UPLOAD_ERR_OK) {
    $uploaded = uploadProductImageFile($_FILES['product_photo'], $userId);
    if ($uploaded) {
        $primaryImageUrl = $uploaded;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Primary photo must be a valid JPG, PNG, or WEBP under 8MB.']);
        exit;
    }
} elseif (empty($primaryImageUrl) && $productId === 0) {
    // New product requires primary photo
    echo json_encode(['status' => 'error', 'message' => 'A primary product photo is compulsory. Customers need to see what they are purchasing.']);
    exit;
}

// Fallback image for categories if still empty (edge case)
if (empty($primaryImageUrl)) {
    $categoryName = $pdo->prepare("SELECT category_name FROM product_categories WHERE category_id = :cid");
    $categoryName->execute([':cid' => $categoryId]);
    $cat = $categoryName->fetchColumn();

    if (stripos($cat, 'Fruit') !== false) {
        $primaryImageUrl = 'assets/images/products/apples.svg';
    } elseif (stripos($cat, 'Dairy') !== false || stripos($cat, 'Egg') !== false) {
        $primaryImageUrl = 'assets/images/products/eggs.svg';
    } elseif (stripos($cat, 'Bakery') !== false) {
        $primaryImageUrl = 'assets/images/products/sourdough.svg';
    } elseif (stripos($cat, 'Honey') !== false) {
        $primaryImageUrl = 'assets/images/products/honey.svg';
    } elseif (stripos($cat, 'Herb') !== false) {
        $primaryImageUrl = 'assets/images/products/herbs.svg';
    } else {
        $primaryImageUrl = 'assets/images/products/tomatoes.jpg';
    }
}

// 2. Handle Up to 4 Optional Additional Photos
$additionalUploadedPaths = [];
for ($i = 2; $i <= 5; $i++) {
    $fieldKey = 'photo_' . $i;
    if (!empty($_FILES[$fieldKey]) && $_FILES[$fieldKey]['error'] === UPLOAD_ERR_OK) {
        $path = uploadProductImageFile($_FILES[$fieldKey], $userId);
        if ($path) {
            $additionalUploadedPaths[] = ['path' => $path, 'order' => $i];
        }
    }
}

// Also check multiple file input array if used
if (!empty($_FILES['additional_photos']['name'][0])) {
    $files = $_FILES['additional_photos'];
    $count = count($files['name']);
    for ($j = 0; $j < min($count, 4); $j++) {
        if ($files['error'][$j] === UPLOAD_ERR_OK) {
            $singleFile = [
                'name'     => $files['name'][$j],
                'type'     => $files['type'][$j],
                'tmp_name' => $files['tmp_name'][$j],
                'error'    => $files['error'][$j],
                'size'     => $files['size'][$j],
            ];
            $path = uploadProductImageFile($singleFile, $userId);
            if ($path) {
                $additionalUploadedPaths[] = ['path' => $path, 'order' => count($additionalUploadedPaths) + 2];
            }
        }
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
            ':img'      => $primaryImageUrl,
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

        // Update primary image in product_images
        $checkPrimary = $pdo->prepare("SELECT image_id FROM product_images WHERE product_id = :pid AND is_primary = 1 LIMIT 1");
        $checkPrimary->execute([':pid' => $productId]);
        if ($checkPrimary->fetch()) {
            $pdo->prepare("UPDATE product_images SET image_url = :img WHERE product_id = :pid AND is_primary = 1")
                ->execute([':img' => $primaryImageUrl, ':pid' => $productId]);
        } else {
            $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, display_order, created_at) VALUES (:pid, :img, 1, 1, NOW())")
                ->execute([':pid' => $productId, ':img' => $primaryImageUrl]);
        }

        // Insert newly added additional gallery photos
        $imgInsertStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, display_order, created_at) VALUES (:pid, :img, 0, :ord, NOW())");
        foreach ($additionalUploadedPaths as $addPhoto) {
            $imgInsertStmt->execute([
                ':pid' => $productId,
                ':img' => $addPhoto['path'],
                ':ord' => $addPhoto['order']
            ]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Produce listing and photos updated successfully!']);
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
            ':img'   => $primaryImageUrl
        ]);
        $newProdId = (int)$pdo->lastInsertId();

        // 1. Insert primary photo into product_images
        $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, display_order, created_at) VALUES (:pid, :img, 1, 1, NOW())")
            ->execute([':pid' => $newProdId, ':img' => $primaryImageUrl]);

        // 2. Insert any optional additional photos (up to 4)
        $imgInsertStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, display_order, created_at) VALUES (:pid, :img, 0, :ord, NOW())");
        foreach ($additionalUploadedPaths as $addPhoto) {
            $imgInsertStmt->execute([
                ':pid' => $newProdId,
                ':img' => $addPhoto['path'],
                ':ord' => $addPhoto['order']
            ]);
        }

        // Retrieve farmer's stalls
        $stallStmt = $pdo->prepare("SELECT stall_id FROM farmer_market_stalls WHERE farmer_id = :fid AND status = 'active'");
        $stallStmt->execute([':fid' => $userId]);
        $stalls = $stallStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($stalls)) {
            // Check if there is any active market or assign stall 1
            $marketId = (int)$pdo->query("SELECT market_id FROM markets WHERE status = 'active' LIMIT 1")->fetchColumn();
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

        echo json_encode([
            'status' => 'success', 
            'message' => 'New produce listed with ' . (1 + count($additionalUploadedPaths)) . ' photo(s) successfully!'
        ]);
    }
} catch (Exception $e) {
    error_log("Save Product Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while saving product: ' . $e->getMessage()]);
}
