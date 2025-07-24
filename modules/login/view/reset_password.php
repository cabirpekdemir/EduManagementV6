<h2>Yeni Parola Belirle</h2>
<p>Lütfen yeni parolanızı girin.</p>

<?php if (isset($_GET['error_message'])): ?>
    <p style="color:red; border: 1px solid red; padding: 10px; margin-top: 15px;">
        <?= htmlspecialchars_decode($_GET['error_message']) ?>
    </p>
<?php endif; ?>

<form method="POST" action="index.php?module=login&action=save_reset_password">
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
    <input type="hidden" name="user_id" value="<?= e($user_id ?? '') ?>">

    <label for="new_password">Yeni Parola:</label><br>
    <input type="password" id="new_password" name="new_password" required>
    <p style="font-size:0.8em; color:#666;">Parola en az 8 karakter olmalı, en az 1 büyük harf, 1 küçük harf ve 1 rakam içermelidir. Sıralı veya tekrar eden karakterler içermemelidir.</p>
    <br><br>

    <label for="confirm_password">Yeni Parola (Tekrar):</label><br>
    <input type="password" id="confirm_password" name="confirm_password" required><br><br>

    <button type="submit">Parolayı Sıfırla</button>
</form>
<p style="margin-top:20px;"><a href="index.php?module=login&action=index">Giriş sayfasına geri dön</a></p>