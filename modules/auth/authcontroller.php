<?php
require_once __DIR__ . '/../../core/database.php';

class AuthController
{
    protected $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function verify()
    {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            return ['view' => 'verify_error', 'message' => 'Geçersiz doğrulama bağlantısı.'];
        }

        $rows = $this->db->select("
            SELECT id, email_verified_at, token_expires_at
              FROM users
             WHERE verification_token = ?
             LIMIT 1
        ", [$token]);

        if (!$rows) {
            return ['view' => 'verify_error', 'message' => 'Token bulunamadı veya zaten kullanılmış.'];
        }

        $u = $rows[0];

        if (!empty($u['email_verified_at'])) {
            return ['view' => 'verify_success', 'message' => 'E-posta zaten doğrulanmış.'];
        }

        if (!empty($u['token_expires_at']) && strtotime($u['token_expires_at']) < time()) {
            return ['view' => 'verify_error', 'message' => 'Doğrulama bağlantısının süresi dolmuş.'];
        }

        $this->db->execute("
            UPDATE users
               SET email_verified_at = NOW(),
                   verification_token = NULL,
                   token_expires_at = NULL
             WHERE verification_token = ?
        ", [$token]);

        return ['view' => 'verify_success', 'message' => 'E-posta başarıyla doğrulandı.'];
    }
}
