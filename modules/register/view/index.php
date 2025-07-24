<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Üyelik - EduManagement</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
</head>
<body class="hold-transition register-page">
<div class="register-box">
  <div class="register-logo">
    <a href="index.php"><b>Edu</b>Management</a>
  </div>
  <div class="card">
    <div class="card-body register-card-body">
      <p class="login-box-msg">Yeni bir üyelik başlatın</p>
      
      <?php if (isset($_GET['error_message'])): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error_message']) ?></div>
      <?php endif; ?>

      <form action="index.php?module=register&action=store" method="post">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="name" placeholder="Ad Soyad" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="email" class="form-control" name="email" placeholder="E-posta" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
        </div>
        
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="tc_kimlik" placeholder="TC Kimlik Numarası" required pattern="\d{11}" title="Lütfen 11 haneli TC Kimlik Numaranızı girin.">
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-id-card"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password" placeholder="Şifre" required>
           <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password_confirm" placeholder="Şifre (Tekrar)" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Kayıt Ol</button>
          </div>
        </div>
      </form>
      <a href="index.php?module=login&action=index" class="text-center mt-3 d-block">Zaten bir üyeliğim var</a>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>