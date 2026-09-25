<?php
/**
 * MarketLink - Gemini Real-World AI Agent Service
 * Integrates Google Gemini 3.8 Flash with live MarketLink database access.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/gemini.php';

class GeminiBot {

    /**
     * Answer a user's question using Gemini 3.8 Flash infused with live database context.
     *
     * @param string $userMessage
     * @param array  $history
     * @param int|null $userId
     * @param string|null $userRole
     * @return array
     */
    public static function ask(string $userMessage, array $history = [], ?int $userId = null, ?string $userRole = null): array {
        $userMessage = trim($userMessage);
        if (empty($userMessage)) {
            return [
                'status' => 'error',
                'reply'  => 'Please ask a question about our harvest, markets, farmers, or your orders.'
            ];
        }

        try {
            $pdo = getDBConnection();

            // 1. Gather Live Database Context
            $dbKnowledge = self::buildLiveDatabaseKnowledge($pdo, $userMessage, $userId, $userRole);

            // 2. Build Gemini System Prompt
            $systemInstruction = self::buildSystemInstruction($dbKnowledge);

            // 3. Format Conversation Contents
            $contents = self::formatContents($history, $userMessage);

            // 4. Send Request to Gemini API
            $response = self::callGeminiAPI($systemInstruction, $contents);

            if ($response['success']) {
                return [
                    'status' => 'success',
                    'reply'  => $response['text'],
                    'model'  => GEMINI_MODEL
                ];
            } else {
                error_log("Gemini API Error: " . ($response['error'] ?? 'Unknown error'));
                // Intelligent fallback based on local search
                $fallbackReply = self::buildLocalFallbackReply($pdo, $userMessage, $userId);
                return [
                    'status' => 'success',
                    'reply'  => $fallbackReply,
                    'model'  => 'marketlink-local-engine'
                ];
            }
        } catch (Exception $e) {
            error_log("GeminiBot Exception: " . $e->getMessage());
            return [
                'status' => 'error',
                'reply'  => "I apologize, but I encountered a temporary connection issue while querying our harvest records. Please try asking again in a moment!"
            ];
        }
    }

    /**
     * Extract rich live facts from the database.
     */
    private static function buildLiveDatabaseKnowledge(PDO $pdo, string $userMessage, ?int $userId, ?string $userRole): array {
        $knowledge = [];

        // Today's context
        $knowledge['current_time'] = date('l, Y-m-d H:i');
        $knowledge['day_of_week']  = date('l');

        // A. Active Farmers Markets
        $stmtMarkets = $pdo->query("SELECT market_id, market_name, address, city, state, postal_code, operating_days, operating_hours FROM markets WHERE status = 'active' ORDER BY market_name ASC");
        $knowledge['markets'] = $stmtMarkets->fetchAll();

        // B. Approved Farmers & Stalls
        $stmtFarmers = $pdo->query("
            SELECT fp.farmer_id, fp.stall_name, fp.contact_person, fp.business_phone, fp.business_email, 
                   fp.description, fp.address, fp.order_cutoff_time,
                   GROUP_CONCAT(DISTINCT CONCAT(m.market_name, ' (Stall: ', COALESCE(fms.stall_number_location, 'General Area'), ', Days: ', COALESCE(fms.operating_days, m.operating_days), ')') SEPARATOR ' | ') as market_stalls
            FROM farmer_profiles fp
            LEFT JOIN farmer_market_stalls fms ON fp.farmer_id = fms.farmer_id AND fms.status = 'active'
            LEFT JOIN markets m ON fms.market_id = m.market_id
            WHERE fp.approval_status = 'approved'
            GROUP BY fp.farmer_id
            ORDER BY fp.stall_name ASC
        ");
        $knowledge['farmers'] = $stmtFarmers->fetchAll();

        // C. Categories
        $stmtCats = $pdo->query("SELECT category_id, category_name, description FROM product_categories WHERE is_active = 1 ORDER BY category_name ASC");
        $knowledge['categories'] = $stmtCats->fetchAll();

        // D. Active Products with live Weekly Inventory & Pricing
        $stmtProducts = $pdo->query("
            SELECT p.product_id, p.product_name, pc.category_name, p.unit, p.description,
                   fp.stall_name, fp.farmer_id,
                   wi.day_of_week, wi.stock_quantity, wi.price, wi.is_available,
                   m.market_name
            FROM products p
            JOIN product_categories pc ON p.category_id = pc.category_id
            JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
            LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id AND wi.is_available = 1
            LEFT JOIN farmer_market_stalls fms ON wi.stall_id = fms.stall_id
            LEFT JOIN markets m ON fms.market_id = m.market_id
            WHERE fp.approval_status = 'approved'
            ORDER BY p.product_name ASC
        ");
        $knowledge['products'] = $stmtProducts->fetchAll();

        // E. Platform Announcements
        $stmtAnnouncements = $pdo->query("SELECT title, content, target_role, created_at FROM platform_announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");
        $knowledge['announcements'] = $stmtAnnouncements->fetchAll();

        // F. User Specific Context (if logged in)
        $knowledge['user'] = null;
        if ($userId) {
            $uStmt = $pdo->prepare("SELECT user_id, username, email, role, phone_number FROM users WHERE user_id = :uid");
            $uStmt->execute([':uid' => $userId]);
            $userData = $uStmt->fetch();

            if ($userData) {
                // If customer profile
                $custProfile = null;
                if ($userData['role'] === 'customer') {
                    $cpStmt = $pdo->prepare("SELECT full_name, default_address FROM customer_profiles WHERE customer_id = :uid");
                    $cpStmt->execute([':uid' => $userId]);
                    $custProfile = $cpStmt->fetch();
                }

                // Recent orders
                $recentOrders = [];
                $oStmt = $pdo->prepare("
                    SELECT o.order_id, o.order_number, o.order_status, o.total_amount, o.pickup_date,
                           fp.stall_name, m.market_name, ps.start_time, ps.end_time,
                           GROUP_CONCAT(CONCAT(p.product_name, ' (Qty: ', oi.quantity, ' ', p.unit, ' @ Rs.', oi.unit_price, ')') SEPARATOR ', ') as ordered_items
                    FROM orders o
                    JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                    JOIN markets m ON o.market_id = m.market_id
                    LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    WHERE o.customer_id = :uid
                    GROUP BY o.order_id
                    ORDER BY o.created_at DESC
                    LIMIT 6
                ");
                $oStmt->execute([':uid' => $userId]);
                $recentOrders = $oStmt->fetchAll();

                // Current Cart items
                $cartItems = [];
                $cStmt = $pdo->prepare("
                    SELECT ci.quantity, p.product_name, p.unit, fp.stall_name
                    FROM cart_items ci
                    JOIN products p ON ci.product_id = p.product_id
                    JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                    WHERE ci.customer_id = :uid
                ");
                $cStmt->execute([':uid' => $userId]);
                $cartItems = $cStmt->fetchAll();

                $knowledge['user'] = [
                    'user_id'       => $userData['user_id'],
                    'username'      => $userData['username'],
                    'full_name'     => $custProfile['full_name'] ?? $userData['username'],
                    'role'          => $userData['role'],
                    'email'         => $userData['email'],
                    'phone'         => $userData['phone_number'],
                    'default_addr'  => $custProfile['default_address'] ?? 'Not set',
                    'recent_orders' => $recentOrders,
                    'cart_items'    => $cartItems
                ];
            }
        }

        // G. Targeted Specific Order Query (e.g. if user asks "What is status of order ML-2026-0012?" or "#12")
        if (preg_match('/(ML-\d{4}-\d+|\b\d{1,6}\b)/i', $userMessage, $match)) {
            $searchTerm = $match[1];
            $soStmt = $pdo->prepare("
                SELECT o.order_id, o.order_number, o.order_status, o.total_amount, o.pickup_date, o.cancellation_reason,
                       fp.stall_name, fp.business_phone as farmer_phone, m.market_name, ps.start_time, ps.end_time,
                       GROUP_CONCAT(CONCAT(p.product_name, ' (Qty: ', oi.quantity, ' ', p.unit, ')') SEPARATOR ', ') as items
                FROM orders o
                JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                JOIN markets m ON o.market_id = m.market_id
                LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.order_number LIKE :onum OR o.order_id = :oid
                GROUP BY o.order_id
                LIMIT 1
            ");
            $soStmt->execute([
                ':onum' => '%' . $searchTerm . '%',
                ':oid'  => is_numeric($searchTerm) ? (int)$searchTerm : 0
            ]);
            $specificOrder = $soStmt->fetch();
            if ($specificOrder) {
                $knowledge['queried_order'] = $specificOrder;
            }
        }

        return $knowledge;
    }

    /**
     * Constructs the comprehensive AI system prompt with live MarketLink data.
     */
    private static function buildSystemInstruction(array $k): string {
        $marketsJson = json_encode(array_map(function($m) {
            return [
                'name'    => $m['market_name'],
                'address' => $m['address'] . ', ' . $m['city'],
                'days'    => $m['operating_days'],
                'hours'   => $m['operating_hours']
            ];
        }, $k['markets'] ?? []), JSON_UNESCAPED_UNICODE);

        $farmersJson = json_encode(array_map(function($f) {
            return [
                'stall_name'  => $f['stall_name'],
                'farmer_name' => $f['contact_person'],
                'phone'       => $f['business_phone'],
                'cutoff'      => $f['order_cutoff_time'],
                'markets'     => $f['market_stalls']
            ];
        }, $k['farmers'] ?? []), JSON_UNESCAPED_UNICODE);

        $categoriesList = implode(', ', array_column($k['categories'] ?? [], 'category_name'));

        // Compact list of products with current pricing & inventory
        $productsSummaries = [];
        foreach ($k['products'] ?? [] as $p) {
            $pName = $p['product_name'];
            $stall = $p['stall_name'];
            $price = !empty($p['price']) ? 'Rs. ' . (float)$p['price'] . '/' . $p['unit'] : 'Variable';
            $stock = !empty($p['stock_quantity']) ? (float)$p['stock_quantity'] . ' ' . $p['unit'] : 'In Season';
            $cat   = $p['category_name'];
            $day   = $p['day_of_week'] ?? 'Weekly';
            $mkt   = $p['market_name'] ?? 'Assigned Stalls';
            $productsSummaries[] = "{$pName} ({$cat}): {$price}, Stock: {$stock} on {$day} at {$stall} ({$mkt})";
        }
        $productsText = implode("\n", array_slice($productsSummaries, 0, 60));

        // User info string
        $userSection = "GUEST USER (Not logged in). If they ask about their personal orders or cart, politely invite them to log in to their MarketLink account.";
        if (!empty($k['user'])) {
            $u = $k['user'];
            $ordersText = "No orders placed yet.";
            if (!empty($u['recent_orders'])) {
                $oLines = [];
                foreach ($u['recent_orders'] as $ord) {
                    $slot = ($ord['start_time'] && $ord['end_time']) ? " ({$ord['start_time']} - {$ord['end_time']})" : "";
                    $oLines[] = "Order #{$ord['order_number']}: Status: [{$ord['order_status']}], Pickup: {$ord['pickup_date']}{$slot} at {$ord['stall_name']} ({$ord['market_name']}), Total: Rs. {$ord['total_amount']}, Items: {$ord['ordered_items']}";
                }
                $ordersText = implode("\n", $oLines);
            }

            $cartText = "Cart is empty.";
            if (!empty($u['cart_items'])) {
                $cLines = [];
                foreach ($u['cart_items'] as $ci) {
                    $cLines[] = "{$ci['quantity']}x {$ci['product_name']} ({$ci['unit']}) from {$ci['stall_name']}";
                }
                $cartText = implode(", ", $cLines);
            }

            $userSection = "CURRENT LOGGED IN USER:
- Name: {$u['full_name']} (Username: {$u['username']})
- Role: {$u['role']}
- Email: {$u['email']}
- Phone: {$u['phone']}
- Default Address: {$u['default_addr']}
- Current Cart Items: {$cartText}
- Recent Orders:\n{$ordersText}";
        }

        $specificOrderSection = "";
        if (!empty($k['queried_order'])) {
            $qo = $k['queried_order'];
            $slot = ($qo['start_time'] && $qo['end_time']) ? " ({$qo['start_time']} - {$qo['end_time']})" : "";
            $specificOrderSection = "\nEXPLICITLY QUERIED ORDER IN DATABASE:
Order Number: {$qo['order_number']}
Order Status: {$qo['order_status']}
Total Amount: Rs. {$qo['total_amount']}
Pickup Date & Slot: {$qo['pickup_date']}{$slot}
Farmer Stall: {$qo['stall_name']} (Farmer Contact: {$qo['farmer_phone']})
Market Location: {$qo['market_name']}
Items: {$qo['items']}
Cancellation Reason (if any): " . ($qo['cancellation_reason'] ?? 'None');
        }

        return <<<EOT
You are **MarketLink FarmBot**, the official, intelligent, real-world AI agent for **MarketLink** (a modern farm-to-table platform connecting local farmers directly with consumers).

### CORE RULES & AGENT BEHAVIOR:
1. **LIVE WEBSITE DATA ACCESS**: You have real-time live access to MarketLink's database below. You know all active markets, approved farmers/stalls, categories, inventory products, pricing, operating days, and the user's live profile, cart, and orders.
2. **AUTHENTICITY**: Base all facts, prices, stalls, and timings strictly on this provided real-world database. Do not invent products or fake markets. If a product is not listed, politely let the customer know that it's not currently stocked by our verified farmers and suggest the closest available alternative.
3. **MULTILINGUAL**: Respond in the same language the user uses. If the user asks in English, reply in fluent English. If the user asks in Urdu or Roman Urdu (e.g. "tamatar ka rate kya hai?", "order kab aayega?", "kya organic vegetables hain?"), reply naturally in Roman Urdu or Urdu!
4. **PERSONAL ASSISTANCE**: If the logged-in customer asks about their orders, cart, or pickup date, answer accurately using their specific user details provided below.
5. **HOW MARKETLINK WORKS**:
   - Customers pre-order fresh harvest during the week to eliminate agricultural food waste.
   - Farmers harvest freshly and bring items to assigned weekend Farmers Market stalls (Saturdays & Sundays).
   - Customers choose a 30-min pickup slot during checkout and collect their pre-packed produce at the farmer's stall.
   - Payment is Cash on Pickup / stall collection.
   - Cancellation is allowed up to 2 hours before the farmer's daily order cutoff time.
6. **FORMATTING**: Use clean, modern Markdown with bold headings, clear bullet points, emojis (🌱, 📍, 🛒, 📦, 🍅, ⏰), and transparent pricing (e.g., **Rs. 140/kg**). Keep responses structured, concise, and helpful.

---
### REAL-TIME LIVE MARKETLINK DATABASE CONTEXT:
Current Platform Time: {$k['current_time']}

#### ACTIVE FARMERS MARKETS:
{$marketsJson}

#### APPROVED FARMERS & STALLS:
{$farmersJson}

#### PRODUCT CATEGORIES:
{$categoriesList}

#### LIVE INVENTORY, STOCKS & PRICING:
{$productsText}

#### {$userSection}
{$specificOrderSection}
---
Always be polite, enthusiastic about fresh agriculture, and guide the customer seamlessly!
EOT;
    }

    /**
     * Format conversation contents array for Gemini API.
     */
    private static function formatContents(array $history, string $userMessage): array {
        $contents = [];

        // Add history if valid
        if (!empty($history) && is_array($history)) {
            // Keep last 10 turns to stay responsive and fast
            $recentHistory = array_slice($history, -10);
            foreach ($recentHistory as $msg) {
                $role = ($msg['role'] ?? '') === 'user' ? 'user' : 'model';
                $text = trim($msg['text'] ?? $msg['content'] ?? '');
                if (!empty($text)) {
                    $contents[] = [
                        'role'  => $role,
                        'parts' => [['text' => $text]]
                    ];
                }
            }
        }

        // Append current user message
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        return $contents;
    }

    /**
     * Executes the HTTP POST call to Gemini API with automatic model fallback on busy spikes.
     */
    private static function callGeminiAPI(string $systemInstruction, array $contents): array {
        $apiKey = GEMINI_API_KEY;
        $candidateModels = [
            GEMINI_MODEL,              // gemini-3.8-flash
            'gemini-3.5-flash',
            'gemini-3.1-flash-lite',
            'gemini-3-flash-preview'
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 1200,
                'topP'            => 0.95
            ]
        ];
        $jsonPayload = json_encode($payload);

        $lastError = 'Unknown error';

        foreach ($candidateModels as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $rawResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $lastError = "cURL error on $model: " . $curlError;
                continue;
            }

            $decoded = json_decode($rawResponse, true);

            if ($httpCode === 200) {
                $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!empty($text)) {
                    return [
                        'success' => true,
                        'text'    => trim($text),
                        'model'   => $model
                    ];
                }
            }

            $lastError = $decoded['error']['message'] ?? "HTTP Status $httpCode on $model";
            // If it's 503 or 429, continue to next candidate model
        }

        return ['success' => false, 'error' => $lastError];
    }

    /**
     * Local intelligent fallback in case of Gemini quota limit or network timeout.
     */
    private static function buildLocalFallbackReply(PDO $pdo, string $userMessage, ?int $userId): string {
        $q = strtolower($userMessage);

        // 1. Order lookup
        if ($userId && (str_contains($q, 'order') || str_contains($q, 'status'))) {
            $oStmt = $pdo->prepare("SELECT o.order_number, o.order_status, o.total_amount, o.pickup_date, fp.stall_name, m.market_name 
                                    FROM orders o 
                                    JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id 
                                    JOIN markets m ON o.market_id = m.market_id 
                                    WHERE o.customer_id = :uid ORDER BY o.created_at DESC LIMIT 1");
            $oStmt->execute([':uid' => $userId]);
            $lastOrder = $oStmt->fetch();
            if ($lastOrder) {
                return "📦 **Your Latest Order:**\n• Order Number: **{$lastOrder['order_number']}**\n• Status: **" . strtoupper($lastOrder['order_status']) . "**\n• Pickup Date: {$lastOrder['pickup_date']} at {$lastOrder['stall_name']} ({$lastOrder['market_name']})\n• Total: Rs. {$lastOrder['total_amount']}";
            }
        }

        // 2. Product search
        $pStmt = $pdo->prepare("SELECT p.product_name, p.unit, wi.price, fp.stall_name, m.market_name 
                                FROM products p 
                                JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id 
                                LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id AND wi.is_available = 1
                                LEFT JOIN farmer_market_stalls fms ON wi.stall_id = fms.stall_id
                                LEFT JOIN markets m ON fms.market_id = m.market_id
                                WHERE p.product_name LIKE :term1 OR p.description LIKE :term2 LIMIT 3");
        $words = explode(' ', $q);
        $searchTerm = '%' . ($words[0] ?? '') . '%';
        $pStmt->execute([':term1' => $searchTerm, ':term2' => $searchTerm]);
        $prods = $pStmt->fetchAll();

        if (!empty($prods)) {
            $list = [];
            foreach ($prods as $p) {
                $pr = !empty($p['price']) ? "Rs. {$p['price']}/{$p['unit']}" : "In Season";
                $list[] = "• **{$p['product_name']}** ({$pr}) at *{$p['stall_name']}*";
            }
            return "🌱 **Live Produce Available:**\n\n" . implode("\n", $list) . "\n\nYou can pre-order these directly from the 'Browse Harvest' tab!";
        }

        // 3. Markets
        if (str_contains($q, 'market') || str_contains($q, 'time') || str_contains($q, 'where') || str_contains($q, 'saturday') || str_contains($q, 'sunday')) {
            $mStmt = $pdo->query("SELECT market_name, operating_days, operating_hours, address FROM markets WHERE status = 'active' LIMIT 3");
            $markets = $mStmt->fetchAll();
            $mList = [];
            foreach ($markets as $m) {
                $mList[] = "📍 **{$m['market_name']}**\n   Days: {$m['operating_days']} ({$m['operating_hours']})\n   Address: {$m['address']}";
            }
            return "🌱 **MarketLink Active Farmers Markets:**\n\n" . implode("\n\n", $mList);
        }

        return "I am your **MarketLink AI Assistant**! You can ask me anything about fresh produce, stall locations, weekend market hours, or your order tracking. How can I assist you today?";
    }
}
