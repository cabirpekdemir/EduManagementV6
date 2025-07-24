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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        /* Sayfanın arka plan rengi gibi küçük özelleştirmeler için */
        body.login-page {
            background-color: #fffff;
            text-align: center;
            align:center;
        }
        div.login-box {
            align: center;
        }
        
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
        <div class="logo">
            <img src="themes/default/logo.png" alt="Logo" width="240px">
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Oturum açmak için giriş yapın</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center"><?= $error ?></div>
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
  <input type="password" id="password" name="password" class="form-control" placeholder="Şifre">
  <div class="input-group-append">
    <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
      <i id="toggleEye" class="fas fa-eye"></i>
    </span><div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
          </div>
        </div>
  </div>
</div>

      </form>
                <p class="mt-3 mb-1 text-center">
                    <a href="#">Şifremi unuttum</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('toggleEye');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        }
    </script>
    
  <!-- /.login-logo -->
 
<!-- /.login-box -->

<!-- Gerekli Scriptler -->
<script src="themes/default/assets/js/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
