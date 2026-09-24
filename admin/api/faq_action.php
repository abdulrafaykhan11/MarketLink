<?php
/**
 * MarketLink Admin API - AI Chatbot FAQ Management
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$pdo = getDBConnection();
$action = trim($_POST['action'] ?? 'save');

try {
    if ($action === 'save') {
        $faqId    = (int)($_POST['faq_id'] ?? 0);
        $category = trim($_POST['category'] ?? 'General');
        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        $active   = isset($_POST['is_active']) ? 1 : 0;

        if (empty($question) || empty($answer)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Question and answer cannot be empty.']);
            exit;
        }

        if ($faqId > 0) {
            $stmt = $pdo->prepare("UPDATE ai_chatbot_faqs SET category = :cat, question = :q, answer = :a, keywords = :kw, is_active = :act WHERE faq_id = :id");
            $stmt->execute([
                ':cat' => $category,
                ':q'   => $question,
                ':a'   => $answer,
                ':kw'  => $keywords,
                ':act' => $active,
                ':id'  => $faqId
            ]);
            echo json_encode(['success' => true, 'message' => 'Chatbot FAQ updated successfully!']);
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO ai_chatbot_faqs (category, question, answer, keywords, is_active) VALUES (:cat, :q, :a, :kw, :act)");
            $stmt->execute([
                ':cat' => $category,
                ':q'   => $question,
                ':a'   => $answer,
                ':kw'  => $keywords,
                ':act' => $active
            ]);
            echo json_encode(['success' => true, 'message' => 'New FAQ added to AI chatbot knowledge base!']);
            exit;
        }
    }

    if ($action === 'delete') {
        $faqId = (int)($_POST['faq_id'] ?? 0);
        $pdo->prepare("DELETE FROM ai_chatbot_faqs WHERE faq_id = :id")->execute([':id' => $faqId]);
        echo json_encode(['success' => true, 'message' => 'FAQ deleted.']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Exception $e) {
    error_log("FAQ action error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
