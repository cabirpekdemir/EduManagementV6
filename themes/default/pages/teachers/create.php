<h2>Yeni Öğretmen Ekle</h2>
<form action="?module=teachers&action=store" method="post" enctype="multipart/form-data">
    <label>Ad Soyad: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>TC Kimlik No: <input type="tc_kimlik" name="tc_kimlik" required></label><br>
    <label>Şifre: <input type="password" name="password" required></label><br>
    <label>Profil Fotoğrafı: <input type="file" name="profile_photo" accept="image/*"></label><br>
    <button type="submit">Kaydet</button>
</form>
<a href="?module=teachers&action=index">Listeye Dön</a>
