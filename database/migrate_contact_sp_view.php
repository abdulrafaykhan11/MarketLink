<?php
/**
 * MarketLink - Migration for Contact Us Stored Procedure & Admin Inquiries View
 */
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
echo "Starting Contact Us Stored Procedure & View Migration...\n";

try {
    // 1. Create or Replace MySQL View for Admin Inquiries
    $viewSql = "CREATE OR REPLACE VIEW view_admin_contact_inquiries AS
    SELECT 
        cm.id,
        cm.name,
        cm.email,
        cm.phone,
        cm.subject,
        cm.message,
        cm.admin_reply,
        cm.replied_at,
        cm.replied_by,
        cm.status,
        cm.ip_address,
        cm.created_at,
        cm.updated_at,
        u.username AS replied_by_username,
        u.email AS replied_by_email,
        CONCAT('INQ-', LPAD(cm.id, 5, '0')) AS ticket_number
    FROM contact_messages cm
    LEFT JOIN users u ON cm.replied_by = u.user_id";

    $pdo->exec($viewSql);
    echo "✔ View `view_admin_contact_inquiries` created successfully.\n";

    // 2. Drop existing stored procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_submit_contact_message");

    // 3. Create Stored Procedure for Contact Form Submission
    $spSql = "CREATE PROCEDURE sp_submit_contact_message(
        IN p_name VARCHAR(100),
        IN p_email VARCHAR(120),
        IN p_phone VARCHAR(30),
        IN p_subject VARCHAR(150),
        IN p_message TEXT,
        IN p_ip VARCHAR(45)
    )
    BEGIN
        INSERT INTO contact_messages (
            name,
            email,
            phone,
            subject,
            message,
            status,
            ip_address,
            created_at
        ) VALUES (
            p_name,
            p_email,
            p_phone,
            p_subject,
            p_message,
            'unread',
            p_ip,
            NOW()
        );

        SELECT LAST_INSERT_ID() AS inquiry_id;
    END";

    $pdo->exec($spSql);
    echo "✔ Stored Procedure `sp_submit_contact_message` created successfully.\n";

    echo "Migration completed successfully with 100% integrity!\n";
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}

