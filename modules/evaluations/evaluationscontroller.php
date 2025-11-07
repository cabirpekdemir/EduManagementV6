<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class EvaluationsController 
{
    protected $db;
    protected $userId;
    protected $userRole;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userId = $_SESSION['user']['id'] ?? null;
        $this->userRole = $_SESSION['user']['role'] ?? null;
        if (!$this->userId) {
            redirect('index.php?module=login&action=index');
            exit;
        }
    }

    private function getEvaluationTypes()
    {
        return ['Yazılı Sınav', 'Sözlü', 'Ödev', 'Etkinlik', 'Davranış', 'Proje', 'Diğer'];
    }

    public function index() 
    {
        $evaluations = $this->db->select("
            SELECT e.*, c.name as class_name 
            FROM evaluations e
            LEFT JOIN classes c ON c.id = e.class_id
            ORDER BY e.exam_date DESC, e.exam_time DESC
        ");
        
        return [
            'pageTitle' => 'Tüm Değerlendirmeler', 
            'evaluations' => $evaluations
        ];
    }

    public function create() 
    {
        // Öğrenciler - durum bilgisi ile
        $all_students = $this->db->select("
            SELECT id, name, enrollment_status, sinif 
            FROM users 
            WHERE role = 'student' OR role_id = 3
            ORDER BY name ASC
        ");
        
        $all_teachers = $this->db->select("
            SELECT id, name 
            FROM users 
            WHERE role = 'teacher' OR role_id = 2
            ORDER BY name ASC
        ");
        
        // Sınıflar - seviye ile gruplu
        $classes = $this->db->select("
            SELECT id, name, level 
            FROM classes 
            ORDER BY level ASC, name ASC
        ");
        
        // Sınıfları seviyeye göre grupla
        $classes_by_level = [];
        foreach ($classes as $class) {
            $level = $class['level'] ?? 'ilkokul';
            $classes_by_level[$level][] = $class;
        }
        
        // Durum listesi
        $enrollment_statuses = [
            'on_kayit' => 'Ön Kayıt',
            'sinav_secim' => 'Sınav Seçim',
            'sinav_secimi_yapti' => 'Sınav Seçimi Yaptı',
            'ders_secimi_yapan' => 'Ders Seçimi Yapan',
            'aktif' => 'Aktif',
            'mezun' => 'Mezun'
        ];
        
        return [
            'pageTitle' => 'Yeni Değerlendirme Oluştur',
            'classes_by_level' => $classes_by_level,
            'all_students' => $all_students,
            'all_teachers' => $all_teachers,
            'enrollment_statuses' => $enrollment_statuses,
            'evaluation_types' => $this->getEvaluationTypes()
        ];
    }

 public function store()
{
    $pdo = $this->db->getConnection();
    try {
        $pdo->beginTransaction();
        
        $exam_date = $_POST['exam_date'] ?: null;
        $exam_time = $_POST['exam_time'] ?: null;
        
        // class_or_level parse et
        $class_id = null;
        $level_filter = null;
        
        if (!empty($_POST['class_or_level'])) {
            $value = $_POST['class_or_level'];
            if (strpos($value, 'level:') === 0) {
                // Seviye seçilmiş (ilkokul/ortaokul)
                $level_filter = str_replace('level:', '', $value);
            } elseif (strpos($value, 'class:') === 0) {
                // Sınıf ID seçilmiş
                $class_id = (int)str_replace('class:', '', $value);
            }
        }
        
        $sql = "INSERT INTO evaluations 
                (name, description, evaluation_type, exam_date, exam_time, 
                 class_id, level_filter, creator_id, max_score, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['evaluation_type'],
            $exam_date,
            $exam_time,
            $class_id,
            $level_filter,
            $this->userId,
            $_POST['max_score'] ?: 100.00,
            $_POST['status'] ?? 'draft'
        ]);
        
        $evaluation_id = $pdo->lastInsertId();

        // Öğrenci atamaları (mevcut kod devam eder...)
        $students_to_assign = [];
        
        // 1. Bireysel seçilen öğrenciler
        if (!empty($_POST['students'])) {
            $students_to_assign = array_merge($students_to_assign, $_POST['students']);
        }
        
        // 2. Duruma göre toplu atama
        if (!empty($_POST['assign_by_status'])) {
            $status = $_POST['assign_by_status'];
            $students_by_status = $this->db->select(
                "SELECT id FROM users WHERE enrollment_status = ? AND (role = 'student' OR role_id = 3)",
                [$status]
            );
            foreach ($students_by_status as $s) {
                $students_to_assign[] = $s['id'];
            }
        }
        
        // 3. Sınıf seviyesine göre toplu atama
        if (!empty($_POST['assign_by_grade'])) {
            $grade = $_POST['assign_by_grade'];
            if (strpos($grade, '-') !== false) {
                list($from_grade, $to_grade) = explode('-', $grade);
                $students_by_grade = $this->db->select(
                    "SELECT id FROM users 
                     WHERE (sinif LIKE ? OR sinif LIKE ?) 
                     AND (role = 'student' OR role_id = 3)",
                    ["%$from_grade%", "%$to_grade%"]
                );
                foreach ($students_by_grade as $s) {
                    $students_to_assign[] = $s['id'];
                }
            }
        }
        
        // Tekrarları temizle
        $students_to_assign = array_unique($students_to_assign);
        
        // Öğrencileri kaydet
        if (!empty($students_to_assign)) {
            $student_sql = "INSERT INTO evaluation_students (evaluation_id, student_id) VALUES (?, ?)";
            $student_stmt = $pdo->prepare($student_sql);
            foreach ($students_to_assign as $student_id) {
                $student_stmt->execute([$evaluation_id, $student_id]);
            }
        }

        // Öğretmen atamaları
        if (!empty($_POST['teachers'])) {
            $teacher_sql = "INSERT INTO evaluation_teachers (evaluation_id, teacher_id, role) VALUES (?, ?, ?)";
            $teacher_stmt = $pdo->prepare($teacher_sql);
            foreach ($_POST['teachers'] as $teacher_assignment) {
                if (!empty($teacher_assignment['id']) && !empty($teacher_assignment['role'])) {
                    $teacher_stmt->execute([
                        $evaluation_id,
                        $teacher_assignment['id'],
                        $teacher_assignment['role']
                    ]);
                }
            }
        }

        $pdo->commit();
        redirect('index.php?module=evaluations&action=index&status=success');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Hata: " . $e->getMessage());
    }
}
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) redirect('index.php?module=evaluations&action=index');

        $evaluation = $this->db->select("SELECT * FROM evaluations WHERE id = ?", [$id])[0] ?? null;
        if (!$evaluation) die("Değerlendirme bulunamadı!");
        
        $all_students = $this->db->select("
            SELECT id, name, enrollment_status, sinif 
            FROM users 
            WHERE role = 'student' OR role_id = 3
            ORDER BY name ASC
        ");
        
        $all_teachers = $this->db->select("
            SELECT id, name 
            FROM users 
            WHERE role = 'teacher' OR role_id = 2
            ORDER BY name ASC
        ");
        
        $classes = $this->db->select("
            SELECT id, name, level 
            FROM classes 
            ORDER BY level ASC, name ASC
        ");
        
        $classes_by_level = [];
        foreach ($classes as $class) {
            $level = $class['level'] ?? 'ilkokul';
            $classes_by_level[$level][] = $class;
        }
        
        $assigned_students_raw = $this->db->select(
            "SELECT student_id FROM evaluation_students WHERE evaluation_id = ?",
            [$id]
        );
        $assigned_students = array_column($assigned_students_raw, 'student_id');

        $assigned_teachers = $this->db->select(
            "SELECT teacher_id, role FROM evaluation_teachers WHERE evaluation_id = ?",
            [$id]
        );
        
        $enrollment_statuses = [
            'on_kayit' => 'Ön Kayıt',
            'sinav_secim' => 'Sınav Seçim',
            'sinav_secimi_yapti' => 'Sınav Seçimi Yaptı',
            'ders_secimi_yapan' => 'Ders Seçimi Yapan',
            'aktif' => 'Aktif',
            'mezun' => 'Mezun'
        ];

        return [
            'pageTitle' => 'Değerlendirmeyi Düzenle',
            'evaluation' => $evaluation,
            'classes_by_level' => $classes_by_level,
            'all_students' => $all_students,
            'all_teachers' => $all_teachers,
            'assigned_students' => $assigned_students,
            'assigned_teachers' => $assigned_teachers,
            'enrollment_statuses' => $enrollment_statuses,
            'evaluation_types' => $this->getEvaluationTypes()
        ];
    }

  public function update()
{
    $evaluation_id = $_POST['id'] ?? null;
    if (!$evaluation_id) redirect('index.php?module=evaluations&action=index');
    
    $pdo = $this->db->getConnection();
    try {
        $pdo->beginTransaction();
        
        $exam_date = $_POST['exam_date'] ?: null;
        $exam_time = $_POST['exam_time'] ?: null;
        
        // class_or_level parse et
        $class_id = null;
        $level_filter = null;
        
        if (!empty($_POST['class_or_level'])) {
            $value = $_POST['class_or_level'];
            if (strpos($value, 'level:') === 0) {
                $level_filter = str_replace('level:', '', $value);
            } elseif (strpos($value, 'class:') === 0) {
                $class_id = (int)str_replace('class:', '', $value);
            }
        }
        
        $sql = "UPDATE evaluations SET 
                name = ?, description = ?, evaluation_type = ?, 
                exam_date = ?, exam_time = ?, class_id = ?, level_filter = ?,
                max_score = ?, status = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['evaluation_type'],
            $exam_date,
            $exam_time,
            $class_id,
            $level_filter,
            $_POST['max_score'] ?: 100.00,
            $_POST['status'] ?? 'draft',
            $evaluation_id
        ]);

        // Öğrenci ve öğretmen atamalarını yeniden yap
        $pdo->prepare("DELETE FROM evaluation_students WHERE evaluation_id = ?")->execute([$evaluation_id]);
        $pdo->prepare("DELETE FROM evaluation_teachers WHERE evaluation_id = ?")->execute([$evaluation_id]);

        if (!empty($_POST['students'])) {
            $student_sql = "INSERT INTO evaluation_students (evaluation_id, student_id) VALUES (?, ?)";
            $student_stmt = $pdo->prepare($student_sql);
            foreach ($_POST['students'] as $student_id) {
                $student_stmt->execute([$evaluation_id, $student_id]);
            }
        }
        
        if (!empty($_POST['teachers'])) {
            $teacher_sql = "INSERT INTO evaluation_teachers (evaluation_id, teacher_id, role) VALUES (?, ?, ?)";
            $teacher_stmt = $pdo->prepare($teacher_sql);
            foreach ($_POST['teachers'] as $teacher_assignment) {
                if (!empty($teacher_assignment['id']) && !empty($teacher_assignment['role'])) {
                    $teacher_stmt->execute([
                        $evaluation_id,
                        $teacher_assignment['id'],
                        $teacher_assignment['role']
                    ]);
                }
            }
        }
        
        $pdo->commit();
        redirect("index.php?module=evaluations&action=edit&id={$evaluation_id}&status=updated");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Hata: " . $e->getMessage());
    }
}
    
 public function results()
{
    $evaluation_id = $_GET['id'] ?? null;
    if (!$evaluation_id) redirect('index.php?module=evaluations&action=index');

    $evaluation = $this->db->select("SELECT * FROM evaluations WHERE id = ?", [$evaluation_id])[0] ?? null;
    if (!$evaluation) die("Değerlendirme bulunamadı!");

    $students = [];

    // 1. Bireysel atanmış öğrenciler
    $assigned_students = $this->db->select("
        SELECT u.id, u.name, u.enrollment_status 
        FROM users u 
        JOIN evaluation_students es ON u.id = es.student_id 
        WHERE es.evaluation_id = ? 
        ORDER BY u.name ASC",
        [$evaluation_id]
    );

    if (!empty($assigned_students)) {
        $students = $assigned_students;
    } 
    // 2. Seviye filtresi var mı? (ilkokul/ortaokul)
    elseif (!empty($evaluation['level_filter'])) {
        $level = $evaluation['level_filter'];
        
        // İlgili seviyedeki TÜM sınıfların ID'lerini bul
        $levelClasses = $this->db->select(
            "SELECT id FROM classes WHERE level = ?",
            [$level]
        );
        
        if (!empty($levelClasses)) {
            $classIds = array_column($levelClasses, 'id');
            $placeholders = str_repeat('?,', count($classIds) - 1) . '?';
            
            $students = $this->db->select("
                SELECT id, name, enrollment_status 
                FROM users 
                WHERE class_id IN ($placeholders) 
                AND (role = 'student' OR role_id = 3)
                ORDER BY name ASC",
                $classIds
            );
        }
    }
    // 3. Belirli bir sınıf seçilmiş mi?
    elseif (!empty($evaluation['class_id'])) {
        $students = $this->db->select("
            SELECT id, name, enrollment_status 
            FROM users 
            WHERE class_id = ? AND (role = 'student' OR role_id = 3)
            ORDER BY name ASC",
            [$evaluation['class_id']]
        );
    }
    
    // Mevcut sonuçları çek
    $existing_results_raw = $this->db->select(
        "SELECT * FROM evaluation_results WHERE evaluation_id = ?",
        [$evaluation_id]
    );
    $existing_results = array_column($existing_results_raw, null, 'student_id');

    return [
        'pageTitle' => ($evaluation['name'] ?? '') . ' Sonuç Girişi',
        'evaluation' => $evaluation,
        'students' => $students,
        'existing_results' => $existing_results
    ];
}
    public function store_results()
    {
        $evaluation_id = $_POST['evaluation_id'] ?? null;
        if (!$evaluation_id) die("Değerlendirme ID eksik!");
        
        $evaluation = $this->db->select("SELECT * FROM evaluations WHERE id = ?", [$evaluation_id])[0] ?? null;
        $is_written_exam = ($evaluation['evaluation_type'] ?? '') === 'Yazılı Sınav';
        
        $results = $_POST['results'] ?? [];

        foreach ($results as $student_id => $data) {
            $score = $data['score'] !== '' ? $data['score'] : null;
            $comments = $data['comments'] ?? '';
            
            // Alt puanlar (sadece Yazılı Sınav için)
            $score_dil = $is_written_exam && isset($data['score_dil']) && $data['score_dil'] !== '' 
                ? $data['score_dil'] : null;
            $score_sekil_uzay = $is_written_exam && isset($data['score_sekil_uzay']) && $data['score_sekil_uzay'] !== '' 
                ? $data['score_sekil_uzay'] : null;
            $score_ayird_etme = $is_written_exam && isset($data['score_ayird_etme']) && $data['score_ayird_etme'] !== '' 
                ? $data['score_ayird_etme'] : null;
            $score_sayisal = $is_written_exam && isset($data['score_sayisal']) && $data['score_sayisal'] !== '' 
                ? $data['score_sayisal'] : null;
            $score_akil_yurutme = $is_written_exam && isset($data['score_akil_yurutme']) && $data['score_akil_yurutme'] !== '' 
                ? $data['score_akil_yurutme'] : null;
            $score_genel = $is_written_exam && isset($data['score_genel']) && $data['score_genel'] !== '' 
                ? $data['score_genel'] : null;

            $existing_result = $this->db->select(
                "SELECT id FROM evaluation_results WHERE evaluation_id = ? AND student_id = ?",
                [$evaluation_id, $student_id]
            );

            if (!empty($existing_result)) {
                $result_id = $existing_result[0]['id'];
                $this->db->getConnection()->prepare("
                    UPDATE evaluation_results SET 
                        score = ?, comments = ?,
                        score_dil = ?, score_sekil_uzay = ?, score_ayird_etme = ?,
                        score_sayisal = ?, score_akil_yurutme = ?, score_genel = ?
                    WHERE id = ?
                ")->execute([
                    $score, $comments,
                    $score_dil, $score_sekil_uzay, $score_ayird_etme,
                    $score_sayisal, $score_akil_yurutme, $score_genel,
                    $result_id
                ]);
            } else {
                if ($score !== null || !empty($comments) || $score_dil !== null) {
                    $this->db->getConnection()->prepare("
                        INSERT INTO evaluation_results 
                        (evaluation_id, student_id, score, comments, entry_by_user_id,
                         score_dil, score_sekil_uzay, score_ayird_etme,
                         score_sayisal, score_akil_yurutme, score_genel)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ")->execute([
                        $evaluation_id, $student_id, $score, $comments, $this->userId,
                        $score_dil, $score_sekil_uzay, $score_ayird_etme,
                        $score_sayisal, $score_akil_yurutme, $score_genel
                    ]);
                }
            }
        }
        
        redirect("index.php?module=evaluations&action=results&id={$evaluation_id}&status=saved");
        exit;
    }
}