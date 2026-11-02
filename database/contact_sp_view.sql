-- ============================================================================
-- MarketLink: Contact Us Stored Procedure & Admin Inquiries View
-- ============================================================================

-- 1. MySQL View for Admin Inquiries and Support Central
CREATE OR REPLACE VIEW view_admin_contact_inquiries AS
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
LEFT JOIN users u ON cm.replied_by = u.user_id;

-- 2. MySQL Stored Procedure for Contact Form Submission
DROP PROCEDURE IF EXISTS sp_submit_contact_message;

DELIMITER $$
CREATE PROCEDURE sp_submit_contact_message(
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
END$$
DELIMITER ;

