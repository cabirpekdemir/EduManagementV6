<?php
// modules/students/studentscontroller.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/database.php';

class StudentsController {
    
    private $db;
    
    public function __construct() {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: /?module=login');
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Öğrenci Listesi - İYİLEŞTİRİLMİŞ ARAMA + SIRALAMA
     */
    public function list() {
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'name';
        $order = $_GET['order'] ?? 'asc';
        
        // Güvenli sıralama kolonları
        $allowedSorts = ['name', 'full_name', 'tc_kimlik', 'school', 'birth_date', 'class_name', 'phone'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        // Kolon adı çevirisi (view'daki adlar -> database kolon adları)
        $sortMapping = [
            'name' => 's.name',
            'full_name' => 's.name',  // full_name gelirse name'e map et
            'tc_kimlik' => 's.tc_kimlik',
            'school' => 's.okul',  // view'da 'school' -> DB'de 'okul'
            'birth_date' => 's.birth_date',
            'class_name' => 'c.name',  // class tablosundan
            'phone' => 's.phone'
        ];
        
        $sortColumn = $sortMapping[$sort] ?? 's.name';
        
        // SQL oluştur
        $baseSql = "SELECT s.*, c.name as class_name,
                    (SELECT COUNT(*) FROM course_students WHERE student_id = s.id) as course_count
                    FROM users s 
                    LEFT JOIN classes c ON s.class_id = c.id 
                    WHERE s.role = 'student'";
        
        if ($search) {
            $baseSql .= " AND (
                            s.name LIKE ? OR 
                            s.tc_kimlik LIKE ? OR 
                            s.phone LIKE ? OR
                            s.phone2 LIKE ? OR
                            s.phone3 LIKE ?
                        )";
        }
        
        $baseSql .= " ORDER BY {$sortColumn} {$order}";
        
        if ($search) {
            $searchParam = "%{$search}%";
            $stmt = $this->db->prepare($baseSql);
            $stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->db->query($baseSql);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $this->renderView('list', [
            'students' => $students,
            'search' => $search,
            'sort' => $sort,
            'order' => $order
        ]);
    }
    
    /**
     * Yeni Öğrenci Formu
     */
    public function create() {
        $stmt = $this->db->query("SELECT id, name FROM classes ORDER BY name ASC");
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->renderView('create', [
            'classes' => $classes,
            'formData' => $_SESSION['form_data'] ?? []
        ]);
        
        unset($_SESSION['form_data']);
    }
    
    /**
     * Öğrenci Kaydetme
     */
public function save() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?module=students&action=list');
        exit;
    }
    
    $requiredFields = [
        'name' => 'Ad Soyad',
        'phone' => 'Telefon 1',
        'okul' => 'Okul Adı',
        'birth_date' => 'Doğum Tarihi',
        'tc_kimlik' => 'T.C. Kimlik No',
        'gender' => 'Cinsiyet',
        'mother_name' => 'Anne Adı',
        'sinif' => 'Sınıf',
        'teaching_type' => 'Eğitim Tipi'
    ];
    
    $errors = [];
    foreach ($requiredFields as $field => $label) {
        if (empty($_POST[$field])) {
            $errors[] = $label . ' zorunludur.';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        $_SESSION['form_data'] = $_POST;
        header('Location: index.php?module=students&action=create');
        exit;
    }
    
    // TC Kimlik duplicate kontrolü ve temizleme
    $tcNo = $_POST['tc_kimlik'] ?? '';
    $tcNo = preg_replace('/\D/', '', $tcNo); // Sadece rakamlar
    $tcNo = !empty($tcNo) ? $tcNo : null; // Boşsa NULL yap
    
    if ($tcNo !== null) {
        // TC format kontrolü
        if (!preg_match('/^[1-9][0-9]{10}$/', $tcNo)) {
            $_SESSION['flash_error'] = 'TC Kimlik No 11 haneli olmalıdır ve sıfır ile başlayamaz.';
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?module=students&action=create');
            exit;
        }
        
        // TC duplicate kontrolü (NULL değerler hariç)
        $stmt = $this->db->prepare("SELECT id FROM users WHERE tc_kimlik = ? AND tc_kimlik IS NOT NULL AND role = 'student'");
        $stmt->execute([$tcNo]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'Bu TC Kimlik No başka bir öğrencide kayıtlı.';
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?module=students&action=create');
            exit;
        }
    }
    
    try {
        // Telefon numaralarını temizle
        $phone1 = $this->cleanPhone($_POST['phone'] ?? '');
        $phone2 = $this->cleanPhone($_POST['phone2'] ?? '');
        $phone3 = $this->cleanPhone($_POST['phone3'] ?? '');
        
        // Gender - null-safe
        $gender = trim($_POST['gender'] ?? '');
        $gender = !empty($gender) ? $gender : null;
        
        $email = $_POST['email'] ?? null;
        $isEmailProvided = !empty($email);
        
        if (!$isEmailProvided) {
            // TC varsa TC'yi kullan, yoksa unique ID oluştur
            if ($tcNo !== null) {
                $email = $tcNo . '@temp.edu';
            } else {
                $email = 'student_' . uniqid() . '_' . time() . '@temp.edu';
            }
        }
        
        // ⭐ GÜÇLÜ ŞİFRE OLUŞTUR
        $tempPassword = Security::generateStrongPassword(12);
        $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        // Fotoğraf işleme (mevcut kodunuz)
        $profilePhoto = null;
        if (!empty($_POST['photo_data'])) {
            $photoData = $_POST['photo_data'];
            $photoData = preg_replace('#^data:image/\w+;base64,#i', '', $photoData);
            $photoData = base64_decode($photoData);
            
            $fileName = 'student_' . uniqid() . '_' . time() . '.jpg';
            $uploadDir = __DIR__ . '/../../uploads/students/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filePath = $uploadDir . $fileName;
            if (file_put_contents($filePath, $photoData)) {
                $profilePhoto = 'uploads/students/' . $fileName;
            }
        }
        
        // ⭐ EKSIK KOLONLARI EKLE
        $enrollmentStatus = $_POST['enrollment_status'] ?? 'on_kayit';
        $specialTalent = !empty($_POST['special_talent']) ? 1 : 0;
        $approved = !empty($_POST['approved']) ? 1 : 0;
        
        // Sağlık bilgileri
        $chronicCondition = trim($_POST['chronic_condition'] ?? '');
        $medications = trim($_POST['medications'] ?? '');
        $allergy = trim($_POST['allergy'] ?? '');
        $bloodType = trim($_POST['blood_type'] ?? '');
        $healthNotes = trim($_POST['health_notes'] ?? '');
        
        $sql = "INSERT INTO users (
                    role, name, email, password, tc_kimlik, gender, birth_date, birth_place,
                    phone, phone2, phone3, okul, student_number,
                    sinif, teaching_type, mother_name, father_name, guardian_name, 
                    address, profile_photo, 
                    email_verified, must_change_password, 
                    enrollment_status, special_talent, approved,
                    chronic_condition, medications, allergy, blood_type, health_notes,
                    is_active, created_at
                ) VALUES (
                    'student', ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, 
                    ?, ?,
                    ?, 1,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    1, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $_POST['name'], $email, $passwordHash,
            $tcNo, $gender, $_POST['birth_date'], $_POST['birth_date'] ?? null,
            $phone1, $phone2, $phone3,
            $_POST['okul'], $_POST['student_number'] ?? null,
            $_POST['sinif'] ?? null, $_POST['teaching_type'] ?? null,
            $_POST['mother_name'] ?? null, $_POST['father_name'] ?? null, $_POST['guardian_name'] ?? null,
            $_POST['address'] ?? null, $profilePhoto,
            $isEmailProvided ? 0 : 1,
            $enrollmentStatus, $specialTalent, $approved,
            $chronicCondition, $medications, $allergy, $bloodType, $healthNotes
        ]);
        
        $newStudentId = $this->db->lastInsertId();
        
        // ⭐ E-POSTA GÖNDER (Gerçek mail varsa)
        if ($isEmailProvided) {
            Security::sendEmail($email, 'first_login', [
                'name' => $_POST['name'],
                'email' => $email,
                'temp_password' => $tempPassword,
                'login_link' => 'http://localhost:8080/index.php?module=login',
                'app_name' => 'Hipotez Eğitim'
            ]);
            $_SESSION['flash_success'] = 'Öğrenci kaydedildi. Giriş bilgileri e-postaya gönderildi.';
        } else {
            $_SESSION['flash_success'] = 'Öğrenci kaydedildi.<br><strong>GEÇİCİ ŞİFRE:</strong> ' . $tempPassword;
        }
        
        header('Location: index.php?module=students&action=view&id=' . $newStudentId);
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Hata: ' . $e->getMessage();
        header('Location: index.php?module=students&action=create');
        exit;
    }
}
    /**
     * Öğrenci Görüntüleme
     */
    public function view() {
        $id = $_GET['id'] ?? 0;
        
        // ⭐ GÜVENLİK: ID kontrolü
        if (!$id || $id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz öğrenci ID.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        $stmt = $this->db->prepare("
            SELECT s.*, c.name as class_name 
            FROM users s 
            LEFT JOIN classes c ON s.class_id = c.id 
            WHERE s.id = ? AND s.role = 'student'
        ");
        $stmt->execute([$id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Öğrenci bulunamadı.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        // Öğrencinin aldığı dersleri getir (term, day, time bilgileriyle courses tablosundan)
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   c.term as semester,
                   YEAR(c.created_at) as year,
                   c.day as day_of_week,
                   CONCAT(TIME_FORMAT(c.start_time, '%H:%i'), '-', TIME_FORMAT(c.end_time, '%H:%i')) as time_slot,
                   u.name as teacher_name,
                   (SELECT COUNT(*) FROM course_students WHERE course_id = c.id) as student_count
            FROM course_students cs
            INNER JOIN courses c ON cs.course_id = c.id
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE cs.student_id = ?
            ORDER BY c.name ASC
        ");
        $stmt->execute([$id]);
        $studentCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->renderView('view', [
            'student' => $student,
            'studentCourses' => $studentCourses  // ⭐ DÜZELTİLDİ: studentCourses eklendi
        ]);
    }
    
    /**
     * Öğrenci Düzenleme
     */
    public function edit() {
        $id = $_GET['id'] ?? 0;
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
        $stmt->execute([$id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Öğrenci bulunamadı.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        $stmt = $this->db->query("SELECT id, name FROM classes ORDER BY name ASC");
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->renderView('edit', [
            'student' => $student,
            'classes' => $classes
        ]);
    }
    
 public function update()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?module=students&action=index');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    
    // Zorunlu alanlar
    $requiredFields = [
        'name' => 'Ad Soyad',
        'email' => 'E-posta',
        'mother_name' => 'Anne Adı',
        'sinif' => 'Sınıf',
        'teaching_type' => 'Eğitim Tipi'
    ];
    
    $errors = [];
    foreach ($requiredFields as $field => $label) {
        if (empty($_POST[$field])) {
            $errors[] = $label . ' zorunludur.';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        $_SESSION['form_data'] = $_POST; // Form verilerini kaydet
        header('Location: index.php?module=students&action=edit&id=' . $id);
        exit;
    }
    
    // Verileri al
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tc = preg_replace('/\D/', '', $_POST['tc_kimlik'] ?? '');
    $tc = !empty($tc) ? $tc : null;
    
    // Telefon numaralarını temizle
    $phone = $this->cleanPhone($_POST['phone'] ?? '');
    $phone2 = $this->cleanPhone($_POST['phone2'] ?? '');
    $phone3 = $this->cleanPhone($_POST['phone3'] ?? '');
    
    $okul = trim($_POST['okul'] ?? '');
    $sinif = trim($_POST['sinif'] ?? '');
    $motherName = trim($_POST['mother_name'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $guardianName = trim($_POST['guardian_name'] ?? '');
    $birthDate = trim($_POST['birth_date'] ?? '');
    $birthPlace = trim($_POST['birth_place'] ?? '');
    
    // Gender - null-safe
    $gender = trim($_POST['gender'] ?? '');
    $gender = !empty($gender) ? $gender : null;
    
    $address = trim($_POST['address'] ?? '');
    $studentNumber = trim($_POST['student_number'] ?? '');
    
    // Teaching Type - null-safe ve ENUM değerlerini kontrol et
    $teachingType = trim($_POST['teaching_type'] ?? '');
    if (!in_array($teachingType, ['tam_gun', 'sabahci', 'oglenci', ''])) {
        $teachingType = null;
    }
    $teachingType = !empty($teachingType) ? $teachingType : null;
    
    // Enrollment Status - null-safe
    $enrollmentStatus = trim($_POST['enrollment_status'] ?? 'on_kayit');
    if (empty($enrollmentStatus)) {
        $enrollmentStatus = 'on_kayit';
    }
    
    $specialTalent = !empty($_POST['special_talent']) ? 1 : 0;
    $isActive = !empty($_POST['is_active']) ? 1 : 0;
    $approved = !empty($_POST['approved']) ? 1 : 0;
    
    // Sağlık bilgileri
    $chronicCondition = trim($_POST['chronic_condition'] ?? '');
    $medications = trim($_POST['medications'] ?? '');
    $allergy = trim($_POST['allergy'] ?? '');
    $bloodType = trim($_POST['blood_type'] ?? '');
    $healthNotes = trim($_POST['health_notes'] ?? '');
    
    $password = trim($_POST['password'] ?? '');
    
    // Fotoğraf işleme
    $profilePhoto = null;
    if (!empty($_POST['photo_data'])) {
        $photoData = $_POST['photo_data'];
        $photoData = preg_replace('#^data:image/\w+;base64,#i', '', $photoData);
        $photoData = base64_decode($photoData);
        
        if ($photoData !== false) {
            $fileName = 'student_' . $id . '_' . time() . '.jpg';
            $uploadDir = __DIR__ . '/../../uploads/students/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filePath = $uploadDir . $fileName;
            if (file_put_contents($filePath, $photoData)) {
                $profilePhoto = 'uploads/students/' . $fileName;
                
                // Eski fotoğrafı sil
                $stmt = $this->db->prepare("SELECT profile_photo FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $oldPhoto = $stmt->fetchColumn();
                if ($oldPhoto && file_exists(__DIR__ . '/../../' . $oldPhoto)) {
                    @unlink(__DIR__ . '/../../' . $oldPhoto);
                }
            }
        }
    }

    // TC kontrolü (eğer girildiyse)
    if (!empty($tc) && !preg_match('/^[1-9][0-9]{10}$/', $tc)) {
        $_SESSION['flash_error'] = 'TC Kimlik No 11 haneli olmalıdır ve sıfır ile başlayamaz.';
        header('Location: index.php?module=students&action=edit&id=' . $id);
        exit;
    }

    // Duplicate check
    if ($tc !== null) {
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE (email = ? OR (tc_kimlik = ? AND tc_kimlik IS NOT NULL)) 
            AND id != ?
        ");
        $stmt->execute([$email, $tc, $id]);
    } else {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
    }
    
    if ($stmt->fetch()) {
        if ($tc !== null) {
            $_SESSION['flash_error'] = 'Bu email veya TC Kimlik No başka bir öğrencide kayıtlı.';
        } else {
            $_SESSION['flash_error'] = 'Bu email başka bir öğrencide kayıtlı.';
        }
        $_SESSION['form_data'] = $_POST; // Form verilerini kaydet
        header('Location: index.php?module=students&action=edit&id=' . $id);
        exit;
    }

    try {
        // Hangi kolonların mevcut olduğunu kontrol et
        $stmt = $this->db->query("SHOW COLUMNS FROM users");
        $existingColumns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[] = $row['Field'];
        }
        
        $hasHealthColumns = in_array('chronic_condition', $existingColumns);
        
        // Şifre güncellemesi varsa
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($profilePhoto) {
                // Fotoğraf da güncelleniyor
                $sql = "UPDATE users 
                        SET name=?, email=?, tc_kimlik=?, password=?, 
                            phone=?, phone2=?, phone3=?, okul=?, 
                            sinif=?, mother_name=?, father_name=?, guardian_name=?,
                            birth_date=?, birth_place=?, gender=?, address=?,
                            student_number=?, teaching_type=?, enrollment_status=?,
                            special_talent=?, is_active=?, approved=?, profile_photo=?";
                
                $params = [
                    $name, $email, $tc, $hashedPassword, 
                    $phone, $phone2, $phone3, $okul,
                    $sinif, $motherName, $fatherName, $guardianName,
                    $birthDate, $birthPlace, $gender, $address,
                    $studentNumber, $teachingType, $enrollmentStatus,
                    $specialTalent, $isActive, $approved, $profilePhoto
                ];
            } else {
                // Sadece diğer alanlar
                $sql = "UPDATE users 
                        SET name=?, email=?, tc_kimlik=?, password=?, 
                            phone=?, phone2=?, phone3=?, okul=?, 
                            sinif=?, mother_name=?, father_name=?, guardian_name=?,
                            birth_date=?, birth_place=?, gender=?, address=?,
                            student_number=?, teaching_type=?, enrollment_status=?,
                            special_talent=?, is_active=?, approved=?";
                
                $params = [
                    $name, $email, $tc, $hashedPassword, 
                    $phone, $phone2, $phone3, $okul,
                    $sinif, $motherName, $fatherName, $guardianName,
                    $birthDate, $birthPlace, $gender, $address,
                    $studentNumber, $teachingType, $enrollmentStatus,
                    $specialTalent, $isActive, $approved
                ];
            }
            
            // Sağlık kolonları varsa ekle
            if ($hasHealthColumns) {
                $sql .= ", chronic_condition=?, medications=?, allergy=?, blood_type=?, health_notes=?";
                $params[] = $chronicCondition;
                $params[] = $medications;
                $params[] = $allergy;
                $params[] = $bloodType;
                $params[] = $healthNotes;
            }
            
            $sql .= " WHERE id=? AND role='student'";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
            // Şifre güncellenmiyorsa
            if ($profilePhoto) {
                // Fotoğraf güncelleniyor
                $sql = "UPDATE users 
                        SET name=?, email=?, tc_kimlik=?, 
                            phone=?, phone2=?, phone3=?, okul=?, 
                            sinif=?, mother_name=?, father_name=?, guardian_name=?,
                            birth_date=?, birth_place=?, gender=?, address=?,
                            student_number=?, teaching_type=?, enrollment_status=?,
                            special_talent=?, is_active=?, approved=?, profile_photo=?";
                
                $params = [
                    $name, $email, $tc, 
                    $phone, $phone2, $phone3, $okul,
                    $sinif, $motherName, $fatherName, $guardianName,
                    $birthDate, $birthPlace, $gender, $address,
                    $studentNumber, $teachingType, $enrollmentStatus,
                    $specialTalent, $isActive, $approved, $profilePhoto
                ];
            } else {
                // Sadece diğer alanlar
                $sql = "UPDATE users 
                        SET name=?, email=?, tc_kimlik=?, 
                            phone=?, phone2=?, phone3=?, okul=?, 
                            sinif=?, mother_name=?, father_name=?, guardian_name=?,
                            birth_date=?, birth_place=?, gender=?, address=?,
                            student_number=?, teaching_type=?, enrollment_status=?,
                            special_talent=?, is_active=?, approved=?";
                
                $params = [
                    $name, $email, $tc, 
                    $phone, $phone2, $phone3, $okul,
                    $sinif, $motherName, $fatherName, $guardianName,
                    $birthDate, $birthPlace, $gender, $address,
                    $studentNumber, $teachingType, $enrollmentStatus,
                    $specialTalent, $isActive, $approved
                ];
            }
            
            // Sağlık kolonları varsa ekle
            if ($hasHealthColumns) {
                $sql .= ", chronic_condition=?, medications=?, allergy=?, blood_type=?, health_notes=?";
                $params[] = $chronicCondition;
                $params[] = $medications;
                $params[] = $allergy;
                $params[] = $bloodType;
                $params[] = $healthNotes;
            }
            
            $sql .= " WHERE id=? AND role='student'";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // ⭐ KONTROL: Gerçekten update oldu mu?
            if ($stmt->rowCount() === 0) {
                // Hiçbir satır güncellenmedi - ya kayıt yok ya da değerler aynı
                $checkStmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND role = 'student'");
                $checkStmt->execute([$id]);
                if (!$checkStmt->fetch()) {
                    $_SESSION['flash_error'] = 'Öğrenci bulunamadı!';
                    header('Location: index.php?module=students&action=list');
                    exit;
                }
                // Kayıt var ama değişmemiş - bu normal olabilir
            }
        }

        $_SESSION['flash_success'] = 'Öğrenci bilgileri başarıyla güncellendi.';
        header('Location: index.php?module=students&action=view&id=' . $id);
        exit;

    } catch (PDOException $e) {
        $_SESSION['flash_error'] = 'Güncelleme sırasında hata: ' . $e->getMessage();
        header('Location: index.php?module=students&action=edit&id=' . $id);
        exit;
    }
}
    
    /**
     * Öğrenci Silme
     */
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->execute([$id]);
            
            $_SESSION['flash_success'] = 'Öğrenci başarıyla silindi.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Silme sırasında hata: ' . $e->getMessage();
        }
        
        header('Location: index.php?module=students&action=list');
        exit;
    }
    
    /**
     * Öğretmen Notu Güncelleme
     */
    public function update_teacher_note() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        if (!in_array($_SESSION['user']['role'] ?? '', ['admin', 'teacher'])) {
            $_SESSION['flash_error'] = 'Bu işlem için yetkiniz yok.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        $studentId = $_POST['student_id'] ?? 0;
        $teacherNote = $_POST['teacher_note'] ?? '';
        
        try {
            $sql = "UPDATE users SET teacher_note = ? WHERE id = ? AND role = 'student'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$teacherNote, $studentId]);
            
            $_SESSION['flash_success'] = 'Öğretmen notu güncellendi.';
            header('Location: index.php?module=students&action=view&id=' . $studentId);
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Not güncellenirken hata: ' . $e->getMessage();
            header('Location: index.php?module=students&action=view&id=' . $studentId);
            exit;
        }
    }
    
    /**
     * Ders Kaydı Sayfası
     */
    public function assign_course() {
        $id = $_GET['id'] ?? 0;
        
        $stmt = $this->db->prepare("
            SELECT s.*, c.name as class_name 
            FROM users s 
            LEFT JOIN classes c ON s.class_id = c.id 
            WHERE s.id = ? AND s.role = 'student'
        ");
        $stmt->execute([$id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Öğrenci bulunamadı.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        $stmt = $this->db->query("
            SELECT c.*, u.name as teacher_name 
            FROM courses c 
            LEFT JOIN users u ON c.teacher_id = u.id 
            WHERE c.is_active = 1
            ORDER BY c.name ASC
        ");
        $allCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->prepare("
            SELECT c.*, u.name as teacher_name 
            FROM course_students cs
            INNER JOIN courses c ON cs.course_id = c.id
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE cs.student_id = ?
            ORDER BY c.name ASC
        ");
        $stmt->execute([$id]);
        $studentCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->renderView('assign_course', [
            'student' => $student,
            'allCourses' => $allCourses,
            'studentCourses' => $studentCourses
        ]);
    }
    
    /**
     * Ders Kaydı Kaydetme
     */
    public function save_course_assignment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        $studentId = $_POST['student_id'] ?? 0;
        $courseId = $_POST['course_id'] ?? 0;
        
        if (!$studentId || !$courseId) {
            $_SESSION['flash_error'] = 'Öğrenci ve ders seçmelisiniz.';
            header('Location: index.php?module=students&action=assign_course&id=' . $studentId);
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM course_students 
                WHERE student_id = ? AND course_id = ?
            ");
            $stmt->execute([$studentId, $courseId]);
            
            if ($stmt->fetch()) {
                $_SESSION['flash_error'] = 'Bu öğrenci zaten bu derse kayıtlı.';
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO course_students (course_id, student_id, enrolled_at) 
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$courseId, $studentId]);
                
                $_SESSION['flash_success'] = 'Ders kaydı başarıyla eklendi.';
            }
            
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Ders kaydı eklenirken hata: ' . $e->getMessage();
        }
        
        header('Location: index.php?module=students&action=assign_course&id=' . $studentId);
        exit;
    }
    
    /**
     * Ders Kaydı Kaldırma
     */
    public function remove_course_assignment() {
        $studentId = $_GET['student_id'] ?? 0;
        $courseId = $_GET['course_id'] ?? 0;
        
        if (!$studentId || !$courseId) {
            $_SESSION['flash_error'] = 'Geçersiz parametreler.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                DELETE FROM course_students 
                WHERE student_id = ? AND course_id = ?
            ");
            $stmt->execute([$studentId, $courseId]);
            
            $_SESSION['flash_success'] = 'Ders kaydı kaldırıldı.';
            
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Ders kaydı kaldırılırken hata: ' . $e->getMessage();
        }
        
        header('Location: index.php?module=students&action=assign_course&id=' . $studentId);
        exit;
    }
    
    /**
     * Öğrenci Durumunu Değiştir
     */
    public function change_status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        $studentId = (int)($_POST['student_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';
        
        // Geçerli durumlar
        $validStatuses = [
            'on_kayit', 'sinav_secim', 'sinav_secimi_yapti', 'ders_secimi_yapan',
            'sinav_sonuc_girisi', 'sinavi_kazanamayan', 'aktif', 
            'kayit_dondurma', 'kayit_silinen', 'mezun'
        ];
        
        if (!$studentId || !in_array($newStatus, $validStatuses)) {
            $_SESSION['flash_error'] = 'Geçersiz durum değeri.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET enrollment_status = ? 
                WHERE id = ? AND role = 'student'
            ");
            $stmt->execute([$newStatus, $studentId]);
            
            // Durum isimlerini Türkçeleştir
            $statusNames = [
                'on_kayit' => 'Ön Kayıt',
                'sinav_secim' => 'Sınav Seçim',
                'sinav_secimi_yapti' => 'Sınav Seçimi Yaptı',
                'ders_secimi_yapan' => 'Ders Seçimi Yapan',
                'sinav_sonuc_girisi' => 'Sınav Sonuç Girişi',
                'sinavi_kazanamayan' => 'Sınavı Kazanamayan',
                'aktif' => 'Aktif Öğrenci',
                'kayit_dondurma' => 'Kayıt Dondurma',
                'kayit_silinen' => 'Kayıt Silinen',
                'mezun' => 'Mezun'
            ];
            
            $statusText = $statusNames[$newStatus] ?? $newStatus;
            $_SESSION['flash_success'] = "Öğrenci durumu '{$statusText}' olarak güncellendi.";
            
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Durum güncellenirken hata: ' . $e->getMessage();
        }
        
        header('Location: index.php?module=students&action=view&id=' . $studentId);
        exit;
    }
    
    /**
     * Öğrencinin aktif/pasif durumunu değiştir (toggle)
     */
    public function toggle_active_status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Geçersiz istek.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        $studentId = (int)($_POST['student_id'] ?? 0);
        $newStatus = (int)($_POST['is_active'] ?? 0);
        
        if (!$studentId) {
            $_SESSION['flash_error'] = 'Geçersiz öğrenci ID.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        try {
            // Öğrenci var mı kontrol et
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND role = 'student'");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                $_SESSION['flash_error'] = 'Öğrenci bulunamadı.';
                header('Location: index.php?module=students&action=list');
                exit;
            }
            
            // Durumu güncelle
            $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$newStatus, $studentId]);
            
            $statusText = $newStatus ? 'Aktif' : 'Pasif';
            $_SESSION['flash_success'] = "Öğrenci durumu '{$statusText}' olarak güncellendi.";
            
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Durum güncellenirken hata: ' . $e->getMessage();
        }
        
        header('Location: index.php?module=students&action=view&id=' . $studentId);
        exit;
    }
    
    /**
     * Sınıf bilgisinden kademe hesapla (İlkokul/Ortaokul)
     */
    private function getKademe($sinif) {
        if (empty($sinif)) return null;
        
        // "1. sınıf" -> "1" çıkar
        $sinifNo = (int) substr($sinif, 0, 1);
        
        if ($sinifNo >= 1 && $sinifNo <= 4) {
            return 'İlkokul';
        } else if ($sinifNo >= 5 && $sinifNo <= 8) {
            return 'Ortaokul';
        }
        
        return null;
    }
    
    /**
     * Telefon numarasını temizle - sadece rakamlar
     */
    private function cleanPhone($phone) {
        if (empty($phone)) {
            return null;
        }
        
        // Sadece rakamları al
        $cleaned = preg_replace('/\D/', '', $phone);
        
        // Boş string ise null döndür
        return !empty($cleaned) ? $cleaned : null;
    }
    
    /**
     * View Render
     */
    private function renderView($viewName, $data = []) {
        extract($data);
        
        $viewPath = __DIR__ . "/view/{$viewName}.php";
        
        if (!file_exists($viewPath)) {
            die("View bulunamadı: {$viewPath}");
        }
        
        ob_start();
        include $viewPath;
        $pageContent = ob_get_clean();
        
        $pageTitle = $data['title'] ?? 'Öğrenciler';
        
        require_once __DIR__ . '/../../themes/default/layout.php';
    }
    
    /**
     * Transkript PDF Oluştur ve İndir
     */
    public function transcript_pdf() {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['flash_error'] = 'Geçersiz öğrenci ID.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        // Öğrenci bilgilerini çek
        $stmt = $this->db->prepare("
            SELECT s.*, c.name as class_name 
            FROM users s 
            LEFT JOIN classes c ON s.class_id = c.id 
            WHERE s.id = ? AND s.role = 'student'
        ");
        $stmt->execute([$id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Öğrenci bulunamadı.';
            header('Location: index.php?module=students&action=list');
            exit;
        }
        
        // Öğrencinin aldığı dersler ve notları
        $stmt = $this->db->prepare("
            SELECT c.name as course_name,
                   c.term as semester,
                   YEAR(c.created_at) as year,
                   c.credits,
                   u.name as teacher_name
            FROM course_students cs
            INNER JOIN courses c ON cs.course_id = c.id
            LEFT JOIN users u ON c.teacher_id = u.id
            WHERE cs.student_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Toplam devamsızlık
        $totalAbsence = 0; // Şimdilik 0, gerçek veritabanı bağlantısı gerekirse eklenecek
        
        // HTML oluştur
        $this->generateTranscriptHTML($student, $courses, $totalAbsence);
    }
    
    /**
     * Transkript HTML Template
     */
    private function generateTranscriptHTML($student, $courses, $totalAbsence) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Transkript - <?= htmlspecialchars($student['name']) ?></title>
            <style>
                @page { margin: 20mm; }
                body {
                    font-family: 'DejaVu Sans', Arial, sans-serif;
                    font-size: 11pt;
                    line-height: 1.6;
                    color: #333;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 3px solid #667eea;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #667eea;
                    margin: 0 0 10px 0;
                    font-size: 24pt;
                }
                .header p {
                    margin: 5px 0;
                    color: #666;
                }
                .student-info {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                }
                .student-info table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .student-info td {
                    padding: 8px;
                    border-bottom: 1px solid #dee2e6;
                }
                .student-info td:first-child {
                    font-weight: bold;
                    width: 150px;
                    color: #667eea;
                }
                .courses-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .courses-table thead {
                    background: #667eea;
                    color: white;
                }
                .courses-table th {
                    padding: 12px;
                    text-align: left;
                    font-weight: bold;
                }
                .courses-table td {
                    padding: 10px;
                    border-bottom: 1px solid #dee2e6;
                }
                .courses-table tbody tr:nth-child(even) {
                    background: #f8f9fa;
                }
                .summary {
                    margin-top: 30px;
                    background: #e7f3ff;
                    padding: 20px;
                    border-left: 4px solid #667eea;
                }
                .summary h3 {
                    margin-top: 0;
                    color: #667eea;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    color: #999;
                    font-size: 9pt;
                    border-top: 1px solid #dee2e6;
                    padding-top: 20px;
                }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <!-- Header -->
            <div class="header">
                <h1>HİPOTEZ EĞİTİM</h1>
                <p><strong>AKADEMİK TRANSKRİPT</strong></p>
                <p>Öğrenci Başarı Belgesi</p>
            </div>
            
            <!-- Student Info -->
            <div class="student-info">
                <table>
                    <tr>
                        <td>Öğrenci Adı Soyadı:</td>
                        <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Öğrenci Numarası:</td>
                        <td><?= htmlspecialchars($student['student_number'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td>T.C. Kimlik No:</td>
                        <td><?= htmlspecialchars($student['tc_kimlik'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td>Doğum Tarihi:</td>
                        <td><?= $student['birth_date'] ? date('d.m.Y', strtotime($student['birth_date'])) : '—' ?></td>
                    </tr>
                    <tr>
                        <td>Sınıf:</td>
                        <td><?= htmlspecialchars($student['sinif'] ?? $student['class_name'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td>Kayıt Tarihi:</td>
                        <td><?= date('d.m.Y', strtotime($student['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Courses -->
            <h2 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                Alınan Dersler
            </h2>
            
            <?php if (empty($courses)): ?>
                <p style="text-align: center; padding: 40px; color: #999;">
                    Henüz ders kaydı bulunmamaktadır.
                </p>
            <?php else: ?>
                <table class="courses-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Ders Adı</th>
                            <th style="width: 100px;">Dönem</th>
                            <th style="width: 80px;">Yıl</th>
                            <th style="width: 80px;">Kredi</th>
                            <th>Öğretmen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalCredits = 0;
                        foreach ($courses as $index => $course): 
                            $credits = (int)($course['credits'] ?? 0);
                            $totalCredits += $credits;
                        ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($course['course_name']) ?></strong></td>
                                <td><?= htmlspecialchars($course['semester'] ?? 'Güz') ?></td>
                                <td><?= htmlspecialchars($course['year'] ?? date('Y')) ?></td>
                                <td style="text-align: center;"><?= $credits ?></td>
                                <td><?= htmlspecialchars($course['teacher_name'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Summary -->
                <div class="summary">
                    <h3>ÖZET BİLGİLER</h3>
                    <table style="width: 100%;">
                        <tr>
                            <td><strong>Toplam Alınan Ders:</strong></td>
                            <td><strong><?= count($courses) ?> ders</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Toplam Kredi:</strong></td>
                            <td><strong><?= $totalCredits ?> kredi</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Toplam Devamsızlık:</strong></td>
                            <td><strong><?= $totalAbsence ?> gün</strong></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="footer">
                <p>Bu belge <?= date('d.m.Y H:i') ?> tarihinde elektronik ortamda oluşturulmuştur.</p>
                <p><strong>Hipotez Eğitim</strong> | www.hipotezegitim.com</p>
            </div>
            
            <!-- Print Button -->
            <div class="no-print" style="text-align: center; margin-top: 30px;">
                <button onclick="window.print()" style="
                    background: #667eea;
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    font-size: 14pt;
                    border-radius: 5px;
                    cursor: pointer;
                ">
                    <i class="fa fa-print"></i> PDF Olarak Kaydet / Yazdır
                </button>
                <button onclick="window.close()" style="
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    font-size: 14pt;
                    border-radius: 5px;
                    cursor: pointer;
                    margin-left: 10px;
                ">
                    Kapat
                </button>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();
        echo $html;
        exit;
    }
    
public function export_excel() {
    $type = $_GET['type'] ?? 'all';
    
    if ($type === 'selected' && isset($_POST['student_ids'])) {
        $ids = $_POST['student_ids'];
        $ids = explode(',', $ids);
        $ids = array_map('intval', $ids);
        $idList = implode(',', $ids);
        $sql = "SELECT * FROM users WHERE id IN ($idList) AND role = 'student' ORDER BY name ASC";
    } else {
        $sql = "SELECT * FROM users WHERE is_active = 1 AND role = 'student' ORDER BY name ASC";
    }
    
    $result = $this->db->query($sql);
    
    $filename = 'ogrenci_listesi_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, [
        'No', 'Öğrenci No', 'Ad Soyad', 'TC Kimlik', 'Doğum Tarihi', 'Yaş', 
        'Cinsiyet', 'Telefon 1', 'Telefon 2', 'Email', 'Adres', 'Sınıf',
        'Anne Adı', 'Baba Adı', 'Veli Adı', 'Durum', 'Aktif/Pasif', 'Kayıt Tarihi'
    ], ';');
    
    $index = 1;
    while ($student = $result->fetch(PDO::FETCH_ASSOC)) {
        $age = '';
        if ($student['birth_date']) {
            $birthDate = new DateTime($student['birth_date']);
            $today = new DateTime('today');
            $age = $birthDate->diff($today)->y;
        }
        
        $statusNames = [
            'on_kayit' => 'Ön Kayıt',
            'aktif' => 'Aktif',
            'ders_secimi_yapan' => 'Ders Seçimi',
            'mezun' => 'Mezun'
        ];
        $status = $statusNames[$student['enrollment_status'] ?? ''] ?? $student['enrollment_status'];
        
        fputcsv($output, [
            $index++,
            $student['student_number'] ?? '',
            $student['name'],
            $student['tc_kimlik'] ?? '',
            $student['birth_date'] ? date('d.m.Y', strtotime($student['birth_date'])) : '',
            $age,
            $student['gender'] ?? '',
            $student['phone'] ?? '',
            $student['phone2'] ?? '',
            $student['email'] ?? '',
            $student['address'] ?? '',
            $student['sinif'] ?? '',
            $student['mother_name'] ?? '',
            $student['father_name'] ?? '',
            $student['guardian_name'] ?? '',
            $status,
            $student['is_active'] ? 'Aktif' : 'Pasif',
            $student['created_at'] ? date('d.m.Y', strtotime($student['created_at'])) : ''
        ], ';');
    }
    
    fclose($output);
    exit;
}

public function bulk_action() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_message'] = 'Geçersiz istek!';
        header('Location: index.php?module=students&action=list');
        exit;
    }
    
    $actionType = $_POST['action_type'] ?? '';
    $studentIds = $_POST['student_ids'] ?? '';
    $newStatus = $_POST['new_status'] ?? '';
    
    if (empty($studentIds)) {
        $_SESSION['error_message'] = 'Hiç öğrenci seçilmedi!';
        header('Location: index.php?module=students&action=list');
        exit;
    }
    
    $ids = explode(',', $studentIds);
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids);
    
    if (empty($ids)) {
        $_SESSION['error_message'] = 'Geçersiz öğrenci seçimi!';
        header('Location: index.php?module=students&action=list');
        exit;
    }
    
    $count = count($ids);
    $idList = implode(',', $ids);
    
    try {
        switch ($actionType) {
            case 'activate':
                $sql = "UPDATE users SET is_active = 1 WHERE id IN ($idList) AND role = 'student'";
                $this->db->query($sql);
                $_SESSION['success_message'] = "$count öğrenci aktif yapıldı!";
                break;
                
            case 'deactivate':
                $sql = "UPDATE users SET is_active = 0 WHERE id IN ($idList) AND role = 'student'";
                $this->db->query($sql);
                $_SESSION['success_message'] = "$count öğrenci pasif yapıldı!";
                break;
                
            case 'change_status':
                if (empty($newStatus)) {
                    throw new Exception('Yeni durum belirtilmedi!');
                }
                
                $sql = "UPDATE users SET enrollment_status = ? WHERE id IN ($idList) AND role = 'student'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$newStatus]);
                
                $_SESSION['success_message'] = "$count öğrencinin durumu değiştirildi!";
                break;
                
            case 'delete':
                $sql = "UPDATE users SET is_active = 0, deleted_at = NOW() WHERE id IN ($idList) AND role = 'student'";
                $this->db->query($sql);
                $_SESSION['success_message'] = "$count öğrenci silindi!";
                break;
                
            default:
                throw new Exception('Geçersiz işlem tipi!');
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Hata: ' . $e->getMessage();
    }
    
    header('Location: index.php?module=students&action=list');
    exit;
}
}