<?php
// /modules/dashboard/dashboardcontroller.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/database.php';
//require_once __DIR__ . '/../models/AllWidgets.php';


class DashboardController {

    private $db;
    private $user;

    public function __construct() {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: /?module=login');
            exit;
        }

        $this->db = Database::getInstance()->getConnection();
        $this->user = $_SESSION['user'];
    }

    public function index() {
        $role = $this->user['role'];
        
        switch ($role) {
            case 'admin':
                $this->adminDashboard();
                break;
            case 'teacher':
                $this->teacherDashboard();
                break;
            case 'student':
                $this->studentDashboard();
                break;
            case 'parent':
                $this->parentDashboard();
                break;
            default:
                $this->renderErrorView("Yetkisiz Erişim", "Dashboard'a erişim yetkiniz bulunmamaktadır.");
                break;
        }
    }
    
    /**
     * Widget yönetim sayfası
     */
    public function manage() {
        // Sadece admin widget yönetebilir
        if ($this->user['role'] !== 'admin') {
            $this->renderErrorView("Yetkisiz Erişim", "Widget yönetimi sadece admin için.");
            return;
        }
        
        $pageTitle = "Widget Yönetimi";
        
        try {
            // Tüm widget'ları getir
            $stmt = $this->db->query("
                SELECT 
                    w.*,
                    GROUP_CONCAT(wc.role ORDER BY wc.role) as roles
                FROM dashboard_widgets w
                LEFT JOIN dashboard_widget_configs wc ON w.id = wc.widget_id
                GROUP BY w.id
                ORDER BY w.sort_order ASC
            ");
            $widgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->renderView('widget_manage', [
                'pageTitle' => $pageTitle,
                'widgets' => $widgets
            ]);
        } catch (PDOException $e) {
            error_log('manageWidgets error: ' . $e->getMessage());
            $this->renderErrorView("Hata", "Widget'lar yüklenirken hata oluştu.");
        }
    }
    
    /**
     * Widget aktif/pasif yap
     */
    public function toggleWidget() {
        if ($this->user['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        try {
            $widgetId = $_POST['widget_id'] ?? 0;
            
            $stmt = $this->db->prepare("UPDATE dashboard_widgets SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$widgetId]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
 
    
    /**
     * Widget sil
     */
    public function delete() {
        if ($this->user['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        try {
            $widgetId = $_POST['widget_id'] ?? 0;
            
            // Önce config'leri sil
            $this->db->prepare("DELETE FROM dashboard_widget_configs WHERE widget_id = ?")->execute([$widgetId]);
            
            // Sonra widget'ı sil
            $this->db->prepare("DELETE FROM dashboard_widgets WHERE id = ?")->execute([$widgetId]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Yeni widget oluştur
     */
    public function store() {
        if ($this->user['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        try {
            $title = $_POST['title'] ?? '';
            $type = $_POST['type'] ?? 'stat';
            $icon = $_POST['icon'] ?? 'fas fa-info';
            $color = $_POST['color'] ?? 'primary';
            $config = $_POST['config'] ?? '{}';
            $roles = $_POST['roles'] ?? [];
            
            // Son sırayı al
            $stmt = $this->db->query("SELECT MAX(sort_order) FROM dashboard_widgets");
            $maxOrder = $stmt->fetchColumn() ?? 0;
            
            // Widget ekle
            $stmt = $this->db->prepare("
                INSERT INTO dashboard_widgets (title, widget_type, icon, color, config, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$title, $type, $icon, $color, $config, $maxOrder + 1]);
            $widgetId = $this->db->lastInsertId();
            
            // Rolleri ekle
            $stmt = $this->db->prepare("INSERT INTO dashboard_widget_configs (widget_id, role, widget_order, is_enabled) VALUES (?, ?, 0, 1)");
            foreach ($roles as $role) {
                $stmt->execute([$widgetId, $role]);
            }
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Widget'ı güncelle
     */
    public function update() {
        if ($this->user['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        try {
            $widgetId = $_POST['widget_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $icon = $_POST['icon'] ?? 'fas fa-info';
            $color = $_POST['color'] ?? 'primary';
            $config = $_POST['config'] ?? '{}';
            
            // Widget'ı güncelle
            $stmt = $this->db->prepare("
                UPDATE dashboard_widgets 
                SET title = ?, icon = ?, color = ?, config = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $icon, $color, $config, $widgetId]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function adminDashboard() {
        $pageTitle = "Yönetim Paneli";
        $stats = [];
        $stats['student_count'] = $this->db->query("SELECT COUNT(id) FROM users WHERE role = 'student'")->fetchColumn();
        $stats['teacher_count'] = $this->db->query("SELECT COUNT(id) FROM users WHERE role = 'teacher'")->fetchColumn();
        $stats['parent_count'] = $this->db->query("SELECT COUNT(id) FROM users WHERE role = 'parent'")->fetchColumn();
        
        try {
            $stats['course_count'] = $this->db->query("SELECT COUNT(id) FROM courses")->fetchColumn();
        } catch (PDOException $e) {
            $stats['course_count'] = 0;
        }

        $appointmentData = $this->getAppointmentDataForStaff();
        $todayCourses = $this->getTodayCoursesForAdmin();
        
        // ⭐ WIDGET SİSTEMİ - YENİ EKLENEN!
        $widgets = $this->getWidgetsForRole('admin');
        
        $this->renderView('admin_dashboard', [
            'pageTitle' => $pageTitle, 
            'stats' => $stats, 
            'user' => $this->user,
            'appointmentData' => $appointmentData,
            'todayCourses' => $todayCourses,
            'widgets' => $widgets  // ⭐ YENİ EKLENEN!
        ]);
    }

    private function teacherDashboard() {
        $pageTitle = "Öğretmen Paneli";
        
        $appointmentData = $this->getAppointmentDataForStaff();
        $todayCourses = $this->getTodayCoursesForTeacher($this->user['id']);
        
        // ⭐ WIDGET SİSTEMİ - YENİ EKLENEN!
        $widgets = $this->getWidgetsForRole('teacher');
        
        $this->renderView('teacher_dashboard', [
            'pageTitle' => $pageTitle, 
            'user' => $this->user,
            'appointmentData' => $appointmentData,
            'todayCourses' => $todayCourses,
            'widgets' => $widgets  // ⭐ YENİ EKLENEN!
        ]);
    }

    private function studentDashboard() {
        $pageTitle = "Öğrenci Paneli";
        
        $appointmentData = $this->getAppointmentDataForStudent($this->user['id']);
        
        // ⭐ WIDGET SİSTEMİ - YENİ EKLENEN!
        $widgets = $this->getWidgetsForRole('student');
        
        $this->renderView('student_dashboard', [
            'pageTitle' => $pageTitle, 
            'user' => $this->user,
            'appointmentData' => $appointmentData,
            'widgets' => $widgets  // ⭐ YENİ EKLENEN!
        ]);
    }
    
    private function parentDashboard() {
        $pageTitle = "Veli Paneli";
        
        $appointmentData = $this->getAppointmentDataForParent($this->user['id']);
        
        // ⭐ WIDGET SİSTEMİ - YENİ EKLENEN!
        $widgets = $this->getWidgetsForRole('parent');
        
        $this->renderView('parent_dashboard', [
            'pageTitle' => $pageTitle, 
            'user' => $this->user,
            'appointmentData' => $appointmentData,
            'widgets' => $widgets  // ⭐ YENİ EKLENEN!
        ]);
    }

    /* ==================== WIDGET SİSTEMİ - YENİ METODLAR ==================== */

    /**
     * Widget datası çek
     */
    private function getWidgetData($widget) {
        $config = json_decode($widget['config'], true);
        
        if ($widget['widget_type'] === 'action') {
            // Aksiyon widget'ları için config'i döndür
            return $config;
        }
        
        if ($widget['widget_type'] === 'stat' && isset($config['query'])) {
            try {
                $query = $config['query'];
                
                // {user_id} kaç kere geçiyor?
                $userIdCount = substr_count($query, '{user_id}');
                
                // Tüm {user_id}'leri ? ile değiştir
                $query = str_replace('{user_id}', '?', $query);
                
                // Sorguyu çalıştır
                if ($userIdCount > 0) {
                    // Her {user_id} için aynı user id'yi gönder
                    $params = array_fill(0, $userIdCount, $this->user['id']);
                    $stmt = $this->db->prepare($query);
                    $stmt->execute($params);
                } else {
                    $stmt = $this->db->query($query);
                }
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return ['value' => $result['value'] ?? 0];
            } catch (PDOException $e) {
                error_log('Widget query error: ' . $e->getMessage());
                return ['value' => 0];
            }
        }
        
        if ($widget['widget_type'] === 'list' && isset($config['query'])) {
            try {
                $query = $config['query'];
                
                // {user_id} kaç kere geçiyor?
                $userIdCount = substr_count($query, '{user_id}');
                
                // Tüm {user_id}'leri ? ile değiştir
                $query = str_replace('{user_id}', '?', $query);
                
                // Sorguyu çalıştır
                if ($userIdCount > 0) {
                    // Her {user_id} için aynı user id'yi gönder
                    $params = array_fill(0, $userIdCount, $this->user['id']);
                    $stmt = $this->db->prepare($query);
                    $stmt->execute($params);
                } else {
                    $stmt = $this->db->query($query);
                }
                
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return ['items' => $items];
            } catch (PDOException $e) {
                error_log('Widget list query error: ' . $e->getMessage());
                return ['items' => []];
            }
        }
        
        return [];
    }

   
    private function getTodayCoursesForAdmin() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.id, 
                    c.name, 
                    c.category,
                    c.color,
                    u.name AS teacher_name,
                    (SELECT COUNT(DISTINCT s.id) 
                     FROM users s
                     INNER JOIN student_enrollments se ON s.id = se.student_id
                     WHERE se.course_id = c.id 
                     AND se.status = 'active'
                     AND s.role = 'student') AS student_count,
                    (SELECT COUNT(*) 
                     FROM lesson_attendance la 
                     WHERE la.course_id = c.id 
                     AND DATE(la.lesson_date) = CURDATE()) AS today_attendance_count
                FROM courses c
                LEFT JOIN users u ON c.teacher_id = u.id
                WHERE c.is_active = 1
                ORDER BY 
                    CASE WHEN (SELECT COUNT(*) FROM lesson_attendance la WHERE la.course_id = c.id AND DATE(la.lesson_date) = CURDATE()) = 0 
                    THEN 0 ELSE 1 END,
                    c.name ASC
                LIMIT 8
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Dashboard getTodayCoursesForAdmin error: ' . $e->getMessage());
            return [];
        }
    }

    private function getTodayCoursesForTeacher($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.id, 
                    c.name, 
                    c.category,
                    c.color,
                    u.name AS teacher_name,
                    (SELECT COUNT(DISTINCT s.id) 
                     FROM users s
                     INNER JOIN student_enrollments se ON s.id = se.student_id
                     WHERE se.course_id = c.id 
                     AND se.status = 'active'
                     AND s.role = 'student') AS student_count,
                    (SELECT COUNT(*) 
                     FROM lesson_attendance la 
                     WHERE la.course_id = c.id 
                     AND DATE(la.lesson_date) = CURDATE()) AS today_attendance_count
                FROM courses c
                LEFT JOIN users u ON c.teacher_id = u.id
                WHERE c.teacher_id = ? AND c.is_active = 1
                ORDER BY 
                    CASE WHEN (SELECT COUNT(*) FROM lesson_attendance la WHERE la.course_id = c.id AND DATE(la.lesson_date) = CURDATE()) = 0 
                    THEN 0 ELSE 1 END,
                    c.name ASC
                LIMIT 8
            ");
            $stmt->execute([$teacherId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Dashboard getTodayCoursesForTeacher error: ' . $e->getMessage());
            return [];
        }
    }

    private function getAppointmentDataForStaff() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM guidance_appointments WHERE status = 'pending'");
            $pendingCount = $stmt->fetchColumn();
            
            $stmt = $this->db->prepare("
                SELECT ga.*, u.name as student_name, u.phone
                FROM guidance_appointments ga
                JOIN users u ON ga.student_id = u.id
                WHERE ga.status = 'approved'
                AND ga.appointment_date = CURDATE()
                ORDER BY ga.appointment_time ASC
            ");
            $stmt->execute();
            $todayAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->prepare("
                SELECT ga.*, u.name as student_name, u.phone
                FROM guidance_appointments ga
                JOIN users u ON ga.student_id = u.id
                WHERE ga.status = 'approved'
                AND ga.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY ga.appointment_date ASC, ga.appointment_time ASC
                LIMIT 5
            ");
            $stmt->execute();
            $weeklyAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'pendingCount' => $pendingCount,
                'todayAppointments' => $todayAppointments,
                'weeklyAppointments' => $weeklyAppointments,
                'isStaff' => true
            ];
        } catch (PDOException $e) {
            error_log('Dashboard getAppointmentDataForStaff error: ' . $e->getMessage());
            return ['isStaff' => true, 'error' => true];
        }
    }

    private function getAppointmentDataForStudent($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT ga.*, c.name as counselor_name
                FROM guidance_appointments ga
                LEFT JOIN users c ON ga.counselor_id = c.id
                WHERE ga.student_id = ? 
                AND ga.status = 'pending'
                ORDER BY ga.created_at DESC
                LIMIT 3
            ");
            $stmt->execute([$studentId]);
            $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->prepare("
                SELECT ga.*, c.name as counselor_name
                FROM guidance_appointments ga
                LEFT JOIN users c ON ga.counselor_id = c.id
                WHERE ga.student_id = ? 
                AND ga.status = 'approved'
                AND ga.appointment_date >= CURDATE()
                ORDER BY ga.appointment_date ASC, ga.appointment_time ASC
                LIMIT 3
            ");
            $stmt->execute([$studentId]);
            $upcomingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'pendingRequests' => $pendingRequests,
                'upcomingAppointments' => $upcomingAppointments,
                'isStudent' => true
            ];
        } catch (PDOException $e) {
            error_log('Dashboard getAppointmentDataForStudent error: ' . $e->getMessage());
            return ['isStudent' => true, 'error' => true];
        }
    }

    private function getAppointmentDataForParent($parentId) {
        try {
            $stmt = $this->db->prepare("SELECT student_id FROM parents_students WHERE parent_id = ?");
            $stmt->execute([$parentId]);
            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($children)) {
                return ['isParent' => true, 'noChildren' => true];
            }
            
            $placeholders = implode(',', array_fill(0, count($children), '?'));
            
            $stmt = $this->db->prepare("
                SELECT ga.*, u.name as student_name, c.name as counselor_name
                FROM guidance_appointments ga
                JOIN users u ON ga.student_id = u.id
                LEFT JOIN users c ON ga.counselor_id = c.id
                WHERE ga.student_id IN ($placeholders)
                AND ga.status = 'pending'
                ORDER BY ga.created_at DESC
                LIMIT 3
            ");
            $stmt->execute($children);
            $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->prepare("
                SELECT ga.*, u.name as student_name, c.name as counselor_name
                FROM guidance_appointments ga
                JOIN users u ON ga.student_id = u.id
                LEFT JOIN users c ON ga.counselor_id = c.id
                WHERE ga.student_id IN ($placeholders)
                AND ga.status = 'approved'
                AND ga.appointment_date >= CURDATE()
                ORDER BY ga.appointment_date ASC, ga.appointment_time ASC
                LIMIT 3
            ");
            $stmt->execute($children);
            $upcomingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'pendingRequests' => $pendingRequests,
                'upcomingAppointments' => $upcomingAppointments,
                'isParent' => true
            ];
        } catch (PDOException $e) {
            error_log('Dashboard getAppointmentDataForParent error: ' . $e->getMessage());
            return ['isParent' => true, 'error' => true];
        }
    }

    private function renderView($viewName, $data = []) {
        extract($data);
        
        $viewPath = __DIR__ . "/view/{$viewName}.php";

        if (!file_exists($viewPath)) {
            $this->renderErrorView("Görünüm Dosyası Hatası", "İstenen görünüm dosyası bulunamadı: {$viewPath}");
            return;
        }

        ob_start();
        include $viewPath;
        $pageContent = ob_get_clean();

        require_once __DIR__ . '/../../themes/default/layout.php';
    }

    /**
     * Kullanıcının rolüne göre widget'ları getir
     */
    private function getWidgetsForRole($role) {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT w.*
                FROM dashboard_widgets w
                LEFT JOIN dashboard_widget_configs wc ON w.id = wc.widget_id
                WHERE w.is_active = 1
                AND (
                    wc.role = ? 
                    OR wc.role IS NULL
                    OR NOT EXISTS (
                        SELECT 1 FROM dashboard_widget_configs 
                        WHERE widget_id = w.id
                    )
                )
                ORDER BY w.sort_order ASC
            ");
            $stmt->execute([$role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('getWidgetsForRole error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Tek bir widget'ı render et
     */
    public function renderWidget($widget, $totalWidgets = null) {
        $config = json_decode($widget['config'], true);
        
        if ($widget['widget_type'] === 'stat') {
            return $this->renderStatWidget($widget, $config, $totalWidgets);
        } elseif ($widget['widget_type'] === 'action') {
            return $this->renderActionWidget($widget, $config, $totalWidgets);
        } elseif ($widget['widget_type'] === 'list') {
            return $this->renderListWidget($widget, $config, $totalWidgets);
        } elseif ($widget['widget_type'] === 'view_as') {
            return $this->renderViewAsWidget($widget, $config, $totalWidgets);
        }
        
        return '';
    }
    
    /**
     * Widget sayısına göre Bootstrap sütun class'ı belirle
     */
    private function getColumnClass($totalWidgets) {
        if ($totalWidgets === null || $totalWidgets > 4) {
            // Varsayılan: Responsive
            return 'col-lg-3 col-md-4 col-sm-6 col-12';
        }
        
        switch ($totalWidgets) {
            case 1:
                return 'col-12'; // Tam satır
            case 2:
                return 'col-md-6 col-12'; // %50 + %50
            case 3:
                return 'col-md-4 col-12'; // 1/3 + 1/3 + 1/3
            case 4:
                return 'col-lg-3 col-md-6 col-12'; // %25 × 4
            default:
                return 'col-lg-3 col-md-4 col-sm-6 col-12';
        }
    }
    
    /**
     * Widget için link al (sadece config'den)
     */
    private function getWidgetLink($title, $config) {
        // Config'de link varsa onu kullan
        if (isset($config['link']) && !empty($config['link'])) {
            return $config['link'];
        }
        
        // Link yoksa boş döndür (href="#" olacak)
        return '#';

    }
    
    /**
     * İstatistik widget'ı render et
     */
    private function renderStatWidget($widget, $config, $totalWidgets = null) {
        $value = 0;
        $hasError = false;
        
        if (isset($config['query']) && !empty($config['query'])) {
            try {
                $stmt = $this->db->query($config['query']);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $value = $result['value'] ?? 0;
            } catch (PDOException $e) {
                error_log('renderStatWidget query error: ' . $e->getMessage());
                error_log('Query was: ' . $config['query']);
                $value = 'Hata';
                $hasError = true;
            }
        }
        
        // Widget için link oluştur
        $link = $this->getWidgetLink($widget['title'] ?? '', $config);
        $hasLink = ($link && $link !== '#');
        
        // Dinamik sütun class'ı
        $colClass = $this->getColumnClass($totalWidgets);
        
        $html = '<div class="' . $colClass . '">';
        
        // Link varsa <a> ile sar, yoksa sade widget
        if ($hasLink) {
            $html .= '<a href="' . htmlspecialchars($link) . '" style="text-decoration: none; color: inherit;">';
        }
        
        $html .= '<div class="small-box bg-' . htmlspecialchars($widget['color'] ?? 'secondary') . '"';
        
        // Sadece link varsa hover efekti ve cursor ekle
        if ($hasLink) {
            $html .= ' style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'"';
        }
        
        $html .= '>';
        $html .= '<div class="inner">';
        $html .= '<h3>' . ($hasError ? 'Hata' : htmlspecialchars((string)$value)) . '</h3>';
        $html .= '<p>' . htmlspecialchars($widget['title'] ?? 'Widget') . '</p>';
        if ($hasError) {
            $html .= '<small class="text-muted">SQL sorgusu hatalı</small>';
        }
        $html .= '</div>';
        $html .= '<div class="icon">';
        $html .= '<i class="' . htmlspecialchars($widget['icon'] ?? 'fas fa-info') . '"></i>';
        $html .= '</div>';
        
        // Footer'ı sadece link varsa göster
        if ($hasLink) {
            $html .= '<div class="small-box-footer">';
            $html .= 'Detaylar <i class="fas fa-arrow-circle-right"></i>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        if ($hasLink) {
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Aksiyon widget'ı render et
     */
    private function renderActionWidget($widget, $config, $totalWidgets = null) {
        $link = $config['link'] ?? '#';
        $buttonText = $config['button_text'] ?? 'Tıkla';
        
        // Dinamik sütun class'ı
        $colClass = $this->getColumnClass($totalWidgets);
        
        $html = '<div class="' . $colClass . '">';
        $html .= '<div class="small-box bg-' . htmlspecialchars($widget['color']) . '" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">';
        $html .= '<div class="inner">';
        $html .= '<h3><i class="' . htmlspecialchars($widget['icon']) . '"></i></h3>';
        $html .= '<p>' . htmlspecialchars($widget['title']) . '</p>';
        $html .= '</div>';
        $html .= '<a href="' . htmlspecialchars($link) . '" class="small-box-footer">';
        $html .= htmlspecialchars($buttonText) . ' <i class="fas fa-arrow-circle-right"></i>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Liste widget'ı render et
     */
    private function renderListWidget($widget, $config, $totalWidgets = null) {
        $items = [];
        
        if (isset($config['query'])) {
            try {
                $stmt = $this->db->query($config['query']);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('renderListWidget query error: ' . $e->getMessage());
            }
        }
        
        // Widget için link oluştur
        $link = $this->getWidgetLink($widget['title'] ?? '', $config);
        $hasLink = ($link && $link !== '#');
        
        // Liste widget'ları için özel sizing
        // 1-2 widget varsa tam satır, yoksa yarım satır
        $colClass = ($totalWidgets !== null && $totalWidgets <= 2) 
            ? 'col-12'      // 1-2 widget: tam satır
            : 'col-lg-6 col-12';  // 3+ widget: yarım satır (responsive)
        
        $html = '<div class="' . $colClass . '">';
        
        // Sadece link varsa hover efekti ekle
        if ($hasLink) {
            $html .= '<div class="card" style="transition: transform 0.2s;" onmouseover="this.style.transform=\'scale(1.02)\'" onmouseout="this.style.transform=\'scale(1)\'">';
        } else {
            $html .= '<div class="card">';
        }
        
        $html .= '<div class="card-header">';
        $html .= '<h3 class="card-title"><i class="' . htmlspecialchars($widget['icon']) . '"></i> ' . htmlspecialchars($widget['title']) . '</h3>';
        $html .= '</div>';
        $html .= '<div class="card-body p-0">';
        
        if (!empty($items)) {
            $html .= '<ul class="list-group list-group-flush">';
            foreach ($items as $item) {
                $html .= '<li class="list-group-item">';
                // İlk sütunu göster
                $firstColumn = reset($item);
                $html .= htmlspecialchars($firstColumn);
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p class="text-muted text-center py-3">Veri bulunamadı.</p>';
        }
        
        $html .= '</div>';
        
        // Footer'ı sadece link varsa göster
        if ($hasLink) {
            $html .= '<div class="card-footer text-center">';
            $html .= '<a href="' . htmlspecialchars($link) . '" class="text-muted">';
            $html .= 'Tümünü Görüntüle <i class="fas fa-arrow-circle-right"></i>';
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * View As widget'ı render et (Small-Box formatında)
     */
    private function renderViewAsWidget($widget, $config, $totalWidgets = null) {
        // Sadece admin görebilir
        if ($this->user['role'] !== 'admin') {
            return '';
        }
        
        // Dinamik sütun class'ı
        $colClass = $this->getColumnClass($totalWidgets);
        
        // Unique ID oluştur
        $modalId = 'viewAsModal_' . $widget['id'];
        
        // Small-box formatında widget
        $html = '<div class="' . $colClass . '">';
        $html .= '<div class="small-box bg-' . htmlspecialchars($widget['color'] ?? 'warning') . '" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" data-toggle="modal" data-target="#' . $modalId . '">';
        $html .= '<div class="inner">';
        $html .= '<h3><i class="' . htmlspecialchars($widget['icon'] ?? 'fas fa-user-secret') . '"></i></h3>';
        $html .= '<p>' . htmlspecialchars($widget['title'] ?? 'Farklı Kullanıcı Olarak Görüntüle') . '</p>';
        $html .= '</div>';
        $html .= '<div class="icon">';
        $html .= '<i class="' . htmlspecialchars($widget['icon'] ?? 'fas fa-user-secret') . '"></i>';
        $html .= '</div>';
        $html .= '<div class="small-box-footer">';
        $html .= 'Tıklayın <i class="fas fa-arrow-circle-right"></i>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Modal
        $html .= '<div class="modal fade" id="' . $modalId . '" tabindex="-1">';
        $html .= '<div class="modal-dialog">';
        $html .= '<div class="modal-content">';
        
        // Modal Header
        $html .= '<div class="modal-header bg-warning">';
        $html .= '<h5 class="modal-title">';
        $html .= '<i class="' . htmlspecialchars($widget['icon'] ?? 'fas fa-user-secret') . '"></i> ';
        $html .= htmlspecialchars($widget['title'] ?? 'View As');
        $html .= '</h5>';
        $html .= '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>';
        $html .= '</div>';
        
        // Modal Body
        $html .= '<div class="modal-body">';
        $html .= '<div class="alert alert-info">';
        $html .= '<i class="fas fa-info-circle"></i> ';
        $html .= 'Başka bir kullanıcı gibi sistemi görüntüleyebilirsiniz.';
        $html .= '</div>';
        
        $html .= '<form action="index.php?module=view_as&action=start" method="POST" id="' . $modalId . '_form">';
        
        // Rol Seçimi
        $html .= '<div class="form-group">';
        $html .= '<label><i class="fas fa-user-tag"></i> Kullanıcı Rolü</label>';
        $html .= '<select class="form-control" name="role" id="' . $modalId . '_role" required>';
        $html .= '<option value="">Rol seçiniz...</option>';
        $html .= '<option value="teacher">Öğretmen</option>';
        $html .= '<option value="student">Öğrenci</option>';
        $html .= '<option value="parent">Veli</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        // Kullanıcı Seçimi
        $html .= '<div class="form-group">';
        $html .= '<label><i class="fas fa-user"></i> Kullanıcı</label>';
        $html .= '<select class="form-control" name="target_user_id" id="' . $modalId . '_user" required>';
        $html .= '<option value="">Önce rol seçiniz...</option>';
        $html .= '</select>';
        $html .= '<small class="form-text text-muted">';
        $html .= '<i class="fas fa-spinner fa-spin" id="' . $modalId . '_loading" style="display:none;"></i>';
        $html .= '<span id="' . $modalId . '_status"></span>';
        $html .= '</small>';
        $html .= '</div>';
        
        $html .= '</form>';
        $html .= '</div>';
        
        // Modal Footer
        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>';
        $html .= '<button type="submit" form="' . $modalId . '_form" class="btn btn-warning">';
        $html .= '<i class="fas fa-eye"></i> Görüntülemeye Başla';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // JavaScript
        $html .= '<script>
        $(document).ready(function() {
            // Rol değiştiğinde kullanıcıları yükle
            $("#' . $modalId . '_role").change(function() {
                const role = $(this).val();
                const userSelect = $("#' . $modalId . '_user");
                const loading = $("#' . $modalId . '_loading");
                const status = $("#' . $modalId . '_status");
                
                if (!role) {
                    userSelect.html("<option value=\"\">Önce rol seçiniz...</option>");
                    return;
                }
                
                loading.show();
                status.text("Yükleniyor...");
                userSelect.html("<option value=\"\">Yükleniyor...</option>").prop("disabled", true);
                
                $.ajax({
                    url: "modules/view_as/get_users_ajax.php",
                    method: "GET",
                    data: { role: role },
                    dataType: "json",
                    success: function(response) {
                        loading.hide();
                        status.text("");
                        
                        if (response.success && response.users.length > 0) {
                            let options = "<option value=\"\">Kullanıcı seçiniz...</option>";
                            response.users.forEach(function(user) {
                                let label = user.name + " (" + user.email + ")";
                                if (user.student_number) {
                                    label += " - " + user.student_number;
                                }
                                options += "<option value=\"" + user.id + "\">" + label + "</option>";
                            });
                            userSelect.html(options).prop("disabled", false);
                        } else {
                            userSelect.html("<option value=\"\">Kullanıcı bulunamadı</option>");
                            status.html("<span class=\"text-warning\">Bu rolde aktif kullanıcı yok</span>");
                        }
                    },
                    error: function() {
                        loading.hide();
                        status.html("<span class=\"text-danger\">Hata oluştu!</span>");
                        userSelect.html("<option value=\"\">Hata!</option>");
                    }
                });
            });
            
            // Form submit
            $("#' . $modalId . '_form").submit(function(e) {
                const userId = $("#' . $modalId . '_user").val();
                if (!userId) {
                    e.preventDefault();
                    alert("Lütfen bir kullanıcı seçin!");
                    return false;
                }
            });
        });
        </script>';
        
        return $html;
    }
    
    /**
     * Widget sırasını değiştir
     */
    public function moveWidget() {
        if ($this->user['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        try {
            $widgetId = $_POST['widget_id'] ?? 0;
            $direction = $_POST['direction'] ?? 'up';
            
            // Mevcut widget'ı al
            $stmt = $this->db->prepare("SELECT sort_order FROM dashboard_widgets WHERE id = ?");
            $stmt->execute([$widgetId]);
            $currentOrder = $stmt->fetchColumn();
            
            if ($direction === 'up') {
                // Yukarıdaki widget'ı bul
                $stmt = $this->db->prepare("SELECT id, sort_order FROM dashboard_widgets WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1");
                $stmt->execute([$currentOrder]);
            } else {
                // Aşağıdaki widget'ı bul
                $stmt = $this->db->prepare("SELECT id, sort_order FROM dashboard_widgets WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1");
                $stmt->execute([$currentOrder]);
            }
            
            $swapWidget = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($swapWidget) {
                // Sıraları değiştir
                $this->db->prepare("UPDATE dashboard_widgets SET sort_order = ? WHERE id = ?")->execute([$swapWidget['sort_order'], $widgetId]);
                $this->db->prepare("UPDATE dashboard_widgets SET sort_order = ? WHERE id = ?")->execute([$currentOrder, $swapWidget['id']]);
            }
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Widget rollerini güncelle
     */
    public function updateWidgetRoles() {
        if ($this->user['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        try {
            $widgetId = $_POST['widget_id'] ?? 0;
            $roles = $_POST['roles'] ?? [];
            
            // Önce mevcut rolleri sil
            $this->db->prepare("DELETE FROM dashboard_widget_configs WHERE widget_id = ?")->execute([$widgetId]);
            
            // Yeni rolleri ekle
            if (!empty($roles)) {
                $stmt = $this->db->prepare("INSERT INTO dashboard_widget_configs (widget_id, role, widget_order, is_enabled) VALUES (?, ?, 0, 1)");
                foreach ($roles as $role) {
                    $stmt->execute([$widgetId, $role]);
                }
            }
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function renderErrorView($title, $message) {
        $pageTitle = "Hata";
        ob_start();
        echo '<div class="alert alert-danger"><h4>' . htmlspecialchars($title) . '</h4><p>' . htmlspecialchars($message) . '</p></div>';
        $pageContent = ob_get_clean();
        require_once __DIR__ . '/../../themes/default/layout.php';
    }
}