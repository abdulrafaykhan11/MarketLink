<?php
/**
 * Generates beautiful, responsive SVGs for produce and categories
 */
$base = __DIR__ . '/../assets/images';

$svgs = [
    'products/apples.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%"><rect width="200" height="200" rx="24" fill="#fef2f2"/><circle cx="100" cy="115" r="62" fill="#ef4444"/><circle cx="85" cy="112" r="50" fill="#dc2626"/><ellipse cx="120" cy="85" rx="16" ry="8" fill="#ffffff" opacity="0.3" transform="rotate(-30 120 85)"/><path d="M100 55 C95 40, 110 30, 120 35" stroke="#78350f" stroke-width="6" fill="none" stroke-linecap="round"/><path d="M102 50 C115 35, 140 40, 135 55 C120 60, 105 55, 102 50 Z" fill="#22c55e"/></svg>',
    'products/honey.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%"><rect width="200" height="200" rx="24" fill="#fffbeb"/><rect x="55" y="70" width="90" height="95" rx="20" fill="#f59e0b"/><rect x="65" y="80" width="70" height="75" rx="12" fill="#d97706"/><rect x="68" y="52" width="64" height="20" rx="8" fill="#b45309"/><ellipse cx="100" cy="52" rx="36" ry="10" fill="#fcd34d"/><rect x="75" y="100" width="50" height="40" rx="6" fill="#fff" opacity="0.85"/><text x="100" y="125" font-family="sans-serif" font-weight="bold" font-size="14" fill="#92400e" text-anchor="middle">PURE</text><text x="100" y="137" font-family="sans-serif" font-weight="bold" font-size="9" fill="#b45309" text-anchor="middle">HONEY</text></svg>',
    'products/spinach.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%"><rect width="200" height="200" rx="24" fill="#f0fdf4"/><path d="M100 170 C70 120, 50 70, 100 35 C150 70, 130 120, 100 170 Z" fill="#16a34a"/><path d="M100 170 L100 45" stroke="#15803d" stroke-width="4" stroke-linecap="round"/><path d="M75 160 C50 120, 30 80, 70 50 C110 80, 95 120, 75 160 Z" fill="#22c55e" opacity="0.85"/><path d="M125 160 C105 120, 90 80, 130 50 C170 80, 150 120, 125 160 Z" fill="#15803d" opacity="0.85"/></svg>',
    'products/eggs.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%"><rect width="200" height="200" rx="24" fill="#fefce8"/><ellipse cx="78" cy="115" rx="32" ry="42" fill="#d97706" transform="rotate(-15 78 115)"/><ellipse cx="76" cy="113" rx="28" ry="38" fill="#fde68a" transform="rotate(-15 76 113)"/><ellipse cx="122" cy="110" rx="34" ry="44" fill="#b45309" transform="rotate(12 122 110)"/><ellipse cx="120" cy="108" rx="30" ry="40" fill="#fef08a" transform="rotate(12 120 108)"/><ellipse cx="100" cy="130" rx="36" ry="46" fill="#fef9c3"/><ellipse cx="98" cy="128" rx="32" ry="42" fill="#fef08a"/></svg>',
    'products/butter.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%"><rect width="200" height="200" rx="24" fill="#fefce8"/><polygon points="45,130 135,130 160,95 70,95" fill="#facc15"/><polygon points="45,130 70,95 70,65 45,100" fill="#eab308"/><polygon points="70,95 160,95 160,65 70,65" fill="#fde047"/><rect x="40" y="130" width="125" height="15" rx="6" fill="#d4d4d8"/></svg>',
    'products/sourdough.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%"><rect width="200" height="200" rx="24" fill="#fffbeb"/><ellipse cx="100" cy="115" rx="68" ry="45" fill="#92400e"/><ellipse cx="100" cy="110" rx="65" ry="42" fill="#d97706"/><path d="M60 105 Q100 85 140 105" stroke="#78350f" stroke-width="5" fill="none" stroke-linecap="round"/><path d="M70 120 Q100 105 130 120" stroke="#78350f" stroke-width="4" fill="none" stroke-linecap="round"/></svg>',
    'products/herbs.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%"><rect width="200" height="200" rx="24" fill="#f0fdf4"/><path d="M100 165 C85 125, 75 80, 100 40 C125 80, 115 125, 100 165 Z" fill="#15803d"/><path d="M70 150 C55 120, 50 85, 75 60 C95 85, 85 120, 70 150 Z" fill="#22c55e"/><path d="M130 150 C115 120, 105 85, 125 60 C145 85, 145 120, 130 150 Z" fill="#16a34a"/><circle cx="100" cy="165" r="12" fill="#854d0e"/></svg>',
    'cat-vegetables.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="#dcfce7"/><path d="M50 78 C35 60 30 35 50 20 C70 35 65 60 50 78 Z" fill="#16a34a"/></svg>',
    'cat-fruits.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="#fee2e2"/><circle cx="50" cy="55" r="28" fill="#ef4444"/><path d="M50 28 C48 20 56 16 60 18" stroke="#78350f" stroke-width="3" fill="none"/></svg>',
    'cat-dairy.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="#fef9c3"/><ellipse cx="50" cy="55" rx="22" ry="28" fill="#fde047"/></svg>',
    'cat-bakery.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="#ffedd5"/><ellipse cx="50" cy="55" rx="30" ry="20" fill="#d97706"/></svg>',
    'cat-honey.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="#fef3c7"/><rect x="32" y="38" width="36" height="40" rx="10" fill="#f59e0b"/></svg>',
    'cat-herbs.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="#ccfbf1"/><path d="M50 75 C40 55 35 35 50 22 C65 35 60 55 50 75 Z" fill="#0d9488"/></svg>'
];

foreach ($svgs as $rel => $content) {
    file_put_contents($base . '/' . $rel, $content);
}
echo "Produce and Category SVGs created successfully!\n";

require_once __DIR__ . '/db.php';
$pdo = getDBConnection();
$pdo->exec("UPDATE products SET image_url = 'assets/images/products/tomatoes.jpg' WHERE product_name LIKE '%Tomato%'");
$pdo->exec("UPDATE products SET image_url = 'assets/images/products/carrots.jpg' WHERE product_name LIKE '%Carrot%'");
echo "Product photos linked in DB!\n";
