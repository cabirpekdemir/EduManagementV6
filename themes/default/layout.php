<?php
// Gerekli değişkenler ($pageTitle, $pageContent vb.) ana index.php tarafından sağlanmalıdır.
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- TEMEL KÜTÜPHANE VE ÖZEL STİL DOSYALARI -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="themes/default/assets/css/main_responsive.css">

    <!-- SAYFAYA ÖZEL EKLENECEK HEAD ETİKETLERİ -->
    <?= $extraHead ?? '' ?>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar (Üst Başlık) -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Sol Navbar Linkleri -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                 <h1 class="current-page-title"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Kontrol Paneli' ?></h1>
            </li>
        </ul>

        <!-- Sağ Navbar Linkleri -->
        <ul class="navbar-nav ml-auto">
             <li class="nav-item">
                <div class="header-right">
                    <div class="notifications-icon" id="notificationsBell" title="Bildirimler">
                        🔔 
                        <span class="notification-count">0</span> 
                    </div>
                    <div class="user-profile-menu">
                        <?php if (isset($_SESSION['user'])): ?>
                            <a href="index.php?module=profile&action=index" class="user-name">
                                <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span> 
                                (<span><?= htmlspecialchars($_SESSION['user']['role']) ?></span>)
                            </a>
                            <a href="index.php?module=login&action=logout" class="logout-button">Çıkış Yap</a>
                        <?php else: ?>
                            <a href="index.php?module=login&action=index">Giriş Yap</a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Ana Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Marka Logosu (Düzeltilmiş Hali) -->
        <a href="index.php" class="brand-link">
             <img src="themes/default/logo.png" alt="EduSystem Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
             <span class="brand-text font-weight-light">EduManagement</span>
        </a>
        <!-- Sidebar -->
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <?php 
                        if (file_exists(__DIR__ . '/sol_menu.php')) {
                            include __DIR__ . '/sol_menu.php';
                        } else {
                            echo '<li class="nav-item"><a href="#" class="nav-link"><p>HATA: Menü dosyası bulunamadı!</p></a></li>';
                        }
                    ?>
                </ul>
            </nav>
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Sayfa içeriğini barındırır -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid pt-3">
                 <?= $pageContent ?? '<p>İçerik yüklenemedi.</p>' ?>
            </div>
        </section>
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>&copy; <?= date('Y') ?> <a href="#">EduManagement Sistemi</a>.</strong>
        Tüm hakları saklıdır.
    </footer>

</div>
<!-- ./wrapper -->

<!-- GEREKLİ JAVASCRIPT DOSYALARI (Sayfa sonunda yüklenmeli) -->
<script src="themes/default/assets/js/jquery-3.7.1.min.js"></script>
<!-- AdminLTE 3, Bootstrap 4 gerektirir. Bootstrap 5 ile çakışabilir. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- SAYFAYA ÖZEL EKLENECEK FOOTER ETİKETLERİ -->
<?= $extraFoot ?? '' ?>

</body>
</html>
