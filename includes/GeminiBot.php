<?php
/**
 * MarketLink - Gemini & Enterprise Semantic Real-World AI Agent Service
 * Integrates Google Gemini Flash with live database context and an intelligent
 * local multilingual (Roman Urdu / English) NLP & RAG fallback engine.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/gemini.php';

class GeminiBot {

    /**
     * Answer a user's question using Gemini Flash infused with live database context,
     * or seamlessly fall back to our local RAG semantic engine.
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
                'reply'  => 'Please ask a question about our farm harvest, rates, farmers, market locations, or your orders.'
            ];
        }

        try {
            $pdo = getDBConnection();

            // 1. First attempt Google Gemini API with live RAG prompt if key configured
            $geminiKey = defined('GEMINI_API_KEY') ? trim(GEMINI_API_KEY) : '';
            if (!empty($geminiKey)) {
                $dbKnowledge = self::buildLiveDatabaseKnowledge($pdo, $userMessage, $userId, $userRole);
                $systemInstruction = self::buildSystemInstruction($dbKnowledge);
                $contents = self::formatContents($history, $userMessage);

                $response = self::callGeminiAPI($geminiKey, $systemInstruction, $contents);
                if (!empty($response['success']) && !empty($response['text'])) {
                    return [
                        'status' => 'success',
                        'reply'  => $response['text'],
                        'model'  => $response['model'] ?? 'gemini-flash'
                    ];
                }
            }

            // 2. Intelligent, Multilingual Local Semantic RAG Engine
            $localReply = self::buildLocalSemanticReply($pdo, $userMessage, $userId);
            return [
                'status' => 'success',
                'reply'  => $localReply,
                'model'  => 'marketlink-rag-engine'
            ];

        } catch (Exception $e) {
            error_log("GeminiBot Exception: " . $e->getMessage());
            return [
                'status' => 'error',
                'reply'  => "Assalam-o-Alaikum! I encountered a temporary connection glitch while checking live farm records. Please ask your question again in a moment!"
            ];
        }
    }

    /**
     * Executes the HTTP POST call to Gemini API with fast timeout and valid model fallback.
     */
    private static function callGeminiAPI(string $apiKey, string $systemInstruction, array $contents): array {
        $candidateModels = [
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-1.5-pro'
        ];

        if (defined('GEMINI_MODEL') && !empty(GEMINI_MODEL) && !in_array(GEMINI_MODEL, ['gemini-3.8-flash', 'gemini-3.7-flash', 'gemini-3.6-flash', 'gemini-3.5-flash'])) {
            array_unshift($candidateModels, GEMINI_MODEL);
            $candidateModels = array_values(array_unique($candidateModels));
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => 0.3,
                'maxOutputTokens' => 1200,
                'topP'            => 0.9
            ]
        ];
        $jsonPayload = json_encode($payload);

        foreach ($candidateModels as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $rawResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // If API key is rejected (401 or 403), stop attempting other models immediately
            if ($httpCode === 401 || $httpCode === 403) {
                break;
            }

            if ($curlError || $httpCode !== 200) {
                continue;
            }

            $decoded = json_decode($rawResponse, true);
            $parts = $decoded['candidates'][0]['content']['parts'] ?? [];
            $text = null;
            foreach ($parts as $part) {
                if (empty($part['thought']) && !empty($part['text'])) {
                    $text = $part['text'];
                    break;
                }
            }
            if ($text === null && !empty($parts[0]['text'])) {
                $text = $parts[0]['text'];
            }

            if (!empty($text)) {
                return [
                    'success' => true,
                    'text'    => trim($text),
                    'model'   => $model
                ];
            }
        }

        return ['success' => false, 'error' => 'Gemini API not available.'];
    }

    /**
     * Gathers live context from the database for Gemini Prompt injection.
     */
    private static function buildLiveDatabaseKnowledge(PDO $pdo, string $userMessage, ?int $userId, ?string $userRole): array {
        $k = [];
        $k['current_time'] = date('l, Y-m-d H:i');

        // Markets
        $stmtMarkets = $pdo->query("SELECT market_name, city, address, operating_days, operating_hours FROM markets WHERE status = 'active' ORDER BY city, market_name");
        $k['markets'] = $stmtMarkets->fetchAll(PDO::FETCH_ASSOC);

        // Farmers
        $stmtFarmers = $pdo->query("
            SELECT fp.stall_name, fp.contact_person, fp.business_phone,
                   GROUP_CONCAT(DISTINCT m.market_name SEPARATOR ', ') as markets
            FROM farmer_profiles fp
            LEFT JOIN farmer_market_stalls fms ON fp.farmer_id = fms.farmer_id AND fms.status = 'active'
            LEFT JOIN markets m ON fms.market_id = m.market_id
            WHERE fp.approval_status = 'approved'
            GROUP BY fp.farmer_id
            ORDER BY fp.stall_name
        ");
        $k['farmers'] = $stmtFarmers->fetchAll(PDO::FETCH_ASSOC);

        // Products with deduplicated pricing
        $stmtProducts = $pdo->query("
            SELECT p.product_name, pc.category_name, p.unit, fp.stall_name,
                   MIN(wi.price) as min_price, MAX(wi.price) as max_price
            FROM products p
            JOIN product_categories pc ON p.category_id = pc.category_id
            JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
            LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id AND wi.is_available = 1
            WHERE fp.approval_status = 'approved'
            GROUP BY p.product_id
            ORDER BY pc.category_name, p.product_name
        ");
        $k['products'] = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

        // User info
        $k['user'] = null;
        if ($userId) {
            $uStmt = $pdo->prepare("SELECT user_id, username, email, role FROM users WHERE user_id = :uid");
            $uStmt->execute([':uid' => $userId]);
            $userData = $uStmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                // Orders
                $oStmt = $pdo->prepare("
                    SELECT o.order_number, o.order_status, o.total_amount, o.pickup_date,
                           fp.stall_name, m.market_name, ps.start_time, ps.end_time,
                           GROUP_CONCAT(CONCAT(p.product_name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items
                    FROM orders o
                    JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                    JOIN markets m ON o.market_id = m.market_id
                    LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    WHERE o.customer_id = :uid
                    GROUP BY o.order_id
                    ORDER BY o.created_at DESC LIMIT 3
                ");
                $oStmt->execute([':uid' => $userId]);
                $userData['orders'] = $oStmt->fetchAll(PDO::FETCH_ASSOC);
                $k['user'] = $userData;
            }
        }

        return $k;
    }

    /**
     * Constructs Gemini System Prompt.
     */
    private static function buildSystemInstruction(array $k): string {
        $marketsJson = json_encode($k['markets'] ?? [], JSON_UNESCAPED_UNICODE);
        $farmersJson = json_encode($k['farmers'] ?? [], JSON_UNESCAPED_UNICODE);
        
        $prodLines = [];
        foreach ($k['products'] ?? [] as $p) {
            $rate = $p['min_price'] ? ("Rs. " . (float)$p['min_price'] . '/' . $p['unit']) : 'Market Rate';
            $prodLines[] = "• {$p['product_name']} [{$p['category_name']}]: {$rate} at {$p['stall_name']}";
        }
        $prodsText = implode("\n", array_slice($prodLines, 0, 50));

        $userSection = "GUEST USER (Not logged in). If user asks for their personal orders, politely tell them to log in.";
        if (!empty($k['user'])) {
            $u = $k['user'];
            $orders = !empty($u['orders']) ? json_encode($u['orders'], JSON_UNESCAPED_UNICODE) : "No orders yet.";
            $userSection = "LOGGED IN USER: Username: {$u['username']}, Role: {$u['role']}\nRecent Orders: {$orders}";
        }

        return <<<EOT
You are **MarketLink Farm AI**, the real-world agricultural assistant for **MarketLink** (a farm-to-table platform in Pakistan connecting verified farmers with customers for weekly market pre-orders).

RULES:
1. Ground answers strictly in the provided live database facts.
2. If asked in Roman Urdu or Urdu (e.g. "tamatar ka rate", "order cancel kaise hoga"), respond fluently and naturally in Roman Urdu! If asked in English, reply in English.
3. HOW MARKETLINK WORKS:
   - Customers pre-order fresh produce weekly to eliminate food waste.
   - Farmers harvest within 24 hours and bring pre-packed orders to assigned weekend market stalls (Saturdays & Sundays).
   - Customers choose a 30-min pickup slot at checkout and pay CASH ON PICKUP at the stall. No online advance payment.
   - Cancellation is permitted up to the farmer's daily order cut-off time (usually 6:00 PM before market day).
4. Use clean Markdown with bullet points, bold product rates (e.g., **Rs. 95/kg**), and emojis (🌱, 📍, 🛒, 📦, 🍅, ⏰).

LIVE DATABASE DATA:
Markets: {$marketsJson}
Farmers: {$farmersJson}
Live Harvest & Rates:
{$prodsText}
{$userSection}
EOT;
    }

    /**
     * Format multi-turn conversation.
     */
    private static function formatContents(array $history, string $userMessage): array {
        $contents = [];
        if (!empty($history) && is_array($history)) {
            foreach (array_slice($history, -8) as $msg) {
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
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $userMessage]]
        ];
        return $contents;
    }

    /**
     * =========================================================================
     * REAL-WORLD LOCAL SEMANTIC RAG & NLP ENGINE
     * Handles any question about MarketLink in English or Roman Urdu accurately.
     * =========================================================================
     */
    public static function buildLocalSemanticReply(PDO $pdo, string $userMessage, ?int $userId): string {
        $raw = trim($userMessage);
        $q = strtolower($raw);

        // Urdu / Roman Urdu Dictionary to English Canonical Concepts
        $synonyms = [
            'tamatar'   => 'tomato',
            'tamatr'    => 'tomato',
            'aalu'      => 'potato',
            'aloo'      => 'potato',
            'pyaaz'     => 'onion',
            'pyaz'      => 'onion',
            'lehsan'    => 'garlic',
            'lasan'     => 'garlic',
            'adrak'     => 'ginger',
            'kheera'    => 'cucumber',
            'khira'     => 'cucumber',
            'palak'     => 'spinach',
            'bhindi'    => 'okra',
            'gajar'     => 'carrot',
            'gaajar'    => 'carrot',
            'mooli'     => 'radish',
            'karela'    => 'karela',
            'arvi'      => 'arvi',
            'seb'       => 'apple',
            'saib'      => 'apple',
            'kela'      => 'banana',
            'kelay'     => 'banana',
            'aam'       => 'mango',
            'angoor'    => 'grape',
            'kinnow'    => 'kinnow',
            'anar'      => 'pomegranate',
            'anaar'     => 'pomegranate',
            'doodh'     => 'milk',
            'dudh'      => 'milk',
            'dhud'      => 'milk',
            'anda'      => 'egg',
            'anday'     => 'egg',
            'ande'      => 'egg',
            'murgi'     => 'chicken',
            'murghi'    => 'chicken',
            'desi murgi'=> 'chicken',
            'makhan'    => 'butter',
            'makhhan'   => 'butter',
            'paneer'    => 'paneer',
            'dahi'      => 'dahi',
            'yogurt'    => 'dahi',
            'shehad'    => 'honey',
            'shehd'     => 'honey',
            'chawal'    => 'rice',
            'chaawal'   => 'rice',
            'atta'      => 'atta',
            'gandum'    => 'wheat',
            'flour'     => 'atta',
            'kisan'     => 'farmer',
            'kissaan'   => 'farmer',
            'kashtkar'  => 'farmer',
            'zamindar'  => 'farmer',
            'mandi'     => 'market',
            'bazaar'    => 'market',
            'bazar'     => 'market',
            'rate'      => 'price',
            'rates'     => 'price',
            'keemat'    => 'price',
            'bhaav'     => 'price',
            'bhao'      => 'price',
            'paisa'     => 'price',
            'paise'     => 'price',
            'pesay'     => 'price',
            'rupay'     => 'price',
            'kharidna'  => 'order',
            'mangwana'  => 'order',
            'khatam'    => 'cancel',
            'mansookh'  => 'cancel',
            'uthaana'   => 'pickup',
            'wasool'    => 'pickup',
            'taza'      => 'fresh',
            'khalis'    => 'organic',
            'desi'      => 'organic'
        ];

        // Normalized mapped query
        $mappedQuery = $q;
        foreach ($synonyms as $syn => $eng) {
            $mappedQuery = preg_replace('/\b' . preg_quote($syn, '/') . '\b/i', $eng, $mappedQuery);
        }

        // ---------------------------------------------------------
        // 1. ORDER LOOKUP / STATUS (User's personal orders or order #)
        // ---------------------------------------------------------
        if (preg_match('/(ML-\d{4}-\d+|\b\d{1,6}\b)/i', $raw, $orderMatch) && (str_contains($q, 'order') || str_contains($q, 'status') || str_contains($q, 'track') || str_contains($q, 'mera') || str_contains($q, 'my'))) {
            $searchTerm = $orderMatch[1];
            $soStmt = $pdo->prepare("
                SELECT o.order_number, o.order_status, o.total_amount, o.pickup_date, o.cancellation_reason,
                       fp.stall_name, fp.business_phone as farmer_phone, m.market_name, ps.start_time, ps.end_time,
                       GROUP_CONCAT(CONCAT(p.product_name, ' (x', oi.quantity, ' ', p.unit, ')') SEPARATOR ', ') as items
                FROM orders o
                JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                JOIN markets m ON o.market_id = m.market_id
                LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.order_number LIKE :onum OR o.order_id = :oid
                GROUP BY o.order_id LIMIT 1
            ");
            $soStmt->execute([
                ':onum' => '%' . $searchTerm . '%',
                ':oid'  => is_numeric($searchTerm) ? (int)$searchTerm : 0
            ]);
            $ord = $soStmt->fetch(PDO::FETCH_ASSOC);

            if ($ord) {
                $statusBadge = strtoupper($ord['order_status']);
                $slotStr = ($ord['start_time'] && $ord['end_time']) ? " ({$ord['start_time']} - {$ord['end_time']})" : "";
                return "📦 **Order Details for #{$ord['order_number']}:**\n\n"
                    . "• **Status:** `[{$statusBadge}]`\n"
                    . "• **Pickup Date & Slot:** {$ord['pickup_date']}{$slotStr}\n"
                    . "• **Farmer Stall:** *{$ord['stall_name']}* (Phone: {$ord['farmer_phone']})\n"
                    . "• **Market Location:** {$ord['market_name']}\n"
                    . "• **Reserved Items:** {$ord['items']}\n"
                    . "• **Total Amount:** **Rs. " . number_format((float)$ord['total_amount'], 2) . "** *(Pay cash on pickup at stall)*";
            }
        }

        // If user asks "mera order" / "my order" without specific order number
        if ((str_contains($q, 'mera order') || str_contains($q, 'my order') || str_contains($q, 'order status') || str_contains($q, 'track my order')) && $userId) {
            $myOrdStmt = $pdo->prepare("
                SELECT o.order_number, o.order_status, o.total_amount, o.pickup_date,
                       fp.stall_name, m.market_name, ps.start_time, ps.end_time,
                       GROUP_CONCAT(CONCAT(p.product_name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items
                FROM orders o
                JOIN farmer_profiles fp ON o.farmer_id = fp.farmer_id
                JOIN markets m ON o.market_id = m.market_id
                LEFT JOIN pickup_slots ps ON o.pickup_slot_id = ps.pickup_slot_id
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.customer_id = :uid
                GROUP BY o.order_id
                ORDER BY o.created_at DESC LIMIT 3
            ");
            $myOrdStmt->execute([':uid' => $userId]);
            $myOrders = $myOrdStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($myOrders)) {
                $out = "📦 **Aapke Recent Orders:**\n\n";
                foreach ($myOrders as $o) {
                    $slot = ($o['start_time'] && $o['end_time']) ? " ({$o['start_time']} - {$o['end_time']})" : "";
                    $out .= "• **Order #{$o['order_number']}** — `[" . strtoupper($o['order_status']) . "]`\n"
                         . "  Pickup: **{$o['pickup_date']}{$slot}** at *{$o['stall_name']}* ({$o['market_name']})\n"
                         . "  Items: {$o['items']}\n"
                         . "  Total: **Rs. " . number_format((float)$o['total_amount'], 2) . "**\n\n";
                }
                $out .= "Aap in orders ko customer dashboard me ja kar track aur manage kar sakte hain!";
                return $out;
            } else {
                return "📦 **Koi Order Nahi Mila!** Aapne abhi tak MarketLink par koi order place nahi kiya hai. Aap **Browse Harvest** me ja kar fresh vegetables, fruits aur dairy items pre-order kar sakte hain!";
            }
        }

        // ---------------------------------------------------------
        // 2. REGISTRATION & HOW TO SELL / BECOME FARMER (Check before farmer search)
        // ---------------------------------------------------------
        if (str_contains($q, 'register') || str_contains($q, 'signup') || str_contains($q, 'sell') || str_contains($q, 'vendor') || str_contains($q, 'stall booking') || str_contains($q, 'account banana') || str_contains($q, 'join as farmer')) {
            return "🌾 **MarketLink par Farmer / Seller Kaise Banein?**\n\n"
                 . "1. **Registration:** Top navigation me **'Register'** par click karein aur **Farmer** select karein. Apna Farm name, contact number, CNIC aur farm address enter karein.\n"
                 . "2. **Admin Verification:** MarketLink administration farm ki authenticity verify kar ke profile approve karti hai.\n"
                 . "3. **Stall Allotment & Inventory:** Approval ke baad farmer ko weekend markets me stall allot hota hai jahan wo weekly harvest aur rates post kar ke direct pre-orders receive kar sakta hai!\n\n"
                 . "*(Customers instant signup kar ke bina kisi approval ke foran pre-orders place kar sakte hain!)*";
        }

        // ---------------------------------------------------------
        // 3. SPECIFIC PRODUCT & LIVE RATES SEARCH (e.g. Tamatar, Aloo, Eggs, Chicken, Honey, Vegetables)
        // ---------------------------------------------------------
        $productKeywords = [
            'tomato'    => ['tomato', 'tamatar'],
            'potato'    => ['potato', 'aloo', 'aalu'],
            'onion'     => ['onion', 'pyaz', 'pyaaz'],
            'chicken'   => ['chicken', 'murgi', 'murghi', 'poultry'],
            'egg'       => ['egg', 'eggs', 'anda', 'anday'],
            'milk'      => ['milk', 'doodh', 'dudh'],
            'butter'    => ['butter', 'makhan'],
            'paneer'    => ['paneer', 'cheese'],
            'dahi'      => ['dahi', 'yogurt'],
            'honey'     => ['honey', 'shehad', 'shehd'],
            'apple'     => ['apple', 'seb', 'saib'],
            'banana'    => ['banana', 'kela', 'kelay'],
            'orange'    => ['orange', 'kinnow', 'malta'],
            'spinach'   => ['spinach', 'palak'],
            'carrot'    => ['carrot', 'gajar'],
            'rice'      => ['rice', 'chawal', 'basmati'],
            'atta'      => ['atta', 'flour', 'wheat', 'gandum'],
            'radish'    => ['radish', 'mooli'],
            'karela'    => ['karela', 'bitter gourd'],
            'mushroom'  => ['mushroom', 'mushrooms'],
            'cucumber'  => ['cucumber', 'kheera']
        ];

        $matchedConcept = null;
        foreach ($productKeywords as $concept => $words) {
            foreach ($words as $w) {
                if (preg_match('/\b' . preg_quote($w, '/') . '\b/i', $q) || preg_match('/\b' . preg_quote($w, '/') . '\b/i', $mappedQuery)) {
                    $matchedConcept = $concept;
                    break 2;
                }
            }
        }

        // Category-level requests (vegetables, fruits, dairy)
        $categoryMatch = null;
        if (str_contains($q, 'vegetable') || str_contains($q, 'vegetables') || str_contains($q, 'sabzi') || str_contains($q, 'sabziyan') || str_contains($q, 'organic vegetable')) {
            $categoryMatch = 'Fresh Vegetables';
        } elseif (str_contains($q, 'fruit') || str_contains($q, 'fruits') || str_contains($q, 'phal')) {
            $categoryMatch = 'Orchard Fruits';
        } elseif (str_contains($q, 'dairy') || str_contains($q, 'dairy products')) {
            $categoryMatch = 'Dairy & Farm Eggs';
        }

        $isGeneralRateQuery = str_contains($q, 'rate list') || str_contains($q, 'price list') || str_contains($q, 'rates') || str_contains($q, 'kya kya hai') || str_contains($q, 'harvest in stock') || str_contains($q, 'rate kya hai');

        if ($matchedConcept || $categoryMatch || $isGeneralRateQuery) {
            $searchSql = "
                SELECT p.product_id, p.product_name, pc.category_name, p.unit, p.description,
                       fp.stall_name, fp.contact_person, fp.business_phone,
                       COALESCE(wi.price, 0) as price, COALESCE(wi.stock_quantity, 0) as stock,
                       wi.day_of_week, m.market_name, m.city
                FROM products p
                JOIN product_categories pc ON p.category_id = pc.category_id
                JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                LEFT JOIN weekly_inventory wi ON p.product_id = wi.product_id AND wi.is_available = 1
                LEFT JOIN farmer_market_stalls fms ON wi.stall_id = fms.stall_id
                LEFT JOIN markets m ON fms.market_id = m.market_id
                WHERE fp.approval_status = 'approved'
            ";

            if ($matchedConcept) {
                $searchSql .= " AND (p.product_name LIKE :term1 OR p.description LIKE :term2 OR pc.category_name LIKE :term3) ";
            } elseif ($categoryMatch) {
                $searchSql .= " AND pc.category_name LIKE :term1 ";
            }
            $searchSql .= " GROUP BY p.product_id ORDER BY wi.price ASC LIMIT 6 ";

            $pStmt = $pdo->prepare($searchSql);
            if ($matchedConcept) {
                $term = '%' . $matchedConcept . '%';
                $pStmt->execute([':term1' => $term, ':term2' => $term, ':term3' => $term]);
            } elseif ($categoryMatch) {
                $pStmt->execute([':term1' => '%' . $categoryMatch . '%']);
            } else {
                $pStmt->execute();
            }
            $prods = $pStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($prods)) {
                $title = $matchedConcept ? "🌱 **Live Rates & Stock for " . ucfirst($matchedConcept) . ":**" : ($categoryMatch ? "🌱 **Live Rates for {$categoryMatch}:**" : "🌱 **MarketLink Fresh Harvest & Live Rates:**");
                $out = $title . "\n\n";
                foreach ($prods as $p) {
                    $priceStr = ($p['price'] > 0) ? "**Rs. " . number_format((float)$p['price'], 2) . "/{$p['unit']}**" : "*Market Standard Rate*";
                    $stockStr = ($p['stock'] > 0) ? "(Available Stock: " . (float)$p['stock'] . " {$p['unit']})" : "";
                    $mktStr = !empty($p['market_name']) ? " at {$p['market_name']} (" . ($p['city'] ?? '') . ")" : "";

                    $out .= "• **{$p['product_name']}**\n"
                         . "  💰 Rate: {$priceStr} {$stockStr}\n"
                         . "  🌾 Farmer: *{$p['stall_name']}*{$mktStr}\n\n";
                }
                $out .= "🛒 **How to Pre-Order:** Aap in items ko 'Browse Harvest' tab se pre-order kar sakte hain aur weekend par stall se fresh pick kar ke cash ada kar sakte hain!";
                return $out;
            }
        }

        // ---------------------------------------------------------
        // 4. MARKETS, LOCATIONS & TIMINGS
        // ---------------------------------------------------------
        if (str_contains($q, 'market') || str_contains($q, 'mandi') || str_contains($q, 'bazaar') || str_contains($q, 'bazar') || str_contains($q, 'location') || str_contains($q, 'timing') || str_contains($q, 'timings') || str_contains($q, 'kahan') || str_contains($q, 'address') || str_contains($q, 'lahore') || str_contains($q, 'karachi') || str_contains($q, 'islamabad')) {
            
            $mktSql = "SELECT market_name, city, address, operating_days, operating_hours FROM markets WHERE status = 'active'";
            $params = [];
            
            if (str_contains($q, 'lahore')) {
                $mktSql .= " AND city LIKE '%Lahore%'";
            } elseif (str_contains($q, 'karachi')) {
                $mktSql .= " AND city LIKE '%Karachi%'";
            } elseif (str_contains($q, 'islamabad') || str_contains($q, 'rawalpindi')) {
                $mktSql .= " AND (city LIKE '%Islamabad%' OR city LIKE '%Islamabd%')";
            }
            $mktSql .= " ORDER BY city, market_name LIMIT 6";

            $mStmt = $pdo->prepare($mktSql);
            $mStmt->execute($params);
            $markets = $mStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($markets)) {
                $out = "📍 **MarketLink Active Farmers Markets & Timings:**\n\n";
                foreach ($markets as $m) {
                    $out .= "• **{$m['market_name']}** ({$m['city']})\n"
                         . "  🗓 Days: **{$m['operating_days']}**\n"
                         . "  ⏰ Timings: **{$m['operating_hours']}**\n"
                         . "  📍 Address: {$m['address']}\n\n";
                }
                $out .= "Aap interactive GPS map aur pin drop directions **Markets & Map** page par live dekh sakte hain!";
                return $out;
            }
        }

        // ---------------------------------------------------------
        // 5. FARMERS & STALLS LOOKUP
        // ---------------------------------------------------------
        if (str_contains($q, 'farmer') || str_contains($q, 'farmers') || str_contains($q, 'kisan') || str_contains($q, 'kissaan') || str_contains($q, 'stall') || str_contains($q, 'stalls') || str_contains($q, 'seller') || str_contains($q, 'rating') || str_contains($q, 'top rated')) {
            $fStmt = $pdo->query("
                SELECT fp.stall_name, fp.contact_person, fp.business_phone, fp.address,
                       COALESCE(ROUND(AVG(fr.rating), 1), 5.0) as avg_rating,
                       COUNT(fr.review_id) as total_reviews,
                       GROUP_CONCAT(DISTINCT m.market_name SEPARATOR ', ') as markets
                FROM farmer_profiles fp
                LEFT JOIN farmer_reviews fr ON fp.farmer_id = fr.farmer_id
                LEFT JOIN farmer_market_stalls fms ON fp.farmer_id = fms.farmer_id AND fms.status = 'active'
                LEFT JOIN markets m ON fms.market_id = m.market_id
                WHERE fp.approval_status = 'approved'
                GROUP BY fp.farmer_id
                ORDER BY avg_rating DESC, total_reviews DESC
                LIMIT 5
            ");
            $farmers = $fStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($farmers)) {
                $out = "🌾 **MarketLink Verified Farmers & Stalls:**\n\n";
                foreach ($farmers as $f) {
                    $rating = $f['avg_rating'] ? "⭐ {$f['avg_rating']}/5" : "⭐ 5.0/5";
                    $mkts = !empty($f['markets']) ? $f['markets'] : "Assigned Weekend Markets";
                    $out .= "• **{$f['stall_name']}** ({$rating})\n"
                         . "  👤 Grower: {$f['contact_person']} | 📞 Phone: {$f['business_phone']}\n"
                         . "  📍 Markets: {$mkts}\n\n";
                }
                $out .= "Tamam farmers verified hain aur weekend par apni fresh harvest direct consumer ko supply karte hain!";
                return $out;
            }
        }

        // ---------------------------------------------------------
        // 6. CANCELLATION & CUT-OFF RULES
        // ---------------------------------------------------------
        if (str_contains($q, 'cancel') || str_contains($q, 'cutoff') || str_contains($q, 'cut off') || str_contains($q, 'khatam') || str_contains($q, 'mansookh') || str_contains($q, 'modify')) {
            return "❌ **Order Cancellation Policy & Cut-Off Rules:**\n\n"
                 . "1. **Cut-off Time se Pehle:** Aap apna order Customer Dashboard me **'My Orders'** section me ja kar kisi bhi waqt cancel kar sakte hain jab tak farmer ka daily cut-off time cross na hua ho (aam tor par market day se pehle shaam 6:00 PM tak).\n"
                 . "2. **Cut-off Time ke Baad:** Cut-off time ke baad cancellation band ho jati hai, kyunke farmer aapke reserved order ke mutabiq khet se taaza sabzi aur phal subah harvest karta hai taake food waste na ho.\n"
                 . "3. **Payment Security:** Chunke MarketLink par advance online payment nahi hoti (sirf Cash on Pickup hota hai), isliye refund ka koi issue nahi hota!";
        }

        // ---------------------------------------------------------
        // 7. PAYMENT RULES (Cash on Pickup)
        // ---------------------------------------------------------
        if (str_contains($q, 'pay') || str_contains($q, 'payment') || str_contains($q, 'online') || str_contains($q, 'card') || str_contains($q, 'cash') || str_contains($q, 'cod') || str_contains($q, 'advance') || str_contains($q, 'paise')) {
            return "💵 **Payment Policy — 100% Cash on Pickup (Pay-at-Stall):**\n\n"
                 . "• **No Advance Online Payment:** MarketLink par order place karte waqt credit card ya online bank transfer ki zaroorat nahi hoti.\n"
                 . "• **Inspect First, Pay Later:** Weekend market me jab aap farmer ke stall par apna pre-packaged order pick karne jayenge, pehle apni harvest ki taazgi aur quality inspect karein, phir cash ada karein.\n"
                 . "• **Zero Hidden Fees:** Customers aur farmers dono ke liye 0% online gateway transaction fee hai!";
        }

        // ---------------------------------------------------------
        // 8. PICKUP SLOTS & HOW THEY WORK
        // ---------------------------------------------------------
        if (str_contains($q, 'pickup') || str_contains($q, 'slot') || str_contains($q, 'slots') || str_contains($q, 'uthaana') || str_contains($q, 'timing')) {
            return "⏰ **Pickup Slots Kaise Kaam Karte Hain?**\n\n"
                 . "• **30-Minute Time Window:** Checkout ke waqt aap apni pasand ka 30-minute pickup slot select karte hain (maslan 08:00 AM - 08:30 AM, 09:00 AM - 09:30 AM).\n"
                 . "• **No Rush or Queues:** Is slot ka faida yeh hai ke stall par bheer nahi lagti aur farmer aapka saman pehle se weigh aur pack kar ke tayyar rakhta hai.\n"
                 . "• **Collection:** Apne slot ke dauran farmer ke stall par pohanch kar apna Order Number batayein aur produce collect karein!";
        }

        // ---------------------------------------------------------
        // 9. ORGANIC ASSURANCE & FRESHNESS
        // ---------------------------------------------------------
        if (str_contains($q, 'organic') || str_contains($q, 'fresh') || str_contains($q, 'quality') || str_contains($q, 'khalis') || str_contains($q, 'chemical') || str_contains($q, 'taza')) {
            return "🌱 **Freshness & Quality Guarantee:**\n\n"
                 . "• **Harvest within 24 Hours:** Farmers market day se sirf 12-24 ghante pehle khet se harvest karte hain.\n"
                 . "• **Zero Cold Storage:** Supermarket ki tarah hafton purani cold-storage sabzi nahi hoti, nutrient-rich aur 100% natural hoti hai.\n"
                 . "• **Verified Local Growers:** Har farmer verified hai jo pesticide-free aur sustainable farming practices follow karta hai!";
        }

        // ---------------------------------------------------------
        // 10. CONTACT & HELPLINE SUPPORT
        // ---------------------------------------------------------
        if (str_contains($q, 'contact') || str_contains($q, 'support') || str_contains($q, 'helpline') || str_contains($q, 'phone') || str_contains($q, 'rabta') || str_contains($q, 'help')) {
            return "📞 **MarketLink Support & Contact:**\n\n"
                 . "• **Customer Support Email:** support@marketlink.pk\n"
                 . "• **Contact Form:** Website ke footer me **Contact Us** page se direct message send kar sakte hain.\n"
                 . "• **Farmer Direct Contact:** Agar aapka order active hai, to order receipt par aapke farmer ka direct phone number mojood hota hai!";
        }

        // ---------------------------------------------------------
        // 11. SEMANTIC MATCH AGAINST ai_chatbot_faqs DATABASE TABLE
        // ---------------------------------------------------------
        $faqStmt = $pdo->query("SELECT faq_id, question, answer, keywords FROM ai_chatbot_faqs WHERE is_active = 1");
        $faqs = $faqStmt->fetchAll(PDO::FETCH_ASSOC);

        $bestScore = 0;
        $bestFaq = null;

        $words = preg_split('/[\s,\?!]+/', $mappedQuery, -1, PREG_SPLIT_NO_EMPTY);
        $meaningfulWords = array_filter($words, function($w) {
            return strlen($w) >= 3 && !in_array($w, ['the', 'and', 'for', 'with', 'are', 'hai', 'hain', 'kya', 'hota', 'hoti', 'kaise', 'hoga', 'mein', 'kahan', 'mujhe', 'batao']);
        });

        foreach ($faqs as $faq) {
            $score = 0;
            $faqText = strtolower($faq['question'] . ' ' . $faq['keywords'] . ' ' . $faq['answer']);
            foreach ($meaningfulWords as $mw) {
                if (str_contains($faqText, $mw)) {
                    $score += 3;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestFaq = $faq;
            }
        }

        if ($bestFaq && $bestScore >= 3) {
            return "🌱 **" . $bestFaq['question'] . "**\n\n" . $bestFaq['answer'];
        }

        // ---------------------------------------------------------
        // 12. GREETING / GENERAL WELCOME
        // ---------------------------------------------------------
        if (str_contains($q, 'salam') || str_contains($q, 'hello') || str_contains($q, 'hi') || str_contains($q, 'kaise') || str_contains($q, 'hey') || str_contains($q, 'assalam')) {
            return "Assalam-o-Alaikum! 🌱 Main **MarketLink Farm AI Assistant** hoon. Main aapki live website data se madad kar sakta hoon:\n\n"
                 . "• **Live Rates:** Tamatar, Aloo, Desi Murgi, Eggs, Honey, Doodh wagera ke rates\n"
                 . "• **Markets & Timings:** Weekend markets ke din aur auqaat\n"
                 . "• **Order Tracking:** Aapke orders aur pickup slots ka status\n"
                 . "• **Farmer Info:** Verified farmers aur unke stalls ki details\n\n"
                 . "Aap Urdu ya English me koi bhi sawal pooch sakte hain!";
        }

        // ---------------------------------------------------------
        // 13. SMART DEFAULT (Shows quick active options from live DB)
        // ---------------------------------------------------------
        $sampleMkts = $pdo->query("SELECT market_name FROM markets WHERE status = 'active' LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
        $sampleMktsStr = implode(', ', $sampleMkts);

        return "Assalam-o-Alaikum! Main **MarketLink Farm AI** hoon. Aap hamari website ke baare me kuch bhi pooch sakte hain:\n\n"
             . "• **Sabziyon aur Phal ke Rates** (e.g. *'Tamatar ka rate kya hai?'* ya *'Desi murgi kitne ki hai?'*)\n"
             . "• **Weekend Markets ki Locations** (e.g. *'Lahore me market kahan hai?'*)\n"
             . "• **Order aur Pickup** (e.g. *'Pickup slots kaise kaam krte hain?'* ya *'Order cancel kaise hoga?'*)\n"
             . "• **Farmers & Stalls** (e.g. *'Top rated farmers kon hain?'*)\n\n"
             . "Humare active weekend markets: *{$sampleMktsStr}*. Aap kis cheez ke mutalliq maloomat chahte hain?";
    }
}
