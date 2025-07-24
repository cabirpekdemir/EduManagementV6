<?php
// Eğer $user değişkeni controller'dan gelmiyorsa veya null ise boş bir dizi olarak başlatalım
$user = $user ?? []; 
?>
<h2>Kullanıcı Düzenle: <?= htmlspecialchars($user['name'] ?? 'Bilinmiyor') ?></h2>
<?php if (isset($_GET['error_message'])): ?>
    <p style="color:red; border: 1px solid red; padding: 10px; margin-top: 15px;">
        <?= htmlspecialchars_decode($_GET['error_message']) ?>
    </p>
<?php endif; ?>

<form method="post" action="index.php?module=users&action=update&id=<?= htmlspecialchars($user['id'] ?? '') ?>" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id'] ?? '') ?>">

    <label for="name">Ad Soyad:</label><br>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required><br><br>

    <label for="email">E-posta:</label><br>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required><br><br>

    <label for="password">Yeni Parola (Değiştirmeyecekseniz boş bırakın):</label><br>
    <input type="password" id="password" name="password" value="">
    <p style="font-size:0.8em; color:#666;">Parola en az 8 karakter olmalı, en az 1 büyük harf, 1 küçük harf ve 1 rakam içermelidir. Sıralı veya tekrar eden karakterler içermemelidir.</p>
    <br><br>

    <label for="tc_kimlik">TC Kimlik:</label><br>
    <input type="text" id="tc_kimlik" name="tc_kimlik" value="<?= e($user['tc_kimlik'] ?? '') ?>" maxlength="11" required><br><br>

    <label for="role">Rol:</label><br>
    <select id="role" name="role" required>
        <option value="student" <?= (($user['role'] ?? '') == 'student') ? 'selected' : '' ?>>Öğrenci</option>
        <option value="teacher" <?= (($user['role'] ?? '') == 'teacher') ? 'selected' : '' ?>>Öğretmen</option>
        <option value="admin" <?= (($user['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
        <option value="parent" <?= (($user['role'] ?? '') == 'parent') ? 'selected' : '' ?>>Veli</option>
    </select><br><br>

    <div id="class_select_div" style="display: <?= (isset($user['role']) && $user['role'] == 'student') ? 'block' : 'none' ?>;">
        <label for="class_id">Sınıf (Öğrenci ise):</label><br>
        <select id="class_id" name="class_id">
            <option value="">Seçiniz</option>
            <?php 
            if (isset($all_classes) && is_array($all_classes)):
                $selected_class = $user['class_id'] ?? '';
                foreach ($all_classes as $class): ?>
                    <option value="<?= e($class['id']) ?>" <?= ((string)$selected_class === (string)$class['id']) ? 'selected' : '' ?>>
                        <?= e($class['name']) ?>
                    </option>
                <?php endforeach; 
            endif; ?>
        </select><br><br>
    </div>

    <div id="parent_select_div" style="display: <?= (isset($user['role']) && $user['role'] == 'parent') ? 'block' : 'none' ?>;">
        <label for="parent_of_student_id">Velisi Olduğu Öğrenci (Veli ise):</label><br>
        <select id="parent_of_student_id" name="parent_of_student_id">
            <option value="">Seçiniz</option>
            <?php 
            // $all_students değişkeninin controller'dan gelmesi gerekiyor
            if (isset($all_students) && is_array($all_students)):
                $selected_student_for_parent = $user['parent_id'] ?? ''; // users tablosunda parent_id sütunu var demiştik, bu sütun velisi olduğu öğrencinin ID'si olacak
                foreach ($all_students as $student): ?>
                    <option value="<?= e($student['id']) ?>" <?= ((string)$selected_student_for_parent === (string)$student['id']) ? 'selected' : '' ?>>
                        <?= e($student['name']) ?>
                    </option>
                <?php endforeach; 
            endif; ?>
        </select><br><br>
    </div>

    <label for="profile_photo">Profil Fotoğrafı:</label><br>
    <input type="file" id="profile_photo" name="profile_photo"><br>
    <?php if (!empty($user['profile_photo'])): ?>
        <p style="margin-top: 5px;">Mevcut Fotoğraf: <a href="<?= e($user['profile_photo']) ?>" target="_blank">Görüntüle</a></p>
        <img src="<?= e($user['profile_photo']) ?>" alt="Profil Fotoğrafı" style="max-width: 150px; height: auto; display: block; margin-top: 5px;"><br>
    <?php endif; ?>
    <button type="submit">Kullanıcıyı Güncelle</button>
    <a href="index.php?module=users&action=index">Vazgeç</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const classSelectDiv = document.getElementById('class_select_div');
    const parentSelectDiv = document.getElementById('parent_select_div');

    function toggleFields() {
        if (roleSelect.value === 'student') {
            classSelectDiv.style.display = 'block';
            parentSelectDiv.style.display = 'none';
        } else if (roleSelect.value === 'parent') {
            classSelectDiv.style.display = 'none';
            parentSelectDiv.style.display = 'block';
        } else {
            classSelectDiv.style.display = 'none';
            parentSelectDiv.style.display = 'none';
        }
    }

    roleSelect.addEventListener('change', toggleFields);
    toggleFields(); // Sayfa yüklendiğinde başlangıç durumunu ayarla
});
</script>