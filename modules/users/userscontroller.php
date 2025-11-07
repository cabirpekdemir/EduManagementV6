<?php
// modules/users/userscontroller.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/database.php';

class UsersController {
    
    private $db;
    
    public function __construct() {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: index.php?module=login');
            exit;
        }
        
        // Sadece admin erişebilir
        if ($_SESSION['user']['role'] != 'admin') {
            $_SESSION['flash_error'] = 'Bu sayfaya erişim yetkiniz yok.';
            header('Location: index.php?module=dashboard');
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Kullanıcı Listesi
     */
    public function index() {
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'pageTitle' => 'Tüm Kullanıcılar',
            'users' => $users,
            'search' => $search,
            'role' => $role
        ];
    }
    
    /**
     * Kullanıcı Detay Sayfası
     */
    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz kullanıcı ID.';
            header('Location: index.php?module=users&action=index');
            exit;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'Kullanıcı bulunamadı.';
            header('Location: index.php?module=users&action=index');
            exit;
        }
        
        return [
            'pageTitle' => 'Kullanıcı Detay',
            'user' => $user
        ];
    }
    
    /**
     * Kullanıcı Görüntüleme (show ile aynı)
     */
    public function view() {
        return $this->show();
    }
    
    /**
     * Kullanıcı Düzenleme Sayfası
     */
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz kullanıcı ID.';
            header('Location: index.php?module=users&action=index');
            exit;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'Kullanıcı bulunamadı.';
            header('Location: index.php?module=users&action=index');
            exit;
        }
        
        return [
            'pageTitle' => 'Kullanıcı Düzenle',
            'user' => $user
        ];
    }
    
    /**
     * Kullanıcı Güncelleme
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=users&action=index');
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        
        if ($id <= 0 || empty($name) || empty($email)) {
            $_SESSION['flash_error'] = 'Ad Soyad ve E-posta zorunludur.';
            header('Location: index.php?module=users&action=edit&id=' . $id);
            exit;
        }
        
        // E-posta benzersizlik kontrolü
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'Bu e-posta başka bir kullanıcıda kayıtlı.';
            header('Location: index.php?module=users&action=edit&id=' . $id);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $isActive, $id]);
            
            $_SESSION['flash_success'] = 'Kullanıcı başarıyla güncellendi.';
            header('Location: index.php?module=users&action=show&id=' . $id);
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Güncelleme sırasında hata: ' . $e->getMessage();
            header('Location: index.php?module=users&action=edit&id=' . $id);
            exit;
        }
    }
    
    /**
     * Kullanıcı Silme
     */
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz kullanıcı ID.';
            header('Location: index.php?module=users&action=index');
            exit;
        }
        
        // Kendi kendini silemesin
        if ($id == $_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Kendi hesabınızı silemezsiniz!';
            header('Location: index.php?module=users&action=index');
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['flash_success'] = 'Kullanıcı başarıyla silindi.';
            
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Silme sırasında hata: ' . $e->getMessage();
        }
        
        header('Location: index.php?module=users&action=index');
        exit;
    }
    
    /**
     * Kullanıcı Oluşturma Sayfası
     */
    public function create() {
        return [
            'pageTitle' => 'Yeni Kullanıcı Ekle',
            'user' => null
        ];
    }
    
/**
 * Kullanıcı Kaydetme
 */
public function store() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?module=users&action=create');
        exit;
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tc = preg_replace('/\D/', '', $_POST['tc_kimlik'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $password = trim($_POST['password'] ?? '');
    
    // VALIDATION
    if (empty($name) || empty($email) || empty($tc)) {
        $_SESSION['flash_error'] = 'Ad Soyad, E-posta ve TC Kimlik zorunludur.';
        header('Location: index.php?module=users&action=create');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Geçerli bir e-posta adresi giriniz.';
        header('Location: index.php?module=users&action=create');
        exit;
    }
    
    if (!preg_match('/^[1-9][0-9]{10}$/', $tc)) {
        $_SESSION['flash_error'] = 'TC Kimlik No 11 haneli olmalı ve 0 ile başlamamalıdır.';
        header('Location: index.php?module=users&action=create');
        exit;
    }
    
    // E-posta kontrolü
    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['flash_error'] = 'Bu e-posta zaten kayıtlı.';
        header('Location: index.php?module=users&action=create');
        exit;
    }
    
    // TC kontrolü
    $stmt = $this->db->prepare("SELECT id FROM users WHERE tc_kimlik = ?");
    $stmt->execute([$tc]);
    if ($stmt->fetch()) {
        $_SESSION['flash_error'] = 'Bu TC kimlik numarası zaten kayıtlı.';
        header('Location: index.php?module=users&action=create');
        exit;
    }
    
    // ⭐ Şifre oluştur
    if (empty($password)) {
        require_once __DIR__ . '/../../core/security.php';
        $tempPassword = Security::generateStrongPassword(12);
    } else {
        $tempPassword = $password;
    }
    
    $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
    
    try {
        // INSERT
        $stmt = $this->db->prepare("
            INSERT INTO users (
                name, email, tc_kimlik, password, phone, role, 
                is_active, email_verified, must_change_password, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 1, 0, 1, NOW())
        ");
        
        $stmt->execute([$name, $email, $tc, $hashedPassword, $phone, $role]);
        
        $newUserId = $this->db->lastInsertId();
        
        // ⭐ E-POSTA GÖNDER
        require_once __DIR__ . '/../../core/security.php';
        $emailSent = false;
        try {
            $emailSent = Security::sendEmail(
                $email,
                'first_login',
                [
                    'name' => $name,
                    'email' => $email,
                    'temp_password' => $tempPassword,
                    'login_link' => 'https://hipotezegitim.com.tr/edu/index.php?module=login',
                    'app_name' => 'Hipotez Eğitim'
                ]
            );
        } catch (Exception $e) {
            error_log("Mail gönderim hatası: " . $e->getMessage());
        }
        
        // ⭐ MESAJ GÖSTER
        if ($emailSent) {
            $_SESSION['flash_success'] = 'Kullanıcı oluşturuldu. ✅ Giriş bilgileri e-postaya gönderildi.';
        } else {
            // Mail gönderilemedi, şifreyi ekranda göster
            $_SESSION['flash_success'] = 'Kullanıcı oluşturuldu. Geçici şifre: <code style="background:#fff3cd; padding:5px 10px; font-weight:bold; color:#d32f2f;">' . $tempPassword . '</code> <small>(Bu şifreyi kullanıcıya manuel olarak iletin)</small>';
        }
        
        header('Location: index.php?module=users&action=show&id=' . $newUserId);
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Kayıt hatası: ' . $e->getMessage();
        header('Location: index.php?module=users&action=create');
        exit;
    }
}
    /**
     * Liste (index ile aynı - uyumluluk için)
     */
    public function list() {
        return $this->index();
    }
}