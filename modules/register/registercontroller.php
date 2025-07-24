<?php
// PHPMailer sınıflarını dahil et
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../core/PHPMailer/Exception.php';
require_once __DIR__ . '/../../core/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../core/PHPMailer/SMTP.php';

class RegisterController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Kayıt formunu gösterir
    public function index() {
        require_once __DIR__ . '/view/index.php';
    }

    // Kayıt formundan gelen verileri işler
   public function store() {
    // Form verilerini al
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $tc_kimlik = $_POST['tc_kimlik'] ?? ''; // TC Kimlik No eklendi
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Doğrulamalar (TC Kimlik No eklendi)
    if (empty($name) || empty($email) || empty($password) || empty($tc_kimlik)) {
        redirect('index.php?module=register&action=index&error_message=Tüm alanlar zorunludur.');
    }
    if ($password !== $password_confirm) {
        redirect('index.php?module=register&action=index&error_message=Şifreler uyuşmuyor.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('index.php?module=register&action=index&error_message=Geçerli bir e-posta adresi girin.');
    }

    // E-posta veya TC zaten var mı diye kontrol et
    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? OR tc_kimlik = ?");
    $stmt->execute([$email, $tc_kimlik]);
    if ($stmt->fetch()) {
        redirect('index.php?module=register&action=index&error_message=Bu e-posta veya TC Kimlik Numarası zaten kayıtlı.');
    }

    // Güvenli verileri hazırla
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(32));
    $token_expires_at = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

    // === SQL SORGUSU GÜNCELLENDİ: tc_kimlik sütunu eklendi ===
    $sql = "INSERT INTO users (name, email, tc_kimlik, password, role_id, is_active, verification_token, token_expires_at) VALUES (?, ?, ?, ?, ?, 0, ?, ?)";
    $params = [$name, $email, $tc_kimlik, $hashed_password, 3, $verification_token, $token_expires_at];
    
    try {
        $this->db->prepare($sql)->execute($params);
    } catch (PDOException $e) {
        // Bu catch bloğu, yukarıdaki ön kontrole rağmen oluşabilecek race condition gibi durumlar için bir güvencedir.
        if ($e->errorInfo[1] == 1062) {
             redirect('index.php?module=register&action=index&error_message=Bu e-posta veya TC Kimlik Numarası zaten kayıtlı.');
        }
        redirect('index.php?module=register&action=index&error_message=Veritabanı hatası oluştu. Lütfen tekrar deneyin.');
    }

    // Onay e-postası gönderme mantığı burada devam ediyor...
    $mail = new PHPMailer(true);
    // ... (e-posta gönderme kodunun geri kalanı aynı) ...
    // ...
    try {
        // --- SMTP AYARLARI ---
        $mail->isSMTP();
        $mail->Host       = 'mail.hipotezegitim.com.tr';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bilgi@hipotezegitim.com.tr';
        $mail->Password   = '^R^Wg%oR*2?MyE^S';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';
        
        $verification_link = BASE_URL . "index.php?module=register&action=verify&token=" . $verification_token;
        $mail->setFrom('no-reply@example.com', 'EduManagement Sistemi');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Hesabınızı Onaylayın - EduManagement';
        $mail->Body    = "Merhaba {$name},<br><br>Hesabınızı aktifleştirmek için lütfen aşağıdaki bağlantıya tıklayın:<br><a href='{$verification_link}'>Hesabımı Onayla</a>...";
        
        $mail->send();
        redirect('index.php?module=register&action=check_email');
    } catch (Exception $e) {
        redirect('index.php?module=register&action=index&error_message=Onay e-postası gönderilemedi. Lütfen daha sonra tekrar deneyin.');
    }
}

    // Kullanıcı e-postadaki linke tıkladığında çalışır
    public function verify() {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $this->showVerificationResult('Geçersiz onay linki.');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->showVerificationResult('Geçersiz veya daha önce kullanılmış bir onay linki.');
            return;
        }

        if (new DateTimeImmutable() > new DateTimeImmutable($user['token_expires_at'])) {
            $this->showVerificationResult('Onay linkinin süresi dolmuş. Lütfen şifremi unuttum özelliğini kullanın.');
            return;
        }
        
        // Kullanıcıyı aktifleştir ve token'ı temizle
        $sql = "UPDATE users SET is_active = 1, verification_token = NULL, token_expires_at = NULL WHERE id = ?";
        $this->db->prepare($sql)->execute([$user['id']]);

        redirect('index.php?module=login&action=index&status_message=Hesabınız başarıyla aktifleştirildi! Şimdi giriş yapabilirsiniz.');
    }
    
    // Onay sonrası bilgilendirme mesajı için
    public function check_email() {
        echo '<!DOCTYPE html><html lang="tr"><head><title>E-postanızı Kontrol Edin</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css"></head><body class="hold-transition login-page"><div class="login-box"><div class="card"><div class="card-body login-card-body"><p class="login-box-msg text-success">Kayıt Başarılı!</p><p>Lütfen e-posta kutunuzu kontrol edin ve size gönderdiğimiz onay linkine tıklayarak hesabınızı aktifleştirin.</p><a href="index.php" class="btn btn-primary btn-block">Ana Sayfaya Dön</a></div></div></div></body></html>';
    }

    private function showVerificationResult($message) {
         echo '<!DOCTYPE html><html lang="tr"><head><title>Onay Durumu</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css"></head><body class="hold-transition login-page"><div class="login-box"><div class="card"><div class="card-body login-card-body"><p class="login-box-msg">'.$message.'</p><a href="index.php?module=login&action=index" class="btn btn-primary btn-block">Giriş Sayfasına Dön</a></div></div></div></body></html>';
    }
}