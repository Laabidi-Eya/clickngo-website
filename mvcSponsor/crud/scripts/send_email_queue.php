<?php
require_once __DIR__ . '../../../../mvcEvent/Config.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = config::getConnexion();

try {
    // Fetch unsent emails from email_queue
    $stmt = $db->prepare("SELECT * FROM email_queue WHERE sent_at IS NULL ORDER BY created_at ASC LIMIT 10");
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($emails as $email) {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'yesminechourou04@gmail.com';
            $mail->Password   = 'juxinvxhfzvrgvbt';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('yesminechourou04@gmail.com', 'Click\'N\'Go');
            $mail->addAddress($email['recipient_email'], $email['recipient_name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $email['subject'];
            $mail->Body    = $email['body'];

            $mail->send();

            // Mark email as sent
            $update = $db->prepare("UPDATE email_queue SET sent_at = NOW() WHERE id = ?");
            $update->execute([$email['id']]);

            error_log("Email sent to " . $email['recipient_email']);
        } catch (Exception $e) {
            error_log("Failed to send email to " . $email['recipient_email'] . ": " . $e->getMessage());
            // Optionally, implement retry count or error flagging here
        }
    }
} catch (Exception $e) {
    error_log("Error processing email queue: " . $e->getMessage());
}
?>
