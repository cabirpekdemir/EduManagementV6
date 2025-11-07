<?php
/**
 * WidgetLibrary - Widget Sistemi Yönetim Merkezi
 * ✅ Database Singleton pattern ile uyumlu
 * ✅ Cache sistemi dahil
 */

class WidgetLibrary {
    
    private $db;
    private $role;
    private $userId;
    private $widgets = [];
    
    private $widgetClasses = [
        'StatWidget' => 'StatWidget',
        'ActionWidget' => 'ActionWidget',
        'ListWidget' => 'ListWidget',
    ];
    
    /**
     * Constructor - Database bağlantısı kontrolü ile
     */
    public function __construct($db, $role, $userId) {
        // Database instance kontrolü
        if (!$db || !($db instanceof Database)) {
            throw new Exception("WidgetLibrary: Geçerli bir Database instance'ı gerekli!");
        }
        
        $this->db = $db;
        $this->role = $role;
        $this->userId = $userId;
    }
    
    /**
     * Rol için widget'ları yükle
     */
    public function loadWidgetsForRole() {
        // Cache'den kontrol et
        $cacheKey = "widgets_role_{$this->role}";
        $cached = $this->getCached($cacheKey);
        
        if ($cached !== null) {
            $this->widgets = json_decode($cached, true);
            return;
        }
        
        // Database'den çek
        $sql = "
            SELECT 
                w.*,
                p.can_view,
                p.can_refresh
            FROM dashboard_widgets w
            INNER JOIN dashboard_widget_permissions p ON w.id = p.widget_id
            WHERE p.role = ?
            AND w.is_active = 1
            ORDER BY w.sort_order ASC, w.id ASC
        ";
        
        $widgets = $this->db->select($sql, [$this->role]);
        
        if ($widgets) {
            $this->widgets = $widgets;
            // Cache'e kaydet (1 saat)
            $this->setCache($cacheKey, json_encode($widgets), 3600);
        }
    }
    
    /**
     * Tüm widget'ları render et
     */
    public function renderAll() {
        if (empty($this->widgets)) {
            return '<div class="col-12"><div class="alert alert-info">Bu rol için tanımlı widget bulunamadı.</div></div>';
        }
        
        $html = '';
        
        foreach ($this->widgets as $widget) {
            if ($widget['can_view']) {
                $html .= $this->renderWidget($widget['widget_key']);
            }
        }
        
        return $html;
    }
    
    /**
     * Tek bir widget render et
     */
    public function renderWidget($widgetKey) {
        // Widget tanımını bul
        $widget = null;
        foreach ($this->widgets as $w) {
            if ($w['widget_key'] === $widgetKey) {
                $widget = $w;
                break;
            }
        }
        
        if (!$widget) {
            return "<!-- Widget bulunamadı: {$widgetKey} -->";
        }
        
        // Cache kontrolü
        $cacheKey = "widget_{$widgetKey}_{$this->role}_{$this->userId}";
        $cached = $this->getCached($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        // Widget class'ını oluştur
        $widgetClass = $this->widgetClasses[$widget['widget_type']] ?? null;
        
        if (!$widgetClass || !class_exists($widgetClass)) {
            return "<!-- Widget class bulunamadı: {$widget['widget_type']} -->";
        }
        
        try {
            // Widget instance'ı oluştur
            $widgetInstance = new $widgetClass($this->db, $widget);
            
            // Data'yı çek
            $data = $this->fetchWidgetData($widget);
            
            // Render et
            $html = $widgetInstance->render($data);
            
            // Cache'e kaydet
            if ($widget['cache_duration'] > 0) {
                $this->setCache($cacheKey, $html, $widget['cache_duration']);
            }
            
            return $html;
            
        } catch (Exception $e) {
            error_log("Widget Render Error [{$widgetKey}]: " . $e->getMessage());
            return "<!-- Widget render hatası: {$widgetKey} -->";
        }
    }
    
    /**
     * Widget için data çek
     */
    private function fetchWidgetData($widget) {
        $config = json_decode($widget['config_json'], true) ?? [];
        $dataSource = $widget['data_source'] ?? null;
        
        if (!$dataSource) {
            return $config;
        }
        
        // Data source'a göre veri çek
        switch ($dataSource) {
            case 'students':
                return $this->fetchStudentData($config);
                
            case 'teachers':
                return $this->fetchTeacherData($config);
                
            case 'courses':
                return $this->fetchCourseData($config);
                
            case 'attendance':
                return $this->fetchAttendanceData($config);
                
            case 'announcements':
                return $this->fetchAnnouncementData($config);
                
            default:
                return $config;
        }
    }
    
    /**
     * Öğrenci verisi
     */
    private function fetchStudentData($config) {
        // Basit count - status kolonu kontrolü yok
        $sql = "SELECT COUNT(*) as total FROM students";
        $result = $this->db->fetch($sql);
        
        return array_merge($config, [
            'total_students' => $result['total'] ?? 0
        ]);
    }
    
    /**
     * Öğretmen verisi
     */
    private function fetchTeacherData($config) {
        // Basit count - status kolonu kontrolü yok
        $sql = "SELECT COUNT(*) as total FROM teachers";
        $result = $this->db->fetch($sql);
        
        return array_merge($config, [
            'total_teachers' => $result['total'] ?? 0
        ]);
    }
    
    /**
     * Ders verisi
     */
    private function fetchCourseData($config) {
        // Basit count
        $sql = "SELECT COUNT(*) as total FROM courses";
        $result = $this->db->fetch($sql);
        
        return array_merge($config, [
            'total_courses' => $result['total'] ?? 0
        ]);
    }
    
    /**
     * Devamsızlık verisi
     */
    private function fetchAttendanceData($config) {
        // Attendance tablosu varsa
        try {
            $sql = "SELECT COUNT(*) as total FROM attendance WHERE DATE(created_at) = CURDATE()";
            $result = $this->db->fetch($sql);
            
            return array_merge($config, [
                'today_total' => $result['total'] ?? 0,
                'today_present' => 0,
                'today_absent' => 0
            ]);
        } catch (Exception $e) {
            // Tablo yoksa veya hata varsa boş döndür
            return array_merge($config, [
                'today_total' => 0,
                'today_present' => 0,
                'today_absent' => 0
            ]);
        }
    }
    
    /**
     * Duyuru verisi
     */
    private function fetchAnnouncementData($config) {
        try {
            $sql = "
                SELECT *
                FROM announcements
                WHERE is_active = 1
                ORDER BY created_at DESC
                LIMIT 5
            ";
            
            $announcements = $this->db->select($sql);
            
            return array_merge($config, [
                'announcements' => $announcements ?? []
            ]);
        } catch (Exception $e) {
            // Tablo yoksa boş döndür
            return array_merge($config, [
                'announcements' => []
            ]);
        }
    }
    
    /**
     * İstatistikler (Admin için)
     */
    public function getStatistics() {
        $stats = [];
        
        // Toplam widget sayısı
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM dashboard_widgets");
        $stats['total_widgets'] = $result['total'] ?? 0;
        
        // Aktif widget sayısı
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM dashboard_widgets WHERE is_active = 1");
        $stats['active_widgets'] = $result['total'] ?? 0;
        
        // Cache sayısı
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM dashboard_widget_cache");
        $stats['cached_items'] = $result['total'] ?? 0;
        
        // Cache boyutu (MB)
        $result = $this->db->fetch("SELECT SUM(LENGTH(cache_value)) as total FROM dashboard_widget_cache");
        $stats['cache_size_mb'] = round(($result['total'] ?? 0) / 1024 / 1024, 2);
        
        return $stats;
    }
    
    // ============================================
    // CACHE YÖNETİMİ
    // ============================================
    
    /**
     * Cache'den oku
     */
    private function getCached($key) {
        $sql = "
            SELECT cache_value 
            FROM dashboard_widget_cache 
            WHERE cache_key = ? 
            AND expires_at > NOW()
        ";
        
        $result = $this->db->fetch($sql, [$key]);
        
        return $result ? $result['cache_value'] : null;
    }
    
    /**
     * Cache'e yaz
     */
    private function setCache($key, $value, $duration = 3600) {
        $expiresAt = date('Y-m-d H:i:s', time() + $duration);
        
        $sql = "
            INSERT INTO dashboard_widget_cache (cache_key, cache_value, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                cache_value = VALUES(cache_value),
                expires_at = VALUES(expires_at),
                created_at = NOW()
        ";
        
        try {
            $this->db->execute($sql, [$key, $value, $expiresAt]);
            return true;
        } catch (Exception $e) {
            error_log("Cache Set Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Süresi dolmuş cache'leri temizle
     */
    public function clearExpiredCache() {
        $sql = "DELETE FROM dashboard_widget_cache WHERE expires_at < NOW()";
        
        try {
            return $this->db->execute($sql);
        } catch (Exception $e) {
            error_log("Clear Expired Cache Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Tüm cache'i temizle (veya belirli bir rol için)
     */
    public function clearAllCache($role = null) {
        if ($role) {
            $sql = "DELETE FROM dashboard_widget_cache WHERE cache_key LIKE ?";
            $params = ["%_role_{$role}_%"];
        } else {
            $sql = "DELETE FROM dashboard_widget_cache";
            $params = [];
        }
        
        try {
            $this->db->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("Clear All Cache Error: " . $e->getMessage());
            return false;
        }
    }
}
