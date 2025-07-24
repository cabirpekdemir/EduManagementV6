<?php
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/helpers.php';

class ClassesController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        $stmt = $this->db->query("
            SELECT c.*, u.name as advisor_name
            FROM classes c
            LEFT JOIN users u ON c.advisor_teacher_id = u.id
            ORDER BY c.name ASC
        ");
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['pageTitle' => 'Sınıflar', 'classes' => $classes];
    }

    public function create() {
        $stmt = $this->db->query("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name ASC");
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['pageTitle' => 'Yeni Sınıf Ekle', 'teachers' => $teachers];
    }

    public function store() {
        $sql = "INSERT INTO classes (name, description, advisor_teacher_id) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        // Eğer advisor_teacher_id boş gelirse, null olarak kaydet.
        // Bu, veritabanı sütununun NULL değer kabul ettiğini varsayar.
        $advisor_id = !empty($_POST['advisor_teacher_id']) ? $_POST['advisor_teacher_id'] : null;
        $stmt->execute([$_POST['name'], $_POST['description'], $advisor_id]);
        redirect('?module=classes&action=index');
        exit;
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('?module=classes&action=index');
            exit; // Yönlendirmeden sonra script'in çalışmasını durdur.
        }

        $stmt_class = $this->db->prepare("SELECT * FROM classes WHERE id = ?");
        $stmt_class->execute([$id]);
        $class = $stmt_class->fetch(PDO::FETCH_ASSOC);

        // Eğer sınıf bulunamazsa ana sayfaya yönlendir
        if (!$class) {
            redirect('?module=classes&action=index');
            exit;
        }

        $stmt_teachers = $this->db->query("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name ASC");
        $teachers = $stmt_teachers->fetchAll(PDO::FETCH_ASSOC);

        return ['pageTitle' => 'Sınıfı Düzenle', 'class' => $class, 'teachers' => $teachers];
    }

    // --- GÜNCELLENMİŞ UPDATE METODU ---
    public function update() {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        // Eğer advisor_teacher_id boş gelirse (yani formdan seçilmezse), null olarak ayarla.
        // Bu, veritabanı sütununun NULL değer kabul ettiğini varsayar.
        $advisor_id = !empty($_POST['advisor_teacher_id']) ? $_POST['advisor_teacher_id'] : null;

        // ID'nin varlığını kontrol et
        if (!$id) {
            // Hata mesajı ile ana sayfaya yönlendirilebilir veya bir hata ekranı gösterilebilir
            redirect('?module=classes&action=index&error=no_id_provided');
            exit;
        }

        // Temel alanların boş olup olmadığını kontrol edebilirsiniz, isteğe bağlı
        if (empty($name) || empty($description)) {
            // Hata mesajı ile geri yönlendirilebilir
            redirect('?module=classes&action=edit&id=' . $id . '&error=missing_fields');
            exit;
        }

        try {
            $sql = "UPDATE classes SET name = ?, description = ?, advisor_teacher_id = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);

            $params = [
                $name,
                $description,
                $advisor_id,
                $id
            ];

            $result = $stmt->execute($params);

            if ($result) {
                // Başarılı olursa sınıflar listesine yönlendir
                redirect('?module=classes&action=index&success=class_updated');
            } else {
                // Eğer execute false dönerse (genellikle PDO hatası olmadığında ama bir sorun olduğunda)
                // Daha detaylı hata mesajı için errorInfo kullanılabilir.
                $errorInfo = $stmt->errorInfo();
                error_log("Class update failed: " . implode(" - ", $errorInfo)); // Hata loguna yaz
                redirect('?module=classes&action=index&error=update_failed&msg=' . urlencode($errorInfo[2] ?? 'Bilinmeyen Hata'));
            }
        } catch (PDOException $e) {
            // Veritabanı bağlantısı veya SQL sorgusu sırasında bir hata oluşursa
            error_log("PDOException in Class update: " . $e->getMessage()); // Hata loguna yaz
            redirect('?module=classes&action=index&error=db_error&msg=' . urlencode($e->getMessage()));
        }
        exit; // Yönlendirmeden sonra script'in çalışmasını durdur.
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->execute([$id]);
        }
        redirect('?module=classes&action=index');
        exit;
    }
}