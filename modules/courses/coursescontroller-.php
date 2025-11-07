<?php
class CoursesController // veya class CoursesController extends Controller
{
    /**
     * @var PDO Veritabanı bağlantı nesnesi
     */
    private $db;

    /**
     * Controller'ın kurucu metodu.
     * *** EN ÖNEMLİ DÜZELTME BURADA ***
     * Bu metot, "undefined method Database::prepare()" hatasını KESİN OLARAK çözer.
     */
    public function __construct()
    {
        // Database yöneticisinden ->getConnection() ile gerçek PDO bağlantısını istiyoruz.
        $this->db = Database::getInstance()->getConnection();
    }

    /* ================= Helpers ================= */

    private function hasCol(string $table, string $col): bool
{
    // SHOW COLUMNS sorgusu parametreli çalışmıyor, direkt çalıştıralım
    try {
        $query = "SHOW COLUMNS FROM `$table` LIKE '$col'";
        $result = $this->db->getConnection()->query($query);
        return $result && $result->rowCount() > 0;
    } catch (\Exception $e) {
        error_log("hasCol Error: " . $e->getMessage());
        return false;
    }
}
    
    private function stageLabel(?string $v): string
    {
        return $v === 'primary' ? 'İlkokul' : ($v === 'middle' ? 'Ortaokul' : '—');
    }

    private function teachers(): array
    {
        return $this->db->select("
            SELECT id, name
              FROM users
             WHERE role='teacher' AND COALESCE(is_active,1)=1
             ORDER BY name
        ");
    }

    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg];
    }

    private function redirect(string $url): void
    {
        header('Location: '.$url); exit;
    }

    /**
     * INSERT/UPDATE ortak yazım
     */
    private function write(bool $isUpdate): array
    {
        // Güvenli alan listesi
        $cols   = ['name','teacher_id'];
        if ($this->hasCol('courses','stage')) {
            $cols[] = 'stage';
        }

        $data = [];
        $params = [];
        foreach ($cols as $c) {
            $val = $_POST[$c] ?? null;
            $data[$c] = is_string($val) ? trim($val) : $val;
            $params[] = $data[$c];
        }

        if ($isUpdate) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                return [
                    'redir' => 'index.php?module=courses&action=index',
                    'flash' => ['type'=>'danger','msg'=>'Geçersiz ID']
                ];
            }
            $assign = implode(',', array_map(fn($c)=>"`$c`=?", array_keys($data)));
            $params[] = $id;
            $this->db->select("UPDATE `courses` SET $assign WHERE `id`=?", $params);

            return [
                'redir' => 'index.php?module=courses&action=edit&id='.$id,
                'flash' => ['type'=>'success','msg'=>'Ders güncellendi.']
            ];
        } else {
            $colStr = '`'.implode('`,`', array_keys($data)).'`';
            $qStr   = rtrim(str_repeat('?,', count($data)), ',');
            $this->db->select("INSERT INTO `courses` ($colStr) VALUES ($qStr)", $params);
            return [
                'redir' => 'index.php?module=courses&action=index',
                'flash' => ['type'=>'success','msg'=>'Ders oluşturuldu.']
            ];
        }
    }

    
    /* ================= Actions ================= */

   public function index()
    {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM courses";
        $countSql = "SELECT COUNT(id) FROM courses";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE name LIKE ?";
            $countSql .= " WHERE name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY name ASC LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalResults = $countStmt->fetchColumn();
        $totalPages = ceil($totalResults / $limit);
        
        $pageTitle = "Atölye ve Dersler";
        
        $this->renderView('index', [
            'courses' => $courses,
            'pageTitle' => $pageTitle,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search
        ]);
    }
public function create(): array
{
    return [
        'view'     => 'modules/courses/create.php',
        'title'    => 'Yeni Ders',
        'teachers' => $this->teachers(),
        'hasStage' => $this->hasCol('courses','stage'),
        'courseTimes' => [] // YENİ
    ];
}

   
   public function show() // Hatanın oluştuğu fonksiyon buydu (line 175)
    {
        $id = $_GET['id'];
        // Hata bu satırda oluşuyordu çünkü $this->db doğru nesne değildi.
        // __construct düzeltildiği için artık bu satır doğru çalışacaktır.
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt_prereq = $this->db->prepare("
            SELECT c.name FROM course_prerequisites cp
            JOIN courses c ON cp.prerequisite_course_id = c.id
            WHERE cp.course_id = ?
        ");
        $stmt_prereq->execute([$id]);
        $prerequisites = $stmt_prereq->fetchAll(PDO::FETCH_COLUMN);

        $pageTitle = "Ders Detayları";
        $this->renderView('show', [
            'course' => $course,
            'prerequisites' => $prerequisites,
            'pageTitle' => $pageTitle
        ]);
    }
    public function edit(): array
{
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        $this->flash('danger','Geçersiz ID.');
        $this->redirect('index.php?module=courses&action=index');
    }

    $c = $this->db->fetch("SELECT * FROM courses WHERE id=? LIMIT 1", [$id]);
    if (!$c) {
        $this->flash('danger','Ders bulunamadı.');
        $this->redirect('index.php?module=courses&action=index');
    }
    
    // Ders zamanlarını çek
    $courseTimes = $this->db->select("SELECT * FROM course_times WHERE course_id = ? ORDER BY day, start_time", [$id]);

    return [
        'view'     => 'modules/courses/edit.php',
        'title'    => 'Ders Düzenle',
        'course'   => $c,
        'teachers' => $this->teachers(),
        'hasStage' => $this->hasCol('courses','stage'),
        'courseTimes' => $courseTimes ?? [] // YENİ
    ];
}

   public function store()
{
    $r = $this->write(false);
    
    // Ders zamanlarını kaydet
    if ($r['flash']['type'] === 'success') {
        $courseId = $this->db->getConnection()->lastInsertId();
        $this->saveCourseTimes($courseId);
    }
    
    $this->flash($r['flash']['type'], $r['flash']['msg']);
    $this->redirect($r['redir']);
}

public function update()
{
    $r = $this->write(true);
    
    // Ders zamanlarını güncelle
    if ($r['flash']['type'] === 'success') {
        $courseId = (int)($_POST['id'] ?? 0);
        $this->saveCourseTimes($courseId);
    }
    
    $this->flash($r['flash']['type'], $r['flash']['msg']);
    $this->redirect($r['redir']);
}

// YENİ METOT - Dosyanın en altına ekleyin
private function saveCourseTimes(int $courseId): void
{
    // Önce mevcut zamanları sil
    $this->db->select("DELETE FROM course_times WHERE course_id = ?", [$courseId]);
    
    // Yeni zamanları ekle
    $days = $_POST['time_day'] ?? [];
    $starts = $_POST['time_start'] ?? [];
    $ends = $_POST['time_end'] ?? [];
    
    foreach ($days as $index => $day) {
        $start = $starts[$index] ?? '';
        $end = $ends[$index] ?? '';
        
        if (!empty($day) && !empty($start) && !empty($end)) {
            $this->db->select(
                "INSERT INTO course_times (course_id, day, start_time, end_time) VALUES (?, ?, ?, ?)",
                [$courseId, (int)$day, $start, $end]
            );
        }
    }
}

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger','Geçersiz ID.');
            $this->redirect('index.php?module=courses&action=index');
        }

        try {
            // Sistem standardınız: tam silme yok → pasif et
            if ($this->hasCol('courses','is_active')) {
                $this->db->select("UPDATE `courses` SET `is_active`=0 WHERE `id`=? LIMIT 1", [$id]);
                $this->flash('success','Ders pasif edildi.');
            } else {
                // is_active kolonu yoksa hard delete fallback (isterseniz ilişkileri önce temizleyin)
                $this->db->select("DELETE FROM `courses` WHERE `id`=? LIMIT 1", [$id]);
                $this->flash('success','Ders silindi.');
            }
        } catch (\Throwable $e) {
            $this->flash('danger', 'Silme/pasif etme başarısız: '.$e->getMessage());
        }

        $this->redirect('index.php?module=courses&action=index');
    }
    // --- BU İKİ YENİ FONKSİYONUN TAMAMINI EKLEYİN ---

    /**
     * Bir dersin kurallarını düzenleme formunu gösterir.
     */
      public function editRules()
    {
        $courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$courseId) { die("Geçersiz Ders ID'si."); }

        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$course) { die("Ders bulunamadı."); }

        $stmt_all = $this->db->prepare("SELECT id, name FROM courses WHERE id != ? ORDER BY name ASC");
        $stmt_all->execute([$courseId]);
        $allOtherCourses = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

        $stmt_prereq = $this->db->prepare("SELECT prerequisite_course_id FROM course_prerequisites WHERE course_id = ?");
        $stmt_prereq->execute([$courseId]);
        $currentPrerequisites = $stmt_prereq->fetchAll(PDO::FETCH_COLUMN);

        $pageTitle = htmlspecialchars($course['name']) . " - Kurallarını Düzenle";
        $this->renderView('edit_rules', [
            'course' => $course,
            'allOtherCourses' => $allOtherCourses,
            'currentPrerequisites' => $currentPrerequisites,
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * Kural düzenleme formundan gelen verileri kaydeder.
     */
        public function updateRules()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?module=courses'); exit;
        }

        $courseId = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
        $min_age = filter_input(INPUT_POST, 'min_age', FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $max_age = filter_input(INPUT_POST, 'max_age', FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $prerequisites = $_POST['prerequisites'] ?? [];

        $stmt_update = $this->db->prepare("UPDATE courses SET min_age = ?, max_age = ?, capacity = ? WHERE id = ?");
        $stmt_update->execute([$min_age, $max_age, $capacity, $courseId]);

        $stmt_delete = $this->db->prepare("DELETE FROM course_prerequisites WHERE course_id = ?");
        $stmt_delete->execute([$courseId]);

        if (!empty($prerequisites)) {
            $stmt_insert = $this->db->prepare("INSERT INTO course_prerequisites (course_id, prerequisite_course_id) VALUES (?, ?)");
            foreach ($prerequisites as $prereqId) {
                if (filter_var($prereqId, FILTER_VALIDATE_INT)) {
                    $stmt_insert->execute([$courseId, $prereqId]);
                }
            }
        }

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Ders kuralları başarıyla güncellendi.'];
        header('Location: /?module=courses&action=editRules&id=' . $courseId);
        exit;
    }

}
