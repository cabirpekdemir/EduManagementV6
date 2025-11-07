<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class BulkUserController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            die("Bu alana erişim yetkiniz yok.");
        }
    }

    public function index() {
        return [
            'view' => 'bulkuser/view/index.php',
            'title' => 'Toplu Kullanıcı Ekleme',
            'pageTitle' => 'Toplu Kullanıcı Ekleme',
            'success_count' => $_GET['success_count'] ?? 0,
            'skipped_count' => $_GET['skipped_count'] ?? 0,
            'error_count' => $_GET['error_count'] ?? 0,
            'error_message' => $_GET['error_message'] ?? null
        ];
    }

    /**
     * Yapıştır yöntemi (Tab-separated)
     */
    public function paste_upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['user_data'])) {
            redirect('index.php?module=bulkuser&action=index&error_message=Veri bulunamadı');
            exit;
        }

        $pasted_data = trim($_POST['user_data']);
        $lines = explode("\n", $pasted_data);

        $success_count = 0;
        $skipped_count = 0;
        $error_count = 0;
        $pdo = $this->db->getConnection();

        foreach ($lines as $lineNum => $line) {
            if (empty(trim($line))) continue;

            $row = array_map('trim', explode("\t", trim($line)));

            try {
                $result = $this->processUserRow($row, $pdo);
                if ($result === 'success') {
                    $success_count++;
                } elseif ($result === 'skipped') {
                    $skipped_count++;
                } else {
                    $error_count++;
                }
            } catch (\Exception $e) {
                error_log("Bulk user paste error (line $lineNum): " . $e->getMessage());
                $error_count++;
            }
        }
        
        redirect("index.php?module=bulkuser&action=index&success_count={$success_count}&skipped_count={$skipped_count}&error_count={$error_count}");
        exit;
    }

    /**
     * CSV dosyası yükleme
     */
    public function csv_upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?module=bulkuser&action=index&error_message=Geçersiz istek');
            exit;
        }

        if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            redirect('index.php?module=bulkuser&action=index&error_message=Dosya yüklenemedi');
            exit;
        }

        $file = $_FILES['csv_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];

        // Dosya uzantısı kontrolü
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExt, ['csv', 'txt'])) {
            redirect('index.php?module=bulkuser&action=index&error_message=Sadece CSV dosyası yükleyebilirsiniz');
            exit;
        }

        $success_count = 0;
        $skipped_count = 0;
        $error_count = 0;
        $pdo = $this->db->getConnection();

        // CSV dosyasını oku
        if (($handle = fopen($fileTmpName, 'r')) !== false) {
            $lineNum = 0;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $lineNum++;
                
                // İlk satır başlık olabilir, atla
                if ($lineNum === 1) {
                    // Başlık satırını kontrol et
                    if (stripos($row[0], 'ad') !== false || stripos($row[0], 'name') !== false) {
                        continue; // Başlık satırını atla
                    }
                }

                if (empty(trim($row[0] ?? ''))) continue;

                try {
                    $result = $this->processUserRow($row, $pdo);
                    if ($result === 'success') {
                        $success_count++;
                    } elseif ($result === 'skipped') {
                        $skipped_count++;
                    } else {
                        $error_count++;
                    }
                } catch (\Exception $e) {
                    error_log("Bulk user CSV error (line $lineNum): " . $e->getMessage());
                    $error_count++;
                }
            }
            
            fclose($handle);
        }
        
        redirect("index.php?module=bulkuser&action=index&success_count={$success_count}&skipped_count={$skipped_count}&error_count={$error_count}");
        exit;
    }

    /**
     * Kullanıcı satırını işle (CSV veya Paste)
     */
    private function processUserRow(array $row, $pdo): string {
        // Sütun indeksleri (0-based)
        $name = trim($row[0] ?? '');
        $email = trim($row[1] ?? '');
        $password = trim($row[2] ?? '');
        $role = trim($row[3] ?? 'student');
        $tc_kimlik = trim($row[4] ?? '');
        $student_number = trim($row[5] ?? '');
        $phone = trim($row[6] ?? '');
        $phone2 = trim($row[7] ?? '');
        $phone3 = trim($row[8] ?? '');
        $okul = trim($row[9] ?? '');
        $sinif = trim($row[10] ?? '');
        $class_id = !empty($row[11]) ? intval($row[11]) : null;
        $teaching_type = trim($row[12] ?? '');
        $special_talent = !empty($row[13]) && (strtolower($row[13]) === 'evet' || $row[13] === '1' || strtolower($row[13]) === 'true') ? 1 : 0;
        $enrollment_status = trim($row[14] ?? 'on_kayit');
        $birth_place = trim($row[15] ?? '');
        $birth_date = !empty($row[16]) ? trim($row[16]) : null;
        $gender = trim($row[17] ?? '');
        $address = trim($row[18] ?? '');
        $mother_name = trim($row[19] ?? '');
        $father_name = trim($row[20] ?? '');
        $guardian_name = trim($row[21] ?? '');

        // Zorunlu alanları kontrol et
        if (empty($name) || empty($email) || empty($password) || empty($tc_kimlik)) {
            return 'skipped'; // Eksik veri
        }

        // E-posta formatı kontrolü
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'skipped';
        }

        // TC Kimlik kontrolü (11 karakter)
        if (strlen($tc_kimlik) !== 11) {
            return 'skipped';
        }

        // Rol kontrolü
        if (!in_array($role, ['student', 'teacher', 'parent', 'admin'])) {
            $role = 'student';
        }

        // Sınıf kontrolü (student için)
        if ($role === 'student' && $class_id !== null) {
            $class_stmt = $pdo->prepare("SELECT id FROM classes WHERE id = ?");
            $class_stmt->execute([$class_id]);
            if (!$class_stmt->fetch()) {
                $class_id = null; // Geçersiz sınıf ID'si
            }
        }

        // E-posta mükerrer mi?
        $email_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $email_stmt->execute([$email]);
        if ($email_stmt->fetch()) {
            return 'skipped'; // E-posta zaten kayıtlı
        }

        // TC Kimlik mükerrer mi?
        $tc_stmt = $pdo->prepare("SELECT id FROM users WHERE tc_kimlik = ?");
        $tc_stmt->execute([$tc_kimlik]);
        if ($tc_stmt->fetch()) {
            return 'skipped'; // TC kimlik zaten kayıtlı
        }

        // Öğrenci numarası mükerrer mi? (boş değilse)
        if (!empty($student_number)) {
            $sn_stmt = $pdo->prepare("SELECT id FROM users WHERE student_number = ?");
            $sn_stmt->execute([$student_number]);
            if ($sn_stmt->fetch()) {
                return 'skipped'; // Öğrenci numarası zaten kayıtlı
            }
        }

        // Tarih formatını düzenle (DD.MM.YYYY veya DD/MM/YYYY -> YYYY-MM-DD)
        if ($birth_date) {
            $birth_date = $this->formatDate($birth_date);
        }

        // Teaching type kontrolü
        $teaching_type = strtolower($teaching_type);
        if (!in_array($teaching_type, ['tam_gun', 'sabahci', 'oglenci'])) {
            $teaching_type = null;
        }

        // Enrollment status kontrolü
        $valid_statuses = ['on_kayit', 'sinav_secim', 'sinav_secimi_yapti', 'ders_secimi_yapan', 
                          'sinav_sonuc_girisi', 'sinavi_kazanamayan', 'aktif', 'kayit_silinen', 'mezun'];
        if (!in_array($enrollment_status, $valid_statuses)) {
            $enrollment_status = 'on_kayit';
        }

        // Cinsiyet kontrolü
        if (!in_array($gender, ['Erkek', 'Kadın', 'Diğer'])) {
            $gender = null;
        }

        // Şifre hash'le
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Veritabanına ekle
        try {
            $sql = "INSERT INTO users (
                name, email, password, role, tc_kimlik, student_number,
                phone, phone2, phone3, okul, sinif, class_id,
                teaching_type, special_talent, enrollment_status,
                birth_place, birth_date, gender, address,
                mother_name, father_name, guardian_name,
                is_active, approved
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)";

            $insert_stmt = $pdo->prepare($sql);
            $insert_stmt->execute([
                $name, $email, $hashed_password, $role, $tc_kimlik, 
                empty($student_number) ? null : $student_number,
                empty($phone) ? null : $phone,
                empty($phone2) ? null : $phone2,
                empty($phone3) ? null : $phone3,
                empty($okul) ? null : $okul,
                empty($sinif) ? null : $sinif,
                $class_id,
                empty($teaching_type) ? null : $teaching_type,
                $special_talent,
                $enrollment_status,
                empty($birth_place) ? null : $birth_place,
                $birth_date,
                empty($gender) ? null : $gender,
                empty($address) ? null : $address,
                empty($mother_name) ? null : $mother_name,
                empty($father_name) ? null : $father_name,
                empty($guardian_name) ? null : $guardian_name
            ]);

            return 'success';
        } catch (\Exception $e) {
            error_log("User insert error: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Tarih formatını düzenle
     */
    private function formatDate(?string $date): ?string {
        if (empty($date)) return null;

        // DD.MM.YYYY veya DD/MM/YYYY formatını kontrol et
        if (preg_match('/^(\d{1,2})[\.\-\/](\d{1,2})[\.\-\/](\d{4})$/', $date, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }

        // YYYY-MM-DD formatını kontrol et
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date)) {
            return $date;
        }

        return null; // Geçersiz format
    }

    /**
     * Örnek CSV şablonunu indir
     */
    public function download_template() {
        $filename = 'toplu_kullanici_sablonu.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM ekle (Excel için)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Başlık satırı
        fputcsv($output, [
            'Ad Soyad',
            'E-posta',
            'Şifre',
            'Rol (student/teacher/parent)',
            'TC Kimlik (11 haneli)',
            'Öğrenci No',
            'Telefon 1',
            'Telefon 2',
            'Telefon 3',
            'Okul',
            'Sınıf',
            'Sınıf ID',
            'Öğretim Türü (tam_gun/sabahci/oglenci)',
            'Özel Yetenek (evet/hayır)',
            'Durum (on_kayit/aktif/vb)',
            'Doğum Yeri',
            'Doğum Tarihi (DD.MM.YYYY)',
            'Cinsiyet (Erkek/Kadın/Diğer)',
            'Adres',
            'Anne Adı',
            'Baba Adı',
            'Veli Adı'
        ]);

        // Örnek satırlar
        fputcsv($output, [
            'Ahmet Yılmaz',
            'ahmet@ornek.com',
            'Parola123',
            'student',
            '12345678901',
            '2024001',
            '05321234567',
            '05321234568',
            '',
            '75 YIL İLKOKULU',
            '3-A',
            '1',
            'tam_gun',
            'hayır',
            'aktif',
            'İstanbul',
            '15.03.2017',
            'Erkek',
            'Sultangazi, İstanbul',
            'Fatma Yılmaz',
            'Mehmet Yılmaz',
            'Fatma Yılmaz'
        ]);

        fputcsv($output, [
            'Ayşe Demir',
            'ayse@ornek.com',
            'Sifre456',
            'student',
            '23456789012',
            '2024002',
            '05339876543',
            '',
            '',
            'NECİP FAZIL KISAKÜREK İMAM HATİP ORTAOKULU',
            '5-B',
            '2',
            'sabahci',
            'evet',
            'on_kayit',
            'Ankara',
            '20.08.2014',
            'Kadın',
            'Çankaya, Ankara',
            'Zeynep Demir',
            'Ali Demir',
            'Zeynep Demir'
        ]);

        fclose($output);
        exit;
    }
}