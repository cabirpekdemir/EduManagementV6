<?php
$isEdit = isset($teacher);
$actionUrl = $isEdit
    ? '?module=teachers&action=update&id=' . $teacher['id']
    : '?module=teachers&action=store';
?>
<h2><?= $isEdit ? 'Öğretmeni Düzenle' : 'Yeni Öğretmen Ekle' ?></h2>
<form method="POST" action="<?= $actionUrl ?>" enctype="multipart/form-data">
    <label>Ad Soyad:</label>
    <input type="text" name="name" required value="<?= $isEdit ? htmlspecialchars($teacher['name']) : '' ?>"><br>
    <label>Email:</label>
    <input type="email" name="email" required value="<?= $isEdit ? htmlspecialchars($teacher['email']) : '' ?>"><br>
    <?php if (!$isEdit): ?>
        <label>Şifre:</label>
        <input type="password" name="password" required><br>
    <?php endif; ?>
    <label>Okul:</label>
    <input type="text" name="okul" value="<?= $isEdit ? htmlspecialchars($teacher['okul']) : '' ?>"><br>
    <label>Sınıf:</label>
    <input type="text" name="sinif" value="<?= $isEdit ? htmlspecialchars($teacher['sinif']) : '' ?>"><br>
    <label>T.C. Kimlik No:</label>
    <input type="text" name="tc_kimlik" maxlength="11" required value="<?= $isEdit ? htmlspecialchars($teacher['tc_kimlik']) : '' ?>"><br>
    <label>Profil Fotoğrafı:</label>
    <input type="file" name="profile_photo" accept="image/*"><br>
    <?php if ($isEdit && !empty($teacher['profile_photo'])): ?>
        <img src="<?= htmlspecialchars($teacher['profile_photo']) ?>" width="80" height="80"><br>
        <input type="hidden" name="current_photo" value="<?= htmlspecialchars($teacher['profile_photo']) ?>">
    <?php endif; ?>
    <button type="submit"><?= $isEdit ? 'Güncelle' : 'Kaydet' ?></button>
</form>
