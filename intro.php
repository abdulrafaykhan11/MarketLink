<?php
/**
 * MarketLink - Intro Page (Disabled / Redirected to Home)
 */
require_once __DIR__ . '/config/db.php';
header('Location: ' . BASE_URL . '/index.php', true, 302);
exit;
