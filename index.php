<?php
// Geliştirme aşamasında tüm hataları görmek için:
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. OTURUMU VE TEMEL AYARLARI BAŞLAT
// Oturum başlatma, projenin en başında, herhangi bir HTML çıktısından önce yapılmalıdır.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. TEMEL ÇEKİRDEK DOSYALARI YÜKLE
// Bu dosyalar, veritabanı bağlantısı ve yardımcı fonksiyonlar gibi temel işlevleri içerir.
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/helpers.php';

// 3. GÜVENLİ ROTA (ROUTE) BELİRLEME
// URL'den gelen 'module' ve 'action' parametrelerini al ve güvenli hale getir.
// Sadece harf, rakam ve alt çizgiye izin vererek olası güvenlik açıklarını önle.
$module = 'dashboard'; // Varsayılan modül
if (isset($_GET['module']) && preg_match('/^[a-zA-Z0-9_]+$/', $_GET['module'])) {
    $module = $_GET['module'];
}

$action = 'index'; // Varsayılan eylem
if (isset($_GET['action']) && preg_match('/^[a-zA-Z0-9_]+$/', $_GET['action'])) {
    $action = $_GET['action'];
}

// Giriş yapmamış kullanıcıları login sayfasına yönlendir (login ve register modülleri hariç).
if (!isset($_SESSION['user']) && !in_array($module, ['login', 'register'])) {
    redirect('index.php?module=login&action=index');
    exit;
}

// Dashboard modülü
if ($module === 'dashboard') {
    
    // Session kontrolü - SİZİN SİSTEMİNİZE UYGUN
    if (!isset($_SESSION['user'])) {
        redirect('index.php?module=login&action=index');
        exit;
    }
    
    // Controller'ı yükle
    require_once __DIR__ . '/modules/dashboard/dashboardcontroller.php';
    
    // Controller oluştur
    $controller = new DashboardController();
    
    // Action'ı çalıştır
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
    
    exit;
}

// 4. KONTROLCÜ (CONTROLLER) DOSYASINI VE SINIFINI BUL
// ⭐ YENİ: view_as gibi özel modüller için farklı isimlendirme desteği
$controllerName = '';
$controllerFile = '';

// Özel modül kontrolü (alt çizgi içeren modüller)
if ($module === 'view_as') {
    // view_as modülü için özel tanımlama
    $controllerName = 'View_asController';
    $controllerFile = __DIR__ . "/modules/view_as/view_ascontroller.php";
} elseif ($module === 'lesson_attendance') {
    // lesson_attendance modülü için özel tanımlama (varsa)
    $controllerName = 'Lesson_attendanceController';
    $controllerFile = __DIR__ . "/modules/lesson_attendance/lesson_attendancecontroller.php";
} else {
    // Genel kural: ModülAdıController (örn: ActivitiesController)
    $controllerName = ucfirst($module) . 'Controller';
    $controllerFile = __DIR__ . "/modules/{$module}/{$module}controller.php";
}

// 5. KONTROLCÜYÜ ÇALIŞTIR VE VIEW İÇİN VERİYİ HAZIRLA
if (file_exists($controllerFile)) {
    require_once $controllerFile;

    if (class_exists($controllerName)) {
        $controller = new $controllerName();

        if (method_exists($controller, $action)) {
            
            // Controller metodunu çalıştırıp view için gerekli veriyi al.
            // Bu $data dizisi, view içinde kullanılacak değişkenleri içerir (örn: $pageTitle, $users vb.).
            $data = $controller->{$action}();
            
            // Controller'dan gelen veriyi değişkenlere ayır.
            if (is_array($data)) {
                 extract($data);
            }

            // Gelişmiş Layout Kontrolü:
            // Eğer controller'dan gelen data içinde 'use_layout' => false varsa,
            // ana şablonu (layout.php) YÜKLEME. Bu, login gibi tam sayfa modüller için kullanılır.
            $useLayout = $data['use_layout'] ?? true;

            if ($useLayout) {
                // Standart sayfa akışı (Ana şablonu kullan)

                // Sayfa içeriğini bir değişkene kaydetmek için output buffer'ı başlat.
                ob_start();
                
                // View dosyasının yolu. NOT: Kendi yapınıza göre `themes/default/pages` klasörünü kullanıyorsunuz.
                $viewFile = __DIR__ . "/modules/{$module}/view/{$action}.php";
                if (file_exists($viewFile)) {
                    include $viewFile;
                } else {
                    echo "<div class='alert alert-danger'>Hata: '$module/$action' için view dosyası bulunamadı: <code>$viewFile</code></div>";
                }
                
                $pageContent = ob_get_clean(); // Buffer'daki içeriği al ve buffer'ı temizle.
                
                // ANA ŞABLONU (LAYOUT) YÜKLE VE SAYFAYI GÖSTER
                // $pageContent, $pageTitle gibi değişkenler artık layout.php içinde kullanılabilir.
                require_once __DIR__ . '/themes/default/layout.php';

            } else {
                // Layout'suz sayfa akışı (Sadece view dosyasını yükle)
                $viewFile = __DIR__ . "/modules/{$module}/view/{$action}.php";
                if (file_exists($viewFile)) {
                    include $viewFile;
                } else {
                     echo "<div class='alert alert-danger'>Hata: '$module/$action' için view dosyası bulunamadı: <code>$viewFile</code></div>";
                }
            }

        } else {
            die("Hata: '$controllerName' kontrolcüsü içinde '$action' metodu bulunamadı.");
        }
    } else {
        die("Hata: '$controllerName' sınıfı bulunamadı. Kontrolcü dosyasının adı ve içindeki sınıf adı doğru mu?");
    }
} else {
    // 404 - Sayfa Bulunamadı
    http_response_code(404);
    die("Hata: İstenen modül ('$module') bulunamadı. Dosya yolu: $controllerFile");
}