<?php
/**
 * MarketLink - AI FAQs Page Retired
 * The manual static FAQs table has been retired and upgraded to
 * Google Gemini 3.8 Flash Real-Time Autonomous AI Agent.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireAdmin();

setFlash('info', 'The manual static FAQs page has been retired. MarketLink is now powered by Google Gemini 3.8 Flash with live database intelligence.');
header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;
