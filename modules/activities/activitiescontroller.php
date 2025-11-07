<?php
// modules/activities/activitiescontroller.php

require_once __DIR__ . '/../../core/auth.php';

class ActivitiesController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $this->checkAuth();
    }

    private function checkAuth(): void
    {
        $role = currentRole();
        $action = $_GET['action'] ?? 'index';
        
        if (in_array($action, ['index', 'show', 'calendar'])) {
            if ($role === 'guest') {
                $this->flashErr('Bu alana erişim için giriş yapmalısınız.');
                header('Location: index.php?module=login'); exit;
            }
            return;
        }
        
        if (!in_array($role, ['admin', 'teacher'])) {
            $this->flashErr('Etkinlik oluşturma/düzenleme yetkisi sadece yönetici ve öğretmenlerde bulunur.');
            header('Location: index.php?module=activities&action=index'); exit;
        }
    }

    private function flashErr(string $m){ $_SESSION['form_error'] = $m; }
    private function flashOk(string $m){ $_SESSION['form_ok'] = $m; }
    
    private function redirect(string $action, array $params=[]){
        $q = http_build_query($params);
        header('Location: index.php?module=activities&action='.$action.($q?('&'.$q):'')); exit;
    }
    
    private function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    private function validateCsrfToken(): bool {
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    private function uploadImage($file, int $activityId): ?string
    {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("Dosya yükleme hatası");
        }
        
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new \Exception("Dosya boyutu çok büyük (max 5MB)");
        }
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception("Geçersiz dosya tipi");
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = 'activity_' . $activityId . '_' . time() . '.' . $extension;
        $uploadDir = __DIR__ . '/../../uploads/activities/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new \Exception("Dosya yüklenemedi");
        }
        
        return 'uploads/activities/' . $fileName;
    }

    private function deleteImage(?string $path): void
    {
        if (empty($path)) return;
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath)) @unlink($fullPath);
    }

    public function index()
    {
        $sql = "SELECT a.*, u.name AS teacher_name
                FROM activities a
                LEFT JOIN users u ON u.id = a.teacher_id
                ORDER BY a.start_date DESC, a.id DESC";
        
        $rows = $this->db->select($sql) ?? [];

        $classNames = [];
        $classes = $this->db->select("SELECT id, name FROM classes") ?? [];
        foreach ($classes as $cl) {
            $classNames[(int)$cl['id']] = $cl['name'];
        }

        if (!empty($rows)) {
            $activityIds = array_column($rows, 'id');
            $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
            
            $classLinks = $this->db->select("
                SELECT activity_id, class_id 
                FROM activity_classes 
                WHERE activity_id IN ($placeholders)
            ", $activityIds) ?? [];
            
            $classesPerActivity = [];
            foreach ($classLinks as $link) {
                $aid = (int)$link['activity_id'];
                $cid = (int)$link['class_id'];
                $classesPerActivity[$aid][] = [
                    'class_id' => $cid,
                    'class_name' => $classNames[$cid] ?? "Sınıf #$cid"
                ];
            }
            
            foreach ($rows as &$r) {
                $r['classes'] = $classesPerActivity[(int)$r['id']] ?? [];
            }
            unset($r);
        }

        return [
            'view' => 'activities/view/index.php',
            'title' => 'Etkinlikler',
            'activities' => $rows,
            'csrf_token' => $this->generateCsrfToken(),
        ];
    }

    public function create()
    {
        $teachers = $this->db->select("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name") ?? [];
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name") ?? [];
        
        return [
            'view' => 'activities/view/create.php',
            'title' => 'Yeni Etkinlik',
            'isEdit' => false,
            'activity' => [],
            'teachers' => $teachers,
            'classes' => $classes,
            'selected_class_ids' => [],
            'formAction' => 'index.php?module=activities&action=store',
            'csrf_token' => $this->generateCsrfToken(),
        ];
    }

    public function store()
    {
        if (!$this->validateCsrfToken()) {
            $this->flashErr('Güvenlik hatası');
            $this->redirect('create');
        }

        try {
            $this->db->beginTransaction();

            $title = trim($_POST['title'] ?? '');
            if ($title === '') throw new \Exception('Başlık boş olamaz');
            
            $description = trim($_POST['description'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $start_date = trim($_POST['start_date'] ?? '');
            $end_date = trim($_POST['end_date'] ?? '');
            $teacher_id = ($_POST['teacher_id'] ?? '') !== '' ? (int)$_POST['teacher_id'] : null;
            $class_ids = array_map('intval', $_POST['class_ids'] ?? []);
            $creator_id = currentUserId();

            if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
                throw new \Exception('Bitiş tarihi başlangıçtan önce olamaz');
            }

            $activity_date = $start_date ?: date('Y-m-d H:i:s');

            $sql = "INSERT INTO activities (creator_id, title, description, location, activity_date, start_date, end_date, teacher_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $this->db->execute($sql, [
                $creator_id,
                $title,
                $description,
                $location ?: null,
                $activity_date,
                $start_date ?: null,
                $end_date ?: null,
                $teacher_id
            ]);

            $aid = (int)$this->db->lastInsertId();

            // Görsel yükle
            if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $imagePath = $this->uploadImage($_FILES['image_path'], $aid);
                    if ($imagePath) {
                        $this->db->execute("UPDATE activities SET image_path = ? WHERE id = ?", [$imagePath, $aid]);
                    }
                } catch (\Exception $e) {
                    // Görsel hatası önemli değil, devam et
                }
            }

            // Sınıfları ekle
            foreach ($class_ids as $cid) {
                $this->db->execute("INSERT INTO activity_classes (activity_id, class_id) VALUES (?, ?)", [$aid, $cid]);
            }

            $this->db->commit();
            $this->flashOk('Etkinlik oluşturuldu');
            $this->redirect('show', ['id' => $aid]);
            
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->flashErr('Hata: ' . $e->getMessage());
            $this->redirect('create');
        }
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashErr('Geçersiz ID');
            $this->redirect('index');
        }

        $activity = $this->db->fetch("SELECT * FROM activities WHERE id = ?", [$id]);
        if (!$activity) {
            $this->flashErr('Etkinlik bulunamadı');
            $this->redirect('index');
        }

        $role = currentRole();
        $userId = currentUserId();
        if ($role === 'teacher' && (int)($activity['creator_id'] ?? 0) !== $userId) {
            $this->flashErr('Bu etkinliği düzenleme yetkiniz yok');
            $this->redirect('show', ['id' => $id]);
        }

        $teachers = $this->db->select("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name") ?? [];
        $classes = $this->db->select("SELECT id, name FROM classes ORDER BY name") ?? [];
        
        $selectedClasses = $this->db->select("SELECT class_id FROM activity_classes WHERE activity_id = ?", [$id]) ?? [];
        $selected_class_ids = array_map('intval', array_column($selectedClasses, 'class_id'));

        return [
            'view' => 'activities/view/edit.php',
            'title' => 'Etkinlik Düzenle',
            'isEdit' => true,
            'activity' => $activity,
            'teachers' => $teachers,
            'classes' => $classes,
            'selected_class_ids' => $selected_class_ids,
            'formAction' => 'index.php?module=activities&action=update&id=' . $id,
            'csrf_token' => $this->generateCsrfToken(),
        ];
    }

    public function update()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0 || !$this->validateCsrfToken()) {
            $this->flashErr('Geçersiz istek');
            $this->redirect('index');
        }

        try {
            $this->db->beginTransaction();

            $existing = $this->db->fetch("SELECT creator_id, image_path FROM activities WHERE id = ?", [$id]);
            if (!$existing) throw new \Exception('Etkinlik bulunamadı');
            
            $role = currentRole();
            $userId = currentUserId();
            if ($role === 'teacher' && (int)($existing['creator_id'] ?? 0) !== $userId) {
                throw new \Exception('Bu etkinliği düzenleme yetkiniz yok');
            }

            $title = trim($_POST['title'] ?? '');
            if ($title === '') throw new \Exception('Başlık boş olamaz');
            
            $description = trim($_POST['description'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $start_date = trim($_POST['start_date'] ?? '');
            $end_date = trim($_POST['end_date'] ?? '');
            $teacher_id = ($_POST['teacher_id'] ?? '') !== '' ? (int)$_POST['teacher_id'] : null;
            $class_ids = array_map('intval', $_POST['class_ids'] ?? []);

            if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
                throw new \Exception('Bitiş tarihi başlangıçtan önce olamaz');
            }

            $activity_date = $start_date ?: date('Y-m-d H:i:s');

            $sql = "UPDATE activities 
                    SET title = ?, description = ?, location = ?, activity_date = ?, 
                        start_date = ?, end_date = ?, teacher_id = ?
                    WHERE id = ?";
            
            $this->db->execute($sql, [
                $title,
                $description,
                $location ?: null,
                $activity_date,
                $start_date ?: null,
                $end_date ?: null,
                $teacher_id,
                $id
            ]);

            // Görsel işlemleri
            if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
                $this->deleteImage($existing['image_path']);
                $this->db->execute("UPDATE activities SET image_path = NULL WHERE id = ?", [$id]);
            } elseif (isset($_FILES['image_path']) && $_FILES['image_path']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $this->deleteImage($existing['image_path']);
                    $imagePath = $this->uploadImage($_FILES['image_path'], $id);
                    if ($imagePath) {
                        $this->db->execute("UPDATE activities SET image_path = ? WHERE id = ?", [$imagePath, $id]);
                    }
                } catch (\Exception $e) {
                    // Görsel hatası önemli değil
                }
            }

            // Sınıfları güncelle
            $this->db->execute("DELETE FROM activity_classes WHERE activity_id = ?", [$id]);
            foreach ($class_ids as $cid) {
                $this->db->execute("INSERT INTO activity_classes (activity_id, class_id) VALUES (?, ?)", [$id, $cid]);
            }

            $this->db->commit();
            $this->flashOk('Etkinlik güncellendi');
            $this->redirect('show', ['id' => $id]);
            
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->flashErr('Hata: ' . $e->getMessage());
            $this->redirect('edit', ['id' => $id]);
        }
    }

    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect('index');

        $activity = $this->db->fetch("
            SELECT a.*, u.name AS teacher_name
            FROM activities a
            LEFT JOIN users u ON u.id = a.teacher_id
            WHERE a.id = ?
        ", [$id]);

        if (!$activity) {
            $this->flashErr('Etkinlik bulunamadı');
            $this->redirect('index');
        }

        $classes = $this->db->select("
            SELECT c.id, c.name
            FROM activity_classes ac
            JOIN classes c ON c.id = ac.class_id
            WHERE ac.activity_id = ?
            ORDER BY c.name
        ", [$id]) ?? [];

        return [
            'view' => 'activities/view/show.php',
            'title' => 'Etkinlik Detay',
            'activity' => $activity,
            'classes' => $classes,
            'csrf_token' => $this->generateCsrfToken(),
        ];
    }

    public function destroy()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !$this->validateCsrfToken()) {
            $this->flashErr('Geçersiz istek');
            $this->redirect('index');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flashErr('Geçersiz ID');
            $this->redirect('index');
        }

        try {
            $this->db->beginTransaction();

            $existing = $this->db->fetch("SELECT creator_id, image_path FROM activities WHERE id = ?", [$id]);
            if (!$existing) throw new \Exception('Etkinlik bulunamadı');
            
            $role = currentRole();
            $userId = currentUserId();
            if ($role === 'teacher' && (int)($existing['creator_id'] ?? 0) !== $userId) {
                throw new \Exception('Bu etkinliği silme yetkiniz yok');
            }

            $this->deleteImage($existing['image_path']);
            $this->db->execute("DELETE FROM activity_classes WHERE activity_id = ?", [$id]);
            $this->db->execute("DELETE FROM activity_attendance WHERE activity_id = ?", [$id]);
            $this->db->execute("DELETE FROM activities WHERE id = ?", [$id]);

            $this->db->commit();
            $this->flashOk('Etkinlik silindi');
            
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->flashErr('Hata: ' . $e->getMessage());
        }

        $this->redirect('index');
    }

    public function calendar()
    {
        $rows = $this->db->select("SELECT * FROM activities ORDER BY start_date DESC") ?? [];
        
        return [
            'view' => 'activities/view/calendar.php',
            'title' => 'Etkinlik Takvimi',
            'activities' => $rows,
        ];
    }
}