<h2>Şifremi Unuttum</h2>
<p>Lütfen kayıtlı e-posta adresinizi girin. Şifrenizi sıfırlamak için size bir bağlantı göndereceğiz.</p>

<?php if (isset($_GET['error_message'])): ?>
    <p style="color:red; border: 1px solid red; padding: 10px; margin-top: 15px;">
        <?= htmlspecialchars_decode($_GET['error_message']) ?>
    </p>
<?php endif; ?>
<?php if (isset($_GET['status_message'])): ?>
    <p style="color:green; border: 1px solid green; padding: 10px; margin-top: 15px;">
        <?= htmlspecialchars_decode($_GET['status_message']) ?>
    </p>
<?php endif; ?>

<form method="POST" action="index.php?module=login&action=send_reset_link">
    <label for="email">E-posta Adresiniz:</label><br>
    <input type="email" id="email" name="email" required><br><br>
    <button type="submit">Şifre Sıfırlama Bağlantısı Gönder</button>
</form>
<p style="margin-top:20px;"><a href="index.php?module=login&action=index">Giriş sayfasına geri dön</a></p>