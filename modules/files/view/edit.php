<h2>Dosya Düzenle</h2>
<form method="post">
    <label>Ad:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($file['name']) ?>" required><br>
    <label>Dosya Yolu:</label><br>
    <input type="text" name="path" value="<?= htmlspecialchars($file['path']) ?>" required><br><br>
    <button type="submit">Güncelle</button>
</form>