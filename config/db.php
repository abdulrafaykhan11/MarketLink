<?php
/**
 * MarketLink - Database Connection & Configuration
 * Using PDO for secure, prepared SQL interactions
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'marketlink_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Base URL helper
define('APP_NAME', 'MarketLink');
define('BASE_URL', '/MarketLink');

function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error internally and show clean error message
            error_log("Database Connection Error: " . $e->getMessage());
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP.'
                ]);
                exit;
            } else {
                die('<div style="font-family:sans-serif;padding:2rem;text-align:center;background:#fef2f2;color:#991b1b;border:1px solid #f87171;border-radius:8px;max-width:600px;margin:3rem auto;">
                    <h2 style="margin:0 0 10px 0;">Database Connection Error</h2>
                    <p>Unable to connect to <strong>' . htmlspecialchars(DB_NAME) . '</strong>. Please verify XAMPP MySQL service is running.</p>
                </div>');
            }
        }
    }

    return $pdo;
}
