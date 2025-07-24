<h2>Öğrenciye Doğrudan Ders/Grup Ata (Admin)</h2>

<?php if (isset($success_message)): ?>
    <p style="color: green; border: 1px solid green; padding: 10px;"><?= e($success_message) ?></p>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <p style="color: red; border: 1px solid red; padding: 10px;"><?= e($error_message) ?></p>
<?php endif; ?>

<form method="post" action="index.php?module=course_requests&action=assign_item_store">
    <div>
        <label for="student_id">Öğrenci Seç:</label><br>
        <select name="student_id" id="student_id" required style="padding: 5px; min-width: 200px;">
            <option value="">-- Öğrenci Seçin --</option>
            <?php if(!empty($students)): foreach($students as $student): ?>
                <option value="<?= e($student['id']) ?>"><?= e($student['name']) ?></option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <br>
    <div>
        <label>Atama Türü:</label><br>
        <input type="radio" name="item_type" value="course" id="type_course" checked onchange="toggleItemSelect()"> <label for="type_course">Bireysel Ders</label>
        <input type="radio" name="item_type" value="group" id="type_group" onchange="toggleItemSelect()"> <label for="type_group">Ders Grubu</label>
    </div>
    <br>
    <div id="course_select_div">
        <label for="course_id">Ders Seç:</label><br>
        <select name="course_id" id="course_id" style="padding: 5px; min-width: 200px;">
            <option value="">-- Ders Seçin --</option>
            <?php if(!empty($courses)): foreach($courses as $course): ?>
                <option value="<?= e($course['id']) ?>"><?= e($course['name']) ?></option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <div id="group_select_div" style="display:none;">
        <label for="course_group_id">Ders Grubu Seç:</label><br>
        <select name="course_group_id" id="course_group_id" style="padding: 5px; min-width: 200px;">
            <option value="">-- Grup Seçin --</option>
            <?php if(!empty($course_groups)): foreach($course_groups as $group): ?>
                <option value="<?= e($group['id']) ?>"><?= e($group['name']) ?></option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <br>
    <button type="submit" style="padding: 10px 15px;">Atamayı Yap</button>
</form>

<script>
function toggleItemSelect() {
    var itemType = document.querySelector('input[name="item_type"]:checked').value;
    var courseSelectDiv = document.getElementById('course_select_div');
    var groupSelectDiv = document.getElementById('group_select_div');
    var courseSelect = document.getElementById('course_id');
    var groupSelect = document.getElementById('course_group_id');

    if (itemType === 'course') {
        courseSelectDiv.style.display = 'block';
        groupSelectDiv.style.display = 'none';
        courseSelect.required = true;
        groupSelect.required = false;
        groupSelect.value = ''; // Diğerini temizle
    } else {
        courseSelectDiv.style.display = 'none';
        groupSelectDiv.style.display = 'block';
        courseSelect.required = false;
        groupSelect.required = true;
        courseSelect.value = ''; // Diğerini temizle
    }
}
// Sayfa yüklendiğinde de çağır
document.addEventListener('DOMContentLoaded', toggleItemSelect);
</script>