<?php
require_once __DIR__ . '/../../core/database.php';

class RulesController
{
    protected $db;

    // Kural tipleri
    const RULE_TYPES = [
        'enrollment' => 'Ders Seçimi',
        'academic'   => 'Akademik',
        'project'    => 'Proje',
        'graduation' => 'Mezuniyet',
        'attendance' => 'Devamsızlık'
    ];

    // Kategoriler
    const CATEGORIES = [
        'ilkokul'  => 'İlkokul',
        'ortaokul' => 'Ortaokul',
        'lise'     => 'Lise'
    ];

    // Sınıf aralıkları
    const GRADE_RANGES = [
        '3-4'   => '3-4. Sınıf',
        '5-6'   => '5-6. Sınıf',
        '7-8'   => '7-8. Sınıf',
        '9-10'  => '9-10. Sınıf',
        '11-12' => '11-12. Sınıf',
        'all'   => 'Tüm Sınıflar'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
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

    /* ==================== TEST ==================== */
   /* ==================== TEST ==================== */
public function test(): array
{
    require_once __DIR__ . '/ruleengine.php';
    
    $studentId = (int)($_GET['student_id'] ?? 0);
    $courseId = (int)($_GET['course_id'] ?? 0);
    
    $ruleEngine = new RuleEngine();
    $canEnroll = false;
    $result = null;
    
    if ($studentId && $courseId) {
        $canEnroll = $ruleEngine->validateEnrollment($studentId, $courseId);
        $result = [
            'can_enroll' => $canEnroll,
            'violations' => $ruleEngine->getViolations(),
            'warnings'   => $ruleEngine->getWarnings()
        ];
    }
    
    // DOĞRUDAN PDO KULLAN
    $pdo = $this->db->getConnection();
    
    // Öğrenciler
    $stmtStudents = $pdo->query("SELECT id, name, sinif FROM users WHERE role = 'student' ORDER BY name");
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);
    
    // Dersler - CODE KOLONU YOK
    $stmtCourses = $pdo->query("SELECT id, name FROM courses ORDER BY name");
    $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'view'       => 'rules/view/test.php',
        'title'      => 'Kural Motoru Test',
        'studentId'  => $studentId,
        'courseId'   => $courseId,
        'students'   => $students,
        'courses'    => $courses,
        'result'     => $result
    ];
}
    /* ==================== INDEX/LIST ==================== */
    public function index(): array
    {
        return $this->list();
    }

    public function list(): array
    {
        $category = $_GET['category'] ?? null;
        $ruleType = $_GET['rule_type'] ?? null;

        $sql = "SELECT * FROM rules WHERE 1=1";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($ruleType) {
            $sql .= " AND rule_type = ?";
            $params[] = $ruleType;
        }

        $sql .= " ORDER BY category, priority DESC, id ASC";

        $rules = $this->db->select($sql, $params) ?? [];

        return [
            'view'       => 'rules/view/list.php',
            'title'      => 'Kurallar',
            'rules'      => $rules,
            'categories' => self::CATEGORIES,
            'ruleTypes'  => self::RULE_TYPES,
            'currentCategory' => $category,
            'currentRuleType' => $ruleType,
            'canDelete'  => ($this->currentRole() === 'admin')
        ];
    }

    /* ==================== CREATE ==================== */
    public function create(): array
    {
        return [
            'view'        => 'rules/view/form.php',
            'title'       => 'Yeni Kural',
            'rule'        => null,
            'categories'  => self::CATEGORIES,
            'ruleTypes'   => self::RULE_TYPES,
            'gradeRanges' => self::GRADE_RANGES,
            'isEdit'      => false,
            'formAction'  => 'index.php?module=rules&action=store'
        ];
    }

    /* ==================== STORE ==================== */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=rules&action=create');
            exit;
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'code'        => strtoupper(trim($_POST['code'] ?? '')),
            'category'    => $_POST['category'] ?? '',
            'grade_range' => $_POST['grade_range'] ?? null,
            'rule_type'   => $_POST['rule_type'] ?? '',
            'description' => trim($_POST['description'] ?? ''),
            'conditions'  => trim($_POST['conditions'] ?? ''),
            'priority'    => !empty($_POST['priority']) ? (int)$_POST['priority'] : 0,
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0
        ];

        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Kural adı zorunludur.';
        }

        if (empty($data['code'])) {
            $errors['code'] = 'Kural kodu zorunludur.';
        }

        if (empty($data['category'])) {
            $errors['category'] = 'Kategori seçimi zorunludur.';
        }

        if (empty($data['rule_type'])) {
            $errors['rule_type'] = 'Kural tipi seçimi zorunludur.';
        }

        // JSON validasyonu
        if (!empty($data['conditions'])) {
            json_decode($data['conditions']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['conditions'] = 'Koşullar geçerli JSON formatında olmalıdır.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=rules&action=create');
            exit;
        }

        // Duplicate check
        $dupCode = $this->db->fetch("SELECT id FROM rules WHERE code = ?", [$data['code']]);
        if ($dupCode) {
            $errors['code'] = 'Bu kural kodu zaten kullanımda.';
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=rules&action=create');
            exit;
        }

        try {
            $this->db->execute("
                INSERT INTO rules (
                    name, code, category, grade_range, rule_type, 
                    description, conditions, priority, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $data['name'], $data['code'], $data['category'], $data['grade_range'],
                $data['rule_type'], $data['description'], $data['conditions'],
                $data['priority'], $data['is_active']
            ]);

            unset($_SESSION['old_input'], $_SESSION['validation_errors']);

            $this->flashSuccess('Kural oluşturuldu.');
            header('Location: index.php?module=rules&action=list');
            exit;

        } catch (\Throwable $e) {
            error_log('Rule store error: ' . $e->getMessage());
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = ['general' => 'Veritabanı hatası: ' . $e->getMessage()];
            header('Location: index.php?module=rules&action=create');
            exit;
        }
    }

    /* ==================== EDIT ==================== */
    public function edit(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        $rule = $this->db->fetch("SELECT * FROM rules WHERE id = ?", [$id]);

        if (!$rule) {
            $this->flashError('Kural bulunamadı.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        $oldInput = $_SESSION['old_input'] ?? [];
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        $ruleData = [
            'id'          => (int)$rule['id'],
            'name'        => $rule['name'] ?? '',
            'code'        => $rule['code'] ?? '',
            'category'    => $rule['category'] ?? '',
            'grade_range' => $rule['grade_range'] ?? null,
            'rule_type'   => $rule['rule_type'] ?? '',
            'description' => $rule['description'] ?? '',
            'conditions'  => $rule['conditions'] ?? '',
            'priority'    => $rule['priority'] ?? 0,
            'is_active'   => (int)($rule['is_active'] ?? 1)
        ];

        if (!empty($oldInput)) {
            $ruleData = array_merge($ruleData, $oldInput);
        }

        return [
            'view'        => 'rules/view/form.php',
            'title'       => 'Kural Düzenle',
            'rule'        => $ruleData,
            'categories'  => self::CATEGORIES,
            'ruleTypes'   => self::RULE_TYPES,
            'gradeRanges' => self::GRADE_RANGES,
            'isEdit'      => true,
            'formAction'  => 'index.php?module=rules&action=update&id=' . $id,
            'errors'      => $errors
        ];
    }

    /* ==================== UPDATE ==================== */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        $rule = $this->db->fetch("SELECT * FROM rules WHERE id = ?", [$id]);

        if (!$rule) {
            $this->flashError('Kural bulunamadı.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'code'        => strtoupper(trim($_POST['code'] ?? '')),
            'category'    => $_POST['category'] ?? '',
            'grade_range' => $_POST['grade_range'] ?? null,
            'rule_type'   => $_POST['rule_type'] ?? '',
            'description' => trim($_POST['description'] ?? ''),
            'conditions'  => trim($_POST['conditions'] ?? ''),
            'priority'    => !empty($_POST['priority']) ? (int)$_POST['priority'] : 0,
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0
        ];

        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Kural adı zorunludur.';
        }

        if (empty($data['code'])) {
            $errors['code'] = 'Kural kodu zorunludur.';
        }

        if (!empty($data['conditions'])) {
            json_decode($data['conditions']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['conditions'] = 'Koşullar geçerli JSON formatında olmalıdır.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=rules&action=edit&id=' . $id);
            exit;
        }

        $dupCode = $this->db->fetch("SELECT id FROM rules WHERE code = ? AND id != ?", [$data['code'], $id]);
        if ($dupCode) {
            $errors['code'] = 'Bu kural kodu başka bir kuralda kullanılıyor.';
        }

        if (!empty($errors)) {
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = $errors;
            header('Location: index.php?module=rules&action=edit&id=' . $id);
            exit;
        }

        try {
            $this->db->execute("
                UPDATE rules SET
                    name=?, code=?, category=?, grade_range=?, rule_type=?,
                    description=?, conditions=?, priority=?, is_active=?
                WHERE id=?
            ", [
                $data['name'], $data['code'], $data['category'], $data['grade_range'],
                $data['rule_type'], $data['description'], $data['conditions'],
                $data['priority'], $data['is_active'], $id
            ]);

            unset($_SESSION['old_input'], $_SESSION['validation_errors']);

            $this->flashSuccess('Kural güncellendi.');
            header('Location: index.php?module=rules&action=show&id=' . $id);
            exit;

        } catch (\Throwable $e) {
            error_log('Rule update error: ' . $e->getMessage());
            $_SESSION['old_input'] = $data;
            $_SESSION['validation_errors'] = ['general' => 'Veritabanı hatası: ' . $e->getMessage()];
            header('Location: index.php?module=rules&action=edit&id=' . $id);
            exit;
        }
    }

    /* ==================== SHOW ==================== */
    public function show(): array
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        $rule = $this->db->fetch("SELECT * FROM rules WHERE id = ?", [$id]);

        if (!$rule) {
            $this->flashError('Kural bulunamadı.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        return [
            'view'       => 'rules/view/show.php',
            'title'      => 'Kural Detay',
            'rule'       => $rule,
            'categories' => self::CATEGORIES,
            'ruleTypes'  => self::RULE_TYPES,
            'gradeRanges'=> self::GRADE_RANGES,
            'canDelete'  => ($this->currentRole() === 'admin')
        ];
    }

    /* ==================== DELETE ==================== */
    public function delete(): void
    {
        if ($this->currentRole() !== 'admin') {
            $this->flashError('Silme yetkiniz yok.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('Geçersiz ID.');
            header('Location: index.php?module=rules&action=list');
            exit;
        }

        try {
            $this->db->execute("DELETE FROM rules WHERE id = ?", [$id]);

            $this->flashSuccess('Kural silindi.');
            header('Location: index.php?module=rules&action=list');
            exit;

        } catch (\Throwable $e) {
            error_log('Rule delete error: ' . $e->getMessage());
            $this->flashError('Silme hatası: ' . $e->getMessage());
            header('Location: index.php?module=rules&action=list');
            exit;
        }
    }
}