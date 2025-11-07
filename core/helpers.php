<?php

// Güvenli HTML çıkışı için kısayol fonksiyonu
if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// HTTP yönlendirme fonksiyonu
if (!function_exists('redirect')) {
    function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }
}

/**
 * Aktivite loguna kayıt ekler.
 * @param string $action CREATE, UPDATE, DELETE, LOGIN, LOGOUT, ACCESS_DENIED vb.
 * @param string $module İşlemin yapıldığı modül (örn: Users, Classes, Activities)
 * @param int|null $record_id Etkilenen kaydın ID'si (varsa)
 * @param string $description İşlemin açıklaması
 * @return bool Başarılı olursa true.
 */
if (!function_exists('log_activity')) {
    function log_activity(string $action, string $module, ?int $record_id, string $description): bool
    {
        // log_activity fonksiyonu içinde Database::getInstance() çağrısı, döngüsel bağımlılık yaratabilir
        // veya Database bağlantısı henüz kurulmadan çağrılırsa sorun yaratabilir.
        // Daha güvenli bir yöntem: log_activity'e doğrudan bir Database nesnesi geçirmek veya
        // sadece loglamak için ayrı bir mekanizma kullanmak.
        // Şimdilik mevcut Database::getInstance() yapısını kullanmaya devam edelim,
        // ancak gerçek bir uygulamada bu iyileştirilebilir.
        try {
            $db_instance = Database::getInstance();
            $pdo = $db_instance->getConnection(); // PDO objesini al

            $user_id = $_SESSION['user']['id'] ?? null;
            $user_name = $_SESSION['user']['name'] ?? 'System';

            $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, user_name, action, module, record_id, description) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$user_id, $user_name, $action, $module, $record_id, $description]);
        } catch (PDOException $e) {
            // Hata loglama sistemine yaz (dosyaya, Sentry'ye vb.)
            error_log("Failed to log activity: " . $e->getMessage());
            return false;
        }
    }
}
// helpers.php

if (!function_exists('menu_roles_for_module')) {
    /**
     * Menülerde tanımlı rolleri modül adına göre getirir.
     * Örn: $module = 'course_groups'
     * Not: m.url içinde ?module=$module geçen satırları eşler.
     */
    function menu_roles_for_module(Database $db, string $module): array {
        $like = "%module={$module}%";
        $rows = $db->select("
            SELECT DISTINCT mr.role
            FROM menus m
            JOIN menu_roles mr ON mr.menu_id = m.id
            WHERE m.is_active = 1
              AND (m.url LIKE ?)
        ", [$like]) ?? [];

        return array_values(array_unique(array_map(
            fn($r) => (string)$r['role'],
            $rows
        )));
    }
}

if (!function_exists('module_allowed_for_role')) {
    /**
     * Menüde bu modül için bir rol tanımı varsa ona uyar.
     * Hiç tanım yoksa (menu_roles boşsa) engelleme yapmaz.
     * Admin her durumda serbest bırakılır.
     */
    function module_allowed_for_role(Database $db, string $module, string $role): bool {
        if ($role === 'admin') return true; // süper yetki
        $roles = menu_roles_for_module($db, $module);
        if (!$roles) return true;           // menüde rol tanımı yoksa kilitleme yapma
        return in_array($role, $roles, true);
    }
}

/**
 * Parola güvenlik kurallarını doğrular.
 *
 * @param string $password Doğrulanacak parola.
 * @param string|null $email Kullanıcının e-postası (parolada geçmemesi için).
 * @param string|null $username Kullanıcının adı (parolada geçmemesi için).
 * @return array Hata mesajları dizisi. Eğer hata yoksa boş dizi.
 */
if (!function_exists('validate_password')) {
    function validate_password(string $password, ?string $email = null, ?string $username = null): array
    {
        $errors = [];

        // 1. En az 8 karakter uzunluğunda olmalı
        if (strlen($password) < 8) {
            $errors[] = "Parola en az 8 karakter uzunluğunda olmalıdır.";
        }

        // 2. En az 1 büyük harf içermeli
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Parola en az bir büyük harf içermelidir.";
        }

        // 3. En az 1 küçük harf içermeli
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Parola en az bir küçük harf içermelidir.";
        }

        // 4. En az 1 rakam içermeli
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Parola en az bir rakam içermelidir.";
        }

        // 5. Sıralı rakamlar (örn. 123, 789, 321) içermemeli
        if (preg_match('/(?:012|123|234|345|456|567|678|789|890|987|876|765|654|543|432|321|210)/', $password)) {
            $errors[] = "Parola sıralı rakamlar içermemelidir (örn. 123, 321).";
        }
        
        // 6. Tekrar eden karakterler (örn. aaa, 111) 3 kereden fazla olmamalı
        if (preg_match('/(.)\1{2,}/', $password)) {
            $errors[] = "Parola ardışık aynı karakterleri 3 veya daha fazla tekrar içermemelidir (örn. aaa, 111).";
        }

        // 7. Kullanıcı adı veya e-posta gibi kişisel bilgiler içermemeli
        if ($email) {
            $email_parts = explode('@', $email);
            $email_local_part = strtolower($email_parts[0]);
            if (str_contains(strtolower($password), $email_local_part)) {
                $errors[] = "Parola, e-posta adresinizin bir kısmını içermemelidir.";
            }
        }
        if ($username) {
            if (str_contains(strtolower($password), strtolower($username))) {
                $errors[] = "Parola, kullanıcı adınızı içermemelidir.";
            }
        }

        return $errors;
    }
}

/**
 * Güvenli, rastgele bir token oluşturur.
 * @param int $length Token uzunluğu (bayt cinsinden, uzunluk 2 katı karakter döndürür)
 * @return string
 */
if (!function_exists('generate_token')) {
    function generate_token(int $length = 32): string // 32 bayt = 64 karakter hex string
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (Exception $e) {
            // Güvenli rastgelelik mümkün değilse, daha az güvenli bir fallback (üretim için önerilmez)
            error_log("Secure token generation failed: " . $e->getMessage());
            return md5(uniqid(mt_rand(), true)); // Daha az güvenli fallback
        }
    }
}

/**
 * Basit bir e-posta gönderme fonksiyonu. PHPMailer gibi bir kütüphane daha güvenli ve özelliklidir.
 * PHP mail() fonksiyonu sunucu yapılandırmasına bağlıdır ve genellikle SMTP ayarı gerektirir.
 *
 * @param string $to Alıcı e-posta adresi
 * @param string $subject E-posta konusu
 * @param string $message E-posta içeriği (HTML veya düz metin)
 * @param array $headers Ek başlıklar (örn: From, Reply-To, Content-type)
 * @return bool E-posta başarıyla gönderilirse true, aksi halde false.
 */
if (!function_exists('send_email')) {
    function send_email(string $to, string $subject, string $message, array $extra_headers = []): bool
    {
        // Temel başlıklar
        $headers = [
            'From' => 'no-reply@yoursystem.com',
            'Reply-To' => 'no-reply@yoursystem.com',
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/plain; charset=UTF-8' // Varsayılan olarak düz metin
        ];

        // Ek başlıkları birleştir
        foreach ($extra_headers as $key => $value) {
            $headers[$key] = $value;
        }

        // Başlıkları string formatına dönüştür
        $header_string = '';
        foreach ($headers as $key => $value) {
            $header_string .= "$key: $value\r\n";
        }
        
        // Bu kısım sunucunuzun mail() fonksiyonu desteğine bağlıdır.
        // Genellikle production ortamında SMTP tabanlı bir çözüm (PHPMailer) tercih edilir.
        return mail($to, $subject, $message, $header_string);
    }
}


// Projenizin temel URL'sini tanımlayın. Bu, config.php gibi bir yerde de olabilir.
// Şifre sıfırlama linki için gereklidir.
if (!defined('BASE_URL')) {
    // Örnek: 'http://localhost/edu_management' veya 'https://yourdomain.com'
    // Sunucunuzdaki projenizin kök dizin URL'si olmalı
    define('BASE_URL', 'http://localhost/edu_management'); 
}

// Google Takvim linki helper (Activities modülü için)
if (!function_exists('generateGoogleCalendarLink')) {
    function generateGoogleCalendarLink($activity) {
        $title = urlencode($activity['title'] ?? 'Etkinlik');
        $description = urlencode($activity['description'] ?? '');
        $location = urlencode($activity['location'] ?? '');
        
        $start_datetime = new DateTime($activity['activity_date']);
        $dates = $start_datetime->format('Ymd\THis'); 

        $link = "https://calendar.google.com/calendar/render?action=TEMPLATE" .
                "&text={$title}" .
                "&dates={$dates}/{$dates}" . 
                "&details={$description}" .
                "&location={$location}";
        
        return $link;
    }
}
function getMenuTree(PDO $pdo, ?int $roleId = null): array {
    // 1) Root (menus) — role göre filtreli
    $params = [];
    $roleJoin = '';
    if ($roleId !== null) {
        $roleJoin = "INNER JOIN menu_roles mr ON mr.menu_id = m.id AND mr.role = :roleName";
        // Sizde role id mi role adı mı var? menu_roles.role VARCHAR(50) olduğundan
        // roleId -> roleName haritasını kendinize göre uyarlayın.
        // Örn: 1=admin,2=teacher,3=student,4=parent ise:
        $map = [1=>'admin',2=>'teacher',3=>'student',4=>'parent'];
        $params[':roleName'] = $map[$roleId] ?? 'admin';
    }

    $sqlMenus = "
        SELECT m.id, m.parent_id, m.title, m.url, m.icon, m.display_order, m.is_active
        FROM menus m
        $roleJoin
        WHERE m.is_active = 1
        ORDER BY m.parent_id, m.display_order, m.id
    ";
    $st = $pdo->prepare($sqlMenus);
    $st->execute($params);
    $menus = $st->fetchAll(PDO::FETCH_ASSOC);

    // 2) menu_items — tümünü al (alt kırılımlar için)
    $sqlItems = "
        SELECT id, parent_id, title, icon, module, action, params, `sort`, visible_roles
        FROM menu_items
        ORDER BY COALESCE(parent_id,0), `sort`, id
    ";
    $items = $pdo->query($sqlItems)->fetchAll(PDO::FETCH_ASSOC);

    // visible_roles= '1,2' vb. format → roleId ile uyum
    $roleCheck = function($visible_roles) use ($roleId): bool {
        if ($roleId === null || $visible_roles === null || $visible_roles === '') return true;
        $list = array_filter(array_map('trim', explode(',', $visible_roles)));
        return in_array((string)$roleId, $list, true);
    };

    // items’ı parent->children map
    $itemsByParent = [];
    foreach ($items as $it) {
        if (!$roleCheck($it['visible_roles'])) continue;
        $pid = $it['parent_id'] ?? 0;
        $itemsByParent[$pid][] = $it;
    }

    // recursive ile item ağacı kur
    $buildItems = function($parentId) use (&$buildItems, &$itemsByParent) {
        $out = [];
        if (!empty($itemsByParent[$parentId])) {
            foreach ($itemsByParent[$parentId] as $row) {
                $row['children'] = $buildItems($row['id']);
                $out[] = $row;
            }
        }
        return $out;
    };

    // Root menüler ağacı (menus.parent_id hiyerarşisi)
    $menusByParent = [];
    foreach ($menus as $m) {
        $pid = (int)$m['parent_id'];
        $menusByParent[$pid][] = $m;
    }
    $buildMenus = function($parentId) use (&$buildMenus, &$menusByParent, $buildItems) {
        $out = [];
        if (!empty($menusByParent[$parentId])) {
            foreach ($menusByParent[$parentId] as $root) {
                // Bu root’un altına menu_items köklerini (parent_id=NULL) da ekleyebiliriz,
                // ama genelde menus root → başlık; altında item’lar durur.
                // İsterseniz menus.id == X iken item parent’larını X ile bağlayacak bir kolon ekleyip
                // yalnızca o root’a ait item’ları gösterebilirsiniz. Şimdilik tüm item ağacını tek ağaç olarak bırakıyoruz.
                $root['items'] = $buildItems(0); // tüm kök item’lar
                $out[] = $root;
            }
        }
        return $out;
    };

    // 0 parent’lı (kök) tüm root menüler
    $tree = $buildMenus(0);
    return $tree;
}

/** Modül/aksiyondan URL üret */
function menuItemUrl(array $n): string {
    if (!empty($n['module']) || !empty($n['action'])) {
        $url = 'index.php?';
        $q = [];
        if (!empty($n['module'])) $q[] = 'module=' . urlencode($n['module']);
        if (!empty($n['action'])) $q[] = 'action=' . urlencode($n['action']);
        if (!empty($n['params'])) {
            // params zaten 'a=1&b=2' gibi ise doğrudan ekle
            $url .= implode('&', $q) . '&' . ltrim($n['params'], '&');
        } else {
            $url .= implode('&', $q);
        }
        return $url;
    }
    // doğrudan absolute/relative URL gelmişse
    if (!empty($n['url'])) return $n['url'];
    return '#';
}