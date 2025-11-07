<?php
require_once __DIR__ . '/../../core/database.php';

class CoursesController
{
    protected $db;
    protected $userId;
    protected $userRole;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
        
        $this->userId = $this->currentUserId();
        $this->userRole = $this->currentRole();
    }

    private function currentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
    }

    private function currentRole(): string
    {
        return $_SESSION['user']['role'] ?? $_SESSION['role'] ?? 'guest';
    }

    private function flashSuccess(string $msg): void
    {
        $_SESSION['flash_success'] = $msg;
    }

    private function flashError(string $msg): void
    {
        $_SESSION['flash_error'] = $msg;
    }

    /* ==================== INDEX/LIST ==================== */
    
    public function index(): array
    {
        return $this->list();
    }

    public function list(): array
    {
        $courses = $this->db->select("
            SELECT c.*, 
                   u.name AS teacher_name,
                   (SELECT COUNT(DISTINCT cc.class_id) 
                    FROM course_classes cc 
                    WHERE cc.course_id = c.id) AS class_count
            FROM courses c
            LEFT JOIN users u ON c.teacher_id = u.id
            ORDER BY c.name ASC
        ") ?? [];

        return [
            'view'      => 'courses/view/list.php',
            'title'     => 'Dersler',
            'courses'   => $courses,
            'canDelete' => ($this->userRole === 'admin')
        ];
    }

    /* ==================== CREATE ==================== */
    
    public function create(): array
    {
        $teachers = $this->db->select("
            SELECT id, name FROM users 
            WHERE role='teacher' AND is_active=1 
            ORDER BY name ASC
        ") ?? [];

        $classes = $this->db->select("
            SELECT id, name FROM classes 
            ORDER BY name ASC
        ") ?? [];

        $oldInput = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        return [
            'view'            => 'courses/view/form.php',
            'title'           => 'Yeni Ders',
            'course'          => $oldInput,
            'teachers'        => $teachers,
            'classes'         => $classes,
            'selectedTeacher' => $oldInput['teacher_id'] ?? null,
            'selectedClasses' => $oldInput['class_ids'] ?? [],
            'schedules'       => [],
            'isEdit'          => false,
            'formAction'      => 'index.php?module=courses&action=store',
            'errors'          => $errors
        ];
    }

    /* ==================== STORE ==================== */
    
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=courses&action=create');
            exit;
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category'] ?? null,
            'teacher_id'  => !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null,
            'color'       => trim($_POST['color'] ?? '#3788d8'),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0
        ];

        $selectedClasses = $_POST['class_ids'] ?? [];
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Ders adı zorunludur.';
        }

        if (empty($data['category'])) {
            $errors['category'] = 'Kademe seçimi zorunludur.';
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = array_merge($data, ['class_ids' => $selectedClasses]);
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=courses&action=create');
            exit;
        }

        $dupName = $this->db->fetch("SELECT id FROM courses WHERE name = ?", [$data['name']]);
        if ($dupName) {
            $_SESSION['old_input'] = array_merge($data, ['class_ids' => $selectedClasses]);
            $_SESSION['validation_errors'] = ['name' => 'Bu ders adı zaten kullanımda.'];
            header('Location: index.php?module=courses&action=create');
            exit;
        }

        try {
            // KREDİ KALDIRILDI
            $this->db->execute("
                INSERT INTO courses (name, description, category, teacher_id, color, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [
                $data['name'], 
                $data['description'], 
                $data['category'],
                $data['teacher_id'], 
                $data['color'],
                $data['is_active']
            ]);

            $courseId = $this->db->lastInsertId();

            if (!empty($selectedClasses)) {
                foreach ($selectedClasses as $classId) {
                    $this->db->execute("
                        INSERT INTO course_classes (course_id, class_id) 
                        VALUES (?, ?)
                    ", [$courseId, (int)$classId]);
                }
            }

            // Schedule kaydetme
            $this->saveSchedules($courseId);

            unset($_SESSION['old_input'], $_SESSION['validation_errors']);
            $this->flashSuccess('Ders oluşturuldu.');
            header('Location: index.php?module=courses&action=show&id=' . $courseId);
            exit;

        } catch (\Throwable $e) {
            error_log('Course store error: ' . $e->getMessage());
            $_SESSION['old_input'] = array_merge($data, ['class_ids' => $selectedClasses]);
            $_SESSION['validation_errors'] = ['general' => 'Veritabanı hatası: ' . $e->getMessage()];
            header('Location: index.php?module=courses&action=create');
            exit;
        }
    }

    /* ==================== EDIT ==================== */
    
    public function edit(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $course = $this->db->fetch("SELECT * FROM courses WHERE id = ?", [$id]);

        if (!$course) {
            $this->flashError('Ders bulunamadı.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $teachers = $this->db->select("
            SELECT id, name FROM users 
            WHERE role='teacher' AND is_active=1 
            ORDER BY name
        ") ?? [];

        $classes = $this->db->select("
            SELECT id, name FROM classes 
            ORDER BY name ASC
        ") ?? [];

        $assignedClasses = $this->db->select("
            SELECT class_id FROM course_classes WHERE course_id = ?
        ", [$id]) ?? [];
        
        $selectedClasses = array_column($assignedClasses, 'class_id');

        $schedules = $this->db->select("
            SELECT * FROM course_schedules 
            WHERE course_id = ? 
            ORDER BY day_of_week, start_time
        ", [$id]) ?? [];

        $oldInput = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        $courseData = [
            'id'          => (int)$course['id'],
            'name'        => $course['name'] ?? '',
            'description' => $course['description'] ?? '',
            'category'    => $course['category'] ?? null,
            'teacher_id'  => $course['teacher_id'] ?? null,
            'color'       => $course['color'] ?? '#3788d8',
            'is_active'   => (int)($course['is_active'] ?? 1)
        ];

        if (!empty($oldInput)) {
            $courseData = array_merge($courseData, $oldInput);
            if (isset($oldInput['class_ids'])) {
                $selectedClasses = $oldInput['class_ids'];
            }
        }

        return [
            'view'            => 'courses/view/form.php',
            'title'           => 'Ders Düzenle',
            'course'          => $courseData,
            'teachers'        => $teachers,
            'classes'         => $classes,
            'selectedTeacher' => $courseData['teacher_id'] ?? null,
            'selectedClasses' => $selectedClasses,
            'schedules'       => $schedules,
            'isEdit'          => true,
            'formAction'      => 'index.php?module=courses&action=update&id=' . $id,
            'errors'          => $errors
        ];
    }

    /* ==================== UPDATE ==================== */
    
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $course = $this->db->fetch("SELECT * FROM courses WHERE id = ?", [$id]);

        if (!$course) {
            $this->flashError('Ders bulunamadı.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category'] ?? null,
            'teacher_id'  => !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null,
            'color'       => trim($_POST['color'] ?? '#3788d8'),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0
        ];

        $selectedClasses = $_POST['class_ids'] ?? [];
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Ders adı zorunludur.';
        }

        if (empty($data['category'])) {
            $errors['category'] = 'Kademe seçimi zorunludur.';
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = array_merge($data, ['class_ids' => $selectedClasses]);
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=courses&action=edit&id=' . $id);
            exit;
        }

        $dupName = $this->db->fetch("SELECT id FROM courses WHERE name = ? AND id != ?", [$data['name'], $id]);
        if ($dupName) {
            $_SESSION['old_input'] = array_merge($data, ['class_ids' => $selectedClasses]);
            $_SESSION['validation_errors'] = ['name' => 'Bu ders adı başka bir derste kullanılıyor.'];
            header('Location: index.php?module=courses&action=edit&id=' . $id);
            exit;
        }

        try {
            // KREDİ KALDIRILDI
            $this->db->execute("
                UPDATE courses SET
                    name=?, description=?, category=?, teacher_id=?, color=?, is_active=?
                WHERE id=?
            ", [
                $data['name'], 
                $data['description'], 
                $data['category'],
                $data['teacher_id'], 
                $data['color'],
                $data['is_active'], 
                $id
            ]);

            $this->db->execute("DELETE FROM course_classes WHERE course_id = ?", [$id]);

            if (!empty($selectedClasses)) {
                foreach ($selectedClasses as $classId) {
                    $this->db->execute("
                        INSERT INTO course_classes (course_id, class_id) 
                        VALUES (?, ?)
                    ", [$id, (int)$classId]);
                }
            }

            $this->saveSchedules($id);

            unset($_SESSION['old_input'], $_SESSION['validation_errors']);
            $this->flashSuccess('Ders güncellendi.');
            header('Location: index.php?module=courses&action=show&id=' . $id);
            exit;

        } catch (\Throwable $e) {
            error_log('Course update error: ' . $e->getMessage());
            $_SESSION['old_input'] = array_merge($data, ['class_ids' => $selectedClasses]);
            $_SESSION['validation_errors'] = ['general' => 'Veritabanı hatası: ' . $e->getMessage()];
            header('Location: index.php?module=courses&action=edit&id=' . $id);
            exit;
        }
    }

    /* ==================== SHOW ==================== */
    
    public function show(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $course = $this->db->fetch("
            SELECT c.*, u.name AS teacher_name
            FROM courses c
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE c.id = ?
        ", [$id]);

        if (!$course) {
            $this->flashError('Ders bulunamadı.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $classes = $this->db->select("
            SELECT cl.id, cl.name
            FROM classes cl
            JOIN course_classes cc ON cc.class_id = cl.id
            WHERE cc.course_id = ?
            ORDER BY cl.name
        ", [$id]) ?? [];

        $students = $this->db->select("
            SELECT u.id, u.name, u.email, c.name AS class_name
            FROM users u
            LEFT JOIN classes c ON c.id = u.class_id
            WHERE u.role = 'student'
            AND u.class_id IN (
                SELECT class_id FROM course_classes WHERE course_id = ?
            )
            ORDER BY u.name
        ", [$id]) ?? [];

        $schedules = $this->db->select("
            SELECT * FROM course_schedules 
            WHERE course_id = ? 
            ORDER BY day_of_week, start_time
        ", [$id]) ?? [];

        $dayNames = [
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi',
            7 => 'Pazar'
        ];

        return [
            'view'      => 'courses/view/show.php',
            'title'     => 'Ders Detay',
            'course'    => $course,
            'classes'   => $classes,
            'students'  => $students,
            'schedules' => $schedules,
            'dayNames'  => $dayNames,
            'canDelete' => ($this->userRole === 'admin')
        ];
    }

    /* ==================== DELETE ==================== */
    
    public function delete(): void
    {
        if ($this->userRole !== 'admin') {
            $this->flashError('Silme yetkiniz yok.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=courses&action=list');
            exit;
        }

        try {
            $this->db->execute("DELETE FROM course_classes WHERE course_id = ?", [$id]);
            $this->db->execute("DELETE FROM course_schedules WHERE course_id = ?", [$id]);
            $this->db->execute("DELETE FROM student_enrollments WHERE course_id = ?", [$id]);
            $this->db->execute("DELETE FROM courses WHERE id = ?", [$id]);

            $this->flashSuccess('Ders silindi.');
            header('Location: index.php?module=courses&action=list');
            exit;

        } catch (\Throwable $e) {
            error_log('Course delete error: ' . $e->getMessage());
            $this->flashError('Silme hatası: ' . $e->getMessage());
            header('Location: index.php?module=courses&action=list');
            exit;
        }
    }

    /* ==================== SCHEDULE - TAKVİM ==================== */
    
    public function schedule(): array
    {
        $view = $_GET['view'] ?? 'week';
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $coursesFilter = '';
        $params = [];
        
        if ($this->userRole === 'teacher') {
            $coursesFilter = " AND c.teacher_id = ?";
            $params[] = $this->userId;
        } elseif ($this->userRole === 'student') {
            $coursesFilter = " AND EXISTS (
                SELECT 1 FROM course_enrollments ce 
                WHERE ce.course_id = c.id AND ce.student_id = ? AND ce.status = 'active'
            )";
            $params[] = $this->userId;
        }
        
        $schedules = $this->db->select("
            SELECT 
                cs.*,
                c.name as course_name,
                c.code as course_code,
                c.color,
                u.name as teacher_name
            FROM course_schedules cs
            INNER JOIN courses c ON cs.course_id = c.id
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE 1=1 $coursesFilter
            ORDER BY cs.day_of_week, cs.start_time
        ", $params) ?? [];
        
        return [
            'view'        => 'courses/view/schedule.php',
            'title'       => 'Ders Programı',
            'schedules'   => $schedules,
            'currentView' => $view,
            'currentDate' => $date,
            'userRole'    => $this->userRole
        ];
    }

    public function scheduleJson(): void
    {
        header('Content-Type: application/json');
        
        $start = $_GET['start'] ?? date('Y-m-d');
        $end = $_GET['end'] ?? date('Y-m-d', strtotime('+7 days'));
        
        $coursesFilter = '';
        $params = [];
        
        if ($this->userRole === 'teacher') {
            $coursesFilter = " AND c.teacher_id = ?";
            $params[] = $this->userId;
        } elseif ($this->userRole === 'student') {
            $coursesFilter = " AND EXISTS (
                SELECT 1 FROM course_enrollments ce 
                WHERE ce.course_id = c.id AND ce.student_id = ? AND ce.status = 'active'
            )";
            $params[] = $this->userId;
        }
        
        $schedules = $this->db->select("
            SELECT 
                cs.*,
                c.name as course_name,
                c.code as course_code,
                c.color,
                u.name as teacher_name
            FROM course_schedules cs
            INNER JOIN courses c ON cs.course_id = c.id
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE 1=1 $coursesFilter
        ", $params) ?? [];
        
        $events = [];
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);
        
        foreach ($schedules as $schedule) {
            $current = clone $startDate;
            while ($current <= $endDate) {
                $dayOfWeek = (int)$current->format('N');
                
                if ($dayOfWeek == $schedule['day_of_week']) {
                    $eventDate = $current->format('Y-m-d');
                    
                    $events[] = [
                        'id' => $schedule['id'] . '_' . $eventDate,
                        'title' => $schedule['course_name'],
                        'start' => $eventDate . 'T' . $schedule['start_time'],
                        'end' => $eventDate . 'T' . $schedule['end_time'],
                        'backgroundColor' => $schedule['color'] ?? '#3788d8',
                        'borderColor' => $schedule['color'] ?? '#3788d8',
                        'extendedProps' => [
                            'course_id' => $schedule['course_id'],
                            'course_code' => $schedule['course_code'],
                            'teacher' => $schedule['teacher_name']
                        ]
                    ];
                }
                
                $current->modify('+1 day');
            }
        }
        
        echo json_encode($events);
        exit;
    }

    /* ==================== HELPER - DÜZELTİLMİŞ ==================== */
    
    /**
     * ✅ DÜZELTİLDİ: color ve location kaldırıldı
     */
    private function saveSchedules(int $courseId): void
    {
        $this->db->execute("DELETE FROM course_schedules WHERE course_id = ?", [$courseId]);
        
        if (!isset($_POST['schedule_days'])) {
            return;
        }
        
        $scheduleDays = $_POST['schedule_days'] ?? [];
        $scheduleStartTimes = $_POST['schedule_start_times'] ?? [];
        $scheduleEndTimes = $_POST['schedule_end_times'] ?? [];
        
        foreach ($scheduleDays as $index => $day) {
            $startTime = $scheduleStartTimes[$index] ?? '';
            $endTime = $scheduleEndTimes[$index] ?? '';
            
            if (!empty($day) && !empty($startTime) && !empty($endTime)) {
                // ✅ SADECE 5 KOLON: course_id, day_of_week, start_time, end_time
                // color ve location KALDIRILDI
                $this->db->execute("
                    INSERT INTO course_schedules 
                    (course_id, day_of_week, start_time, end_time)
                    VALUES (?, ?, ?, ?)
                ", [$courseId, (int)$day, $startTime, $endTime]);
            }
        }
    }
}