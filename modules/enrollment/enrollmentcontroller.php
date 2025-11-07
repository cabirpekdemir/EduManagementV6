<?php
// /modules/enrollment/enrollmentcontroller.php

// Daha önce oluşturduğumuz ve güncellediğimiz kural motoru
require_once __DIR__ . '/enrollmentrules.php'; 
require_once __DIR__ . '/../../core/database.php';

class EnrollmentController {

    private $db;
    private $rules; // Kural motorumuzu burada kullanacağız

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->rules = new EnrollmentRules($this->db);
    }

    /**
     * Personelin bir öğrenciyi bir pakete kaydetme denemesini işler.
     */
    public function enrollStudentInPackage() {
        // Formdan gelen veriler (personel tarafından seçilen)
        $studentId = $_POST['student_id'];
        $packageId = $_POST['package_id'];
        $staffId = $_SESSION['user']['id']; // İşlemi yapan personelin ID'si

        // 1. Pakete dahil olan tüm dersleri al
        $stmt_courses = $this->db->prepare("SELECT course_id FROM package_courses WHERE package_id = ?");
        $stmt_courses->execute([$packageId]);
        $courseIds = $stmt_courses->fetchAll(PDO::FETCH_COLUMN);

         require_once __DIR__ . '/../rules/ruleengine.php';
    $ruleEngine = new RuleEngine();

    if (!$ruleEngine->validateEnrollment($studentId, $courseId)) {
        $violations = $ruleEngine->getViolations();
        $_SESSION['flash_error'] = implode('<br>', $violations);
        header('Location: index.php?module=enrollments&action=create');
        exit;
    }

    // Uyarılar varsa göster ama devam et
    if ($ruleEngine->hasWarnings()) {
        $warnings = $ruleEngine->getWarnings();
        $_SESSION['flash_warning'] = implode('<br>', $warnings);
    }
    
        if (empty($courseIds)) {
            $this->redirectWithError("Seçilen pakete tanımlı bir atölye bulunamadı.");
            return;
        }

        // 2. Her bir ders için TÜM kuralları önceden kontrol et
        $enrollmentErrors = [];
        foreach ($courseIds as $courseId) {
            $result = $this->rules->checkAll($studentId, $courseId);
            if (!$result['eligible']) {
                // Eğer bir ders bile kurala uymuyorsa, hatayı kaydet
                $enrollmentErrors[] = $result['message'];
            }
        }

        // 3. Herhangi bir kural hatası varsa, işlemi durdur ve tüm hataları bildir
        if (!empty($enrollmentErrors)) {
            $errorMessage = "Öğrenci pakete kaydedilemedi. Sebepler: <br> - " . implode("<br> - ", $enrollmentErrors);
            $this->redirectWithError($errorMessage);
            return;
        }

        // 4. HİÇBİR HATA YOKSA, tüm derslere kayıt işlemini gerçekleştir
        $this->db->beginTransaction();
        try {
            foreach ($courseIds as $courseId) {
                $stmt_enroll = $this->db->prepare(
                    "INSERT INTO student_enrollments (student_id, course_id, enrollment_date, status, enrolled_by_staff_id) 
                     VALUES (?, ?, NOW(), 'enrolled', ?)"
                );
                $stmt_enroll->execute([$studentId, $courseId, $staffId]);
            }
            $this->db->commit();
            $this->redirectWithSuccess("Öğrenci paketteki tüm atölyelere başarıyla kaydedildi.");

        } catch (Exception $e) {
            $this->db->rollBack();
            // Gerçekte burada hatayı loglamak daha doğru olur
            $this->redirectWithError("Kayıt sırasında beklenmedik bir veritabanı hatası oluştu.");
        }
    }
    
    // Helper metotlar
    private function redirectWithError($message) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $message];
        // Personeli kayıt yaptığı sayfaya geri yönlendir
        header('Location: /?module=teacher_enrollment'); 
        exit;
    }

    private function redirectWithSuccess($message) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $message];
        header('Location: /?module=teacher_enrollment');
        exit;
    }
}