<?php
// --- Ortak sayfa durumları ---
$module     = $_GET['module'] ?? '';
$action     = $_GET['action'] ?? '';
$isGuest    = empty($_SESSION['user']['id']);
$isAuthPage = in_array($module, ['login', 'password_resets', 'register']);

$showSidebar = (!$isGuest && !$isAuthPage);
$bodyClass = 'hold-transition sidebar-mini' . ($showSidebar ? '' : ' no-sidebar');
if ($isAuthPage) $bodyClass .= ' auth-page';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduManagement Sistemi <?= isset($pageTitle) ? '- ' . htmlspecialchars($pageTitle) : '' ?></title>

    <!-- FAVICON -->
    <link rel="icon" href="themes/default/assets/favicon.png" type="image/x-icon">
    
    <!-- FONT & İKON KÜTÜPHANELERİ -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- TEMEL KÜTÜPHANELER -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="themes/default/assets/css/main_responsive.css">
    
    <!-- ✨ MODERN STIL - YENİ! -->
    <link rel="stylesheet" href="themes/default/assets/css/modern.css">

    <style>
        /* Login sayfası merkezleme */
        .auth-page .content-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 0 !important;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-page .content-wrapper .container-fluid {
            max-width: 450px;
            width: 100%;
        }

        /* Sayfa geçiş animasyonu */
        body.page-transition {
            opacity: 0;
            transform: scale(0.98);
            transition: all 0.3s ease-out;
        }

        body.loaded {
            opacity: 1;
            transform: scale(1);
        }

        /* Sidebar overlay mobil için */
        @media (max-width: 768px) {
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
            }
            
            body.sidebar-open .sidebar-overlay {
                display: block;
            }
        }
    </style>

    <?= $extraHead ?? '' ?>
</head>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS (eğer kullanıyorsanız) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Sizin modern.js dosyanız -->
<script src="themes/default/assets/js/modern.js"></script>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <!-- Flash Mesajlar -->
<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show">
        <?= $_SESSION['flash']['msg'] ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['flash_success'] ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['flash_error'] ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<!-- ⭐ VIEW AS MODE BANNER -->
<?php
require_once __DIR__ . '/../../core/view_as_helper.php';
if (ViewAsHelper::isViewAsMode()): 
?>
<div class="view-as-banner">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center py-2">
            <div>
                <i class="fa fa-eye"></i>
                <strong>DİKKAT:</strong> 
                Şu an <strong><?= htmlspecialchars(ViewAsHelper::getViewAsUserName()) ?></strong> olarak görüntülüyorsunuz.
                <small class="ms-2">
                    (Admin: <?= htmlspecialchars(ViewAsHelper::getOriginalAdminName()) ?>)
                </small>
            </div>
            <div>
                <form method="POST" action="index.php?module=view_as&action=exit" style="display: inline;">
    <button type="submit" class="btn btn-sm btn-light">
        <i class="fa fa-sign-out"></i> Normal Görünüme Dön
    </button>
</form>
            </div>
        </div>
    </div>
</div>
<style>
.view-as-banner {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}
.view-as-banner .btn-light {
    background: white;
    color: #ff6b6b;
    border: none;
    font-weight: 600;
}
.view-as-banner .btn-light:hover {
    background: #f8f9fa;
}
body.view-as-active {
    padding-top: 50px;
}
</style>
<script>
if (document.querySelector('.view-as-banner')) {
    document.body.classList.add('view-as-active');
}
</script>
<?php endif; ?>

<!-- Loading Screen -->
<div id="loadingScreen" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    transition: opacity 0.5s ease, visibility 0.5s ease;
">
    <div style="text-align: center; color: white;">
        <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Yükleniyor...</span>
        </div>
        <h4 style="margin-top: 1rem;">EduManagement</h4>
        <p>Sistem yükleniyor...</p>
    </div>
</div>

<script>
// Sayfa yüklenince loading screen'i kaldır
window.addEventListener('load', function() {
    setTimeout(function() {
        const loadingScreen = document.getElementById('loadingScreen');
        loadingScreen.style.opacity = '0';
        loadingScreen.style.visibility = 'hidden';
        setTimeout(function() {
            loadingScreen.remove();
        }, 500);
    }, 500);
});
</script>
<!-- Sidebar Overlay (Mobil) -->
<div class="sidebar-overlay" onclick="$('body').removeClass('sidebar-open sidebar-collapse')"></div>

<div class="wrapper">

    <!-- ============================================
         NAVBAR - MODERN HEADER
         ============================================ -->
    <?php if (!$isAuthPage): ?>
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <?php if ($showSidebar): ?>
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fa fa-bars"></i>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item d-none d-sm-inline-block">
                <h1 class="current-page-title">
                    <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Kontrol Paneli' ?>
                </h1>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <div class="header-right">
                    <?php if (!$isGuest): ?>
                        <!-- Bildirimler -->
                        <div class="notifications-icon" id="notificationsBell" title="Bildirimler">
                            🔔 <span class="notification-count" style="display: none;">0</span>
                        </div>
                        
                        <!-- Kullanıcı Profil -->
                        <div class="user-profile-menu">
                            <a href="index.php?module=profile&action=index" class="user-name">
                                <i class="fa fa-user-circle"></i>
                                <span><?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></span>
                                <?php if (!empty($_SESSION['user']['role'])): ?>
                                    <small>(<?= htmlspecialchars($_SESSION['user']['role']) ?>)</small>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Çıkış Butonu -->
                            <a href="index.php?module=login&action=logout" class="logout-button">
                                <i class="fa fa-sign-out"></i> Çıkış
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="index.php?module=login&action=index" class="btn btn-primary btn-sm">
                            <i class="fa fa-sign-in"></i> Giriş Yap
                        </a>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- ============================================
         SIDEBAR - MODERN MENU
         ============================================ -->
    <?php if ($showSidebar): ?>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Logo -->
        <a href="index.php" class="brand-link">
            <img src="themes/default/logo.png" alt="EduSystem Logo" 
                 class="brand-image img-circle elevation-3">
            <span class="brand-text font-weight-light">EduManagement</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Kullanıcı Paneli -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="<?= $_SESSION['user']['profile_photo'] ?? 'themes/default/assets/user-default.png' ?>" 
                         class="img-circle elevation-2" alt="User">
                </div>
                <div class="info">
                    <a href="index.php?module=profile&action=index" class="d-block">
                        <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Kullanıcı') ?>
                    </a>
                </div>
            </div>

            <!-- Menu -->
            <nav class="mt-2">
                <?php 
                $menuPath = __DIR__ . '/sol_menu.php';
                if (file_exists($menuPath)) {
                    include $menuPath;
                } else {
                    echo '<ul class="nav nav-pills nav-sidebar flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    <p>Menü dosyası bulunamadı!</p>
                                </a>
                            </li>
                          </ul>';
                }
                ?>
            </nav>
        </div>
    </aside>
    <?php endif; ?>

    <!-- ============================================
         CONTENT WRAPPER - ANA İÇERİK
         ============================================ -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid pt-3">
                <?= $pageContent ?? '<p class="text-center text-muted">İçerik yüklenemedi.</p>' ?>
            </div>
        </section>
    </div>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            <strong>Versiyon</strong> 2.0
        </div>
        <strong>&copy; <?= date('Y') ?> <a href="#">EduManagement Sistemi</a>.</strong>
        Tüm hakları saklıdır.
    </footer>

</div>

<!-- ============================================
     JAVASCRIPT KÜTÜPHANELERİ
     ============================================ -->

<!-- jQuery -->
<script src="themes/default/assets/js/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- ✨ MODERN JavaScript - YENİ! -->
<script src="themes/default/assets/js/modern.js"></script>

<!-- Sayfa Özel Scriptler -->
<?= $extraFoot ?? '' ?>

<!-- jQuery fallback -->
<script>
if (!window.jQuery) {
    document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
}
</script>

<!-- Sayfa yükleme animasyonu -->
<script>
$(window).on('load', function() {
    $('body').addClass('loaded');
});
</body>
</html>