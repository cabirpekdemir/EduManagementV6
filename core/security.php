<?php
// core/security.php

class Security {
    
    private static $db;
    
    public static function init() {
        self::$db = Database::getInstance()->getConnection();
    }
    
    /**
     * Güçlü şifre üret
     */
    public static function generateStrongPassword($length = 12) {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $numbers = '23456789';
        $special = '!@#$%^&*';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        $all = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }
        
        return str_shuffle($password);
    }
    
    /**
     * Şifre gücü kontrolü
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Şifre en az 8 karakter olmalıdır.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Şifre en az 1 büyük harf içermelidir.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Şifre en az 1 küçük harf içermelidir.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Şifre en az 1 rakam içermelidir.';
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Şifre en az 1 özel karakter içermelidir.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Şifre geçmişi kontrolü
     */
    public static function isPasswordInHistory($userId, $newPassword) {
        $stmt = self::$db->prepare("
            SELECT password_hash FROM password_history 
            WHERE user_id = ? ORDER BY created_at DESC LIMIT 3
        ");
        $stmt->execute([$userId]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($newPassword, $row['password_hash'])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Şifre geçmişine kaydet
     */
    public static function savePasswordHistory($userId, $passwordHash) {
        $stmt = self::$db->prepare("
            INSERT INTO password_history (user_id, password_hash) VALUES (?, ?)
        ");
        $stmt->execute([$userId, $passwordHash]);
    }
    
    /**
     * Token üret
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * E-posta gönder (basit versiyon)
     */
 public static function sendEmail($to, $templateKey, $variables) {
    // Template'i al
    $stmt = self::$db->prepare("
        SELECT subject, body FROM email_templates WHERE template_key = ?
    ");
    $stmt->execute([$templateKey]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$template) {
        return false;
    }
    
    // Değişkenleri değiştir
    $subject = $template['subject'];
    $body = $template['body'];
    
    foreach ($variables as $key => $value) {
        $subject = str_replace('{{' . $key . '}}', $value, $subject);
        $body = str_replace('{{' . $key . '}}', $value, $body);
    }
    
    // Mailer sınıfını kullan
    require_once __DIR__ . '/mailer.php';
    return Mailer::send($to, $subject, $body, false);
}
}

Security::init();