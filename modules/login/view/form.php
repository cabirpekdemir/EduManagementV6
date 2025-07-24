<form method="post" action="index.php?module=dashboard&action=index">
    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-group">
        <label>Kullanıcı Adı</label>
        <input type="text" name="username" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Şifre</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Giriş Yap</button>
</form>
<p><a href="index.php?module=login&action=forgotPassword">Şifremi Unuttum</a></p>
