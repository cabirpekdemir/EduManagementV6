<?php
// core/mailer.php - PRODUCTION VERSİYONU

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer {
    
    public static function send($to, $subject, $body, $isHtml = false) {
        $mail = new PHPMailer(true);
        
        try {
            $mail->SMTPDebug = 0; // ⭐ DEBUG KAPALI (production için)
            
            $mail->isSMTP();
            $mail->Host = 'mail.hipotezegitim.com.tr';
            $mail->SMTPAuth = true;
            $mail->Username = 'noreply@hipotezegitim.com.tr';
            $mail->Password = '!~,GSt]adL2MwHy3'; // Senin şifren
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            $mail->setFrom('noreply@hipotezegitim.com.tr', 'Hipotez Eğitim');
            $mail->addAddress($to);
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}