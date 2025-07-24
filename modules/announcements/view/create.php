<h2>Yeni Duyuru Oluştur</h2>
<form method="POST" action="?module=announcements&action=store">
  <label>Başlık:</label><br>
  <input type="text" name="title" required><br><br>
  <label>İçerik:</label><br>
  <textarea name="content" rows="6" cols="50" required></textarea><br><br>
  <label>Hedef Kitle:</label><br>
  <select name="audience" required> <option value="all">Herkes</option>
    <option value="student">Öğrenciler</option>
    <option value="teacher">Öğretmenler</option>
    <option value="parent">Veliler</option>
  </select><br><br>
  <button type="submit">Duyuru Oluştur</button>
</form>