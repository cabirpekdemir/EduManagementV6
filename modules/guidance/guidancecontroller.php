<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class GuidanceController
{
    protected $db;
    protected $currentUser;
    protected $userRole;
    protected $userId;

       public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
        
        // ⭐ SADECE ADMIN VE REHBER ERİŞEBİLİR
        $userRole = $_SESSION['user']['role'] ?? '';
        
        if (!in_array($userRole, ['admin', 'rehber'])) {
            $_SESSION['flash_error'] = 'Bu sayfaya erişim yetkiniz yok.';
            header('Location: index.php?module=dashboard');
            exit;
        }
    }
    

    /* ================== ROL/YARDIMCI FONKSİYONLAR (EKLENDİ) ================== */

    private function safeSelect(string $sql, array $params = []): array {
        try { return $this->db->select($sql, $params); } catch (\Throwable $e) { return []; }
    }

    private function tableExists(string $t): bool {
        try { $this->db->select("SELECT 1 FROM `$t` LIMIT 0"); return true; } catch (\Throwable $e) { return false; }
    }

    private function hasCol(string $t, string $c): bool {
        try { $this->db->select("SELECT `$c` FROM `$t` LIMIT 0"); return true; } catch (\Throwable $e) { return false; }
    }

    /** Öğretmenin öğrencileri: danışman olduğu sınıflar + ders atamaları (varsa) */
    private function teacherStudentIds(int $teacherId): array {
        $ids = [];

        // danışman sınıflar → student_classes/class_students üzerinden öğrenciler
        $scTable = $this->tableExists('student_classes') ? 'student_classes' : ($this->tableExists('class_students') ? 'class_students' : null);
        if ($this->tableExists('class_advisors') && $scTable) {
            $caClass = $this->hasCol('class_advisors','class_id') ? 'class_id' : ($this->hasCol('class_advisors','sinif_id') ? 'sinif_id' : null);
            $scClass = $this->hasCol($scTable,'class_id') ? 'class_id' : ($this->hasCol($scTable,'sinif_id') ? 'sinif_id' : null);
            $scStud  = $this->hasCol($scTable,'student_id') ? 'student_id' : ($this->hasCol($scTable,'ogrenci_id') ? 'ogrenci_id' : null);
            if ($caClass && $scClass && $scStud) {
                $rows = $this->safeSelect(
                    "SELECT sc.`$scStud` AS sid
                     FROM `$scTable` sc
                     INNER JOIN `class_advisors` ca ON ca.`$caClass` = sc.`$scClass`
                     WHERE ca.`teacher_id` = ?", [$teacherId]
                );
                foreach ($rows as $r) { $ids[] = (int)$r['sid']; }
            }
        }

        // ders atamaları → sınıf → öğrenciler
        if ($this->tableExists('teacher_assignments') && $scTable) {
            $taClass = $this->hasCol('teacher_assignments','class_id') ? 'class_id' : ($this->hasCol('teacher_assignments','sinif_id') ? 'sinif_id' : null);
            $scClass = $this->hasCol($scTable,'class_id') ? 'class_id' : ($this->hasCol($scTable,'sinif_id') ? 'sinif_id' : null);
            $scStud  = $this->hasCol($scTable,'student_id') ? 'student_id' : ($this->hasCol($scTable,'ogrenci_id') ? 'ogrenci_id' : null);
            if ($taClass && $scClass && $scStud) {
                $rows = $this->safeSelect(
                    "SELECT sc.`$scStud` AS sid
                     FROM `$scTable` sc
                     INNER JOIN `teacher_assignments` ta ON ta.`$taClass` = sc.`$scClass`
                     WHERE ta.`teacher_id` = ?", [$teacherId]
                );
                foreach ($rows as $r) { $ids[] = (int)$r['sid']; }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Seansların listesi (rol bazlı görünürlük ile)
     */
    function index()
    {
        $sql = "SELECT gs.*, s.name as student_name, c.name as counselor_name 
                FROM guidance_sessions gs
                JOIN users s ON gs.student_id = s.id
                JOIN users c ON gs.counselor_id = c.id";

        $params = [];
        $where_clauses = [];

        if ($this->userRole === 'teacher') { // Öğretmen: kendi girdiği seanslar + kendi öğrencileri
            // 1) Kendi girdiği seanslar
            $teacherParts = ["gs.counselor_id = ?"];
            $teacherParams = [$this->userId];

            // 2) Kendi öğrencileri (varsa) → student_id IN (...)
            $tids = $this->teacherStudentIds($this->userId);
            if (!empty($tids)) {
                $placeholders = implode(',', array_fill(0, count($tids), '?'));
                $teacherParts[] = "gs.student_id IN (" . $placeholders . ")";
                $teacherParams = array_merge($teacherParams, $tids);
            }

            $where_clauses[] = '(' . implode(' OR ', $teacherParts) . ')';
            $params = array_merge($params, $teacherParams);

        } elseif ($this->userRole === 'parent') { // Veli: Kendi çocuğunun/çocuklarının seansları
            // parents_students tablosunu kullanarak velinin ilişkili olduğu öğrencilerin ID'lerini çekiyoruz
            $student_ids_of_parent_raw = $this->db->select(
                "SELECT student_id FROM parents_students WHERE parent_id = ?",
                [$this->userId]
            );
            $student_ids_of_parent = array_column($student_ids_of_parent_raw, 'student_id');

            if (!empty($student_ids_of_parent)) {
                // IN clause için yer tutucuları oluştur
                $placeholders = implode(',', array_fill(0, count($student_ids_of_parent), '?'));
                $where_clauses[] = "gs.student_id IN (" . $placeholders . ")";
                $params = array_merge($params, $student_ids_of_parent);
            } else {
                // Çocuğu olmayan veli için boş liste
                $where_clauses[] = "1=0"; // Hiçbir kayıt dönmez, doğru davranış
            }
        } elseif ($this->userRole === 'student') { // Öğrenci: Kendi seansları
            $where_clauses[] = "gs.student_id = ?";
            $params[] = $this->userId;
        }
        // Admin her şeyi görür, WHERE koşulu eklenmez.

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $sql .= " ORDER BY gs.session_date DESC";
        
        $sessions = $this->db->select($sql, $params);
        return [
            'sessions' => $sessions, 
            'userRole' => $this->userRole, 
            'currentUserId' => $this->userId
        ];
    }

    /**
     * Seans oluşturma formu
     */
    function create()
    {
        if ($this->userRole !== 'admin' && $this->userRole !== 'teacher') {
            redirect('index.php?module=dashboard&error=no_permission');
            exit;
        }

        // Öğretmense mümkünse kendi öğrencileriyle sınırla; aksi halde tüm öğrenciler
        $students = $this->userRole === 'teacher' ?
            array_map(function($r){ return ['id'=>$r['id'],'name'=>$r['name']]; },
                      $this->safeSelect(
                        "SELECT u.id, u.name FROM users u WHERE u.role='student' AND u.id IN (".
                        implode(',', array_fill(0, max(1,count($this->teacherStudentIds($this->userId))), '?' )).
                        ") ORDER BY u.name",
                        (function($ids){ return empty($ids)? [0] : $ids; })($this->teacherStudentIds($this->userId))
                      ))
            : $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name");

        $session = ['student_id'=>'', 'session_date'=>date('Y-m-d'), 'title'=>'', 'notes'=>''];

        return [
            'session' => $session, 
            'students' => $students,
            'isEdit' => false, 
            'formAction' => "index.php?module=guidance&action=store",
            'isMultiple' => true // Yalnızca 'create' için çoklu form göster
        ];
    }

    /**
     * Yeni seansı kaydeder (tekli veya çoklu).
     */
    public function store()
    {
        if ($this->userRole !== 'admin' && $this->userRole !== 'teacher') {
            redirect('index.php?module=guidance&action=index&error_message=no_permission_store');
            exit;
        }

        $isMultiple = isset($_POST['isMultiple']) && $_POST['isMultiple'] == '1';
        $counselor_id = $this->userId;

        if ($isMultiple) {
            // Çoklu kayıt
            $student_ids = $_POST['student_ids'] ?? [];
            $session_dates = $_POST['session_dates'] ?? [];
            $titles = $_POST['titles'] ?? [];
            $notes_list = $_POST['notes_list'] ?? [];

            $inserted_count = 0;
            $error_count = 0;

            foreach ($student_ids as $index => $student_id) {
                $student_id = (int)$student_id;
                $session_date = $session_dates[$index] ?? '';
                $title = trim($titles[$index] ?? '');
                $notes = trim($notes_list[$index] ?? '');

                if (empty($student_id) || empty($session_date) || empty($title)) {
                    $error_count++;
                    log_activity('GUIDANCE_STORE_VALIDATION_ERROR', 'Guidance', null, "Seans eklenirken boş alanlar (Index: $index)");
                    continue;
                }
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $session_date)) {
                    $error_count++;
                    log_activity('GUIDANCE_STORE_VALIDATION_ERROR', 'Guidance', null, "Seans eklenirken geçersiz tarih (Index: $index)");
                    continue;
                }

                try {
                    $stmt = $this->db->getConnection()->prepare(
                        "INSERT INTO guidance_sessions (student_id, counselor_id, session_date, title, notes) VALUES (?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$student_id, $counselor_id, $session_date, $title, $notes]);
                    $inserted_count++;
                    log_activity('CREATE', 'Guidance', $this->db->getConnection()->lastInsertId(), "Seans oluşturuldu (çoklu)");
                } catch (\Throwable $e) {
                    $error_count++;
                    log_activity('GUIDANCE_STORE_DB_ERROR', 'Guidance', null, "DB hata: " . $e->getMessage());
                }
            }

            if ($inserted_count > 0) {
                redirect('index.php?module=guidance&action=index&success_message=created_multiple');
                exit;
            } else {
                redirect('index.php?module=guidance&action=create&error_message=none_inserted');
                exit;
            }

        } else {
            // Tekli kayıt
            $student_id = (int)($_POST['student_id'] ?? 0);
            $session_date = $_POST['session_date'] ?? '';
            $title = trim($_POST['title'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if (empty($student_id) || empty($session_date) || empty($title)) {
                redirect('index.php?module=guidance&action=create&error_message=empty_fields');
                exit;
            }

            if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $session_date)) {
                redirect('index.php?module=guidance&action=create&error_message=invalid_date');
                exit;
            }

            try {
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO guidance_sessions (student_id, counselor_id, session_date, title, notes) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$student_id, $counselor_id, $session_date, $title, $notes]);
                log_activity('CREATE', 'Guidance', $this->db->getConnection()->lastInsertId(), "Seans oluşturuldu");
                redirect('index.php?module=guidance&action=index&success_message=created');
                exit;
            } catch (\Throwable $e) {
                log_activity('GUIDANCE_STORE_DB_ERROR', 'Guidance', null, "DB hata: " . $e->getMessage());
                redirect('index.php?module=guidance&action=create&error_message=db_error');
                exit;
            }
        }
    }

    /**
     * Seans düzenleme formu
     */
    function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $session = $this->db->select("SELECT * FROM guidance_sessions WHERE id=?", [$id])[0] ?? null;

        if (!$session) {
            redirect('index.php?module=guidance&action=index&error_message=not_found');
            exit;
        }

        // Yetki kontrolü: Admin değilse ve seansı kendisi eklememişse düzenleyemez
        if ($this->userRole !== 'admin' && $session['counselor_id'] != $this->userId) {
            redirect('index.php?module=guidance&action=index&error_message=no_permission_edit');
            exit;
        }

        $students = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name");

        return [
            'session' => $session, 
            'students' => $students,
            'isEdit' => true, 
            'formAction' => "index.php?module=guidance&action=update&id=".$id
        ];
    }

    /**
     * Seans güncelle
     */
    public function update()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); 
        $sessionData = $this->db->select("SELECT counselor_id FROM guidance_sessions WHERE id=?", [$id])[0] ?? null;

        if (!$sessionData) {
            redirect('index.php?module=guidance&action=index&error_message=not_found');
            exit;
        }

        if ($this->userRole !== 'admin' && $sessionData['counselor_id'] != $this->userId) {
            redirect('index.php?module=guidance&action=index&error_message=no_permission_update');
            exit;
        }

        $student_id = (int)($_POST['student_id'] ?? 0);
        $session_date = $_POST['session_date'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($student_id) || empty($session_date) || empty($title) || empty($notes)) {
            redirect('index.php?module=guidance&action=edit&id=' . $id . '&error_message=empty_fields');
            exit;
        }
        
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $session_date)) {
             redirect('index.php?module=guidance&action=edit&id=' . $id . '&error_message=invalid_date');
             exit;
        }

        try {
            $this->db->getConnection()->prepare(
                "UPDATE guidance_sessions SET student_id=?, session_date=?, title=?, notes=? WHERE id=?"
            )->execute([$student_id, $session_date, $title, $notes, $id]);

            log_activity('UPDATE', 'Guidance', $id, "Seans güncellendi");
            redirect('index.php?module=guidance&action=index&success_message=updated');
            exit;
        } catch (\Throwable $e) {
            log_activity('GUIDANCE_UPDATE_DB_ERROR', 'Guidance', $id, "DB hata: " . $e->getMessage());
            redirect('index.php?module=guidance&action=edit&id='.$id.'&error_message=db_error');
            exit;
        }
    }

    /**
     * Seans sil
     */
    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        $sessionData = $this->db->select("SELECT counselor_id FROM guidance_sessions WHERE id=?", [$id])[0] ?? null;

        if (!$sessionData) {
            redirect('index.php?module=guidance&action=index&error_message=not_found');
            exit;
        }

        if ($this->userRole !== 'admin' && $sessionData['counselor_id'] != $this->userId) {
            redirect('index.php?module=guidance&action=index&error_message=no_permission_delete');
            exit;
        }

        try {
            $this->db->getConnection()->prepare("DELETE FROM guidance_sessions WHERE id=?")->execute([$id]);
            log_activity('DELETE', 'Guidance', $id, "Seans silindi");
            redirect('index.php?module=guidance&action=index&success_message=deleted');
            exit;
        } catch (\Throwable $e) {
            log_activity('GUIDANCE_DELETE_DB_ERROR', 'Guidance', $id, "DB hata: " . $e->getMessage());
            redirect('index.php?module=guidance&action=index&error_message=db_error');
            exit;
        }
    }

    /**
     * Seans detayı (rol kontrolü ile)
     */
    function view()
    {
        $id = (int)($_GET['id'] ?? 0); // URL'den seans ID'sini al

        // Seans detaylarını veritabanından çek
        $session = $this->db->select("SELECT gs.*, s.name as student_name, c.name as counselor_name 
                                     FROM guidance_sessions gs
                                     JOIN users s ON gs.student_id = s.id
                                     JOIN users c ON gs.counselor_id = c.id
                                     WHERE gs.id = ?", [$id])[0] ?? null;

        if (!$session) {
            redirect('index.php?module=guidance&action=index&error_message=not_found');
            exit;
        }

        // Yetki kontrolü (admin her şeyi görür)
        $has_permission = false;

        if ($this->userRole === 'admin') {
            $has_permission = true;
        } elseif ($this->userRole === 'teacher') {
            // 1) Seansı kendisi girdiyse
            if ($session['counselor_id'] == $this->userId) {
                $has_permission = true;
            }
            // 2) Kendi öğrencileri ise (varsa şema desteği)
            if (!$has_permission) {
                $tids = $this->teacherStudentIds($this->userId);
                if (!empty($tids) && in_array((int)$session['student_id'], array_map('intval', $tids), true)) {
                    $has_permission = true;
                }
            }
        } elseif ($this->userRole === 'student') {
            if ($session['student_id'] == $this->userId) {
                $has_permission = true;
            }
        } elseif ($this->userRole === 'parent') {
            // Veli ise parents_students tablosundan kontrol
            $student_ids_of_parent_raw = $this->db->select(
                "SELECT student_id FROM parents_students WHERE parent_id = ?",
                [$this->userId]
            );
            $student_ids_of_parent = array_column($student_ids_of_parent_raw, 'student_id');

            if (in_array($session['student_id'], $student_ids_of_parent)) {
                $has_permission = true;
            }
        }

        if (!$has_permission) {
            redirect('index.php?module=guidance&action=index&error_message=no_permission_view');
            exit;
        }

        return ['session' => $session, 'pageTitle' => 'Seans Detayı'];
    }
    /* ==================== RANDEVU TALEPLERİ ==================== */

    /**
     * Randevu talep formu (Herkes erişebilir)
     */
    public function requestForm()
    {
        $studentId = null;
        $studentName = null;

        // Eğer öğrenci ise kendi bilgileri
        if ($this->userRole === 'student') {
            $studentId = $this->userId;
            $studentName = $this->currentUser['name'];
        }
        // Eğer veli ise çocuklarını seç
        elseif ($this->userRole === 'parent') {
            $children = $this->db->select("
                SELECT u.id, u.name 
                FROM users u
                INNER JOIN parents_students ps ON u.id = ps.student_id
                WHERE ps.parent_id = ?
                ORDER BY u.name
            ", [$this->userId]) ?? [];

            return [
                'view' => 'guidance/view/request_form.php',
                'pageTitle' => 'Randevu Talebi',
                'children' => $children,
                'isParent' => true,
                'studentId' => null,
                'studentName' => null
            ];
        }
        // Admin veya öğretmen ise öğrenci seçebilir
        else {
            $students = $this->db->select("
                SELECT id, name FROM users 
                WHERE role = 'student' 
                ORDER BY name
            ") ?? [];

            return [
                'view' => 'guidance/view/request_form.php',
                'pageTitle' => 'Randevu Talebi',
                'students' => $students,
                'isStaff' => true,
                'studentId' => null,
                'studentName' => null
            ];
        }

        return [
            'view' => 'guidance/view/request_form.php',
            'pageTitle' => 'Randevu Talebi',
            'studentId' => $studentId,
            'studentName' => $studentName,
            'isParent' => false,
            'isStaff' => false
        ];
    }

    /**
     * Randevu talebi kaydet
     */
    public function submitRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=guidance&action=requestForm');
            exit;
        }

        $studentId = (int)($_POST['student_id'] ?? 0);
        $requestedDate = $_POST['requested_date'] ?? '';
        $requestedTime = $_POST['requested_time'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        // Validasyon
        if (!$studentId || !$requestedDate || !$requestedTime || empty($reason)) {
            redirect('index.php?module=guidance&action=requestForm&error_message=empty_fields');
            exit;
        }

        // Tarih format kontrolü
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $requestedDate)) {
            redirect('index.php?module=guidance&action=requestForm&error_message=invalid_date');
            exit;
        }

        // Geçmiş tarih kontrolü
        if (strtotime($requestedDate) < strtotime(date('Y-m-d'))) {
            redirect('index.php?module=guidance&action=requestForm&error_message=past_date');
            exit;
        }

        $parentId = ($this->userRole === 'parent') ? $this->userId : null;

        try {
            $this->db->execute("
                INSERT INTO guidance_appointments 
                (student_id, parent_id, requested_date, requested_time, reason, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ", [$studentId, $parentId, $requestedDate, $requestedTime, $reason]);

            log_activity('CREATE', 'GuidanceAppointment', $this->db->getConnection()->lastInsertId(), "Randevu talebi oluşturuldu");
            redirect('index.php?module=guidance&action=myRequests&success_message=request_submitted');
            exit;

        } catch (\Throwable $e) {
            log_activity('APPOINTMENT_REQUEST_ERROR', 'GuidanceAppointment', null, "Hata: " . $e->getMessage());
            redirect('index.php?module=guidance&action=requestForm&error_message=db_error');
            exit;
        }
    }

    /**
     * Kullanıcının kendi randevu talepleri
     */
    public function myRequests()
    {
        $sql = "SELECT ga.*, u.name as student_name, c.name as counselor_name
                FROM guidance_appointments ga
                JOIN users u ON ga.student_id = u.id
                LEFT JOIN users c ON ga.counselor_id = c.id
                WHERE 1=1";
        
        $params = [];

        if ($this->userRole === 'student') {
            $sql .= " AND ga.student_id = ?";
            $params[] = $this->userId;
        } elseif ($this->userRole === 'parent') {
            $sql .= " AND ga.parent_id = ?";
            $params[] = $this->userId;
        } else {
            // Admin veya öğretmen tüm talepleri görebilir
        }

        $sql .= " ORDER BY ga.created_at DESC";

        $requests = $this->db->select($sql, $params) ?? [];

        return [
            'view' => 'guidance/view/my_requests.php',
            'pageTitle' => 'Randevu Taleplerim',
            'requests' => $requests,
            'userRole' => $this->userRole
        ];
    }

    /**
     * Randevu yönetimi (Sadece admin ve rehber öğretmen)
     */
    public function appointments()
    {
        // Sadece admin ve rehber öğretmen erişebilir
        if ($this->userRole !== 'admin' && $this->userRole !== 'teacher') {
            redirect('index.php?module=dashboard&error=no_permission');
            exit;
        }

        $filterStatus = $_GET['filter_status'] ?? '';
        $filterDate = $_GET['filter_date'] ?? '';

        $sql = "SELECT ga.*, 
                       u.name as student_name, 
                       u.student_number,
                       u.phone,
                       c.name as counselor_name,
                       p.name as parent_name
                FROM guidance_appointments ga
                JOIN users u ON ga.student_id = u.id
                LEFT JOIN users c ON ga.counselor_id = c.id
                LEFT JOIN users p ON ga.parent_id = p.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filterStatus)) {
            $sql .= " AND ga.status = ?";
            $params[] = $filterStatus;
        }

        if (!empty($filterDate)) {
            $sql .= " AND ga.requested_date = ?";
            $params[] = $filterDate;
        }

        $sql .= " ORDER BY ga.status ASC, ga.requested_date ASC, ga.requested_time ASC";

        $appointments = $this->db->select($sql, $params) ?? [];

        // İstatistikler
        $stats = $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM guidance_appointments
        ") ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'completed' => 0];

        return [
            'view' => 'guidance/view/appointments.php',
            'pageTitle' => 'Randevu Yönetimi',
            'appointments' => $appointments,
            'stats' => $stats,
            'filterStatus' => $filterStatus,
            'filterDate' => $filterDate,
            'userRole' => $this->userRole
        ];
    }

    /**
     * Randevu onayla/güncelle
     */
    public function updateAppointment()
    {
        if ($this->userRole !== 'admin' && $this->userRole !== 'teacher') {
            redirect('index.php?module=guidance&action=appointments&error_message=no_permission');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=guidance&action=appointments');
            exit;
        }

        $appointmentId = (int)($_POST['appointment_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $appointmentDate = $_POST['appointment_date'] ?? null;
        $appointmentTime = $_POST['appointment_time'] ?? null;
        $counselorNotes = trim($_POST['counselor_notes'] ?? '');

        if (!$appointmentId || !in_array($status, ['approved', 'rejected', 'completed', 'cancelled'])) {
            redirect('index.php?module=guidance&action=appointments&error_message=invalid_data');
            exit;
        }

        try {
            $this->db->execute("
                UPDATE guidance_appointments 
                SET status = ?,
                    appointment_date = ?,
                    appointment_time = ?,
                    counselor_notes = ?,
                    counselor_id = ?
                WHERE id = ?
            ", [$status, $appointmentDate, $appointmentTime, $counselorNotes, $this->userId, $appointmentId]);

            log_activity('UPDATE', 'GuidanceAppointment', $appointmentId, "Randevu güncellendi: $status");
            redirect('index.php?module=guidance&action=appointments&success_message=appointment_updated');
            exit;

        } catch (\Throwable $e) {
            log_activity('APPOINTMENT_UPDATE_ERROR', 'GuidanceAppointment', $appointmentId, "Hata: " . $e->getMessage());
            redirect('index.php?module=guidance&action=appointments&error_message=db_error');
            exit;
        }
    }

    /**
     * Randevu talebi iptal et (Öğrenci/Veli)
     */
    public function cancelRequest()
    {
        $appointmentId = (int)($_GET['id'] ?? 0);

        $appointment = $this->db->fetch("
            SELECT student_id, parent_id, status 
            FROM guidance_appointments 
            WHERE id = ?
        ", [$appointmentId]);

        if (!$appointment) {
            redirect('index.php?module=guidance&action=myRequests&error_message=not_found');
            exit;
        }

        // Yetki kontrolü
        $canCancel = false;
        if ($this->userRole === 'student' && $appointment['student_id'] == $this->userId) {
            $canCancel = true;
        } elseif ($this->userRole === 'parent' && $appointment['parent_id'] == $this->userId) {
            $canCancel = true;
        } elseif ($this->userRole === 'admin') {
            $canCancel = true;
        }

        if (!$canCancel) {
            redirect('index.php?module=guidance&action=myRequests&error_message=no_permission');
            exit;
        }

        // Sadece pending durumundakiler iptal edilebilir
        if ($appointment['status'] !== 'pending') {
            redirect('index.php?module=guidance&action=myRequests&error_message=cannot_cancel');
            exit;
        }

        try {
            $this->db->execute("
                UPDATE guidance_appointments 
                SET status = 'cancelled' 
                WHERE id = ?
            ", [$appointmentId]);

            log_activity('UPDATE', 'GuidanceAppointment', $appointmentId, "Randevu talebi iptal edildi");
            redirect('index.php?module=guidance&action=myRequests&success_message=request_cancelled');
            exit;

        } catch (\Throwable $e) {
            log_activity('APPOINTMENT_CANCEL_ERROR', 'GuidanceAppointment', $appointmentId, "Hata: " . $e->getMessage());
            redirect('index.php?module=guidance&action=myRequests&error_message=db_error');
            exit;
        }
    }
}
