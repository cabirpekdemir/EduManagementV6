<?php
// /modules/enrollment/enrollmentrules.php

class EnrollmentRules {

    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Bir öğrencinin belirli bir derse kaydolmaya uygun olup olmadığını tüm kurallara göre kontrol eder.
     * @param int $studentId Öğrenci ID'si
     * @param int $courseId Ders ID'si
     * @return array ['eligible' => bool, 'message' => string]
     */
    public function checkAll(int $studentId, int $courseId): array {
        // Kural 1: Yaş Kontrolü
        $ageCheck = $this->isAgeAppropriate($studentId, $courseId);
        if (!$ageCheck['eligible']) {
            return $ageCheck;
        }

        // Kural 2: Kontenjan Kontrolü
        $capacityCheck = $this->hasCapacity($courseId);
        if (!$capacityCheck['eligible']) {
            return $capacityCheck;
        }

        // Kural 3: Ön Koşul Kontrolü
        $prerequisiteCheck = $this->hasCompletedPrerequisites($studentId, $courseId);
        if (!$prerequisiteCheck['eligible']) {
            return $prerequisiteCheck;
        }

        // Tüm kontrollerden geçtiyse
        return ['eligible' => true, 'message' => 'Öğrenci derse kayıt için uygundur.'];
    }

    /**
     * Öğrencinin yaşının dersin yaş aralığına uygun olup olmadığını kontrol eder.
     */
    private function isAgeAppropriate(int $studentId, int $courseId): array {
        $stmt = $this->db->prepare("SELECT min_age, max_age FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        // Eğer ders için yaş sınırı belirtilmemişse, kuralı geçerli say
        if (empty($course['min_age']) && empty($course['max_age'])) {
            return ['eligible' => true, 'message' => ''];
        }

        // Öğrencinin doğum tarihini al (users tablosunda 'birth_date' kolonu olduğunu varsayıyoruz)
        $stmt_student = $this->db->prepare("SELECT birth_date FROM users WHERE id = ?");
        $stmt_student->execute([$studentId]);
        $student_birth_date = $stmt_student->fetchColumn();

        if (!$student_birth_date) {
            return ['eligible' => false, 'message' => 'Öğrenci doğum tarihi bulunamadı.'];
        }

        $birthDate = new DateTime($student_birth_date);
        $today = new DateTime('today');
        $age = $birthDate->diff($today)->y;

        if (isset($course['min_age']) && $age < $course['min_age']) {
            return ['eligible' => false, 'message' => "Bu ders için minimum yaş sınırı {$course['min_age']}'dır. Öğrencinin yaşı ({$age}) uygun değildir."];
        }

        if (isset($course['max_age']) && $age > $course['max_age']) {
            return ['eligible' => false, 'message' => "Bu ders için maksimum yaş sınırı {$course['max_age']}'dır. Öğrencinin yaşı ({$age}) uygun değildir."];
        }

        return ['eligible' => true, 'message' => ''];
    }

    /**
     * Dersin kontenjanının dolu olup olmadığını kontrol eder.
     */
    private function hasCapacity(int $courseId): array {
        $stmt = $this->db->prepare("SELECT capacity FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        $capacity = $stmt->fetchColumn();

        // Eğer kontenjan belirtilmemişse, kuralı geçerli say (sınırsız)
        if ($capacity === null) {
            return ['eligible' => true, 'message' => ''];
        }

        $stmt_enrollments = $this->db->prepare("SELECT COUNT(id) FROM student_enrollments WHERE course_id = ? AND status = 'enrolled'");
        $stmt_enrollments->execute([$courseId]);
        $currentEnrollments = $stmt_enrollments->fetchColumn();

        if ($currentEnrollments >= $capacity) {
            return ['eligible' => false, 'message' => "Ders kontenjanı ({$capacity}) dolmuştur."];
        }

        return ['eligible' => true, 'message' => ''];
    }

    /**
     * Öğrencinin, ders için gerekli tüm ön koşul derslerini tamamlayıp tamamlamadığını kontrol eder.
     */
    private function hasCompletedPrerequisites(int $studentId, int $courseId): array {
        // Dersin ön koşullarını bul
        $stmt_prereq = $this->db->prepare("SELECT prerequisite_course_id FROM course_prerequisites WHERE course_id = ?");
        $stmt_prereq->execute([$courseId]);
        $prerequisites = $stmt_prereq->fetchAll(PDO::FETCH_COLUMN);

        // Eğer ön koşul yoksa, kuralı geçerli say
        if (empty($prerequisites)) {
            return ['eligible' => true, 'message' => ''];
        }

        // Öğrencinin tamamladığı dersleri bul
        // student_enrollments tablosunda 'status' kolonunun 'completed' olduğunu varsayıyoruz
        $placeholders = rtrim(str_repeat('?,', count($prerequisites)), ',');
        $query = "SELECT course_id FROM student_enrollments WHERE student_id = ? AND status = 'completed' AND course_id IN ({$placeholders})";
        
        $params = array_merge([$studentId], $prerequisites);
        $stmt_completed = $this->db->prepare($query);
        $stmt_completed->execute($params);
        $completedCourses = $stmt_completed->fetchAll(PDO::FETCH_COLUMN);

        // Tamamlanması gereken ile tamamlananlar arasında fark var mı?
        $missingCourses = array_diff($prerequisites, $completedCourses);

        if (!empty($missingCourses)) {
            // Eksik derslerin isimlerini bulup daha açıklayıcı bir mesaj verebiliriz
            $missingCourseIds = implode(',', $missingCourses);
            $stmt_names = $this->db->query("SELECT name FROM courses WHERE id IN ({$missingCourseIds})");
            $missingCourseNames = implode(', ', $stmt_names->fetchAll(PDO::FETCH_COLUMN));

            return ['eligible' => false, 'message' => "Bu derse kaydolmak için önce şu ders(ler)i tamamlamanız gerekmektedir: {$missingCourseNames}."];
        }

        return ['eligible' => true, 'message' => ''];
    }
}