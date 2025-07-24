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
        $this->currentUser = $_SESSION['user'] ?? null;
        $this->userRole = $this->currentUser['role'] ?? 'guest';
        $this->userId = $this->currentUser['id'] ?? 0;
    }

    /**
     * Rehberlik Seansları listesi
     */
    public function index()
    {
        $sql = "SELECT gs.*, s.name as student_name, c.name as counselor_name 
                FROM guidance_sessions gs
                JOIN users s ON gs.student_id = s.id
                JOIN users c ON gs.counselor_id = c.id";

        $params = [];
        $where_clauses = [];

        if ($this->userRole === 'teacher') { // Danışman Öğretmen: Kendi girdiği seanslar
            $where_clauses[] = "gs.counselor_id = ?";
            $params[] = $this->userId;
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
     * Yeni seans ekleme formunu gösterir.
     */
    public function create()
    {
        if ($this->userRole !== 'admin' && $this->userRole !== 'teacher') {
            redirect('index.php?module=dashboard&error=no_permission');
            exit;
        }

        $students = $this->db->select("SELECT id, name FROM users WHERE role='student' ORDER BY name");
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
            redirect('index.php?module=dashboard&error=no_permission');
            exit;
        }

        $counselor_id = $this->userId; // Notu ekleyen rehber öğretmen/admin

        // Çoklu kayıtlar için POST verisi 'sessions' dizisi olarak gelir
        $session_data_array = $_POST['sessions'] ?? [];

        if (empty($session_data_array)) {
            redirect('index.php?module=guidance&action=create&error_message=no_sessions_data');
            exit;
        }

        $inserted_count = 0;
        $error_count = 0;

        foreach ($session_data_array as $index => $session_data) {
            $student_id = (int)($session_data['student_id'] ?? 0);
            $session_date = $session_data['session_date'] ?? '';
            $title = trim($session_data['title'] ?? '');
            $notes = trim($session_data['notes'] ?? '');

            if (empty($student_id) || empty($session_date) || empty($title) || empty($notes)) {
                $error_count++;
                log_activity('GUIDANCE_STORE_VALIDATION_ERROR', 'Guidance', null, "Seans eklenirken zorunlu alanlar boş (Index: $index)");
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
                log_activity('CREATE', 'Guidance', $this->db->getConnection()->lastInsertId(), "Seans oluşturuldu: '$title' (Öğrenci ID: $student_id)");
            } catch (PDOException $e) {
                $error_count++;
                error_log("Guidance Session Store Error (Index: $index): " . $e->getMessage());
                log_activity('ERROR', 'Guidance', null, "Seans eklenirken DB hatası (Index: $index): " . $e->getMessage());
            }
        }
        
        $status_message = "Toplam $inserted_count seans eklendi.";
        if ($error_count > 0) {
            $status_message .= " ($error_count seans kaydedilemedi, lütfen logları kontrol edin.)";
            redirect('index.php?module=guidance&action=index&error_message=' . urlencode($status_message));
        } else {
            redirect('index.php?module=guidance&action=index&status_message=' . urlencode($status_message));
        }
        exit;
    }

    /**
     * Seans düzenleme formunu gösterir.
     */
    public function edit()
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
            'formAction' => "index.php?module=guidance&action=update&id=$id",
            'isMultiple' => false // Düzeltme: 'edit' aksiyonu için tekli form göster
        ];
    }

    /**
     * Seansı günceller.
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
            redirect('index.php?module=guidance&action=index&status_message=updated');
        } catch (PDOException $e) {
            error_log("Guidance Session Update Error: " . $e->getMessage());
            redirect('index.php?module=guidance&action=edit&id=' . $id . '&error_message=db_error');
        }
        exit;
    }

    /**
     * Seansı siler.
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
            redirect('index.php?module=guidance&action=index&status_message=deleted');
        } catch (PDOException $e) {
            error_log("Guidance Session Delete Error: " . $e->getMessage());
            redirect('index.php?module=guidance&action=index&error_message=db_error_delete');
        }
        exit;
    }
    public function view()
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

        // Yetki kontrolü:
        // Admin ve danışman öğretmen her seansı görebilir.
        // Öğrenci sadece kendi seanslarını görebilir.
        // Veli sadece kendi çocuklarının seanslarını görebilir.
        $has_permission = false;
        if ($this->userRole === 'admin' || $this->userRole === 'teacher' && $session['counselor_id'] == $this->userId) {
            $has_permission = true;
        } elseif ($this->userRole === 'student' && $session['student_id'] == $this->userId) {
            $has_permission = true;
        } elseif ($this->userRole === 'parent') {
            // Velinin velisi olduğu öğrencilerin ID'lerini parents_students tablosundan çekiyoruz
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
}