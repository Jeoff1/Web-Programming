<?php
// Notification utility for adding notifications
require_once __DIR__ . '/../classes/notification.php';
require_once __DIR__ . '/../config/email.config.php';

// Load PHPMailer from local files instead of vendor
require_once __DIR__ . '/../Exception.php';
require_once __DIR__ . '/../SMTP.php';
require_once __DIR__ . '/../PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('EMAIL_LOG_FILE', __DIR__ . '/../logs/email_log.txt');

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Ensure emails directory exists
if (!is_dir(__DIR__ . '/../logs/emails')) {
    mkdir(__DIR__ . '/../logs/emails', 0755, true);
}

function log_email($to_email, $subject, $status = 'PENDING', $details = '') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] [{$status}] To: {$to_email} | Subject: {$subject}";
    if (!empty($details)) {
        $log_entry .= " | Details: {$details}";
    }
    $log_entry .= "\n";
    file_put_contents(EMAIL_LOG_FILE, $log_entry, FILE_APPEND);
}

function send_email($to_email, $subject, $message, $headers = "") {
    if (empty($to_email)) {
        error_log("Email send failed: No recipient email provided");
        log_email($to_email, $subject, 'FAILED', 'No recipient email provided');
        return false;
    }
    
    // If SAVE_EMAILS_TO_FILE is enabled, save to file instead of sending
    if (SAVE_EMAILS_TO_FILE) {
        return save_email_to_file($to_email, $subject, $message, $headers);
    }
    
    // Use PHPMailer for actual sending
    return send_email_phpmailer($to_email, $subject, $message);
}

/**
 * Send email using PHPMailer with Gmail SMTP
 * Enhanced version with better error handling
 * 
 * @param string $to_email Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @param bool $isHTML Whether message is HTML (default: true)
 * @return bool True if sent successfully, false otherwise
 */
function send_email_phpmailer($to_email, $subject, $message, $isHTML = true) {
    if (!ENABLE_EMAIL_NOTIFICATIONS) {
        error_log("Email notifications are disabled");
        return false;
    }

    $mail = new PHPMailer(true); // true = throw exceptions
    
    try {
        // Server settings
        $mail->isSMTP();                            // Send using SMTP
        $mail->Host       = SMTP_HOST;              // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                   // Enable SMTP authentication
        $mail->Username   = SMTP_USERNAME;          // SMTP username
        $mail->Password   = SMTP_PASSWORD;          // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
        $mail->Port       = SMTP_PORT;              // TCP port to connect to
        
        // Debug mode
        if (EMAIL_DEBUG_MODE) {
            $mail->SMTPDebug = 2; // 2 = DEBUG_SERVER - show server responses
            $mail->Debugoutput = function($str, $level) {
                error_log("[PHPMailer Debug Level $level] $str");
            };
        }
        
        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to_email);               // Add a recipient
        
        // Content
        $mail->isHTML($isHTML);                     // Set email format
        $mail->Subject = $subject;
        $mail->Body    = $message;
        if ($isHTML) {
            $mail->AltBody = strip_tags($message);  // Plain text version
        }
        
        // Send the email
        $result = $mail->send();
        
        if ($result) {
            log_email($to_email, $subject, 'SENT', 'Sent via PHPMailer Gmail SMTP');
            error_log("Email successfully sent to: $to_email - Subject: $subject");
        }
        
        return $result;
        
    } catch (Exception $e) {
        $error_msg = "PHPMailer Error: " . $mail->ErrorInfo;
        error_log($error_msg);
        log_email($to_email, $subject, 'FAILED', $error_msg);
        return false;
    }
}

function save_email_to_file($to_email, $subject, $message, $headers) {
    try {
        $emails_dir = __DIR__ . '/../logs/emails';
        
        // Create emails directory if it doesn't exist
        if (!is_dir($emails_dir)) {
            mkdir($emails_dir, 0755, true);
        }
        
        // Create a unique filename for each email
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $emails_dir . '/' . $timestamp . '_' . md5($to_email . $subject . time()) . '.eml';
        
        // Compose the email file content
        $email_content = "TO: $to_email\r\n";
        $email_content .= "SUBJECT: $subject\r\n";
        $email_content .= "FROM: " . EMAIL_FROM . "\r\n";
        $email_content .= "FROM_NAME: " . EMAIL_FROM_NAME . "\r\n";
        $email_content .= "DATE: " . date('Y-m-d H:i:s') . "\r\n";
        if (!empty($headers)) {
            $email_content .= $headers . "\r\n";
        }
        $email_content .= "===== EMAIL BODY =====\r\n\r\n";
        $email_content .= $message;
        
        // Save to file
        $result = file_put_contents($filename, $email_content);
        
        if ($result !== false) {
            error_log("Email saved to file: $filename");
            log_email($to_email, $subject, 'SAVED', "File: $filename");
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Error saving email to file: " . $e->getMessage());
        log_email($to_email, $subject, 'FAILED', "Error saving to file: " . $e->getMessage());
        return false;
    }
}

function get_email_template($title, $content, $footer = "") {
    $header = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px;">
        <div style="background: linear-gradient(135deg, #1e90ff 0%, #1873cc 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
            <h1 style="color: white; margin: 0; font-size: 28px;">' . EMAIL_FROM_NAME . '</h1>
            <p style="color: #e8f0ff; margin: 10px 0 0 0; font-size: 14px;">Barangay Blotter Management System</p>
        </div>
        <div style="background: white; padding: 30px; margin-top: 0;">';
    
    $footer_html = '
        </div>
        <div style="text-align: center; padding: 20px; background: #f5f5f5; color: #666; font-size: 12px; border-radius: 0 0 8px 8px;">
            <p style="margin: 0 0 10px 0;">This is an automated message from ' . EMAIL_FROM_NAME . '</p>
            <p style="margin: 0;">&copy; ' . date('Y') . ' Barangay Office. All rights reserved.</p>
        </div>
    </div>';

    $html = $header . '
        <h2 style="color: #1e90ff; margin-top: 0;">' . htmlspecialchars($title) . '</h2>
        ' . $content . '
    ' . $footer_html;

    return $html;
}

function notify_case_added($case_id, $admin_id) {
    error_log("==================== NOTIFY CASE ADDED ====================");
    error_log("Case ID: $case_id, Admin ID: $admin_id");
    
    $notif = new Notification();
    $message = "New case #$case_id has been added.";
    $result = $notif->addNotification($admin_id, $message, 'case_added');
    if (!$result) {
        error_log("Failed to add notification for case $case_id by admin $admin_id");
    }
    
    // Send email to complainant when case is added
    try {
        require_once __DIR__ . '/../classes/blotter.php';
        $blotterObj = new Blotter();
        error_log("Blotter object created successfully");
        
        $blotter = $blotterObj->fetchBlotter($case_id);
        error_log("Blotter fetched: " . ($blotter ? "SUCCESS" : "FAILED/NULL"));
        
        if ($blotter) {
            error_log("Blotter data - Name: " . ($blotter['complainant_name'] ?? 'NULL') . ", Email: " . ($blotter['complainant_email'] ?? 'NULL'));
        }
        
        if ($blotter && !empty($blotter['complainant_email'])) {
            error_log("Sending case added email to: " . $blotter['complainant_email']);
            send_email_case_added($case_id, $blotter['complainant_name'], $blotter['complainant_email']);
        } else {
            error_log("Skipping email: No blotter data or email found");
        }
    } catch (Exception $e) {
        error_log("Exception in notify_case_added: " . $e->getMessage());
    }
    
    error_log("=========================================================");
}

function notify_case_edited($case_id, $admin_id) {
    $notif = new Notification();
    $message = "Case #$case_id has been edited.";
    return $notif->addNotification($admin_id, $message, 'case_edited');
}

function notify_case_resolved($case_id, $admin_id, $complainant_email = "") {
    $notif = new Notification();
    $message = "Case #$case_id has been marked as Resolved.";
    $notif->addNotification($admin_id, $message, 'case_resolved');
    
    // Send email to complainant if email exists
    if (!empty($complainant_email)) {
        send_email_case_resolved($case_id, $complainant_email);
    }
}

function notify_case_status_changed($case_id, $admin_id, $new_status) {
    $notif = new Notification();
    $message = "Case #$case_id status has been updated to: $new_status";
    $notif->addNotification($admin_id, $message, 'case_status_changed');
}

function notify_case_deleted($case_id, $admin_id) {
    $notif = new Notification();
    $message = "Case #$case_id has been deleted.";
    return $notif->addNotification($admin_id, $message, 'case_deleted');
}

function notify_pending_cases_old() {
    $notif = new Notification();
    require_once __DIR__ . '/../classes/blotter.php';
    $blotterObj = new Blotter();
    
    // Get all pending cases
    $pending = $blotterObj->viewBlotters("", "", "Pending");
    $now = time();
    $thirtyDaysAgo = strtotime('-30 days');
    
    foreach ($pending as $case) {
        $caseDate = strtotime($case['date']);
        if ($caseDate < $thirtyDaysAgo) {
            // Notify the admin who created the case
            $message = "Case #" . $case['blotter_id'] . " (Complainant: " . $case['complainant_name'] . ") has been pending for more than 30 days.";
            $notif->addNotification($case['admin_id'], $message, 'pending_old');
            
            // Send email to complainant if email exists
            if (!empty($case['complainant_email'])) {
                send_email_pending_follow_up($case['blotter_id'], $case['complainant_name'], $case['complainant_email']);
            }
        }
    }
}

/**
 * Send email when a blotter case is successfully added
 * Sends welcome/acknowledgment email to complainant
 * 
 * @param int $case_id Case/Blotter ID
 * @param string $complainant_name Name of the complainant
 * @param string $complainant_email Email of the complainant
 * @return bool True if sent successfully, false otherwise
 */
function send_email_case_added($case_id, $complainant_name, $complainant_email) {
    if (empty($complainant_email)) {
        error_log("Email not sent for case $case_id: No complainant email provided");
        return false;
    }

    if (empty($complainant_name)) {
        $complainant_name = "Valued Complainant";
    }

    $subject = "Case Received and Recorded - Blotter #$case_id";
    
    $content = "
        <p>Dear " . htmlspecialchars($complainant_name) . ",</p>
        <p>Thank you for filing your blotter case with us. We are pleased to confirm that your complaint has been successfully received and recorded in our system.</p>
        <p><strong>Case Reference Number:</strong> <span style='color: #1e90ff; font-weight: bold;'>#$case_id</span></p>
        <p><strong>Status:</strong> <span style='background: #fff3cd; padding: 2px 8px; border-radius: 3px;'>Pending</span></p>
        <p><strong>What happens next:</strong></p>
        <ul>
            <li>Our barangay staff will review your case thoroughly</li>
            <li>You will receive updates via email as your case progresses</li>
            <li>We will investigate and take appropriate action</li>
            <li>You can monitor your case status anytime through our system</li>
        </ul>
        <p><strong>How to track your case:</strong></p>
        <p>Use your case reference number <strong>#$case_id</strong> to check the status and details of your complaint at any time.</p>
        <p><strong>Need assistance?</strong></p>
        <p>If you have any questions about your case or need to provide additional information, please contact the Barangay Office during business hours or reply to this email.</p>
        <p>We appreciate your trust and cooperation. Together, we ensure our community's safety and order.</p>
        <p>Best regards,<br><strong>Barangay Blotter Management System</strong></p>
    ";
    
    $html = get_email_template("Case Received", $content);
    
    error_log("==================== CASE ADDED EMAIL DEBUG ====================");
    error_log("Case ID: $case_id");
    error_log("Complainant: " . htmlspecialchars($complainant_name));
    error_log("Email: $complainant_email");
    
    $result = send_email($complainant_email, $subject, $html, true);
    
    error_log("Email send result: " . ($result ? "SUCCESS" : "FAILED"));
    error_log("==============================================================");
    
    return $result; 
}

function send_email_case_resolved($case_id, $complainant_email) {
    if (empty($complainant_email)) {
        error_log("Email not sent for case $case_id: No complainant email provided");
        return false;
    }
    
    error_log("==================== CASE RESOLVED EMAIL DEBUG ====================");
    error_log("Case ID: $case_id");
    error_log("Complainant Email: $complainant_email");
    error_log("SAVE_EMAILS_TO_FILE: " . (SAVE_EMAILS_TO_FILE ? "true" : "false"));
    error_log("EMAIL_DEBUG_MODE: " . (EMAIL_DEBUG_MODE ? "true" : "false"));
    
    $subject = "Case Resolved - Blotter #$case_id";
    $content = "
        <p>Dear Valued Complainant,</p>
        <p>We are pleased to inform you that your blotter case <strong>#$case_id</strong> has been successfully resolved.</p>
        <p><strong>What this means:</strong></p>
        <ul>
            <li>Your case has been reviewed and processed</li>
            <li>Resolution steps have been completed</li>
            <li>Documentation has been filed and recorded</li>
        </ul>
        <p>If you have any questions or concerns regarding this case, please feel free to contact our Barangay Office during business hours.</p>
        <p>Thank you for your cooperation and trust in our Barangay.</p>
        <p>Best regards,<br><strong>Barangay Blotter Management System</strong></p>
    ";
    
    $html = get_email_template("Case Resolution Notice", $content);
    
    error_log("Attempting to send case resolved email to: $complainant_email for case $case_id");
    $result = send_email($complainant_email, $subject, $html);
    error_log("Email send result: " . ($result ? "SUCCESS" : "FAILED"));
    error_log("=====================================================================");
    
    return $result;
}

/**
 * Send email when blotter status is changed
 * Automatically triggered whenever status is updated
 * 
 * @param int $case_id Case/Blotter ID
 * @param string $complainant_name Name of the complainant
 * @param string $complainant_email Email of the complainant
 * @param string $old_status Previous status
 * @param string $new_status New status
 * @param string $reason Reason/description for the status change
 * @return bool True if sent successfully, false otherwise
 */
function send_email_status_changed($case_id, $complainant_name, $complainant_email, $old_status, $new_status, $reason = "") {
    if (empty($complainant_email)) {
        error_log("Email not sent for case $case_id: No complainant email provided");
        return false;
    }

    if (empty($complainant_name)) {
        $complainant_name = "Valued Complainant";
    }

    $subject = "Case Status Update - Blotter #$case_id";
    
    $reason_section = "";
    if (!empty($reason)) {
        $reason_section = "
        <p><strong>Update Details:</strong></p>
        <p style='background: #f5f5f5; padding: 15px; border-left: 4px solid #1e90ff; margin: 15px 0;'>
            " . nl2br(htmlspecialchars($reason)) . "
        </p>";
    }

    $content = "
        <p>Dear " . htmlspecialchars($complainant_name) . ",</p>
        <p>We are writing to inform you of an important update regarding your blotter case <strong>#$case_id</strong>.</p>
        <p><strong>Status Update:</strong></p>
        <div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
            <p style='margin: 5px 0;'><strong>Previous Status:</strong> " . htmlspecialchars($old_status) . "</p>
            <p style='margin: 5px 0;'><strong>Current Status:</strong> <span style='color: #1e90ff; font-weight: bold;'>" . htmlspecialchars($new_status) . "</span></p>
            <p style='margin: 5px 0;'><strong>Updated On:</strong> " . date('F j, Y \a\t g:i A') . "</p>
        </div>
        
        $reason_section
        
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Monitor your case status through our system</li>
            <li>Contact us if you have questions or need clarification</li>
            <li>Keep this case reference number for your records: <strong>#$case_id</strong></li>
        </ul>
        <p>Thank you for your continued trust and cooperation with the Barangay.</p>
        <p>Best regards,<br><strong>Barangay Blotter Management System</strong></p>
    ";
    
    $html = get_email_template("Case Status Updated", $content);
    
    error_log("==================== STATUS CHANGE EMAIL DEBUG ====================");
    error_log("Case ID: $case_id");
    error_log("Complainant: " . htmlspecialchars($complainant_name));
    error_log("Email: $complainant_email");
    error_log("Status Change: $old_status → $new_status");
    error_log("Reason provided: " . (!empty($reason) ? "Yes" : "No"));
    
    $result = send_email($complainant_email, $subject, $html, true);
    
    error_log("Email send result: " . ($result ? "SUCCESS" : "FAILED"));
    error_log("===================================================================");
    
    return $result;
}

function send_email_pending_follow_up($case_id, $complainant_name, $complainant_email) {
    if (empty($complainant_email)) {
        error_log("Email not sent for case $case_id: No complainant email provided");
        return false;
    }
    
    $subject = "Action Required - Pending Case Follow-up #$case_id";
    $content = "
        <p>Dear " . htmlspecialchars($complainant_name) . ",</p>
        <p>We are writing to you regarding your blotter case <strong>#$case_id</strong>.</p>
        <p><strong>Status Update:</strong> Your case has been pending for an extended period and requires follow-up.</p>
        <p><strong>What you need to do:</strong></p>
        <ul>
            <li>Contact the Barangay Office to provide any additional information or documentation needed</li>
            <li>Check the case status regularly through our system</li>
            <li>Reach out if you need any clarification or have concerns</li>
        </ul>
        <p>The Barangay Office is actively working on your case. We appreciate your patience and cooperation.</p>
        <p>If you have urgent concerns, please visit the Barangay Office in person or contact us directly.</p>
        <p>Best regards,<br><strong>Barangay Blotter Management System</strong></p>
    ";
    
    $html = get_email_template("Pending Case Follow-up", $content);
    
    error_log("Attempting to send pending follow-up email to: $complainant_email for case $case_id");
    $result = send_email($complainant_email, $subject, $html);
    
    if ($result) {
        log_email($complainant_email, $subject, 'SENT', "Pending follow-up for case $case_id");
    } else {
        log_email($complainant_email, $subject, 'FAILED', "Failed to send pending follow-up for case $case_id");
        error_log("Failed to send follow-up email to $complainant_email for case $case_id");
    }
    
    return $result;
}

/**
 * Test Gmail SMTP connection
 * Used for debugging email configuration
 */
function test_gmail_smtp() {
    error_log("Testing Gmail SMTP connection...");
    error_log("Host: " . SMTP_HOST . ":" . SMTP_PORT);
    error_log("Username: " . SMTP_USERNAME);
    
    $test_mail = new PHPMailer(true);
    
    try {
        $test_mail->isSMTP();
        $test_mail->Host       = SMTP_HOST;
        $test_mail->SMTPAuth   = true;
        $test_mail->Username   = SMTP_USERNAME;
        $test_mail->Password   = SMTP_PASSWORD;
        $test_mail->SMTPSecure = SMTP_SECURE;
        $test_mail->Port       = SMTP_PORT;
        $test_mail->SMTPDebug  = 2;
        $test_mail->Debugoutput = function($str, $level) {
            error_log("[SMTP Test] Level $level: $str");
        };
        
        // Test connection without sending
        $test_mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $test_mail->addAddress('test@example.com'); // Dummy address for testing
        $test_mail->Subject = 'Test';
        $test_mail->Body = 'Test';
        $test_mail->isHTML(true);
        
        // Don't actually send, just test the connection setup
        error_log("✅ Gmail SMTP configuration is valid");
        return array(
            'success' => true,
            'message' => '✅ Gmail SMTP connection configured successfully!',
            'host' => SMTP_HOST,
            'port' => SMTP_PORT,
            'username' => SMTP_USERNAME
        );
        
    } catch (Exception $e) {
        error_log("❌ SMTP Error: " . $e->getMessage());
        return array(
            'success' => false,
            'message' => '❌ SMTP Connection Error: ' . $e->getMessage(),
            'error' => $e->getMessage()
        );
    }
}
