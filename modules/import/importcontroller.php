<?php
require_once __DIR__ . '/../../core/database.php';

class ImportController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) @session_start();
    }

    private function flashSuccess(string $msg): void
    {
        $_SESSION['flash_success'] = $msg;
    }

    private function flashError(string $msg): void
    {
        $_SESSION['flash_error'] = $msg;
    }

    /* ==================== ANA SAYFA ==================== */
    public function index(): array
    {
        return [
            'view'  => 'import/view/index.php',
            'title' => 'Toplu İçe Aktarım'
        ];
    }
    /* ==================== REHBERLİK SEANSLARI İMPORT ==================== */
    
    public function guidance(): array
    {
        // Öğrenci ve öğretmen listesi
        $students = $this->db->select("
            SELECT id, name, student_number FROM users WHERE role='student' ORDER BY name
        ") ?? [];

        $counselors = $this->db->select("
            SELECT id, name FROM users WHERE role IN ('teacher', 'admin') ORDER BY name
        ") ?? [];

        return [
            'view'       => 'import/view/guidance.php',
            'title'      => 'Rehberlik Seansları İçe Aktar',
            'students'   => $students,
            'counselors' => $counselors
        ];
    }

    public function processGuidance(): void
    {
        if (empty($_FILES['csv_file']['tmp_name'])) {
            $this->flashError('Lütfen bir CSV dosyası seçin.');
            header('Location: index.php?module=import&action=guidance');
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        // Header oku
        $header = fgetcsv($handle, 0, ';');
        if (!$header || count($header) < 2) {
            fseek($handle, 0);
            $header = fgetcsv($handle, 0, ',');
        }
        
        $header = array_map('trim', $header);
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (empty(array_filter($row))) continue;

            if (count($row) === 1) {
                fseek($handle, ftell($handle) - strlen(implode('', $row)));
                $row = fgetcsv($handle, 0, ',');
            }

            $data = array_combine($header, $row);
            
            // Zorunlu alanlar
            $studentIdentifier = trim($data['Öğrenci'] ?? $data['Öğrenci No'] ?? $data['student'] ?? '');
            $sessionDate = trim($data['Görüşme Tarihi'] ?? $data['Tarih'] ?? $data['session_date'] ?? '');
            $title = trim($data['Konu'] ?? $data['Başlık'] ?? $data['title'] ?? '');
            $notes = trim($data['Görüşme Notları'] ?? $data['Notlar'] ?? $data['notes'] ?? '');
            
            // Opsiyonel
            $counselorName = trim($data['Rehber'] ?? $data['Danışman'] ?? $data['counselor'] ?? '');
            $nextSteps = trim($data['Sonraki Adımlar'] ?? $data['next_steps'] ?? '');

            if (empty($studentIdentifier) || empty($sessionDate) || empty($title) || empty($notes)) {
                $skipped++;
                $errors[] = "Atlandı: Zorunlu alanlar eksik";
                continue;
            }

            // Tarih formatını düzelt
            $sessionDate = $this->parseDate($sessionDate);
            if (!$sessionDate) {
                $skipped++;
                $errors[] = "Atlandı: Geçersiz tarih formatı - $studentIdentifier";
                continue;
            }

            // Öğrenci bul (isim veya numara ile)
            $student = $this->db->fetch("
                SELECT id FROM users 
                WHERE role='student' AND (name LIKE ? OR student_number = ?)
                LIMIT 1
            ", ["%$studentIdentifier%", $studentIdentifier]);

            if (!$student) {
                $skipped++;
                $errors[] = "Atlandı: Öğrenci bulunamadı - $studentIdentifier";
                continue;
            }

            $studentId = $student['id'];

            // Rehber bul
            $counselorId = $this->currentUserId(); // Varsayılan: import yapan kişi
            if (!empty($counselorName)) {
                $counselor = $this->db->fetch("
                    SELECT id FROM users 
                    WHERE role IN ('teacher', 'admin') AND name LIKE ?
                    LIMIT 1
                ", ["%$counselorName%"]);
                
                if ($counselor) {
                    $counselorId = $counselor['id'];
                }
            }

            try {
                // Aynı kayıt var mı kontrol et
                $exists = $this->db->fetch("
                    SELECT id FROM guidance_sessions 
                    WHERE student_id = ? AND session_date = ? AND title = ?
                ", [$studentId, $sessionDate, $title]);

                if ($exists) {
                    $skipped++;
                    $errors[] = "Atlandı: Kayıt zaten mevcut - $studentIdentifier ($sessionDate)";
                    continue;
                }

                $this->db->execute("
                    INSERT INTO guidance_sessions 
                    (student_id, counselor_id, session_date, title, notes)
                    VALUES (?, ?, ?, ?, ?)
                ", [$studentId, $counselorId, $sessionDate, $title, $notes]);

                $imported++;

            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Hata: $studentIdentifier - " . $e->getMessage();
            }
        }

        fclose($handle);

        $_SESSION['import_result'] = compact('imported', 'skipped', 'errors');
        $this->flashSuccess("$imported rehberlik seansı içe aktarıldı.");
        header('Location: index.php?module=import&action=result');
        exit;
    }

    /* ==================== RANDEVU KAYITLARI İMPORT ==================== */
    
    public function appointments(): array
    {
        $students = $this->db->select("
            SELECT id, name, student_number FROM users WHERE role='student' ORDER BY name
        ") ?? [];

        $counselors = $this->db->select("
            SELECT id, name FROM users WHERE role IN ('teacher', 'admin') ORDER BY name
        ") ?? [];

        return [
            'view'       => 'import/view/appointments.php',
            'title'      => 'Randevu Kayıtları İçe Aktar',
            'students'   => $students,
            'counselors' => $counselors
        ];
    }

    public function processAppointments(): void
    {
        if (empty($_FILES['csv_file']['tmp_name'])) {
            $this->flashError('Lütfen bir CSV dosyası seçin.');
            header('Location: index.php?module=import&action=appointments');
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        $header = fgetcsv($handle, 0, ';');
        if (!$header || count($header) < 2) {
            fseek($handle, 0);
            $header = fgetcsv($handle, 0, ',');
        }
        
        $header = array_map('trim', $header);
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (empty(array_filter($row))) continue;

            if (count($row) === 1) {
                fseek($handle, ftell($handle) - strlen(implode('', $row)));
                $row = fgetcsv($handle, 0, ',');
            }

            $data = array_combine($header, $row);
            
            $studentIdentifier = trim($data['Öğrenci'] ?? $data['student'] ?? '');
            $requestedDate = trim($data['Talep Tarihi'] ?? $data['requested_date'] ?? '');
            $requestedTime = trim($data['Talep Saati'] ?? $data['requested_time'] ?? '09:00');
            $reason = trim($data['Sebep'] ?? $data['Konu'] ?? $data['reason'] ?? '');
            
            // Opsiyonel
            $status = trim($data['Durum'] ?? $data['status'] ?? 'completed');
            $appointmentDate = trim($data['Randevu Tarihi'] ?? $data['appointment_date'] ?? '');
            $appointmentTime = trim($data['Randevu Saati'] ?? $data['appointment_time'] ?? '');
            $counselorName = trim($data['Rehber'] ?? $data['counselor'] ?? '');
            $counselorNotes = trim($data['Rehber Notları'] ?? $data['counselor_notes'] ?? '');

            if (empty($studentIdentifier) || empty($requestedDate) || empty($reason)) {
                $skipped++;
                $errors[] = "Atlandı: Zorunlu alanlar eksik";
                continue;
            }

            // Tarih formatları
            $requestedDate = $this->parseDate($requestedDate);
            if (!$requestedDate) {
                $skipped++;
                $errors[] = "Atlandı: Geçersiz talep tarihi - $studentIdentifier";
                continue;
            }

            if (!empty($appointmentDate)) {
                $appointmentDate = $this->parseDate($appointmentDate);
            }

            // Durum mapping
            $statusMap = [
                'Bekliyor' => 'pending',
                'Onaylandı' => 'approved',
                'Tamamlandı' => 'completed',
                'Reddedildi' => 'rejected',
                'İptal' => 'cancelled',
                'pending' => 'pending',
                'approved' => 'approved',
                'completed' => 'completed',
                'rejected' => 'rejected',
                'cancelled' => 'cancelled'
            ];
            
            $statusValue = $statusMap[$status] ?? 'completed';

            // Öğrenci bul
            $student = $this->db->fetch("
                SELECT id FROM users 
                WHERE role='student' AND (name LIKE ? OR student_number = ?)
                LIMIT 1
            ", ["%$studentIdentifier%", $studentIdentifier]);

            if (!$student) {
                $skipped++;
                $errors[] = "Atlandı: Öğrenci bulunamadı - $studentIdentifier";
                continue;
            }

            $studentId = $student['id'];

            // Rehber bul
            $counselorId = null;
            if (!empty($counselorName)) {
                $counselor = $this->db->fetch("
                    SELECT id FROM users 
                    WHERE role IN ('teacher', 'admin') AND name LIKE ?
                    LIMIT 1
                ", ["%$counselorName%"]);
                
                $counselorId = $counselor['id'] ?? null;
            }

            try {
                $this->db->execute("
                    INSERT INTO guidance_appointments 
                    (student_id, requested_date, requested_time, reason, status, 
                     appointment_date, appointment_time, counselor_id, counselor_notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $studentId, 
                    $requestedDate, 
                    $requestedTime,
                    $reason, 
                    $statusValue,
                    $appointmentDate ?: null,
                    $appointmentTime ?: null,
                    $counselorId,
                    $counselorNotes ?: null
                ]);

                $imported++;

            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Hata: $studentIdentifier - " . $e->getMessage();
            }
        }

        fclose($handle);

        $_SESSION['import_result'] = compact('imported', 'skipped', 'errors');
        $this->flashSuccess("$imported randevu kaydı içe aktarıldı.");
        header('Location: index.php?module=import&action=result');
        exit;
    }

    /* ==================== YARDIMCI METODLAR ==================== */
    
    /**
     * Farklı tarih formatlarını parse et
     */
    private function parseDate($dateStr): ?string
    {
        if (empty($dateStr)) return null;

        // Zaten Y-m-d formatında mı?
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        // Türkçe format: dd.mm.yyyy veya dd/mm/yyyy
        if (preg_match('/^(\d{1,2})[\.\/](\d{1,2})[\.\/](\d{4})$/', $dateStr, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }

        // Excel serial date (sayı olarak)
        if (is_numeric($dateStr)) {
            $unix = ($dateStr - 25569) * 86400;
            return date('Y-m-d', $unix);
        }

        // strtotime dene
        $timestamp = strtotime($dateStr);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }
    /* ==================== ÖĞRETMEN İMPORT ==================== */
    public function teachers(): array
    {
        return [
            'view'  => 'import/view/teachers.php',
            'title' => 'Öğretmen İçe Aktar'
        ];
    }

    public function processTeachers(): void
    {
        if (empty($_FILES['csv_file']['tmp_name'])) {
            $this->flashError('Lütfen bir CSV dosyası seçin.');
            header('Location: index.php?module=import&action=teachers');
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        // Header oku (noktalı virgül veya virgül)
        $header = fgetcsv($handle, 0, ';');
        if (!$header || count($header) < 2) {
            fseek($handle, 0);
            $header = fgetcsv($handle, 0, ',');
        }
        
        $header = array_map('trim', $header);
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (empty(array_filter($row))) continue;
            
            // Virgülle de dene
            if (count($row) === 1) {
                fseek($handle, ftell($handle) - strlen(implode('', $row)));
                $row = fgetcsv($handle, 0, ',');
            }

            $data = array_combine($header, $row);
            
            $name = trim($data['Ad Soyad'] ?? $data['Adı Soyadı'] ?? $data['name'] ?? '');
            $email = trim($data['E-posta'] ?? $data['email'] ?? '');
            $phone = trim($data['Telefon'] ?? $data['Tel'] ?? $data['phone'] ?? '');
            $branch = trim($data['Branş'] ?? $data['branch'] ?? '');
            $tcKimlik = trim($data['T.C. Kimlik No'] ?? $data['tc_kimlik'] ?? '');

            if (empty($name) || empty($email)) {
                $skipped++;
                $errors[] = "Atlandı: Ad veya email eksik";
                continue;
            }

            // Email kontrolü
            $exists = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
            if ($exists) {
                $skipped++;
                $errors[] = "Atlandı: $name (Email zaten kayıtlı)";
                continue;
            }

            try {
                $this->db->execute("
                    INSERT INTO users (
                        name, email, password, phone, branch, tc_kimlik,
                        role, is_active, approved
                    ) VALUES (?, ?, ?, ?, ?, ?, 'teacher', 1, 1)
                ", [
                    $name,
                    $email,
                    password_hash('123456', PASSWORD_DEFAULT),
                    $phone,
                    $branch,
                    $tcKimlik ?: null
                ]);

                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Hata: $name - " . $e->getMessage();
            }
        }

        fclose($handle);

        $_SESSION['import_result'] = compact('imported', 'skipped', 'errors');
        $this->flashSuccess("$imported öğretmen içe aktarıldı.");
        header('Location: index.php?module=import&action=result');
        exit;
    }

    /* ==================== DERS İMPORT ==================== */
    public function courses(): array
    {
        // Öğretmen listesi
        $teachers = $this->db->select("
            SELECT id, name FROM users WHERE role='teacher' ORDER BY name
        ") ?? [];

        return [
            'view'     => 'import/view/courses.php',
            'title'    => 'Ders İçe Aktar',
            'teachers' => $teachers
        ];
    }

  public function processCourses(): void
{
    if (empty($_FILES['csv_file']['tmp_name'])) {
        $this->flashError('Lütfen bir CSV dosyası seçin.');
        header('Location: index.php?module=import&action=courses');
        exit;
    }

    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    
    // Header oku (noktalı virgül veya virgül)
    $header = fgetcsv($handle, 0, ';');
    if (!$header || count($header) < 2) {
        fseek($handle, 0);
        $header = fgetcsv($handle, 0, ',');
    }
    
    $header = array_map('trim', $header);
    
    $imported = 0;
    $skipped = 0;
    $errors = [];

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        if (empty(array_filter($row))) continue;

        // Virgülle de dene
        if (count($row) === 1) {
            $pos = ftell($handle);
            fseek($handle, $pos - strlen(implode('', $row)) - 1);
            $row = fgetcsv($handle, 0, ',');
        }

        $data = array_combine($header, $row);
        
        // Zorunlu alanlar
        $name = trim($data['Ders Adı'] ?? $data['name'] ?? '');
        
        if (empty($name)) {
            $skipped++;
            $errors[] = "Atlandı: Ders adı eksik";
            continue;
        }

        // Diğer alanlar
        $term = trim($data['Dönem'] ?? $data['term'] ?? '');
        $courseCode = trim($data['Ders Kodu'] ?? $data['course_code'] ?? '');
        $type = trim($data['Tipi'] ?? $data['type'] ?? '');
        $category = trim($data['Kategori'] ?? $data['category'] ?? '');
        $teacherName = trim($data['Eğitmen'] ?? $data['Öğretmen'] ?? $data['teacher'] ?? '');
        $gradeRange = trim($data['Kademe'] ?? $data['grade_range'] ?? '');
        $day = trim($data['Gün'] ?? $data['day'] ?? '');
        $startTime = trim($data['Başlangıç Zamanı'] ?? $data['start_time'] ?? '');
        $endTime = trim($data['Bitiş Zamanı'] ?? $data['end_time'] ?? '');
        $capacity = trim($data['Kontenjan'] ?? $data['capacity'] ?? '');

        // Kategori mapping
        $categoryMap = [
            'İlkokul' => 'ilkokul',
            'İLKOKUL' => 'ilkokul',
            'ilkokul' => 'ilkokul',
            'Ortaokul' => 'ortaokul',
            'ORTAOKUL' => 'ortaokul',
            'ortaokul' => 'ortaokul',
            'Ortaokul I' => 'ortaokul_1',
            'Ortaokul 1' => 'ortaokul_1',
            'Ortaokul II' => 'ortaokul_2',
            'Ortaokul 2' => 'ortaokul_2',
            'Lise' => 'lise',
            'LİSE' => 'lise',
            'lise' => 'lise'
        ];
        
        $categoryValue = $categoryMap[$category] ?? 'ortaokul';

        // Tip mapping
        $typeMap = [
            'Zorunlu' => 'zorunlu',
            'zorunlu' => 'zorunlu',
            'ZORUNLU' => 'zorunlu',
            'Seçmeli' => 'secmeli',
            'seçmeli' => 'secmeli',
            'SEÇMELİ' => 'secmeli',
            'Secmeli' => 'secmeli'
        ];
        
        $typeValue = $typeMap[$type] ?? 'zorunlu';

        // Gün mapping
        $dayMap = [
            'Pazartesi' => 'Pazartesi',
            'pazartesi' => 'Pazartesi',
            'PAZARTESI' => 'Pazartesi',
            'Pzt' => 'Pazartesi',
            'Salı' => 'Salı',
            'salı' => 'Salı',
            'SALI' => 'Salı',
            'Sal' => 'Salı',
            'Çarşamba' => 'Çarşamba',
            'çarşamba' => 'Çarşamba',
            'ÇARŞAMBA' => 'Çarşamba',
            'Çar' => 'Çarşamba',
            'Perşembe' => 'Perşembe',
            'perşembe' => 'Perşembe',
            'PERŞEMBE' => 'Perşembe',
            'Per' => 'Perşembe',
            'Cuma' => 'Cuma',
            'cuma' => 'Cuma',
            'CUMA' => 'Cuma',
            'Cum' => 'Cuma',
            'Cumartesi' => 'Cumartesi',
            'cumartesi' => 'Cumartesi',
            'CUMARTESI' => 'Cumartesi',
            'Cmt' => 'Cumartesi',
            'Pazar' => 'Pazar',
            'pazar' => 'Pazar',
            'PAZAR' => 'Pazar',
            'Paz' => 'Pazar'
        ];
        
        $dayValue = !empty($day) ? ($dayMap[$day] ?? null) : null;

        // Zaman formatını düzelt (HH:MM formatına)
        if (!empty($startTime) && !preg_match('/^\d{2}:\d{2}$/', $startTime)) {
            $startTime = date('H:i', strtotime($startTime));
        }
        if (!empty($endTime) && !preg_match('/^\d{2}:\d{2}$/', $endTime)) {
            $endTime = date('H:i', strtotime($endTime));
        }

        // Kontenjan sayısal olmalı
        $capacityValue = !empty($capacity) && is_numeric($capacity) ? (int)$capacity : null;

        // Ders var mı kontrol et (aynı isim + dönem kombinasyonu)
        if (!empty($term)) {
            $exists = $this->db->fetch("SELECT id FROM courses WHERE name = ? AND term = ?", [$name, $term]);
        } else {
            $exists = $this->db->fetch("SELECT id FROM courses WHERE name = ?", [$name]);
        }
        
        if ($exists) {
            $skipped++;
            $errors[] = "Atlandı: $name (Ders zaten mevcut)";
            continue;
        }

        // Öğretmen bul
        $teacherId = null;
        if (!empty($teacherName)) {
            $teacher = $this->db->fetch("
                SELECT id FROM users 
                WHERE role='teacher' AND name LIKE ?
            ", ["%$teacherName%"]);
            
            $teacherId = $teacher['id'] ?? null;
            
            if (!$teacherId) {
                $errors[] = "Uyarı: $name için öğretmen bulunamadı: $teacherName";
            }
        }

        try {
            // Dersi ekle
            $this->db->execute("
                INSERT INTO courses (
                    name, term, course_code, category, type, 
                    teacher_id, capacity, grade_range, is_active
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ", [
                $name, 
                !empty($term) ? $term : null,
                !empty($courseCode) ? $courseCode : null,
                $categoryValue, 
                $typeValue,
                $teacherId,
                $capacityValue,
                !empty($gradeRange) ? $gradeRange : null
            ]);

            $courseId = $this->db->lastInsertId();

            // Ders saatini ekle (gün ve saat bilgisi varsa)
            if ($courseId && $dayValue && !empty($startTime) && !empty($endTime)) {
                try {
                    $this->db->execute("
                        INSERT INTO course_times (course_id, day, start_time, end_time)
                        VALUES (?, ?, ?, ?)
                    ", [$courseId, $dayValue, $startTime, $endTime]);
                } catch (\Throwable $e) {
                    $errors[] = "Uyarı: $name için ders saati eklenemedi: " . $e->getMessage();
                }
            }

            $imported++;

        } catch (\Throwable $e) {
            $skipped++;
            $errors[] = "Hata: $name - " . $e->getMessage();
        }
    }

    fclose($handle);

    $_SESSION['import_result'] = compact('imported', 'skipped', 'errors');
    $this->flashSuccess("$imported ders içe aktarıldı.");
    header('Location: index.php?module=import&action=result');
    exit;
}
    /* ==================== SONUÇ ==================== */
    public function result(): array
    {
        $result = $_SESSION['import_result'] ?? null;
        unset($_SESSION['import_result']);

        return [
            'view'   => 'import/view/result.php',
            'title'  => 'İçe Aktarma Sonucu',
            'result' => $result
        ];
    }
}