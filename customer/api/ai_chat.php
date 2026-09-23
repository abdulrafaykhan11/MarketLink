<?php
/**
 * MarketLink - AI Farm Assistant Chatbot API
 * Answers live queries using database FAQs, market timings, and product availability.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

$pdo = getDBConnection();
$query = trim($_POST['query'] ?? '');

if (empty($query)) {
    echo json_encode(['status' => 'error', 'reply' => 'Please ask a question about our markets or fresh harvest.']);
    exit;
}

try {
    $qLower = strtolower($query);

    // 1. Check live product search query
    $keywords = explode(' ', preg_replace('/[^a-z0-9 ]/', '', $qLower));
    $relevantKeywords = array_filter($keywords, fn($w) => strlen($w) >= 3 && !in_array($w, ['what', 'where', 'when', 'have', 'from', 'with', 'about', 'some']));

    if (!empty($relevantKeywords)) {
        $likeClauses = [];
        $params = [];
        foreach (array_values($relevantKeywords) as $idx => $kw) {
            $likeClauses[] = "(p.product_name LIKE :kwa{$idx} OR p.description LIKE :kwb{$idx} OR pc.category_name LIKE :kwc{$idx})";
            $params[":kwa{$idx}"] = "%{$kw}%";
            $params[":kwb{$idx}"] = "%{$kw}%";
            $params[":kwc{$idx}"] = "%{$kw}%";
        }
        $sql = "SELECT p.product_name, p.unit, wi.price, fp.stall_name, m.market_name 
                FROM products p
                JOIN product_categories pc ON p.category_id = pc.category_id
                JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                JOIN weekly_inventory wi ON p.product_id = wi.product_id
                JOIN farmer_market_stalls fms ON wi.stall_id = fms.stall_id
                JOIN markets m ON fms.market_id = m.market_id
                WHERE " . implode(' OR ', $likeClauses) . " AND wi.is_available = 1
                GROUP BY p.product_id LIMIT 3";
        $pStmt = $pdo->prepare($sql);
        $pStmt->execute($params);
        $foundProducts = $pStmt->fetchAll();

        if (!empty($foundProducts)) {
            $prodList = [];
            foreach ($foundProducts as $fp) {
                $prodList[] = "• **{$fp['product_name']}** (Rs. {$fp['price']}/{$fp['unit']}) available at *{$fp['stall_name']}* ({$fp['market_name']})";
            }
            $reply = "Here is what our verified farmers have in stock:\n\n" . implode("\n", $prodList) . "\n\nYou can add these to your pre-order basket directly from the 'Browse Harvest' tab!";
            echo json_encode(['status' => 'success', 'reply' => $reply]);
            exit;
        }
    }

    // 2. Check market timing or location queries
    if (str_contains($qLower, 'market') || str_contains($qLower, 'timing') || str_contains($qLower, 'hour') || str_contains($qLower, 'where') || str_contains($qLower, 'day') || str_contains($qLower, 'saturday') || str_contains($qLower, 'sunday')) {
        $mStmt = $pdo->query("SELECT market_name, operating_days, operating_hours, address FROM markets WHERE status = 'active' LIMIT 4");
        $markets = $mStmt->fetchAll();
        if (!empty($markets)) {
            $mList = [];
            foreach ($markets as $m) {
                $mList[] = "📍 **{$m['market_name']}**\n   Operating: {$m['operating_days']} ({$m['operating_hours']})\n   Address: {$m['address']}";
            }
            $reply = "Here are our active farmers market locations and operating windows:\n\n" . implode("\n\n", $mList) . "\n\nYou can also explore them on our interactive GPS map under the 'Markets & Map' page.";
            echo json_encode(['status' => 'success', 'reply' => $reply]);
            exit;
        }
    }

    // 3. Check FAQ table matching keywords
    $faqStmt = $pdo->query("SELECT question, answer, keywords FROM ai_chatbot_faqs WHERE is_active = 1");
    $faqs = $faqStmt->fetchAll();

    foreach ($faqs as $faq) {
        $kwList = explode(',', strtolower($faq['keywords']));
        foreach ($kwList as $kw) {
            $kw = trim($kw);
            if (!empty($kw) && str_contains($qLower, $kw)) {
                echo json_encode(['status' => 'success', 'reply' => $faq['answer']]);
                exit;
            }
        }
    }

    // Default intelligent guidance
    echo json_encode([
        'status' => 'success',
        'reply' => "I am your MarketLink Farm Assistant! You can ask me about:\n\n• What produce is currently in stock (e.g., 'Do you have heirloom tomatoes?')\n• Weekend market operating days and hours\n• How pre-orders and pickup time slots work\n• Where specific farmer stalls are located\n\nHow can I help you today?"
    ]);

} catch (Exception $e) {
    error_log("AI Chat Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'reply' => "I apologize, but I encountered a slight hiccup accessing the farm inventory. Please try again!"]);
}
