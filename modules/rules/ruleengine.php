<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Rule Engine - Kuralları Uygulayan Mantık Motoru
 * 
 * Bu sınıf sistemdeki kuralları kontrol eder ve uygular.
 */
class RuleEngine
{
    protected $db;
    protected $violations = []; // Kural ihlalleri
    protected $warnings = [];   // Uyarılar

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Tüm ihlalleri döndür
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Tüm uyarıları döndür
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * İhlal veya uyarı var mı?
     */
    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * İhlalleri temizle
     */
    public function clearViolations(): void
    {
        $this->violations = [];
        $this->warnings = [];
    }

    /**
     * Belirli kategori ve tipteki aktif kuralları getir
     */
    protected function getRules(?string $category = null, ?string $ruleType = null): array
    {
        $sql = "SELECT * FROM rules WHERE is_active = 1";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($ruleType) {
            $sql .= " AND rule_type = ?";
            $params[] = $ruleType;
        }

        $sql .= " ORDER BY priority DESC, id ASC";

        return $this->db->select($sql, $params) ?? [];
    }

   
    /* ==================== DERS SEÇİMİ KURALLARI ==================== */

    /**
     * Öğrencinin bir derse kayıt olup olamayacağını kontrol et
     * 
     * @param int $studentId Öğrenci ID
     * @param int $courseId Ders ID
     * @return bool Kayıt olabilir mi?
     */
/* ==================== DERS SEÇİMİ KURALLARI ==================== */

/**
 * Öğrencinin bir derse kayıt olup olamayacağını kontrol et
 * 
 * @param int $studentId Öğrenci ID
 * @param int $courseId Ders ID
 * @return bool Kayıt olabilir mi?
 */
/**
 * Öğrencinin bir derse kayıt olup olamayacağını kontrol et
 */
public function validateEnrollment(int $studentId, int $courseId): bool
{
    $this->clearViolations();

    $context = $this->getStudentContext($studentId);
    if (!$context['category']) {
        $this->violations[] = 'Öğrenci kategorisi belirlenemedi. Lütfen öğrencinin sınıf bilgisini kontrol edin.';
        return false;
    }

    // ===== YENİ: KADEME KONTROLÜ =====
    $course = $this->db->fetch("SELECT id, name, category FROM courses WHERE id = ?", [$courseId]);
    if (!$course) {
        $this->violations[] = 'Ders bulunamadı.';
        return false;
    }

    // Ders kategorisi varsa, öğrenci kategorisiyle eşleşmeli
    if (!empty($course['category'])) {
        if ($course['category'] !== $context['category']) {
            $categoryNames = [
                'ilkokul' => 'İlkokul',
                'ortaokul' => 'Ortaokul',
                'lise' => 'Lise'
            ];
            $this->violations[] = sprintf(
                'Kademe Uyumsuzluğu: %s öğrencisi sadece %s derslerine kayıt olabilir. Bu ders %s içindir.',
                $categoryNames[$context['category']] ?? $context['category'],
                $categoryNames[$context['category']] ?? $context['category'],
                $categoryNames[$course['category']] ?? $course['category']
            );
            return false;
        }
    }
    // ===== KADEME KONTROLÜ BİTTİ =====

    // İlgili kuralları getir
    $rules = $this->getRules($context['category'], 'enrollment');

    foreach ($rules as $rule) {
        $conditions = $this->parseConditions($rule['conditions'] ?? '');
        if (!$conditions) continue;

        // Kural: Ders tekrar limiti
        if ($rule['code'] === 'ELEM_REPEAT' || isset($conditions['max_enrollment'])) {
            if (!$this->checkMaxEnrollment($studentId, $courseId, $conditions['max_enrollment'] ?? 1)) {
                $this->violations[] = $rule['name'] . ': ' . $rule['description'];
            }
        }

        // Kural: Yıllık özel ders (Müzik VE Resim aynı yıl alınamaz)
        if ($rule['code'] === 'YEARLY_ART_MUSIC' || isset($conditions['exclusive'])) {
            if (!$this->checkExclusiveCourses($studentId, $courseId, $conditions['courses'] ?? [])) {
                $this->violations[] = $rule['name'] . ': ' . $rule['description'];
            }
        }
    }

    return !$this->hasViolations();
}
/**
 * Ders tekrar limitini kontrol et
 */
protected function checkMaxEnrollment(int $studentId, int $courseId, int $maxEnrollment): bool
{
    // Öğrenci bu dersi kaç kez aldı?
    $count = $this->db->fetch("
        SELECT COUNT(*) as count 
        FROM student_enrollments 
        WHERE student_id = ? AND course_id = ?
    ", [$studentId, $courseId]);

    return ($count['count'] ?? 0) < $maxEnrollment;
}

/**
 * Özel dersler (exclusive) - aynı yıl birden fazla alınamaz
 */
protected function checkExclusiveCourses(int $studentId, int $courseId, array $exclusiveCourses): bool
{
    if (empty($exclusiveCourses)) return true;

    // Bu ders exclusive listede mi?
    $course = $this->db->fetch("SELECT name FROM courses WHERE id = ?", [$courseId]);
    if (!$course) return true;

    $courseName = strtolower($course['name']);
    $isExclusive = false;
    foreach ($exclusiveCourses as $exc) {
        if (stripos($courseName, $exc) !== false) {
            $isExclusive = true;
            break;
        }
    }

    if (!$isExclusive) return true;

    // Bu yıl exclusive listeden başka ders aldı mı?
    $currentYear = date('Y');
    $enrollments = $this->db->select("
        SELECT c.name 
        FROM student_enrollments se
        JOIN courses c ON c.id = se.course_id
        WHERE se.student_id = ? 
        AND YEAR(se.enrolled_at) = ?
    ", [$studentId, $currentYear]) ?? [];

    foreach ($enrollments as $enrollment) {
        $enrolledCourseName = strtolower($enrollment['name']);
        foreach ($exclusiveCourses as $exc) {
            if (stripos($enrolledCourseName, $exc) !== false && $enrolledCourseName !== $courseName) {
                return false; // Başka bir exclusive ders bulundu
            }
        }
    }

    return true;
}

/**
 * Ders tekrar limitini kontrol et
 */
protected function checkMaxEnrollment(int $studentId, int $courseId, int $maxEnrollment): bool
{
    // Öğrenci bu dersi kaç kez aldı?
    $count = $this->db->fetch("
        SELECT COUNT(*) as count 
        FROM student_enrollments 
        WHERE student_id = ? AND course_id = ?
    ", [$studentId, $courseId]);

    return ($count['count'] ?? 0) < $maxEnrollment;
}

/**
 * Özel dersler (exclusive) - aynı yıl birden fazla alınamaz
 */
protected function checkExclusiveCourses(int $studentId, int $courseId, array $exclusiveCourses): bool
{
    if (empty($exclusiveCourses)) return true;

    // Bu ders exclusive listede mi?
    $course = $this->db->fetch("SELECT name FROM courses WHERE id = ?", [$courseId]);
    if (!$course) return true;

    $courseName = strtolower($course['name']);
    $isExclusive = false;
    foreach ($exclusiveCourses as $exc) {
        if (stripos($courseName, $exc) !== false) {
            $isExclusive = true;
            break;
        }
    }

    if (!$isExclusive) return true;

    // Bu yıl exclusive listeden başka ders aldı mı?
    $currentYear = date('Y');
    $enrollments = $this->db->select("
        SELECT c.name 
        FROM student_enrollments se
        JOIN courses c ON c.id = se.course_id
        WHERE se.student_id = ? 
        AND YEAR(se.enrolled_at) = ?
    ", [$studentId, $currentYear]) ?? [];

    foreach ($enrollments as $enrollment) {
        $enrolledCourseName = strtolower($enrollment['name']);
        foreach ($exclusiveCourses as $exc) {
            if (stripos($enrolledCourseName, $exc) !== false && $enrolledCourseName !== $courseName) {
                return false; // Başka bir exclusive ders bulundu
            }
        }
    }

    return true;
}

/* ==================== AKADEMİK KURALLAR ==================== */
/**
 * Öğrencinin kategori ve sınıf seviyesini belirle
 */
protected function getStudentContext(int $studentId): array
{
    $student = $this->db->fetch("
        SELECT u.*, c.name as class_name 
        FROM users u 
        LEFT JOIN classes c ON c.id = u.class_id
        WHERE u.id = ? AND u.role = 'student'
    ", [$studentId]);

    if (!$student) {
        return ['category' => null, 'grade' => null, 'student' => null];
    }

    // Sınıf numarasını çıkar
    $grade = null;
    
    if (!empty($student['sinif'])) {
        if (preg_match('/(\d+)/', $student['sinif'], $matches)) {
            $grade = (int)$matches[1];
        }
    }
    
    if (!$grade && !empty($student['class_name'])) {
        if (preg_match('/(\d+)/', $student['class_name'], $matches)) {
            $grade = (int)$matches[1];
        }
    }

    // Kategori belirle
    $category = null;
    if ($grade) {
        if ($grade >= 1 && $grade <= 4) {
            $category = 'ilkokul';
        } elseif ($grade >= 5 && $grade <= 8) {
            $category = 'ortaokul'; // GENEL ORTAOKUL
        } elseif ($grade >= 9 && $grade <= 12) {
            $category = 'lise';
        }
    }
    
    if (!$category && !empty($student['okul'])) {
        $okul = strtolower($student['okul']);
        if (strpos($okul, 'ilkokul') !== false) {
            $category = 'ilkokul';
        } elseif (strpos($okul, 'ortaokul') !== false) {
            $category = 'ortaokul';
        } elseif (strpos($okul, 'lise') !== false) {
            $category = 'lise';
        }
    }

    return [
        'student' => $student,
        'category' => $category,
        'grade' => $grade
    ];
}

/**
 * İki kategori uyumlu mu? (özel kademe kontrolü)
 */
protected function isCategoryCompatible(string $studentCategory, int $studentGrade, string $courseCategory): bool
{
    // Aynı kategoriyse direkt uyumlu
    if ($studentCategory === $courseCategory) {
        return true;
    }
    
    // Öğrenci "ortaokul" ise, ders "ortaokul_1" veya "ortaokul_2" olabilir
    if ($studentCategory === 'ortaokul') {
        // Öğrencinin sınıf seviyesine bak
        if ($courseCategory === 'ortaokul_1' && $studentGrade >= 5 && $studentGrade <= 6) {
            return true;
        }
        if ($courseCategory === 'ortaokul_2' && $studentGrade >= 7 && $studentGrade <= 8) {
            return true;
        }
    }
    
    return false;
}

/**
 * Öğrencinin bir derse kayıt olup olamayacağını kontrol et
 */
public function validateEnrollment(int $studentId, int $courseId): bool
{
    $this->clearViolations();

    $context = $this->getStudentContext($studentId);
    if (!$context['category']) {
        $this->violations[] = 'Öğrenci kategorisi belirlenemedi. Lütfen öğrencinin sınıf bilgisini kontrol edin.';
        return false;
    }

    // Ders bilgilerini al
    $course = $this->db->fetch("SELECT id, name, category FROM courses WHERE id = ?", [$courseId]);
    if (!$course) {
        $this->violations[] = 'Ders bulunamadı.';
        return false;
    }

    // AKILLI KADEME KONTROLÜ
    if (!empty($course['category'])) {
        if (!$this->isCategoryCompatible($context['category'], $context['grade'] ?? 0, $course['category'])) {
            $categoryNames = [
                'ilkokul' => 'İlkokul (1-4. Sınıf)',
                'ortaokul' => 'Ortaokul (5-8. Sınıf)',
                'ortaokul_1' => 'Ortaokul I. Kademe (5-6. Sınıf)',
                'ortaokul_2' => 'Ortaokul II. Kademe (7-8. Sınıf)',
                'lise' => 'Lise (9-12. Sınıf)'
            ];
            
            $studentCat = $categoryNames[$context['category']] ?? $context['category'];
            $courseCat = $categoryNames[$course['category']] ?? $course['category'];
            
            $this->violations[] = sprintf(
                'Kademe Uyumsuzluğu: %s öğrencisi (Sınıf: %d) "%s" dersine kayıt olamaz. Bu ders %s içindir.',
                $studentCat,
                $context['grade'] ?? 0,
                $course['name'],
                $courseCat
            );
            return false;
        }
    }

    // Diğer kuralları kontrol et
    $rules = $this->getRules($context['category'], 'enrollment');

    foreach ($rules as $rule) {
        $conditions = $this->parseConditions($rule['conditions'] ?? '');
        if (!$conditions) continue;

        if ($rule['code'] === 'ELEM_REPEAT' || isset($conditions['max_enrollment'])) {
            if (!$this->checkMaxEnrollment($studentId, $courseId, $conditions['max_enrollment'] ?? 1)) {
                $this->violations[] = $rule['name'] . ': ' . $rule['description'];
            }
        }

        if ($rule['code'] === 'YEARLY_ART_MUSIC' || isset($conditions['exclusive'])) {
            if (!$this->checkExclusiveCourses($studentId, $courseId, $conditions['courses'] ?? [])) {
                $this->violations[] = $rule['name'] . ': ' . $rule['description'];
            }
        }
    }

    return !$this->hasViolations();
}İ

/**
 * Kural koşullarını JSON'dan çözümle
 */
protected function parseConditions(string $conditions): ?array
{
    if (empty($conditions)) {
        return null;
    }

    $decoded = json_decode($conditions, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
}

    /* ==================== AKADEMİK KURALLAR ==================== */

    /**
     * Not girişini kontrol et
     */
    public function validateGradeEntry(array $evaluationData): bool
    {
        $this->clearViolations();

        $studentId = $evaluationData['student_id'] ?? 0;
        $context = $this->getStudentContext($studentId);

        if (!$context['category']) {
            $this->violations[] = 'Öğrenci kategorisi belirlenemedi.';
            return false;
        }

        $rules = $this->getRules($context['category'], 'academic');

        foreach ($rules as $rule) {
            $conditions = $this->parseConditions($rule['conditions'] ?? '');
            if (!$conditions) continue;

            // Sınıf kademe limiti kontrolü
            if (isset($conditions['allows_mixed_grades'])) {
                // Bu kuralı burada uygulayın
            }
        }

        return !$this->hasViolations();
    }

    /* ==================== MEZUNİYET KURALLARI ==================== */

    /**
     * Öğrencinin mezuniyet koşullarını kontrol et
     */
    public function checkGraduationEligibility(int $studentId): bool
    {
        $this->clearViolations();

        $context = $this->getStudentContext($studentId);
        if (!$context['category']) {
            $this->violations[] = 'Öğrenci kategorisi belirlenemedi.';
            return false;
        }

        $rules = $this->getRules($context['category'], 'graduation');

        foreach ($rules as $rule) {
            $conditions = $this->parseConditions($rule['conditions'] ?? '');
            if (!$conditions) continue;

            // İlkokuldan mezuniyet kuralı
            if ($rule['code'] === 'ELEM_GRADUATION') {
                $grade = $context['grade'];
                if ($grade == 4) {
                    // 4. sınıf öğrencisi, 5. sınıfa geçmeli
                    $this->warnings[] = $rule['description'];
                }
            }
        }

        return !$this->hasViolations();
    }

    /* ==================== DEVAMSIZLIK KURALLARI ==================== */

    /**
     * Devamsızlık kurallarını kontrol et
     */
    public function validateAttendance(int $studentId, int $courseId, int $absenceDays): bool
    {
        $this->clearViolations();

        $context = $this->getStudentContext($studentId);
        if (!$context['category']) {
            $this->violations[] = 'Öğrenci kategorisi belirlenemedi.';
            return false;
        }

        $rules = $this->getRules($context['category'], 'attendance');

        foreach ($rules as $rule) {
            $conditions = $this->parseConditions($rule['conditions'] ?? '');
            if (!$conditions) continue;

            // Devamsızlık sınav hakkı
            if ($rule['code'] === 'ABSENCE_EXAM') {
                // Belirli bir devamsızlık limitini aşarsa sınav gerekir
                $limit = $conditions['absence_limit'] ?? 10;
                if ($absenceDays > $limit) {
                    if (empty($conditions['exceptions'])) {
                        $this->violations[] = $rule['description'];
                    }
                }
            }
        }

        return !$this->hasViolations();
    }

    /* ==================== PROJE KURALLARI ==================== */

    /**
     * Proje kurallarını kontrol et
     */
    public function validateProject(int $studentId, array $projectData): bool
    {
        $this->clearViolations();

        $context = $this->getStudentContext($studentId);
        if (!$context['category']) {
            $this->violations[] = 'Öğrenci kategorisi belirlenemedi.';
            return false;
        }

        $rules = $this->getRules($context['category'], 'project');

        foreach ($rules as $rule) {
            $conditions = $this->parseConditions($rule['conditions'] ?? '');
            if (!$conditions) continue;

            // Proje grup kuralları
            if (isset($conditions['max_group_size'])) {
                $groupSize = count($projectData['members'] ?? []);
                if ($groupSize > $conditions['max_group_size']) {
                    $this->violations[] = $rule['name'] . ': Grup boyutu ' . $conditions['max_group_size'] . ' kişiyi geçemez.';
                }
            }
        }

        return !$this->hasViolations();
    }

    /* ==================== TOPLU KONTROL ==================== */

    /**
     * Tüm kuralları kontrol et (genel bakış)
     */
    public function validateAll(int $studentId): array
    {
        $this->clearViolations();

        $context = $this->getStudentContext($studentId);
        
        return [
            'student' => $context['student'],
            'category' => $context['category'],
            'grade' => $context['grade'],
            'violations' => $this->violations,
            'warnings' => $this->warnings,
            'is_valid' => !$this->hasViolations()
        ];
    }

    /**
     * Belirli bir kurala göre öğrenci listesi getir
     */
    public function getAffectedStudents(int $ruleId): array
    {
        $rule = $this->db->fetch("SELECT * FROM rules WHERE id = ?", [$ruleId]);
        if (!$rule) return [];

        // Kurala göre etkilenen öğrencileri bul
        $students = $this->db->select("
            SELECT u.id, u.name, u.email, u.sinif
            FROM users u
            WHERE u.role = 'student'
            ORDER BY u.name
        ") ?? [];

        $affected = [];
        foreach ($students as $student) {
            // Bu öğrenci bu kuraldan etkileniyor mu?
            // Kategori kontrolü
            $grade = null;
            if (!empty($student['sinif'])) {
                preg_match('/^(\d+)/', $student['sinif'], $matches);
                $grade = $matches[1] ?? null;
            }

            $category = null;
            if ($grade >= 1 && $grade <= 4) $category = 'ilkokul';
            elseif ($grade >= 5 && $grade <= 8) $category = 'ortaokul';
            elseif ($grade >= 9 && $grade <= 12) $category = 'lise';

            if ($category === $rule['category']) {
                // Grade range kontrolü
                if (!empty($rule['grade_range']) && $rule['grade_range'] !== 'all') {
                    $range = explode('-', $rule['grade_range']);
                    if (count($range) === 2) {
                        if ($grade >= $range[0] && $grade <= $range[1]) {
                            $affected[] = $student;
                        }
                    }
                } else {
                    $affected[] = $student;
                }
            }
        }

        return $affected;
    }
}