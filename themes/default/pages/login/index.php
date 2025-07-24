<?php
// Bu sayfa genellikle ana layout.php'yi KULLANMAZ.
// Kendi başına tam bir HTML sayfasıdır.
// Gerekli CSS ve JS dosyalarını doğrudan kendi <head> ve <body> etiketleri içinde barındırır.
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - EduManagement Sistemi</title>
    
    <!-- Gerekli Stiller -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        /* Sayfanın arka plan rengi gibi küçük özelleştirmeler için */
        body.login-page {
            background-color: #f4f7f6;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="index.php"><b>Edu</b>Management</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Oturum açmak için giriş yapın</p>

        <!-- Hata ve Durum Mesajları -->
        <?php if (isset($_GET['error_message'])): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars_decode($_GET['error_message']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['status_message'])): ?>
            <div class="alert alert-success text-center">
                <?= htmlspecialchars_decode($_GET['status_message']) ?>
            </div>
        <?php endif; ?>

      <form action="index.php?module=login&action=login" method="post">
        <div class="input-group mb-3">
          <input type="email" class="form-control" name="email" placeholder="E-posta Adresi" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fa fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password" placeholder="Şifre" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fa fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
          </div>
        </div>
      </form>

      <p class="mb-1 mt-3">
        <a href="index.php?module=login&action=forgot_password">Şifremi unuttum</a>
      </p>
      <?php if (defined('ENABLE_REGISTRATION') && ENABLE_REGISTRATION): ?>
      <p class="mb-0">
        <a href="index.php?module=register&action=index" class="text-center">Yeni bir üyelik oluştur</a>
      </p>
      <?php endif; ?>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- Gerekli Scriptler -->
<script src="themes/default/assets/js/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
