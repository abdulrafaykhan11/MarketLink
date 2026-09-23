<?php
/**
 * MarketLink - Database Seeder Script
 * Seeds realistic markets, categories, farmer stalls, products, weekly inventory,
 * pickup slots, AI chatbot FAQs, and sample customer activity for comprehensive testing.
 */

require_once __DIR__ . '/db.php';

try {
    $pdo = getDBConnection();
    echo "Starting MarketLink Database Seeding...\n";

    // 1. Approve existing farmers & update coordinates/details
    $pdo->exec("UPDATE farmer_profiles SET 
        approval_status = 'approved',
        latitude = 24.8607,
        longitude = 67.0011,
        description = 'Dedicated organic grower cultivating non-GMO heirloom vegetables and fresh orchard fruits with zero synthetic pesticides.'
        WHERE farmer_id = 2");

    $pdo->exec("UPDATE farmer_profiles SET 
        approval_status = 'approved',
        latitude = 24.9180,
        longitude = 67.0971,
        description = 'Family-run sustainable farm producing crisp greens, leafy herbs, organic free-range eggs, and artisanal farm honey.'
        WHERE farmer_id = 6");

    // 2. Seed Product Categories
    $categories = [
        ['category_name' => 'Fresh Vegetables', 'description' => 'Crisp, organically grown root and leafy vegetables picked daily.', 'category_image' => 'assets/images/cat-vegetables.svg'],
        ['category_name' => 'Orchard Fruits', 'description' => 'Sweet, sun-ripened tree fruits and seasonal berries.', 'category_image' => 'assets/images/cat-fruits.svg'],
        ['category_name' => 'Dairy & Farm Eggs', 'description' => 'Pasture-raised organic eggs, fresh cow milk, and cultured butter.', 'category_image' => 'assets/images/cat-dairy.svg'],
        ['category_name' => 'Farm Bakery', 'description' => 'Stone-ground sourdough bread, artisanal pies, and fresh wholewheat rolls.', 'category_image' => 'assets/images/cat-bakery.svg'],
        ['category_name' => 'Honey & Preserves', 'description' => 'Raw wildflower honey, pure bee pollen, and small-batch fruit jams.', 'category_image' => 'assets/images/cat-honey.svg'],
        ['category_name' => 'Aromatic Herbs', 'description' => 'Fresh-cut culinary herbs and fragrant seasoning greens.', 'category_image' => 'assets/images/cat-herbs.svg']
    ];

    $catStmt = $pdo->prepare("INSERT INTO product_categories (category_name, description, category_image, is_active) 
                              VALUES (:name, :desc, :img, 1) 
                              ON DUPLICATE KEY UPDATE description = VALUES(description), category_image = VALUES(category_image)");
    foreach ($categories as $cat) {
        $catStmt->execute([':name' => $cat['category_name'], ':desc' => $cat['description'], ':img' => $cat['category_image']]);
    }
    echo "✔ Product Categories seeded.\n";

    // Retrieve category IDs
    $catMap = $pdo->query("SELECT category_name, category_id FROM product_categories")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Seed Markets
    $markets = [
        [
            'market_name' => 'Downtown Green Plaza Farmers Market',
            'address' => 'Plot 4-A, Civic Center Promenade, Central District',
            'city' => 'Metropolis',
            'state' => 'Sindh',
            'postal_code' => '74200',
            'latitude' => 24.860966,
            'longitude' => 67.010427,
            'operating_days' => 'Saturday, Sunday',
            'operating_hours' => '07:00 AM - 01:00 PM',
            'status' => 'active'
        ],
        [
            'market_name' => 'Riverside Organic Community Market',
            'address' => 'Pier 12, Waterfront Boardwalk, River Sector',
            'city' => 'Metropolis',
            'state' => 'Sindh',
            'postal_code' => '74800',
            'latitude' => 24.832450,
            'longitude' => 67.034500,
            'operating_days' => 'Friday, Saturday',
            'operating_hours' => '08:00 AM - 02:00 PM',
            'status' => 'active'
        ],
        [
            'market_name' => 'Hillside Heritage Open Market',
            'address' => 'Gate 3, Botanical Gardens Esplanade, North Heights',
            'city' => 'Metropolis',
            'state' => 'Sindh',
            'postal_code' => '75300',
            'latitude' => 24.935100,
            'longitude' => 67.098200,
            'operating_days' => 'Wednesday, Sunday',
            'operating_hours' => '06:30 AM - 12:30 PM',
            'status' => 'active'
        ],
        [
            'market_name' => 'Suburban Eco Farm Fair',
            'address' => 'Eco Square, Main Boulevard, Green Valley',
            'city' => 'Metropolis',
            'state' => 'Sindh',
            'postal_code' => '75050',
            'latitude' => 24.905600,
            'longitude' => 67.123400,
            'operating_days' => 'Tuesday, Thursday, Saturday',
            'operating_hours' => '07:30 AM - 01:30 PM',
            'status' => 'active'
        ]
    ];

    $marketStmt = $pdo->prepare("INSERT INTO markets (market_name, address, city, state, postal_code, latitude, longitude, operating_days, operating_hours, status, created_at)
                                 VALUES (:name, :address, :city, :state, :postal, :lat, :lng, :days, :hours, :status, NOW())");
    
    $existingMarkets = $pdo->query("SELECT COUNT(*) FROM markets")->fetchColumn();
    if ($existingMarkets == 0) {
        foreach ($markets as $m) {
            $marketStmt->execute([
                ':name' => $m['market_name'],
                ':address' => $m['address'],
                ':city' => $m['city'],
                ':state' => $m['state'],
                ':postal' => $m['postal_code'],
                ':lat' => $m['latitude'],
                ':lng' => $m['longitude'],
                ':days' => $m['operating_days'],
                ':hours' => $m['operating_hours'],
                ':status' => $m['status']
            ]);
        }
    }
    echo "✔ Markets seeded.\n";

    $marketIds = $pdo->query("SELECT market_id FROM markets ORDER BY market_id ASC")->fetchAll(PDO::FETCH_COLUMN);

    // 4. Seed Farmer Stalls in Markets
    $existingStalls = $pdo->query("SELECT COUNT(*) FROM farmer_market_stalls")->fetchColumn();
    if ($existingStalls == 0 && count($marketIds) >= 2) {
        $stallStmt = $pdo->prepare("INSERT INTO farmer_market_stalls (farmer_id, market_id, stall_number_location, operating_days, status, assigned_at)
                                    VALUES (:farmer, :market, :stall_loc, :days, 'active', NOW())");
        
        // Farmer 2: Sunrise Organic Orchard (Stall A-12 at Market 1, Stall B-04 at Market 2)
        $stallStmt->execute([':farmer' => 2, ':market' => $marketIds[0], ':stall_loc' => 'Stall A-12 (North Aisle)', ':days' => 'Saturday, Sunday']);
        $stallStmt->execute([':farmer' => 2, ':market' => $marketIds[1], ':stall_loc' => 'Stall B-04 (Waterfront Row)', ':days' => 'Friday, Saturday']);

        // Farmer 6: Liaquat Bhai Farm (Stall C-08 at Market 1, Stall H-15 at Market 3)
        $stallStmt->execute([':farmer' => 6, ':market' => $marketIds[0], ':stall_loc' => 'Stall C-08 (Fresh Produce Bay)', ':days' => 'Saturday, Sunday']);
        $stallStmt->execute([':farmer' => 6, ':market' => $marketIds[2] ?? $marketIds[0], ':stall_loc' => 'Stall H-15 (Garden Entrance)', ':days' => 'Wednesday, Sunday']);
    }
    echo "✔ Farmer Market Stalls seeded.\n";

    $stalls = $pdo->query("SELECT stall_id, farmer_id, market_id FROM farmer_market_stalls")->fetchAll(PDO::FETCH_ASSOC);

    // 5. Seed Products
    $existingProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($existingProducts == 0) {
        $products = [
            // Farmer 2
            [
                'farmer_id' => 2,
                'category_name' => 'Fresh Vegetables',
                'product_name' => 'Farm Fresh Organic Heirloom Tomatoes',
                'description' => 'Vine-ripened, juicy heirloom tomatoes bursting with natural acidity and sweetness. Pesticide-free, harvested morning of market.',
                'unit' => 'kg',
                'price' => 140.00,
                'image_url' => 'assets/images/products/tomatoes.svg'
            ],
            [
                'farmer_id' => 2,
                'category_name' => 'Fresh Vegetables',
                'product_name' => 'Crisp Farm-Grown Crunchy Carrots',
                'description' => 'Sweet, vibrant orange root carrots pulled straight from nutrient-rich organic soil. Bundled with fresh leafy tops.',
                'unit' => 'kg',
                'price' => 110.00,
                'image_url' => 'assets/images/products/carrots.svg'
            ],
            [
                'farmer_id' => 2,
                'category_name' => 'Orchard Fruits',
                'product_name' => 'Crisp Mountain Gala Apples',
                'description' => 'Direct from the orchard. Crisp, fragrant, and hand-selected for natural sweetness and crunch without wax coatings.',
                'unit' => 'kg',
                'price' => 280.00,
                'image_url' => 'assets/images/products/apples.svg'
            ],
            [
                'farmer_id' => 2,
                'category_name' => 'Honey & Preserves',
                'product_name' => 'Raw Acacia Wildflower Honey (500g)',
                'description' => 'Unpasteurized, pure cold-extracted honeycomb nectar with delicate floral aroma. Direct from farm apiary.',
                'unit' => 'jar',
                'price' => 750.00,
                'image_url' => 'assets/images/products/honey.svg'
            ],
            // Farmer 6
            [
                'farmer_id' => 6,
                'category_name' => 'Fresh Vegetables',
                'product_name' => 'Tender Baby Spinach & Butterhead Lettuce',
                'description' => 'Hydro-rinsed delicate greens picked at sunrise. Crisp texture, ideal for farm-fresh salads and healthy green smoothies.',
                'unit' => 'bunch',
                'price' => 85.00,
                'image_url' => 'assets/images/products/spinach.svg'
            ],
            [
                'farmer_id' => 6,
                'category_name' => 'Dairy & Farm Eggs',
                'product_name' => 'Pasture-Raised Organic Brown Eggs (Dozen)',
                'description' => 'Golden yolks with rich omega content from happy hens roaming open green pastures. Packaged in biodegradable pulp cartons.',
                'unit' => 'dozen',
                'price' => 320.00,
                'image_url' => 'assets/images/products/eggs.svg'
            ],
            [
                'farmer_id' => 6,
                'category_name' => 'Dairy & Farm Eggs',
                'product_name' => 'Pure Grass-Fed Desi Cow Butter (250g)',
                'description' => 'Traditional slow-churned farmhouse butter with natural sweet cream aroma and golden color.',
                'unit' => 'pack',
                'price' => 450.00,
                'image_url' => 'assets/images/products/butter.svg'
            ],
            [
                'farmer_id' => 6,
                'category_name' => 'Farm Bakery',
                'product_name' => 'Rustic Sourdough Boule (Artisanal)',
                'description' => 'Naturally fermented 36-hour sourdough loaf baked on market dawn with a caramelized crust and soft open crumb.',
                'unit' => 'loaf',
                'price' => 380.00,
                'image_url' => 'assets/images/products/sourdough.svg'
            ],
            [
                'farmer_id' => 6,
                'category_name' => 'Aromatic Herbs',
                'product_name' => 'Sweet Basil, Mint & Italian Coriander Trio',
                'description' => 'Fragrant live herb bouquet with root balls intact. Keep fresh in water or plant directly in your kitchen garden.',
                'unit' => 'bundle',
                'price' => 120.00,
                'image_url' => 'assets/images/products/herbs.svg'
            ]
        ];

        $prodStmt = $pdo->prepare("INSERT INTO products (farmer_id, category_id, product_name, description, unit, is_recurring_template, image_url, created_at)
                                   VALUES (:fid, :cid, :pname, :pdesc, :unit, 1, :img, NOW())");

        foreach ($products as $p) {
            $catId = $catMap[$p['category_name']] ?? 1;
            $prodStmt->execute([
                ':fid' => $p['farmer_id'],
                ':cid' => $catId,
                ':pname' => $p['product_name'],
                ':pdesc' => $p['description'],
                ':unit' => $p['unit'],
                ':img' => $p['image_url']
            ]);
            $newProdId = $pdo->lastInsertId();

            // Seed weekly inventory for active stalls
            foreach ($stalls as $st) {
                if ($st['farmer_id'] == $p['farmer_id']) {
                    $invStmt = $pdo->prepare("INSERT INTO weekly_inventory (product_id, stall_id, day_of_week, stock_quantity, price, is_available)
                                             VALUES (:pid, :stid, :day, :qty, :price, 1)
                                             ON DUPLICATE KEY UPDATE stock_quantity = VALUES(stock_quantity), price = VALUES(price)");
                    
                    foreach (['Saturday', 'Sunday', 'Friday', 'Wednesday'] as $d) {
                        $invStmt->execute([
                            ':pid' => $newProdId,
                            ':stid' => $st['stall_id'],
                            ':day' => $d,
                            ':qty' => rand(25, 80),
                            ':price' => $p['price']
                        ]);
                    }
                }
            }
        }
    }
    echo "✔ Products & Weekly Inventory seeded.\n";

    // 6. Seed Pickup Slots for current and upcoming market days
    $existingSlots = $pdo->query("SELECT COUNT(*) FROM pickup_slots")->fetchColumn();
    if ($existingSlots == 0) {
        $slotStmt = $pdo->prepare("INSERT INTO pickup_slots (stall_id, slot_date, start_time, end_time, max_orders, status, created_at)
                                   VALUES (:stall_id, :slot_date, :start_time, :end_time, :max_orders, 'available', NOW())");
        
        $today = new DateTime();
        for ($i = 0; $i <= 10; $i++) {
            $date = clone $today;
            $date->modify("+$i days");
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->format('l');

            foreach ($stalls as $st) {
                // Morning Slots
                $slotStmt->execute([
                    ':stall_id' => $st['stall_id'],
                    ':slot_date' => $dateStr,
                    ':start_time' => '07:30:00',
                    ':end_time' => '09:30:00',
                    ':max_orders' => 12
                ]);
                $slotStmt->execute([
                    ':stall_id' => $st['stall_id'],
                    ':slot_date' => $dateStr,
                    ':start_time' => '09:30:00',
                    ':end_time' => '11:30:00',
                    ':max_orders' => 15
                ]);
                $slotStmt->execute([
                    ':stall_id' => $st['stall_id'],
                    ':slot_date' => $dateStr,
                    ':start_time' => '11:30:00',
                    ':end_time' => '13:30:00',
                    ':max_orders' => 10
                ]);
            }
        }
    }
    echo "✔ Pickup Slots seeded.\n";

    // 7. Seed AI Chatbot FAQs
    $existingFaqs = $pdo->query("SELECT COUNT(*) FROM ai_chatbot_faqs")->fetchColumn();
    if ($existingFaqs == 0) {
        $faqs = [
            [
                'category' => 'Pre-Orders & Pickup',
                'question' => 'How does pre-ordering work on MarketLink?',
                'answer' => 'Browse products from verified local farmers, add your items to the pre-order basket, and choose an available market pickup slot. When market day arrives, visit the farmer\'s stall, inspect your harvest, and pay in person. No online transaction fees!',
                'keywords' => 'pre-order, how it works, order, reserve, pickup'
            ],
            [
                'category' => 'Payment & Billing',
                'question' => 'Do I have to pay online when placing a pre-order?',
                'answer' => 'No! Per MarketLink policy, all pre-orders are strictly reserved online and paid for in person at the stall upon pickup. You can inspect the freshness of your produce before settling payment.',
                'keywords' => 'pay, payment, online payment, card, cash, pickup'
            ],
            [
                'category' => 'Order Cancellation & Cutoff',
                'question' => 'Can I cancel or modify my pre-order?',
                'answer' => 'Yes, you can cancel or adjust your order through your Orders Dashboard as long as it is before the farmer\'s daily order cut-off time (typically 6:00 PM the evening before market day).',
                'keywords' => 'cancel, modify, cutoff, deadline, change order'
            ],
            [
                'category' => 'Market Timings & Locations',
                'question' => 'Where are the markets located and what are the timings?',
                'answer' => 'We currently serve 4 central locations: Downtown Green Plaza (Sat/Sun 7am-1pm), Riverside Organic Market (Fri/Sat 8am-2pm), Hillside Heritage Market (Wed/Sun 6:30am-12:30pm), and Suburban Eco Fair. Check the "Markets & Map" tab for interactive GPS navigation.',
                'keywords' => 'markets, location, map, timings, hours, directions, where'
            ],
            [
                'category' => 'Quality & Organic Assurance',
                'question' => 'Are all products certified organic and fresh?',
                'answer' => 'All participating farmers are verified by MarketLink. Farmers harvest produce fresh on the morning or evening preceding market day, guaranteeing high nutritional quality and peak crispness without cold-storage delays.',
                'keywords' => 'organic, quality, fresh, pesticide, health, guarantee'
            ],
            [
                'category' => 'Farmer Direct Contact',
                'question' => 'How do I contact a farmer directly regarding special requests?',
                'answer' => 'Each farmer stall profile displays their direct contact person, verified business phone, and stall location number. You can also view customer reviews on their profile.',
                'keywords' => 'contact, phone, farmer, question, stall number'
            ]
        ];

        $faqStmt = $pdo->prepare("INSERT INTO ai_chatbot_faqs (category, question, answer, keywords, is_active, updated_at)
                                  VALUES (:cat, :q, :a, :kw, 1, NOW())");
        foreach ($faqs as $faq) {
            $faqStmt->execute([
                ':cat' => $faq['category'],
                ':q' => $faq['question'],
                ':a' => $faq['answer'],
                ':kw' => $faq['keywords']
            ]);
        }
    }
    echo "✔ AI Chatbot FAQs seeded.\n";

    // 8. Seed sample orders and reviews for Customer 1 (Sara Ahmed) to test Tracking & Reviews
    $existingOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    if ($existingOrders == 0) {
        $pSlot = $pdo->query("SELECT pickup_slot_id FROM pickup_slots WHERE stall_id = 1 LIMIT 1")->fetchColumn();
        $sampleProducts = $pdo->query("SELECT p.product_id, p.product_name, wi.price 
                                        FROM products p 
                                        JOIN weekly_inventory wi ON p.product_id = wi.product_id 
                                        GROUP BY p.product_id 
                                        LIMIT 3")->fetchAll();

        if ($pSlot && count($sampleProducts) >= 2) {
            // Completed Order
            $orderNum1 = 'ML-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $total1 = (2 * $sampleProducts[0]['price']) + (1 * $sampleProducts[1]['price']);

            $ordStmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, farmer_id, market_id, stall_id, pickup_slot_id, total_amount, order_status, pickup_date, created_at)
                                      VALUES (:num, 1, 2, 1, 1, :slot, :tot, 'completed', CURDATE() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY)");
            $ordStmt->execute([
                ':num' => $orderNum1,
                ':slot' => $pSlot,
                ':tot' => $total1
            ]);
            $orderId1 = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (:oid, :pid, :qty, :pr, :sub)");
            $itemStmt->execute([':oid' => $orderId1, ':pid' => $sampleProducts[0]['product_id'], ':qty' => 2, ':pr' => $sampleProducts[0]['price'], ':sub' => 2 * $sampleProducts[0]['price']]);
            $itemStmt->execute([':oid' => $orderId1, ':pid' => $sampleProducts[1]['product_id'], ':qty' => 1, ':pr' => $sampleProducts[1]['price'], ':sub' => 1 * $sampleProducts[1]['price']]);

            // Add Farmer Review & Product Review for Order 1
            $pdo->prepare("INSERT INTO farmer_reviews (order_id, customer_id, farmer_id, rating, review_comment, farmer_response, response_date, is_moderated, created_at)
                           VALUES (:oid, 1, 2, 5, 'The organic heirloom tomatoes were exceptionally fresh and sweet! Stall was well-organized for fast pickup.', 'Thank you Sara! Looking forward to seeing you again this weekend.', NOW() - INTERVAL 1 DAY, 1, NOW() - INTERVAL 1 DAY)")
                ->execute([':oid' => $orderId1]);

            $pdo->prepare("INSERT INTO product_reviews (order_id, product_id, customer_id, rating, comment, is_moderated, created_at)
                           VALUES (:oid, :pid, 1, 5, 'Crispest carrots I have had in months. Highly recommended!', 1, NOW() - INTERVAL 1 DAY)")
                ->execute([':oid' => $orderId1, ':pid' => $sampleProducts[1]['product_id']]);

            // Active Pre-Order (Placed / Accepted)
            $orderNum2 = 'ML-' . strtoupper(substr(md5(uniqid() . 'act'), 0, 8));
            $total2 = (3 * $sampleProducts[0]['price']);
            $ordStmt2 = $pdo->prepare("INSERT INTO orders (order_number, customer_id, farmer_id, market_id, stall_id, pickup_slot_id, total_amount, order_status, pickup_date, created_at)
                                       VALUES (:num, 1, 2, 1, 1, :slot, :tot, 'ready_for_pickup', CURDATE() + INTERVAL 2 DAY, NOW())");
            $ordStmt2->execute([
                ':num' => $orderNum2,
                ':slot' => $pSlot,
                ':tot' => $total2
            ]);
            $orderId2 = $pdo->lastInsertId();
            $itemStmt->execute([':oid' => $orderId2, ':pid' => $sampleProducts[0]['product_id'], ':qty' => 3, ':pr' => $sampleProducts[0]['price'], ':sub' => 3 * $sampleProducts[0]['price']]);

            // Add Notifications for Customer 1
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                                        VALUES (:uid, :title, :msg, :type, 0, NOW())");
            $notifStmt->execute([
                ':uid' => 1,
                ':title' => 'Harvest Ready for Pickup! 🥬',
                ':msg' => "Your pre-order #{$orderNum2} has been harvested, sorted, and is ready for pickup at Sunrise Organic Orchard (Stall A-12).",
                ':type' => 'order_status'
            ]);
            $notifStmt->execute([
                ':uid' => 1,
                ':title' => 'Weekend Market Opening Announcement',
                ':msg' => 'Downtown Green Plaza Farmers Market will open early this Saturday at 7:00 AM with 15+ verified growers.',
                ':type' => 'announcement'
            ]);

            // Add Favorites for Customer 1
            $pdo->prepare("INSERT IGNORE INTO favorite_farmers (customer_id, farmer_id, created_at) VALUES (1, 2, NOW())")->execute();
            $pdo->prepare("INSERT IGNORE INTO favorite_products (customer_id, product_id, created_at) VALUES (1, :pid, NOW())")
                ->execute([':pid' => $sampleProducts[0]['product_id']]);
        }
    }
    echo "✔ Sample Orders, Reviews, Notifications, and Favorites seeded.\n";

    echo "\n=== Database Seeding Complete Successfully! ===\n";

} catch (Exception $e) {
    echo "Database Seeding Error: " . $e->getMessage() . "\n";
    exit(1);
}
