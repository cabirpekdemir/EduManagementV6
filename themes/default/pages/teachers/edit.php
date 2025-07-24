<h2>Öğretmen Düzenle</h2>
<form action="?module=teachers&action=update&id=<?= $teacher['id'] ?>" method="post" enctype="multipart/form-data">
    <label>Ad Soyad: <input type="text" name="name" value="<?= htmlspecialchars($teacher['name']) ?>" required></label><br>
    <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" required></label><br>
    <label>Profil Fotoğrafı:
        <?php if (!empty($teacher['profile_photo'])): ?>
            <img src="<?= $teacher['profile_photo'] ?>" alt="Profil" width="48" height="48"><br>
            <input type="hidden" name="old_photo" value="<?= $teacher['profile_photo'] ?>">
        <?php endif; ?>
        <input type="file" name="profile_photo" accept="image/*">
    </label><br>
    <button type="submit">Güncelle</button>
</form>
<a href="?module=teachers&action=index">Listeye Dön</a>
