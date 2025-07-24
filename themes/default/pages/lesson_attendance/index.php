<h2>Ders Yoklaması</h2>
<p class="lead">Yoklama almak veya mevcut bir yoklamayı düzenlemek için lütfen aşağıdaki adımları takip edin.</p>

<!-- Bildirim Mesajları -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <strong>Hata:</strong> <?= htmlspecialchars($_GET['error'] === 'missing_params' ? 'Lütfen tüm alanları doldurun.' : $_GET['error']) ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
    <div class="alert alert-success">Yoklama başarıyla kaydedildi.</div>
<?php endif; ?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Yoklama Seçim Formu</h3>
    </div>
    <form method="GET" action="index.php">
        <div class="card-body">
            <input type="hidden" name="module" value="lesson_attendance">
            <input type="hidden" name="action" value="take">

            <div class="form-group">
                <label for="course_id_select"><b>1. Adım:</b> Ders Seçin</label>
                <select name="course_id" id="course_id_select" required class="form-control">
                    <option value="">-- Ders Seçiniz --</option>
                    <?php if(!empty($courses)): foreach($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="lesson_slot_id_select"><b>2. Adım:</b> Ders Saati Seçin</label>
                <select name="lesson_slot_id" id="lesson_slot_id_select" required class="form-control" disabled>
                    <option value="">-- Önce Ders Seçiniz --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="date_select"><b>3. Adım:</b> Yoklama Tarihi Seçin</label>
                <input type="date" name="date" id="date_select" value="<?= date('Y-m-d') ?>" required class="form-control">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-list-ul"></i> Yoklama Listesini Getir
            </button>
        </div>
    </form>
</div>

<hr>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Raporlar</h3>
    </div>
    <div class="card-body">
        <p>Tüm dersler ve öğrenciler için detaylı yoklama geçmişini görüntüleyin.</p>
        <a href="index.php?module=lesson_attendance&action=report" class="btn btn-info">
            <i class="fa fa-bar-chart"></i> Detaylı Yoklama Raporları
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_id_select');
    const lessonSlotSelect = document.getElementById('lesson_slot_id_select');

    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        lessonSlotSelect.innerHTML = '<option value=\"\">Yükleniyor...</option>';
        lessonSlotSelect.disabled = true;

        if (courseId) {
            // URL'yi projenizin yapısına göre ayarlayın
            fetch('index.php?module=lesson_attendance&action=get_lesson_slots&course_id=' + courseId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    lessonSlotSelect.innerHTML = '<option value=\"\">-- Ders Saati Seçiniz --</option>';
                    if (data.success && data.slots && data.slots.length > 0) {
                        data.slots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = slot.id;
                            // Örnek Çıktı: Pazartesi (09:00 - 10:30)
                            option.textContent = `${slot.day} (${slot.start_f} - ${slot.end_f})`;
                            lessonSlotSelect.appendChild(option);
                        });
                        lessonSlotSelect.disabled = false;
                    } else {
                        lessonSlotSelect.innerHTML = '<option value=\"\">Bu derse ait zaman dilimi bulunamadı.</option>';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    lessonSlotSelect.innerHTML = '<option value=\"\">Veri alınırken hata oluştu.</option>';
                });
        } else {
            lessonSlotSelect.innerHTML = '<option value=\"\">-- Önce Ders Seçiniz --</option>';
        }
    });
});
</script>
