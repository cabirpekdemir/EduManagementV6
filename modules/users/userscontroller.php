<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php'; // validate_password() fonksiyonu için

class UsersController
{
    protected $db;
    protected $currentUser;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentUser = $_SESSION['user'] ?? null;
    }

    public function index()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            die("Yetkiniz yok!");
        }

        $users = $this->db->select("SELECT u.*, c.name as class_name FROM users u LEFT JOIN classes c ON u.class_id = c.id ORDER BY u.name ASC");
        return ['users' => $users, 'pageTitle' => 'Kullanıcı Listesi'];
    }

    public function create()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            die("Yetkiniz yok!");
        }
        $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        $all_students = $this->db->select("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC"); // Velinin öğrencisi için
        return [
            'user' => null,
            'all_classes' => $all_classes,
            'all_students' => $all_students,
            'pageTitle' => 'Yeni Kullanıcı Oluştur'
        ];
    }

    public function store()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            die("Yetkiniz yok!");
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';
        $class_id = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
        $tc_kimlik = $_POST['tc_kimlik'] ?? '';
        $profile_photo = null; 
        $parent_of_student_id = null; 
         if ($role === 'admin') {
            redirect('index.php?module=users&action=create&error_message=' . urlencode('Bu arayüzden "Admin" rolünde kullanıcı oluşturulamaz.'));
            exit;
        }
        if ($role === 'parent') {
            $parent_of_student_id = !empty($_POST['parent_of_student_id']) ? (int)$_POST['parent_of_student_id'] : null;
            if (empty($parent_of_student_id)) {
                redirect('index.php?module=users&action=create&error_message=' . urlencode('Veli rolündeki kullanıcılar için velisi olduğu öğrenci seçilmelidir.'));
                exit;
            }
        }
        
        $password_errors = validate_password($password, $email, $name);
        if (!empty($password_errors)) {
            redirect('index.php?module=users&action=create&error_message=' . urlencode(implode("<br>", $password_errors)));
            exit;
        }

        $existing_user = $this->db->select("SELECT id FROM users WHERE email = ? OR tc_kimlik = ?", [$email, $tc_kimlik]);
        if (!empty($existing_user)) {
            redirect('index.php?module=users&action=create&error_message=' . urlencode('E-posta veya TC Kimlik zaten kayıtlı.'));
            exit;
        }

        if (empty($name) || empty($email) || empty($password) || empty($tc_kimlik)) {
            redirect('index.php?module=users&action=create&error_message=' . urlencode('Ad, E-posta, Parola ve TC Kimlik zorunlu alanlardır.'));
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0 && !empty($_FILES['profile_photo']['name'])) {
            $target_dir = __DIR__ . "/../../uploads/users/";
            if (!is_dir($target_dir)) { @mkdir($target_dir, 0775, true); }
            $filename = uniqid() . '-' . basename($_FILES["profile_photo"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $profile_photo = "uploads/users/" . $filename;
            } else {
                redirect('index.php?module=users&action=create&error_message=' . urlencode('Fotoğraf yüklenirken bir hata oluştu.'));
                exit;
            }
        }

        try {
            $this->db->getConnection()->beginTransaction();

            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO users (name, email, password, role, class_id, tc_kimlik, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $name, $email, $hashed_password, $role, $class_id, $tc_kimlik, $profile_photo
            ]);
            $user_id = $this->db->getConnection()->lastInsertId();

            if ($role === 'parent' && !empty($parent_of_student_id)) {
                $stmt_parent_student = $this->db->getConnection()->prepare(
                    "INSERT INTO parents_students (parent_id, student_id) VALUES (?, ?)"
                );
                $stmt_parent_student->execute([$user_id, $parent_of_student_id]);
            }

            $this->db->getConnection()->commit();

            log_activity('CREATE', 'Users', $user_id, "Yeni kullanıcı oluşturdu: '$name' ($role)");
            redirect('index.php?module=users&action=index&status_message=' . urlencode('Kullanıcı başarıyla oluşturuldu.'));

        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("User Store Error: " . $e->getMessage());
            redirect('index.php?module=users&action=create&error_message=' . urlencode('Kullanıcı kaydedilirken bir veritabanı hatası oluştu: ' . $e->getMessage()));
        }
        exit;
    }

    public function edit()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            die("Yetkiniz yok!");
        }

        $id = $_GET['id'] ?? 0;
        $user = $this->db->select("SELECT * FROM users WHERE id = ?", [$id])[0] ?? null;

        if (!$user) {
            redirect('index.php?module=users&action=index&error_message=' . urlencode('Kullanıcı bulunamadı.'));
            exit;
        }

        $all_classes = $this->db->select("SELECT id, name FROM classes ORDER BY name ASC");
        $all_students = $this->db->select("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC");
        
        $assigned_student_id_for_parent = null;
        if ($user['role'] === 'parent') {
            $parent_student_relation = $this->db->select("SELECT student_id FROM parents_students WHERE parent_id = ?", [$user['id']])[0] ?? null;
            $assigned_student_id_for_parent = $parent_student_relation['student_id'] ?? null;
        }
        $user['parent_id'] = $assigned_student_id_for_parent;


        return [
            'user' => $user,
            'all_classes' => $all_classes,
            'all_students' => $all_students,
            'pageTitle' => 'Kullanıcı Düzenle'
        ];
    }

    public function update()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            die("Yetkiniz yok!");
        }

        $id = $_POST['id'] ?? 0;
        if (!$id) {
            redirect('index.php?module=users&action=index&error_message=' . urlencode('Kullanıcı ID\'si eksik.'));
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';
        $class_id = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
        $tc_kimlik = $_POST['tc_kimlik'] ?? '';
        $parent_of_student_id = null;


$current_user_data = $this->db->select("SELECT password, email, name, profile_photo, role FROM users WHERE id = ?", [$id])[0] ?? null;

        if (!$current_user_data) {
            redirect('index.php?module=users&action=index&error_message=' . urlencode('Kullanıcı bulunamadı.'));
            exit;
        }

        // YENİ EKLENEN ADMİN KORUMA KONTROLLERİ
        // 1. Mevcut bir adminin rolü değiştirilmeye çalışılıyorsa engelle
        if ($current_user_data['role'] === 'admin' && $role !== 'admin') {
            redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode('Admin kullanıcısının rolü değiştirilemez.'));
            exit;
        }

        // 2. Başka bir kullanıcı Admin yapılmaya çalışılıyorsa engelle
        if ($role === 'admin' && $current_user_data['role'] !== 'admin') {
            redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode('Kullanıcılar bu arayüzden Admin rolüne yükseltilemez.'));
            exit;
        }
        if (!$current_user_data) {
            redirect('index.php?module=users&action=index&error_message=' . urlencode('Kullanıcı bulunamadı.'));
            exit;
        }

        if ($role === 'parent') {
            $parent_of_student_id = !empty($_POST['parent_of_student_id']) ? (int)$_POST['parent_of_student_id'] : null;
            if (empty($parent_of_student_id)) {
                redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode('Veli rolündeki kullanıcılar için velisi olduğu öğrenci seçilmelidir.'));
                exit;
            }
        }

        $update_password = false;
        $hashed_password = $current_user_data['password']; 

        if (!empty($password)) {
            $password_errors = validate_password($password, $email, $name); 
            if (!empty($password_errors)) {
                redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode(implode("<br>", $password_errors)));
                exit;
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_password = true;
        }
        
        if (empty($name) || empty($email) || empty($tc_kimlik)) {
            redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode('Ad, E-posta ve TC Kimlik zorunlu alanlardır.'));
            exit;
        }

        $existing_user_check = $this->db->select("SELECT id FROM users WHERE (email = ? OR tc_kimlik = ?) AND id != ?", [$email, $tc_kimlik, $id]);
        if (!empty($existing_user_check)) {
            redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode('E-posta veya TC Kimlik zaten başka bir kullanıcıya ait.'));
            exit;
        }

        $profile_photo = $current_user_data['profile_photo'] ?? null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0 && !empty($_FILES['profile_photo']['name'])) {
            if ($profile_photo && file_exists(__DIR__ . "/../../" . $profile_photo)) {
                @unlink(__DIR__ . "/../../" . $profile_photo);
            }
            $target_dir = __DIR__ . "/../../uploads/users/";
            if (!is_dir($target_dir)) { @mkdir($target_dir, 0775, true); }
            $filename = uniqid() . '-' . basename($_FILES["profile_photo"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $profile_photo = "uploads/users/" . $filename;
            } else {
                redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode('Yeni fotoğraf yüklenirken bir hata oluştu.'));
                exit;
            }
        }
        
        try {
            $this->db->getConnection()->beginTransaction();

            $sql_update = "UPDATE users SET name = ?, email = ?, role = ?, class_id = ?, tc_kimlik = ?, profile_photo = ?";
            $params = [$name, $email, $role, $class_id, $tc_kimlik, $profile_photo];

            if ($update_password) {
                $sql_update .= ", password = ?";
                $params[] = $hashed_password;
            }
            $sql_update .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $this->db->getConnection()->prepare($sql_update);
            $stmt->execute($params);

            if ($current_user_data['role'] === 'parent' || $role === 'parent') {
                $this->db->getConnection()->prepare("DELETE FROM parents_students WHERE parent_id = ?")->execute([$id]);
                if ($role === 'parent' && !empty($parent_of_student_id)) {
                    $stmt_parent_student = $this->db->getConnection()->prepare(
                        "INSERT INTO parents_students (parent_id, student_id) VALUES (?, ?)"
                    );
                    $stmt_parent_student->execute([$id, $parent_of_student_id]);
                }
            }

            $this->db->getConnection()->commit();

            log_activity('UPDATE', 'Users', $id, "Kullanıcı bilgileri güncellendi. ID: $id");
            redirect('index.php?module=users&action=index&status_message=' . urlencode('Kullanıcı başarıyla güncellendi.'));

        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("User Update Error: " . $e->getMessage());
            redirect('index.php?module=users&action=edit&id=' . $id . '&error_message=' . urlencode('Kullanıcı güncellenirken bir veritabanı hatası oluştu: ' . $e->getMessage()));
        }
        exit;
    }
    
    public function delete()
    {
        if (($this->currentUser['role'] ?? 'guest') !== 'admin') {
            die("Yetkiniz yok!");
        }

        $id = $_GET['id'] ?? 0;

        // YENİ EKLENEN KONTROL
        if ($id) {
            $user_to_check = $this->db->select("SELECT role FROM users WHERE id = ?", [$id])[0] ?? null;
            if ($user_to_check && $user_to_check['role'] === 'admin') {
                redirect('index.php?module=users&action=index&error_message=' . urlencode('Admin rolündeki kullanıcılar sistemden silinemez.'));
                exit;
            }
        }   

        $id = $_GET['id'] ?? 0;
        if (!$id) {
            redirect('index.php?module=users&action=index&error_message=' . urlencode('Kullanıcı ID\'si eksik.'));
            exit;
        }

        $user_to_delete = $this->db->select("SELECT * FROM users WHERE id = ?", [$id])[0] ?? null;

        if (!$user_to_delete) {
            redirect('index.php?module=users&action=index&error_message=' . urlencode('Kullanıcı bulunamadı.'));
            exit;
        }

        try {
            $this->db->getConnection()->beginTransaction();

            // 1. Kullanıcıyı `erased_users` tablosuna arşivle
            $extra_data_json = json_encode($user_to_delete);
            $stmt_archive = $this->db->getConnection()->prepare(
                "INSERT INTO erased_users (user_id, name, email, role, okul, sinif, tc_kimlik, extra_data, erased_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt_archive->execute([
                $user_to_delete['id'], $user_to_delete['name'] ?? null, $user_to_delete['email'] ?? null,
                $user_to_delete['role'] ?? null, $user_to_delete['okul'] ?? null, $user_to_delete['sinif'] ?? null,
                $user_to_delete['tc_kimlik'] ?? null, $extra_data_json
            ]);

            // 2. Tüm ilişkili tablolardaki kayıtları temizle
            
            // --- İçerik Tutulan Tablolar (UPDATE -> SET NULL) ---
            // Bu tablolardaki kayıtları, kimin oluşturduğu bilgisi kaybolsa da saklamak istiyoruz.
            $this->db->getConnection()->prepare("UPDATE assignments SET student_id = NULL WHERE student_id = ?")->execute([$id]);
            $this->db->getConnection()->prepare("UPDATE messages SET sender_id = NULL WHERE sender_id = ?")->execute([$id]);
            $this->db->getConnection()->prepare("UPDATE messages SET receiver_id = NULL WHERE receiver_id = ?")->execute([$id]);
            $this->db->getConnection()->prepare("UPDATE grades SET student_id = NULL WHERE student_id = ?")->execute([$id]);
            $this->db->getConnection()->prepare("UPDATE guidance_sessions SET student_id = NULL WHERE student_id = ?")->execute([$id]);
            
            // --- Bağlantı (Pivot) Tabloları (DELETE) ---
            // Bu tablolardaki kayıtlar, kullanıcı silinince anlamsızlaşır, bu yüzden doğrudan silinirler.
            $this->db->getConnection()->prepare("DELETE FROM parents_students WHERE parent_id = ? OR student_id = ?")->execute([$id, $id]);
            $this->db->getConnection()->prepare("DELETE FROM student_courses WHERE student_id = ?")->execute([$id]);
           // SON EKLENEN SATIR
            $this->db->getConnection()->prepare("DELETE FROM teachers_students WHERE student_id = ? OR teacher_id = ?")->execute([$id, $id]);
            // 3. Profil fotoğrafını sunucudan sil
            if (!empty($user_to_delete['profile_photo']) && file_exists(__DIR__ . "/../../" . $user_to_delete['profile_photo'])) {
                @unlink(__DIR__ . "/../../" . $user_to_delete['profile_photo']);
            }

            // 4. Artık güvenli: Kullanıcıyı ana `users` tablosundan sil
            $this->db->getConnection()->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

            $this->db->getConnection()->commit();

            log_activity('ARCHIVE_DELETE', 'Users', $id, "Kullanıcı arşivlendi ve silindi: '{$user_to_delete['name']}' ({$user_to_delete['email']})");
            redirect('index.php?module=users&action=index&status_message=' . urlencode('Kullanıcı başarıyla silindi ve arşivlendi.'));

        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("User Archive/Delete Error: " . $e->getMessage());
            redirect('index.php?module=users&action=index&error_message=' . urlencode('Kullanıcı silinirken bir veritabanı hatası oluştu: ' . $e->getMessage()));
        }
        exit;
    }
}