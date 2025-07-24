<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php'; // send_email() ve generate_token() için

class LoginController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        // Eğer zaten giriş yapmışsa, anasayfaya yönlendir
        if (isset($_SESSION['user']['id'])) {
            redirect('index.php?module=dashboard&action=index');
            exit;
        }
        return ['pageTitle' => 'Giriş'];
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                redirect('index.php?module=login&action=index&error_message=Lütfen e-posta ve şifrenizi girin.');
                exit;
            }

            $user = $this->db->select("SELECT * FROM users WHERE email = ?", [$email])[0] ?? null;

            if ($user && password_verify($password, $user['password'])) {
                // Giriş başarılı
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                log_activity('LOGIN_SUCCESS', 'Login', $user['id'], '');
                redirect('index.php?module=dashboard&action=index');
            } else {
                // Giriş başarısız
                log_activity('LOGIN_FAILED', 'Login', null, 'Yanlış e-posta veya şifre: ' . $email);
                redirect('index.php?module=login&action=index&error_message=Yanlış e-posta veya şifre.');
            }
        } else {
            redirect('index.php?module=login&action=index');
        }
        exit;
    }

    public function logout()
    {
        if (isset($_SESSION['user']['id'])) {
            log_activity('LOGOUT', 'Login', $_SESSION['user']['id'], 'Kullanıcı çıkış yaptı: ' . $_SESSION['user']['email']);
            session_destroy();
        }
        redirect('index.php?module=login&action=index');
        exit;
    }

    /**
     * Şifremi unuttum talebi formu.
     */
    public function forgot_password()
    {
        // Eğer giriş yapmışsa, anasayfaya yönlendir
        if (isset($_SESSION['user']['id'])) {
            redirect('index.php?module=dashboard&action=index');
            exit;
        }
        return ['pageTitle' => 'Şifremi Unuttum'];
    }

    /**
     * Şifre sıfırlama token'ı oluşturur ve e-posta gönderir.
     */
    public function send_reset_link()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=login&action=forgot_password');
            exit;
        }

        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            redirect('index.php?module=login&action=forgot_password&error_message=' . urlencode('Lütfen e-posta adresinizi girin.'));
            exit;
        }

        $user = $this->db->select("SELECT id, name, email FROM users WHERE email = ?", [$email])[0] ?? null;

        if (!$user) {
            redirect('index.php?module=login&action=forgot_password&status_message=' . urlencode('Eğer e-posta adresiniz sistemimizde kayıtlıysa, şifre sıfırlama bağlantısı gönderilmiştir.'));
            exit;
        }

        // Eski token'ları temizle (her kullanıcının sadece bir aktif token'ı olsun)
        $this->db->getConnection()->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

        // Yeni token oluştur
        $token = generate_token();
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // 1 saat geçerli

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$user['id'], $token, $expires_at]);

        // Sıfırlama linki oluştur
        $reset_link = BASE_URL . '/index.php?module=login&action=reset_password&token=' . $token; 

        // E-posta içeriği
        $subject = 'Şifre Sıfırlama Talebi';
        $message = "Merhaba {$user['name']},\n\n" .
                   "Şifrenizi sıfırlamak için aşağıdaki bağlantıyı kullanın:\n" .
                   "$reset_link\n\n" .
                   "Bu bağlantı 1 saat içinde geçerliliğini yitirecektir. Eğer bu talebi siz yapmadıysanız, bu e-postayı dikkate almayın.\n\n" .
                   "Saygılarımızla,\nSistem Yöneticisi";
        
        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-8'
        ];

        if (send_email($user['email'], $subject, $message, $headers)) {
            redirect('index.php?module=login&action=forgot_password&status_message=' . urlencode('Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.'));
        } else {
            error_log("Password Reset Email Failed to Send to {$user['email']}"); // Hata loguna yaz
            redirect('index.php?module=login&action=forgot_password&error_message=' . urlencode('Şifre sıfırlama bağlantısı gönderilemedi. Lütfen daha sonra tekrar deneyin.'));
        }
        exit;
    }

    /**
     * Yeni parola belirleme formunu gösterir ve token'ı doğrular.
     */
    public function reset_password()
    {
        // Eğer giriş yapmışsa, anasayfaya yönlendir
        if (isset($_SESSION['user']['id'])) {
            redirect('index.php?module=dashboard&action=index');
            exit;
        }

        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            redirect('index.php?module=login&action=forgot_password&error_message=' . urlencode('Geçersiz veya eksik şifre sıfırlama tokenı.'));
            exit;
        }

        $reset_record = $this->db->select(
            "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()",
            [$token]
        )[0] ?? null;

        if (!$reset_record) {
            redirect('index.php?module=login&action=forgot_password&error_message=' . urlencode('Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş.'));
            exit;
        }

        return [
            'token' => $token,
            'pageTitle' => 'Yeni Parola Belirle',
            'user_id' => $reset_record['user_id'] // Parola sıfırlama için user_id'yi de forma aktarıyoruz
        ];
    }

    /**
     * Yeni parolayı kaydeder.
     */
    public function save_reset_password()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=login&action=forgot_password');
            exit;
        }

        $token = $_POST['token'] ?? '';
        $user_id = (int)($_POST['user_id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($user_id) || empty($new_password) || empty($confirm_password)) {
            redirect('index.php?module=login&action=reset_password&token=' . $token . '&error_message=' . urlencode('Tüm alanlar zorunludur.'));
            exit;
        }
        if ($new_password !== $confirm_password) {
            redirect('index.php?module=login&action=reset_password&token=' . $token . '&error_message=' . urlencode('Parolalar eşleşmiyor.'));
            exit;
        }

        // Parola Güvenliği Kontrolü
        $user_for_validation = $this->db->select("SELECT name, email FROM users WHERE id = ?", [$user_id])[0] ?? null;
        if (!$user_for_validation) {
             redirect('index.php?module=login&action=forgot_password&error_message=' . urlencode('Kullanıcı bulunamadı.'));
             exit;
        }
        $password_errors = validate_password($new_password, $user_for_validation['email'], $user_for_validation['name']);
        if (!empty($password_errors)) {
            redirect('index.php?module=login&action=reset_password&token=' . $token . '&error_message=' . urlencode(implode("<br>", $password_errors)));
            exit;
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        try {
            // Parolayı güncelle
            $this->db->getConnection()->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed_password, $user_id]);

            // Token'ı sil (kullanıldıktan sonra tekrar kullanılmasın)
            $this->db->getConnection()->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user_id]);

            log_activity('PASSWORD_RESET', 'Login', $user_id, "Şifre sıfırlandı.");
            redirect('index.php?module=login&action=index&status_message=' . urlencode('Şifreniz başarıyla sıfırlandı. Yeni şifrenizle giriş yapabilirsiniz.'));
        } catch (PDOException $e) {
            error_log("Password Reset Save Error: " . $e->getMessage());
            redirect('index.php?module=login&action=reset_password&token=' . $token . '&error_message=' . urlencode('Şifre sıfırlanırken bir hata oluştu.'));
        }
        exit;
    }
}