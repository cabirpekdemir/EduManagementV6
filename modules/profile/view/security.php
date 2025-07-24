<h2>Güvenlik Ayarları</h2>
<p>Parolanızı değiştirmek için aşağıdaki formu kullanın.</p>

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

<form method="POST" action="index.php?module=profile&action=update_password">
    <label for="current_password">Mevcut Parola:</label><br>
    <input type="password" id="current_password" name="current_password" required><br><br>

    <label for="new_password">Yeni Parola:</label><br>
    <input type="password" id="new_password" name="new_password" required>
    <p style="font-size:0.8em; color:#666;">Parola en az 8 karakter olmalı, en az 1 büyük harf, 1 küçük harf ve 1 rakam içermelidir. Sıralı veya tekrar eden karakterler içermemelidir.</p>
    <br><br>

    <label for="confirm_password">Yeni Parola (Tekrar):</label><br>
    <input type="password" id="confirm_password" name="confirm_password" required><br><br>

    <button type="submit">Parolayı Değiştir</button>
</form>

<p style="margin-top:20px;">
    <a href="index.php?module=profile&action=index">Profil Bilgilerine Geri Dön</a>
</p>