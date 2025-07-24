<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class ActivitiesController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;
        // Rol kontrolü genel olarak her metodun başında veya burada merkezi olarak yapılabilir.
        // Şimdilik metodların içinde bırakıyorum, ama isterseniz __construct'a taşıyabiliriz.
    }

    public function index()
    {
        $sql = "SELECT a.*, u.name as creator_name, ac.name as category_name
                FROM activities a 
                JOIN users u ON a.creator_id = u.id
                LEFT JOIN activity_categories ac ON a.category_id = ac.id";
        $params = [];

        // Rol bazlı filtreleme (Öğretmen sadece kendi oluşturduklarını ve onaylanmışları görür gibi)
        if (($this->currentUser['role'] ?? 'guest') === 'teacher') {
            $sql .= " WHERE a.creator_id = ? OR a.status = 'approved'";
            $params[] = $this->currentUser['id'];
        }
        $sql .= " ORDER BY a.activity_date DESC";
        
        $activities = $this->db->select($sql, $params);
        
        return [
            'activities' => $activities, 
            'userRole' => $this->currentUser['role'] ?? 'guest', // View'da rol bazlı gösterim için
            'currentUserId' => $this->currentUser['id'] ?? 0 // View'da yetki kontrolü için
        ];
    }

    public function create()
    {
        if (!in_array($this->currentUser['role'] ?? 'guest', ['admin', 'teacher'])) {
            die("Yetkiniz yok.");
        }
        $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        $all_categories = $this->db->select("SELECT id, name FROM activity_categories ORDER BY name ASC");

        return [
            'activity' => null,
            'all_classes' => $all_classes,
            'selected_class_ids' => [],
            'all_categories' => $all_categories,
            'isEdit' => false,
            'formAction' => 'index.php?module=activities&action=store'
        ];
    }

    public function store()
    {
        if (!in_array($this->currentUser['role'] ?? 'guest', ['admin', 'teacher'])) {
            log_activity('STORE_DENIED', 'Activities', null, 'Yetkisiz etkinlik oluşturma denemesi.');
            die("Yetkiniz yok.");
        }

        $creator_id = $this->currentUser['id'];
        $status = ($this->currentUser['role'] === 'admin') ? 'approved' : 'pending';

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $activity_date = $_POST['activity_date'] ?? '';
        $location = $_POST['location'] ?? '';
        $include_parents = isset($_POST['include_parents']) ? 1 : 0;
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $selected_class_ids = $_POST['class_ids'] ?? [];

        if (empty($title) || empty($activity_date)) {
            redirect('index.php?module=activities&action=create&error=empty_fields');
            exit;
        }

        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
            $target_dir = __DIR__ . "/../../uploads/activities/";
            if (!is_dir($target_dir)) { @mkdir($target_dir, 0775, true); } // Hata kontrolü eklendi @ ile
            $filename = uniqid() . '-' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/activities/" . $filename;
            }
        }

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO activities (creator_id, title, description, activity_date, location, image_path, status, include_parents, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $creator_id, $title, $description, $activity_date, $location,
            $image_path, $status, $include_parents, $category_id
        ]);
        $activity_id = $this->db->getConnection()->lastInsertId();

        if ($activity_id && !empty($selected_class_ids)) {
            $class_stmt = $this->db->getConnection()->prepare(
                "INSERT INTO activity_classes (activity_id, class_id) VALUES (?, ?)"
            );
            foreach ($selected_class_ids as $class_id) {
                if(!empty($class_id)){
                    $class_stmt->execute([$activity_id, (int)$class_id]);
                }
            }
        }
        
        log_activity('CREATE', 'Activities', $activity_id, "Etkinlik oluşturdu: '$title'");
        redirect('index.php?module=activities&action=index&status_message=created');
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $activity = $this->db->select("SELECT * FROM activities WHERE id = ?", [$id])[0] ?? null;

        if (!$activity) {
            redirect('index.php?module=activities&action=index&error_message=not_found');
            exit;
        }
        if (!in_array($this->currentUser['role'], ['admin', 'teacher']) || ($this->currentUser['role'] === 'teacher' && $activity['creator_id'] != $this->currentUser['id'])) {
             log_activity('EDIT_DENIED', 'Activities', $id, 'Yetkisiz düzenleme denemesi');
             die("Bu etkinliği düzenleme yetkiniz yok.");
        }

        $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        $selected_classes_raw = $this->db->select("SELECT class_id FROM activity_classes WHERE activity_id = ?", [$id]);
        $selected_class_ids = array_column($selected_classes_raw, 'class_id');
        $all_categories = $this->db->select("SELECT id, name FROM activity_categories ORDER BY name ASC");

        return [
            'activity' => $activity,
            'all_classes' => $all_classes,
            'selected_class_ids' => $selected_class_ids,
            'all_categories' => $all_categories,
            'isEdit' => true,
            'formAction' => 'index.php?module=activities&action=update&id=' . $id,
            'userRole' => $this->currentUser['role'] // Adminin status değiştirebilmesi için
        ];
    }

    public function update()
    {
        $id = $_POST['id'] ?? 0; // Formdan hidden id almak daha güvenli
        if (!$id) {
            $id = $_GET['id'] ?? 0; // GET'ten de kontrol edelim
        }

        $activity_check = $this->db->select("SELECT creator_id, status, image_path FROM activities WHERE id = ?", [$id])[0] ?? null;

        if (!$id || !$activity_check) {
             redirect('index.php?module=activities&action=index&error_message=not_found_on_update');
             exit;
        }
        if (!in_array($this->currentUser['role'], ['admin', 'teacher']) || ($this->currentUser['role'] === 'teacher' && $activity_check['creator_id'] != $this->currentUser['id'])) {
             log_activity('UPDATE_DENIED', 'Activities', $id, 'Yetkisiz güncelleme denemesi');
             die("Bu etkinliği güncelleme yetkiniz yok.");
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $activity_date = $_POST['activity_date'] ?? '';
        $location = $_POST['location'] ?? '';
        $include_parents = isset($_POST['include_parents']) ? 1 : 0;
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $selected_class_ids = $_POST['class_ids'] ?? [];
        
        $status = $activity_check['status']; // Mevcut statusu koru
        if ($this->currentUser['role'] === 'admin' && isset($_POST['status']) && in_array($_POST['status'], ['pending', 'approved', 'rejected'])) {
            $status = $_POST['status'];
        } elseif ($this->currentUser['role'] === 'teacher' && $activity_check['creator_id'] == $this->currentUser['id']) {
             // Öğretmen kendi etkinliğini düzenlediğinde tekrar onaya düşsün (iş mantığına göre değişebilir)
            $status = 'pending';
        }

        if (empty($title) || empty($activity_date)) {
            redirect('index.php?module=activities&action=edit&id=' . $id . '&error_message=empty_fields');
            exit;
        }

        $image_path = $activity_check['image_path'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
            if ($image_path && file_exists(__DIR__ . "/../../" . $image_path)) {
                @unlink(__DIR__ . "/../../" . $image_path);
            }
            $target_dir = __DIR__ . "/../../uploads/activities/";
            if (!is_dir($target_dir)) { @mkdir($target_dir, 0775, true); }
            $filename = uniqid() . '-' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/activities/" . $filename;
            }
        }

        $stmt = $this->db->getConnection()->prepare(
            "UPDATE activities SET title=?, description=?, activity_date=?, location=?, image_path=?, status=?, include_parents=?, category_id=? WHERE id=?"
        );
        $stmt->execute([
            $title, $description, $activity_date, $location, $image_path, 
            $status, $include_parents, $category_id, $id
        ]);

        $this->db->getConnection()->prepare("DELETE FROM activity_classes WHERE activity_id = ?")->execute([$id]);
        if (!empty($selected_class_ids)) {
            $class_stmt = $this->db->getConnection()->prepare(
                "INSERT INTO activity_classes (activity_id, class_id) VALUES (?, ?)"
            );
            foreach ($selected_class_ids as $class_id) {
                 if(!empty($class_id)){
                    $class_stmt->execute([$id, (int)$class_id]);
                }
            }
        }

        log_activity('UPDATE', 'Activities', $id, "Etkinliği güncelledi: '$title'");
        redirect('index.php?module=activities&action=index&status_message=updated');
    }
    
    public function approve()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            log_activity('APPROVE_DENIED', 'Activities', ($_GET['id'] ?? 0), 'Yetkisiz onay denemesi');
            die("Yetkiniz yok.");
        }
        
        $activity_id = $_GET['id'] ?? 0;
        $activity = $this->db->select("SELECT * FROM activities WHERE id = ?", [$activity_id])[0] ?? null;
        if (!$activity) {
             redirect('index.php?module=activities&action=index&error_message=not_found');
             exit;
        }

        $this->db->getConnection()->prepare("UPDATE activities SET status='approved' WHERE id=?")->execute([$activity_id]);
        $log_message = "Etkinliği (ID: {$activity_id}, Başlık: {$activity['title']}) onayladı.";

        // Formdan gelen checkbox değerlerini kontrol et
        $send_notification = $_POST['send_notification'] ?? null; // Onay formundan gelir
        $add_to_announcements = $_POST['add_to_announcements'] ?? null; // Onay formundan gelir

        if ($send_notification === '1') {
            // TODO: Bildirim gönderme mantığı buraya
            // Örnek: notifications_controller üzerinden bir metod çağrılabilir
            // Veya doğrudan notifications tablosuna kayıt eklenebilir.
            // Hedef kitle: activity_classes'dan öğrenciler + include_parents ise veliler
            log_activity('NOTIFICATION_SENT_PENDING', 'Activities', $activity_id, "Onaylanan etkinlik için bildirim gönderme işaretlendi (henüz gönderilmedi).");
            $log_message .= " Bildirim gönderilmesi istendi.";
        }
        if ($add_to_announcements === '1') {
            // TODO: Duyurulara ekleme mantığı buraya
            // Örnek: announcements_controller üzerinden bir metod çağrılabilir
            // Veya doğrudan announcements tablosuna kayıt eklenebilir.
            log_activity('ANNOUNCEMENT_ADD_PENDING', 'Activities', $activity_id, "Onaylanan etkinlik için duyurulara ekleme işaretlendi (henüz eklenmedi).");
            $log_message .= " Duyurulara eklenmesi istendi.";
        }
        log_activity('APPROVE', 'Activities', $activity_id, $log_message);
        redirect('index.php?module=activities&action=index&status_message=approved');
    }
    
    public function reject()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
             log_activity('REJECT_DENIED', 'Activities', ($_GET['id'] ?? 0), 'Yetkisiz red denemesi');
            die("Yetkiniz yok.");
        }
        $activity_id = $_GET['id'] ?? 0;
        // İsteğe bağlı: Reddetme notu alınabilir
        // $reject_reason = $_POST['reject_reason'] ?? '';
        $this->db->getConnection()->prepare("UPDATE activities SET status='rejected' WHERE id=?")->execute([$activity_id]);
        log_activity('REJECT', 'Activities', $activity_id, "Etkinliği (ID: {$activity_id}) reddetti.");
        redirect('index.php?module=activities&action=index&status_message=rejected');
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        if(!$id) {
            redirect('index.php?module=activities&action=index&error_message=missing_id');
            exit;
        }
        $activity = $this->db->select("SELECT creator_id, title, image_path FROM activities WHERE id = ?", [$id])[0] ?? null;
        if (!$activity) {
            redirect('index.php?module=activities&action=index&error_message=not_found');
            exit;
        }
        if (($this->currentUser['role'] ?? 'guest') !== 'admin' && $activity['creator_id'] != $this->currentUser['id']) {
             log_activity('DELETE_DENIED', 'Activities', $id, 'Yetkisiz silme denemesi');
             die("Bu etkinliği silme yetkiniz yok.");
        }
        
        if (!empty($activity['image_path']) && file_exists(__DIR__ . "/../../" . $activity['image_path'])) {
            @unlink(__DIR__ . "/../../" . $activity['image_path']);
        }

        $this->db->getConnection()->prepare("DELETE FROM activities WHERE id = ?")->execute([$id]);
        // activity_classes tablosundaki ilişkili kayıtlar ON DELETE CASCADE ile otomatik silinir.
        log_activity('DELETE', 'Activities', $id, "Etkinliği sildi: '{$activity['title']}'");
        redirect('index.php?module=activities&action=index&status_message=deleted');
    }

    public function get_calendar_events()
    {
        header('Content-Type: application/json');
        $sql = "SELECT a.id, a.title, a.activity_date as start, 
                       cat.name as category_name,
                       CASE 
                           WHEN a.category_id = 1 THEN '#3a87ad' 
                           WHEN a.category_id = 2 THEN '#c09853' 
                           ELSE '#337ab7'        
                       END as color,
                       'index.php?module=activities&action=edit&id=' || a.id as url -- Detay/Düzenleme sayfası için
                FROM activities a
                LEFT JOIN activity_categories cat ON a.category_id = cat.id
                WHERE a.status = 'approved'";
        
        $events_raw = $this->db->select($sql);
        $events = [];

        if (!empty($events_raw)) {
            foreach ($events_raw as $event_item) {
                $display_title = $event_item['title'];
                if (!empty($event_item['category_name'])) {
                    $display_title .= ' (' . e($event_item['category_name']) . ')';
                }
                $events[] = [
                    'id' => $event_item['id'],
                    'title' => $display_title,
                    'start' => $event_item['start'],
                    'url' => 'index.php?module=activities&action=edit&id=' . $event_item['id'], 
                    'color' => $event_item['color'],
                    'borderColor' => $event_item['color']
                ];
            }
        }
        echo json_encode($events);
        exit;
    }

     public function calendar()
    {
        // FullCalendar için gerekli dosyaları ana şablona (layout.php) ekletiyoruz.
        $extraHead = '
            <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
        ';
        $extraFoot = '
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/tr.js"></script>
        ';
        
        // Bu verileri layout.php'de kullanılması için döndürüyoruz.
        return [
            'pageTitle' => 'Etkinlik Takvimi',
            'extraHead' => $extraHead,
            'extraFoot' => $extraFoot
        ];
    }
    
    /**
     * FullCalendar'ın 'events' parametresi için veri çeker ve JSON olarak döndürür.
     */
    public function calendar_data()
    {
        // Bu metod sadece JSON veri döndüreceği için layout kullanmaz.
        header('Content-Type: application/json');
        
        $sql = "SELECT 
                    a.id, 
                    a.title, 
                    a.activity_date as start,
                    a.description,
                    cat.name as category_name,
                    CASE 
                       WHEN a.category_id = 1 THEN '#007bff' -- Mavi
                       WHEN a.category_id = 2 THEN '#ffc107' -- Sarı
                       WHEN a.category_id = 3 THEN '#28a745' -- Yeşil
                       ELSE '#6c757d' -- Gri       
                    END as color
                FROM activities a
                LEFT JOIN activity_categories cat ON a.category_id = cat.id
                WHERE a.status = 'approved'";
        
        $events_raw = $this->db->select($sql);
        
        $events = [];
        if (!empty($events_raw)) {
            foreach ($events_raw as $event_item) {
                $events[] = [
                    'id'        => $event_item['id'],
                    'title'     => $event_item['title'],
                    'start'     => $event_item['start'],
                    'url'       => 'index.php?module=activities&action=edit&id=' . $event_item['id'], // Tıklayınca düzenleme sayfasına gider.
                    'color'     => $event_item['color'],
                    'borderColor' => $event_item['color'],
                    // extendedProps, eventClick içinde ek bilgiye ulaşmak için kullanılır.
                    'extendedProps' => [
                        'description' => $event_item['description'] ?? 'Açıklama yok.',
                        'category' => $event_item['category_name'] ?? 'Genel'
                    ]
                ];
            }
        }
        
        echo json_encode($events);
        exit; // JSON bastıktan sonra script'i sonlandır.
    }
}