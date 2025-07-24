<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';

// Tüm modül controller dosyalarını dahil et
foreach (glob(__DIR__ . '/../modules/*/*controller.php') as $file) {
    require_once $file;
}

// Modül ve aksiyon yakalama
$module = $_GET['module'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Controller sınıf adını oluştur (ilk harf büyük + Controller son eki)
$controllerName = ucfirst(strtolower($module)) . 'Controller';

if (class_exists($controllerName)) {
    $controller = new $controllerName();
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        echo "🔴 İşlem bulunamadı: <strong>$action</strong><br>❌ Hata: '$controllerName' içinde '$action' metodu bulunamadı.";
    }
} else {
    echo "🔴 Modül bulunamadı: <strong>$module</strong><br>❌ Hata: '$controllerName' sınıfı bulunamadı.";
}
