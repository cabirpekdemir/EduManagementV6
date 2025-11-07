<?php
// core/view_as_helper.php
/**
 * View As Helper - Admin başka kullanıcı olarak sistemi görüntüleyebilir
 */
class ViewAsHelper {
    
    /**
     * Kullanıcı olarak görüntülemeyi başlat
     * 
     * @param int $targetUserId Hedef kullanıcının ID'si
     * @return bool Başarılı mı?
     */
    public static function startViewAs($targetUserId) {
        if (!isset($_SESSION['user'])) {
            return false;
        }
        
        // Sadece admin yapabilir
        $currentUser = $_SESSION['original_user'] ?? $_SESSION['user'];
        if ($currentUser['role'] !== 'admin') {
            return false;
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            
            // Hedef kullanıcıyı bul
            $stmt = $db->prepare("
                SELECT id, name, email, role, phone, 
                       CASE 
                           WHEN role = 'student' THEN student_number
                           ELSE NULL
                       END as student_number
                FROM users 
                WHERE id = ? AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$targetUserId]);
            $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$targetUser) {
                return false;
            }
            
            // Orijinal kullanıcıyı sakla (ilk kez ise)
            if (!isset($_SESSION['original_user'])) {
                $_SESSION['original_user'] = $_SESSION['user'];
            }
            
            // Hedef kullanıcı olarak oturum aç
            $_SESSION['user'] = $targetUser;
            $_SESSION['viewing_as'] = true;
            
            return true;
            
        } catch (PDOException $e) {
            error_log('ViewAsHelper::startViewAs error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Normal görünüme dön
     * 
     * @return bool Başarılı mı?
     */
    public static function exitViewAs() {
        if (!isset($_SESSION['original_user'])) {
            return false;
        }
        
        // Orijinal kullanıcıya geri dön
        $_SESSION['user'] = $_SESSION['original_user'];
        
        // View as bilgilerini temizle
        unset($_SESSION['original_user']);
        unset($_SESSION['viewing_as']);
        
        return true;
    }
    
    /**
     * Şu anda view as modunda mı?
     * 
     * @return bool View as aktif mi?
     */
    public static function isViewingAs() {
        return isset($_SESSION['viewing_as']) && $_SESSION['viewing_as'] === true;
    }
    
    /**
     * Şu anda view as modunda mı? (Alias metod)
     * Geriye dönük uyumluluk için isViewingAs() ile aynı
     * 
     * @return bool View as aktif mi?
     */
    public static function isViewAsMode() {
        return self::isViewingAs();
    }
    
    /**
     * View as yapılan kullanıcının adını al
     * 
     * @return string|null Kullanıcı adı veya null
     */
    public static function getViewAsUserName() {
        if (!self::isViewingAs()) {
            return null;
        }
        
        return $_SESSION['user']['name'] ?? null;
    }
    
    /**
     * Orijinal admin kullanıcının adını al
     * 
     * @return string|null Admin adı veya null
     */
    public static function getOriginalUserName() {
        if (!self::isViewingAs()) {
            return null;
        }
        
        return $_SESSION['original_user']['name'] ?? null;
    }
    
    /**
     * Orijinal admin kullanıcının adını al (Alias metod)
     * Geriye dönük uyumluluk için getOriginalUserName() ile aynı
     * 
     * @return string|null Admin adı veya null
     */
    public static function getOriginalAdminName() {
        return self::getOriginalUserName();
    }
    
    /**
     * View as yapılan kullanıcının rolünü al
     * 
     * @return string|null Rol veya null
     */
    public static function getViewAsUserRole() {
        if (!self::isViewingAs()) {
            return null;
        }
        
        return $_SESSION['user']['role'] ?? null;
    }
}